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
include '../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
$container = SymfonyCore::getContainer();
$navigationService = $container->get('navigation.service');
$translator = $container->get('translator');
setting_get('sitemgr_language', $sitemgr_language);
$sitemgrLanguage = substr($sitemgr_language, 0, 2);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Reset tha navigation to the default
    if ($_GET['reset']) {
        $area = $_GET['area'];
        $auxArrayPages = $navigationService->getNavigationPages(ucfirst($area));
        $array_main_pages = $auxArrayPages['mainPages'];
        $navigationService->removesDisabledModules(ucfirst($area), $array_main_pages);
        $navbarHtml = '';

        for ($i = 0, $iMax = count($array_main_pages); $i < $iMax; $i++) {
            $arrayOptions[$i]['label'] = $array_main_pages[$i]['label'];
            $arrayOptions[$i]['custom'] = 0;
            $arrayOptions[$i]['pageId'] = $array_main_pages[$i]['page_id'];
            include INCLUDES_DIR.'/forms/form-navigation-structure.php';
        }

        $navbarHtml .= <<<HTML
        <li class="ui-sortable-handle ui-sortable-add" id="addNavBarItem">
            <a class="sortable-add createItem" data-modalaux="{$_GET['area']}" href="javascript:void(0)">
                <i class="fa fa-plus-circle" aria-hidden="true"></i>
            </a>
        </li>
HTML;


    } else {
        // Create a new item on the navigation
        $i = 1;
        if ($_GET['area']) {
            $area = $_GET['area'];
        }
        $arrayOptions[1]['label'] = $translator->trans('New Item', [], 'widgets', /** @Ignore */
            $sitemgrLanguage);
        $arrayOptions[1]['custom'] = 0;
        $arrayOptions[1]['page_id'] = 1;
        include INCLUDES_DIR.'/forms/form-navigation-structure.php';
    }

    echo $navbarHtml;
}
