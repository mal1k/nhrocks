<?

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
# * FILE: /includes/code/widgetActionAjax.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
$loadSitemgrLangs = true;
include '../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
$container = SymfonyCore::getContainer();
$widgetService = $container->get('widget.service');
$translator = $container->get('translator');
setting_get('sitemgr_language', $sitemgr_language);
$sitemgrLanguage = substr($sitemgr_language, 0, 2);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    /* ModStores Hooks */
    HookFire('widgetactionajax_before_load', [
        'http_get_array' => &$_GET
    ]);

    // Get Original Widget Content to translate (Widget Table)
    /* @var $originalWidget \ArcaSolutions\WysiwygBundle\Entity\Widget */
    $originalWidget = $widgetService->getOriginalWidget($_GET['widgetId']);
    $widgetType = $originalWidget->getType();

    // Get widget information if is already saved on database (Page_Widget Table)
    if ($_GET['pageWidgetId']) {
        $pageWidget = $container->get('pagewidget.service')->getWidgetFromPage($_GET['pageWidgetId']);
        $returnArray['pageWidgetId'] = $pageWidget->getId();
        $returnArray['pageWidgetClass'] = system_generateFriendlyURL($pageWidget->getWidget()->getTitle());
    } else {
        // Use the default information to start editing a new widget
        $pageWidget = $originalWidget;
        if (in_array($widgetType, [\ArcaSolutions\WysiwygBundle\Entity\Widget::HEADER_TYPE, \ArcaSolutions\WysiwygBundle\Entity\Widget::FOOTER_TYPE])){
            $mostUsedWidget = $widgetService->getWidgetInfo($widgetType);

            $mostUsedContent = $mostUsedWidget['content'];

            $originalContent = json_decode($originalWidget->getContent(), true);
            foreach ($mostUsedContent as $key => $value){
                if (!empty($originalContent[$key])){
                    $originalContent[$key] = $value;
                }
            }
            $originalWidget->setContent(json_encode($originalContent));
        }
    }

    $labelsArray = json_decode($originalWidget->getContent(), true);
    // LABELS EXCEPTIONS THAT NEED A DIFFERENT TRANSLATION

    foreach ($labelsArray as $key => $label) {
        // Translations for label fields that initially is null OR is a number field
        if ($key === 'labelCopyrightText') {
            $label = $translator->trans('Copyright', [], 'widgets', /** @Ignore */
                $sitemgrLanguage);
        }
        if ($key === 'limit') {
            $label = $translator->trans('Limit', [], 'widgets', /** @Ignore */
                $sitemgrLanguage);
        }

        ///

        $transLabelsArray[$key] = /** @Ignore */
            $translator->trans($label, [], 'widgets', $sitemgrLanguage);
    }

    // Load Navigation
    if ($_GET['modal'] === 'header') {
        $navbar = $container->get('navigation.service')->reloadNavbar();
        $returnArray['navbar'] = $navbar;
    }

    if ($_GET['modal'] === 'footer') {
        $navbar = $container->get('navigation.service')->reloadNavbar('footer');
        $returnArray['navbarFooter'] = $navbar;
    }

    // Load slider structure
    if ($_GET['modal'] === 'leadgenform' || ($_GET['modal'] === 'slider')) {
        $slider = $container->get('slider.service')->reloadContentSlider($pageWidget);
        $returnArray['slider'] = $slider['sliderHtml'];
        $returnArray['sliderInfo'] = $slider['sliderInfoHtml'];
    }

    // Create return array
    $returnArray['widgetTitle'] = $translator->trans(/** @Ignore */ $originalWidget->getTitle(), [], 'widgets', $sitemgrLanguage);
    $returnArray['widgetType'] = $originalWidget->getType();
    $returnArray['widgetTitleImg'] = $originalWidget->getTitle();
    $returnArray['content'] = $pageWidget->getContent();
    $returnArray['trans'] = json_encode(!empty($transLabelsArray) ? $transLabelsArray : []);

    /* checks if a widget is exclusive of one Page */
    /* @var $widgetPageType \ArcaSolutions\WysiwygBundle\Entity\WidgetPageType */
    $widgetPageTypes = $originalWidget->getPageTypes()->toArray();
    $widgetPageType = $widgetPageTypes[0];
    $returnArray['exclusiveWidget'] = $widgetPageType->getPageTypeId() || count($widgetPageTypes) > 1 ;

    /* ModStores Hooks */
    HookFire('widgetactionajax_after_load', [
        'returnArray' => &$returnArray
    ]);

    if (!empty($_GET['action']) && $_GET['action'] === 'edit') {
        extract($returnArray, null);
        include INCLUDES_DIR.'/modals/widget/'.$_GET['modalFullName'].'.php';
    } else {
        echo json_encode($returnArray);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ModStores Hooks */
    HookFire('widgetactionajax_before_save',[
        'http_get_array'  => &$_GET,
        'http_post_array' => &$_POST
    ]);

    // Save widget information
    $return = [];
    // Prepare content to be saved on the Page_Widget table
    if (!empty($_POST['contentArr'])) {
        $contentArr = json_decode($_POST['contentArr'], true);
        $pageWidgetId = null;
        $page = null;
        $widget = null;
        $theme = null;
        $widgetContent = [];
        $saveWidgetForAllPages = null;

        /* @var $originalWidget \ArcaSolutions\WysiwygBundle\Entity\Widget */
        $originalWidget = $widgetService->getOriginalWidget($_POST['widgetId']);

        if($originalWidget->getType() === \ArcaSolutions\WysiwygBundle\Entity\Widget::CARDS_TYPE) {
            $widgetContent = $contentArr;
            $pageWidgetId = $_POST['pageWidgetId'];
        } else {
            foreach ($contentArr as $content) {
                switch ($content['name']) {
                    case 'pageWidgetId':
                        $pageWidgetId = $content['value'];
                        break;
                    case 'saveWidgetForAllPages':
                        $saveWidgetForAllPages = $content['value'];
                        break;
                    case 'customHtml':
                        $widgetContent[$content['name']] = $_POST['customHtml'] ?: '';
                        break;
                    case 'labelCopyrightText':
                        if (!setting_set('footer_copyright', $content['value'])) {
                            setting_new('footer_copyright', $content['value']);
                        }
                        $widgetContent[$content['name']] = $content['value'];
                        break;
                    //Temporary code, the ideal will be to add value and label indexes for all contents
                    case 'placeholderSearchKeyword':
                    case 'placeholderSearchLocation':
                    case 'placeholderSearchDate':
                    case 'placeholderTitle':
                    case 'placeholderCallToAction':
                        $widgetContent[$content['name']]['value'] = $content['value'];
                        $widgetContent[$content['name']]['label'] = $content['label'];
                        break;
                    case 'placeholderDescription':
                        $widgetContent[$content['name']]['type'] = $content['type'];
                        $widgetContent[$content['name']]['value'] = $content['value'];
                        $widgetContent[$content['name']]['label'] = $content['label'];
                        break;
                    case 'placeholderLink':
                        $widgetContent[$content['name']]['type'] = $content['type'];
                        $widgetContent[$content['name']]['label'] = $content['label'];
                        $widgetContent[$content['name']]['value'] = $content['value'];
                        $widgetContent[$content['name']]['target'] = $content['target'];
                        $widgetContent[$content['name']]['customLink'] = $content['customLink'];
                        $widgetContent[$content['name']]['openWindow'] = $content['openWindow'];
                        break;
                    case 'banners':
                        $widgetContent[$content['name']] = json_decode($content['value']);
                        break;
                    default:
                        $widgetContent[$content['name']] = $content['value'];
                        break;
                }
            }
        }

        if (!empty($_POST['sliderJson']) && $_POST['slidetype'] === 'content') {
            $sliderArr = json_decode($_POST['sliderJson'], true);
            $sliderContent = [];

            foreach ($sliderArr as $key => $slider) {
                foreach ($slider as $sliderField) {
                    $sliderContent[$key][$sliderField['name']] = $sliderField['value'];
                }
            }

            $widgetContent['contentSlider'] = $sliderContent;
        }

        if (!empty($_POST['sliderJson']) && $_POST['slidetype'] === 'video') {
            $videosArr = json_decode($_POST['sliderJson'], true);
            $videosContent = [];

            foreach ($videosArr as $key => $video) {
                /* Getting each field */
                $videoItem = array_column($video, 'value', 'name');
                $videoSnippet = system_getVideoiFrame($videoItem['video_url']);
                if ($videoItem['video_url'] && $videoSnippet && $videoSnippet !== 'error') {
                    $videosContent[] = [
                        'url'         => $videoItem['video_url'],
                        'description' => $videoItem['video_description'],
                        'iframe'      => $videoSnippet,
                        'imageUrl'    => system_getVideoiFrame($videoItem['video_url'], 380, null, true)
                    ];
                }
            }

            $widgetContent['videos'] = $videosContent;
        }

        $return = [
            'success'      => false,
            'errorMessage' => [
                $translator->trans('Something wrong', [], 'widgets', /** @Ignore */
                    $sitemgrLanguage),
            ],
        ];

        if ($pageWidgetId) {
            $returnWidget = $container->get('pagewidget.service')->saveWidgetContent($pageWidgetId, json_encode($widgetContent));
        } else {
            $returnWidget = $container->get('pagewidget.service')->saveWidget(
                json_encode($widgetContent), $_POST['pageId'], $_POST['widgetId']
            );
            $isNew = true;

            //rename lead forms widget content
            if ($_POST['tempWidgetId']) {

                //rename image
                $imageFile = EDIRECTORY_ROOT.BKIMAGE_PATH.'/'.BKIMAGE_NAME.'_lead_';
                if (file_exists($imageFile.$_POST['tempWidgetId'].'.'.BKIMAGE_EXT)) {
                    rename($imageFile.$_POST['tempWidgetId'].'.'.BKIMAGE_EXT, $imageFile.$returnWidget->getId().'.'.BKIMAGE_EXT);
                }

                //rename json file
                $leadFile = EDIRECTORY_ROOT.'/custom/domain_'.$_POST['domain_id'].'/editor/lead/save_';
                if (file_exists($leadFile.$_POST['tempWidgetId'].'.json')) {
                    rename($leadFile.$_POST['tempWidgetId'].'.json', $leadFile.$returnWidget->getId().'.json');
                }

            }
        }

        if ($returnWidget) {
            $return = [
                'success'     => true,
                'isNewWidget' => $isNew ?: false,
                'newWidgetId' => $returnWidget->getId(),
                'message'     => $translator->trans('Widget successfully saved.', [], 'widgets', /** @Ignore */
                    $sitemgrLanguage),
            ];
        }

        //Save the content of this widget to all pages that contains this widget
        if ($saveWidgetForAllPages) {
            $container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->updateWidgetContentForAllPages(
                $_POST['widgetId'],
                $container->get('theme.service')->getSelectedTheme()->getId(),
                json_encode($widgetContent)
            );
        }

        //Save Newsletter
        if (!empty($_POST['modal']) && $_POST['modal'] === 'newsletter') {
            // Label
            if (!setting_set('arcamailer_list_label', $widgetContent['labelSignupFor'])) {
                if (!setting_new('arcamailer_list_label', $widgetContent['labelSignupFor'])) {
                    // Nothing here
                }
            }

            // Description
            if (!setting_set('arcamailer_list_label_sub', $widgetContent['labelNewsletterDesc'])) {
                if (!setting_new('arcamailer_list_label_sub', $widgetContent['labelNewsletterDesc'])) {
                    // Nothing here
                }
            }

            setting_get('arcamailer_customer_listid', $arcamailer);
            if ($arcamailer !== 'on') {
                if (!setting_set('arcamailer_customer_listid', 'on')) {
                    if (!setting_new('arcamailer_customer_listid', 'on')) {
                        // Nothing here
                    }
                }
            }
        }
    }

    //Save Navigation header
    if (!empty($_POST['navbarArr'])) {
        $area = strpos($_POST['modal'], 'header') !== false ? 'header' : 'footer';
        $container->get('navigation.service')->saveNavigation(json_decode($_POST['navbarArr'], true), $area);
    }

    // Save the social links on the database (Header / Footer Widgets)
    if (!empty($_POST['socialLinks'])) {
        $container->get('slider.service')->saveSocialLinks(json_decode($_POST['socialLinks'], true));
    }

    if (!empty($_POST['removeWidget'])) {
        $return = [
            'success'      => false,
            'errorMessage' => $translator->trans('Something went wrong!', [], 'widgets', /** @Ignore */
                $sitemgrLanguage),
        ];

        if (($_POST['pageWidgetId'] !== 'null' && $container->get('pagewidget.service')->deleteWidgetFromPage($_POST['pageWidgetId'])) || $_POST['pageWidgetId'] === 'null') {

            /*
             * Check if there's a lead form associated to this widget to delete the file
             */
            $leadFile = EDIRECTORY_ROOT.'/custom/domain_'.$_POST['domain_id'].'/editor/lead/save_'.$_POST['pageWidgetId'].'.json';;
            if (file_exists($leadFile)) {
                unlink($leadFile);
            }

            $imageFile = EDIRECTORY_ROOT.BKIMAGE_PATH.'/'.BKIMAGE_NAME.'_lead_'.$_POST['pageWidgetId'].'.'.BKIMAGE_EXT;
            if (file_exists($imageFile)) {
                unlink($imageFile);
            }

            $return = [
                'success' => true,
                'message' => $translator->trans('Widget successfully deleted.', [], 'widgets', /** @Ignore */
                    $sitemgrLanguage),
            ];
        }
    }

    /* ModStores Hooks */
    HookFire('widgetactionajax_after_save', [
        'http_get_array'  => &$_GET,
        'http_post_array' => &$_POST,
        'return'          => &$return,
    ]);

    if (!empty($return)) {
        echo json_encode($return);
    }
}
