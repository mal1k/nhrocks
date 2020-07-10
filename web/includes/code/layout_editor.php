<?php

/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
#                                                                    #
# This file may not be redistributed in whole or part.               #
# eDirectory is licensed on a per-domain basis.                      #
#                                                                    #
# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
#                                                                    #
# http://www.edirectory.com | http://www.edirectory.com/license.html #
######################################################################
\*==================================================================*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/code/layout_editor.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# SUBMIT
# ----------------------------------------------------------------------------------------------------
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Entity\Widget;

extract($_POST, null);
extract($_GET, null);

$filethemeConfigPath = EDIRECTORY_ROOT .'/custom/domain_'. SELECTED_DOMAIN_ID .'/theme/theme.inc.php';
unset($array);

// Default CSS class for message
$message_style = 'success';

$container = SymfonyCore::getContainer();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !DEMO_LIVE_MODE && $submitAction === 'changetheme') {

    if ($select_theme) {
        $status = 'success';

        mixpanel_track('Theme selected', [
                'Theme' => ucwords($select_theme)
            ]
        );

        if (!$filethemeConfig = fopen($filethemeConfigPath, 'w+')) {
            $status = 'error';
        } else {

            $buffer = '<?php'. PHP_EOL . "\$edir_theme=\"$select_theme\";" . PHP_EOL;

            if (!fwrite($filethemeConfig, $buffer, strlen($buffer))) {
                $status = 'error';
            }

        }

        // saves theme in yml file
        $domain = new Domain(SELECTED_DOMAIN_ID);
        $classSymfonyYml = new Symfony('domain.yml');
        $theme_domain = [
            'multi_domain' => [
                'hosts' => [
                    $domain->getString('url') => [
                        'template' => $_POST['select_theme'],
                    ]
                ]
            ]
        ];
        $classSymfonyYml->save('Configs', $theme_domain);

        $theme = $container->get('doctrine')->getRepository('WysiwygBundle:Theme')->findOneBy([
            'title' => ucfirst($_POST['select_theme']),
        ]);

        if (!$container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->findBy(['themeId' => $theme->getId()])) {
            $loader = new \Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader($container);
            $loader->loadFromDirectory(EDIRECTORY_ROOT .'/../src/ArcaSolutions/WysiwygBundle/DataFixtures/ORM/Common');
            $loader->loadFromDirectory(EDIRECTORY_ROOT .'/../src/ArcaSolutions/WysiwygBundle/DataFixtures/ORM/Theme'. ucfirst($_POST['select_theme']));

            $em = $container->get('doctrine.orm.domain_entity_manager');
            $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger();

            $executor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor($em, $purger);
            $executor->execute($loader->getFixtures(), true);
        }

        $pageObject = $container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::RESULTS_PAGE);

        $resultsWidget = $container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->getPageWidgetByWidgetName($pageObject->getId(),
            Widget::RESULTS, $theme->getId());

        $resultContent = json_decode($resultsWidget->getContent(), true);

        $settingsResultSize = $resultContent['resultView'] === 'list' ? 'defaultSearchResultSize' : 'defaultSearchResultGridSize';

        $container->get('settings')->setSetting('result_size', $settingsResultSize);

    } else {
        $status = 'error';
    }

    header('Location: '. DEFAULT_URL .'/'. SITEMGR_ALIAS . "/design/themes/index.php?status=$status");
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !DEMO_LIVE_MODE && $submitAction === 'changecolors') {
    if ($action === 'submit') {

        $colors = [];

        //Save colors
        foreach ($availableColors[EDIR_THEME] as $k => $availableColor) {
            if ($_POST[$k]) {
                system_createColorVariances($_POST[$k], $k, $colors);
            }
        }

        system_createBasicColorVariances($colors);

        if(!empty($image_border)) {
            $colors['image_border'] = $image_border;
            $colors['border-base'] = '--border-radius: ' . $image_border . 'px;';
        }

        if(!empty($input_border)) {
            $colors['input_border']       = $input_border;
            $colors['border-input-base']  = '--border-radius-input: '  . $input_border . 'px;';
            $colors['border-button-base'] = '--border-radius-button: ' . $input_border . 'px;';
            $colors['border-icon-base']   = '--border-radius-icon: '   . $input_border . 'px;';
        }

        if(!empty($font)) {
            $colors['font'] = $font;
            $colors['font-base'] = '--font-size-base: ' . $font . 'px;';
        }

        if(!empty($heading_font)) {
            $colors['heading_font'] = $heading_font;
        }

        if(!empty($paragraph_font)) {
            $colors['paragraph_font'] = $paragraph_font;
        }

        if (!empty($colors['highlight'])) {

            $path_base_icons = EDIRECTORY_ROOT . '/assets/icons/base/';
            $iconFiles = glob($path_base_icons.'*.svg');

            $custom_icons_path = THEMEFILE_DIR . '/' . EDIR_THEME . '/icons/';

            if (!is_dir($custom_icons_path) && !mkdir($custom_icons_path, 0777, true)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created',
                    $custom_icons_path));
            }

            foreach ($iconFiles as $file) {
                try {
                    $icon = file_get_contents($file);

                    $svgTemplate = new \SimpleXMLElement($icon);
                    $svgTemplate->registerXPathNamespace('svg', 'http://www.w3.org/2000/svg');

                    if (!empty($svgTemplate->defs->style)) {
                        $old_style = $svgTemplate->defs->style;
                        $svgTemplate->defs->style = str_replace('%color%', $colors['highlight'], $old_style);
                    }

                    $fileName = str_replace($path_base_icons, '', $file);

                    $svgTemplate->asXml($custom_icons_path . $fileName);
                } catch (\Exception $e) {
                }
            }
        }

        $container->get('settings')->setSetting('colorscheme_'. EDIR_THEME, json_encode($colors));

        mixpanel_track('Colors and fonts changed');

        header('Location: '. DEFAULT_URL .'/'. SITEMGR_ALIAS .'/design/colors-fonts/index.php?status=successcolors');
        exit;

    } elseif ($action === 'reset') {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        $sql = 'DELETE FROM Setting WHERE `name` = '.db_formatString('colorscheme_'. EDIR_THEME);
        $dbObj->query($sql);

        $custom_icons_path = THEMEFILE_DIR . '/' . EDIR_THEME . '/icons/';

        if (is_dir($custom_icons_path)) {
            $files = glob($custom_icons_path.'/*.svg');
            foreach ($files as $file) { // iterate files
                if (is_file($file)) {
                    unlink($file);
                } // delete file
            }
        }

        mixpanel_track('Colors and fonts restored');

        header('Location: '. DEFAULT_URL .'/'. SITEMGR_ALIAS .'/design/colors-fonts/index.php?status=successcolors');
        exit;
    }
}

//Messages
if ($status === 'success') {
    $message = system_showText(LANG_SITEMGR_SETTINGS_THEMES_THEMEWASCHANGED);
    $message_style = 'success';
} elseif ($status === 'failed') {
    $message = system_showText(LANG_SITEMGR_MSGERROR_SYSTEMERROR);
    $message_style = 'warning';
} elseif ($status === 'successcolors') {
    $message = system_showText(LANG_SITEMGR_COLOR_SAVED);
    $message_style = 'success';
}
