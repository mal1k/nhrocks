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
# * FILE: /frontend/header.php
# ----------------------------------------------------------------------------------------------------

header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
#header('Cache-Control: no-store, no-cache, must-revalidate');
#header('Cache-Control: post-check=0, pre-check=0', FALSE);
#header('Pragma: no-cache');
header('Content-Type: text/html; charset='.EDIR_CHARSET, TRUE);

//Contact Us info
setting_get('contact_address', $contact_address);
setting_get('contact_zipcode', $contact_zipcode);
setting_get('contact_country', $contact_country);
setting_get('contact_state', $contact_state);
setting_get('contact_city', $contact_city);
setting_get('contact_phone', $contact_phone);

//This function returns the variables to fill in the meta tags content below. Do not change this line.
front_getHeaderTag($headertag_title, $headertag_author);

if (sess_getAccountIdFromSession()) {
    $dbObjWelcome = db_getDBObJect(DEFAULT_DB, true);
    $sqlWelcome = 'SELECT C.first_name, C.last_name, A.has_profile, A.is_sponsor, P.friendly_url, A.username, P.image_id, P.facebook_image, P.nickname FROM Contact C
                                               LEFT JOIN Account A ON (C.account_id = A.id)
                                               LEFT JOIN Profile P ON (P.account_id = A.id)
                                               WHERE A.id = '.sess_getAccountIdFromSession();
    $resultWelcome = $dbObjWelcome->query($sqlWelcome);
    $contactWelcome = mysqli_fetch_assoc($resultWelcome);
}
?>

<!DOCTYPE html>
<html lang="<?=system_getHeaderLang();?>">
<head>
    <!-- Google Tag Manager code -->
    <?=front_googleTagManager();?>

    <meta charset="<?=EDIR_CHARSET;?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>

    <title><?=$headertag_title?></title>
    <meta name="author" content="<?=$headertag_author?>" />

    <?php $metatagHead = true; include INCLUDES_DIR.'/code/smartbanner.php'; ?>

    <!-- This function returns the favicon tag. Do not change this line. -->
    <?=system_getFavicon();?>

    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?=DEFAULT_URL;?>/assets/<?=EDIR_THEME?>/styles/style.min.css" rel="stylesheet"/>

    <?=front_colorScheme();?>

    <!-- CUSTOM CSS -->
    <?php if (file_exists(EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/theme/'.EDIR_THEME.'/csseditor.css')) { ?>
        <link href="<?=DEFAULT_URL;?>/custom/domain_<?=SELECTED_DOMAIN_ID;?>/theme/<?=EDIR_THEME;?>/csseditor.css" rel="stylesheet" type="text/css" media="all" />
    <?php } ?>
</head>

<body>
    <!-- Google Tag Manager code -->
    <?=front_googleTagManager('body');?>

    <?php if (DEMO_LIVE_MODE && file_exists(EDIRECTORY_ROOT.'/frontend/livebar.php')) {
        include EDIRECTORY_ROOT.'/frontend/livebar.php';
    }

    $container = SymfonyCore::getContainer();
    $widgetInfo = $container->get('widget.service')->getWidgetInfo(\ArcaSolutions\WysiwygBundle\Entity\Widget::HEADER_TYPE);
    $widgetFileName = $widgetInfo['twig'];
    $widgetContent = $widgetInfo['content'];
    $translator = $container->get('translator');
    $imagine_filter = $container->get('liip_imagine.cache.manager');

    include EDIRECTORY_ROOT."/frontend/widgets/$widgetFileName.php";
