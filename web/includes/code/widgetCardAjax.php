<?

/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2005 Arca Solutions, Inc. All Rights Reserved.           #
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
# * FILE: /includes/code/widgetCardAjax.php
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
    echo $container->get('wysiwyg.card_service')->getIndividualItemTemplate(null, null, $sitemgrLanguage);

    exit;
}
