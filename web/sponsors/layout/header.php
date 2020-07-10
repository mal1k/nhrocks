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
    # * FILE: /sponsors/layout/header.php
    # ----------------------------------------------------------------------------------------------------

    header("Content-Type: text/html; charset=".EDIR_CHARSET, TRUE);

    front_getHeaderTag($headertag_title, $headertag_author);

    $accountObj = new Account(sess_getAccountIdFromSession());

    $edirlanguageArr = explode("_", EDIR_LANGUAGE);

    $container = SymfonyCore::getContainer();
    $widgetInfo = $container->get('widget.service')->getWidgetInfo(\ArcaSolutions\WysiwygBundle\Entity\Widget::HEADER_TYPE);
    $widgetContent = $widgetInfo['content'];
?>

<!DOCTYPE html>
<html lang="<?=system_getHeaderLang();?>">
    <head>
        <!-- Google Tag Manager code -->
        <?=front_googleTagManager();?>

        <?=front_colorScheme();?>

        <?php
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

        <title><?=( (trim($contactWelcome["first_name"])) ? $contactWelcome["first_name"]." ".$contactWelcome["last_name"].", " : "" ) . system_showText(LANG_MSG_WELCOME) . " - " . $headertag_title?></title>

        <meta name="author" content="<?=$headertag_author?>">
        <meta charset=<?=EDIR_CHARSET;?>>
        <meta name="ROBOTS" content="noindex, nofollow">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,700|Rubik" rel="stylesheet">
        <link href="<?=DEFAULT_URL;?>/assets/<?=EDIR_THEME;?>/styles/style.min.css" rel="stylesheet" type="text/css" media="all">

        <!-- CUSTOM CSS -->
        <?php if (file_exists(EDIRECTORY_ROOT."/custom/domain_".SELECTED_DOMAIN_ID."/theme/".EDIR_THEME."/csseditor.css")) { ?>
            <link href="<?=DEFAULT_URL;?>/custom/domain_<?=SELECTED_DOMAIN_ID;?>/theme/<?=EDIR_THEME;?>/csseditor.css" rel="stylesheet" type="text/css" media="all">
        <? } ?>

        <?=system_getFavicon();?>
    </head>
    <body>
        <!-- Google Tag Manager code -->
        <?=front_googleTagManager('body');?>

        <? if (DEMO_LIVE_MODE && file_exists(EDIRECTORY_ROOT."/frontend/livebar.php")) {
            include(EDIRECTORY_ROOT."/frontend/livebar.php");
        } ?>

        <header class="header" data-type="1" is-inverse="<?=$widgetContent['backgroundColor'] === 'base' ? 'true' : 'false'?>">
            <div class="header-bar" data-align="right">
                <div class="container">
                    <div class="wrapper">
                        <?php if (sess_getAccountIdFromSession()) { ?>
                            <div class="bar-link user-button">
                                <?=system_showText(LANG_LABEL_WELCOME)?>
                                <?= ((trim($contactWelcome["first_name"])) ? $contactWelcome["first_name"].' '.$contactWelcome["last_name"] : "") ?>
                                <i class="fa fa-angle-down"></i>
                                <div class="user-content">
                                    <? if (!empty($_SESSION[SM_LOGGEDIN])) { ?>
                                        <a href="javascript:sitemgrSection();" class="user-link">
                                            <?=system_showText(LANG_LABEL_SITEMGR_SECTION);?>
                                        </a>
                                    <? } else { ?>
                                        <? if ($contactWelcome["has_profile"] == "y" && SOCIALNETWORK_FEATURE == "on") { ?>
                                            <a href="<?=SOCIALNETWORK_URL?>/" class="user-link">
                                                <?=system_showText(LANG_LABEL_PROFILE)?>
                                            </a>
                                        <? } ?>
                                        <a href="<?=DEFAULT_URL?>/" class="user-link">
                                            <?=system_showText(LANG_LABEL_BACK_TO_SEARCH);?>
                                        </a>
                                        <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/help.php" class="user-link">
                                            <?=system_showText(LANG_BUTTON_HELP)?>
                                        </a>
                                        <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/faq.php" class="user-link">
                                            <?=system_showText(LANG_MENU_FAQ);?>
                                        </a>
                                        <a href="<?=DEFAULT_URL."/".MEMBERS_ALIAS."/logout.php"?>" class="user-link">
                                            <?=system_showText(LANG_BUTTON_LOGOUT);?>
                                        </a>
                                    <? } ?>
                                </div>
                            </div>
                        <? } ?>
                    </div>
                </div>
            </div>
            <div class="header-content">
                <div class="container">
                    <div class="wrapper">
                        <div class="content-left">
                            <a href="<?=DEFAULT_URL?>/" target="_parent" <?=(trim(EDIRECTORY_TITLE) ? "title=\"".EDIRECTORY_TITLE."\"" : "")?> class="header-logo" style="background-image: url(<?=image_getLogoImage();?>)"></a>
                        </div>
                        <? if (sess_getAccountIdFromSession()){ ?>
                            <nav class="header-navbar">
                                <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/" class="navbar-link" is-active="<?=(string_strpos($_SERVER["PHP_SELF"], "/".MEMBERS_ALIAS."/index.php") !== false) ? 'true' : 'false'; ?>">
                                    <?=system_showText(LANG_MEMBERS_DASHBOARD)?>
                                </a>

                                <?php if(PAYMENTSYSTEM_FEATURE === 'on') { ?>
                                    <div class="navbar-link navbar-dropdown">
                                        <div class="more-label"><?=system_showText(LANG_LABEL_BILLING)?> <i class="fa fa-angle-down"></i> <span class="sponsor-notify-billing"></span></div>
                                        <div class="dropdown-wrapper">
                                            <div class="more-content">
                                                <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/billing/" class="more-link"><?=system_showText(LANG_MENU_CHECKOUT)?></a>
                                                <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/transactions/" class="more-link"><?=system_showText(LANG_MENU_TRANSACTIONHISTORY)?></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>

                                <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/account/" class="navbar-link" is-active="<?=(string_strpos($_SERVER["PHP_SELF"], "/".MEMBERS_ALIAS."/account/index.php") !== false) ? 'true' : 'false'; ?>">
                                    <?=system_showText(LANG_LABEL_ACCOUNT)?>
                                </a>

                                <? if (!empty($_SESSION[SM_LOGGEDIN])) { ?>
                                    <a href="javascript:sitemgrSection();" class="navbar-link">
                                        <?=system_showText(LANG_LABEL_SITEMGR_SECTION);?>
                                    </a>
                                <?php } ?>
                            </nav>
                            <div class="content-mobile">
                                <button class="toggler-button navbar-toggler"><i class="fa fa-bars"></i></button>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="navbar-mobile">
                <div class="navbar-user">
                    <div class="user-picture">
                        <?php if (!$contactWelcome['facebook_image']) {
                            $imgObj = new Image($contactWelcome['image_id'], true);
                            if ($imgObj->imageExists()) {
                                echo $imgObj->getTag(true, PROFILE_MEMBERS_IMAGE_WIDTH, PROFILE_MEMBERS_IMAGE_HEIGHT, '', false, htmlspecialchars($contactWelcome['nickname']), '');
                            } else { ?>
                                <i class="fa fa-user"></i>
                            <?php }
                        } else {

                            if (HTTPS_MODE == "on") {
                                $info['facebook_image'] = str_replace('http://', 'https://', $info['facebook_image']);
                            } ?>

                            <img src="<?=$info['facebook_image']?>" alt="<?=htmlspecialchars($info['nickname']);?>">

                        <?php } ?>
                    </div>
                    <div class="user-info">
                        <div class="heading user-name">
                            <?=$contactWelcome['nickname']?>
                        </div>

                        <div class="user-date"><?=$contactWelcome['username']?></div>
                    </div>
                </div>
                <?php if (sess_getAccountIdFromSession()){ ?>
                    <nav class="navbar-links">
                        <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/" class="navbar-link" is-active="<?=(string_strpos($_SERVER["PHP_SELF"], "/".MEMBERS_ALIAS."/index.php") !== false) ? 'true' : 'false'; ?>">
                            <?=system_showText(LANG_MEMBERS_DASHBOARD)?>
                        </a>
                    </nav>
                    <nav class="navbar-links">
                        <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/billing/" class="navbar-link" is-active="<?=(string_strpos($_SERVER["PHP_SELF"], "/".MEMBERS_ALIAS."/billing/") !== false) ? 'true' : 'false'; ?>">
                            <?=system_showText(LANG_MENU_CHECKOUT)?>
                            <span class="sponsor-notify-billing mobile-notify"></span>
                        </a>
                        <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/transactions/" class="navbar-link" is-active="<?=(string_strpos($_SERVER["PHP_SELF"], "/".MEMBERS_ALIAS."/transactions/") !== false) ? 'true' : 'false'; ?>"><?=system_showText(LANG_MENU_TRANSACTIONHISTORY)?></a>
                    </nav>
                    <nav class="navbar-links">
                        <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/account/" class="navbar-link" is-active="<?=(string_strpos($_SERVER["PHP_SELF"], "/".MEMBERS_ALIAS."/account/index.php") !== false) ? 'true' : 'false'; ?>">
                            <?=system_showText(LANG_LABEL_ACCOUNT)?>
                        </a>
                        <?php if (!empty($_SESSION[SM_LOGGEDIN])) { ?>
                            <a href="javascript:sitemgrSection();" class="navbar-link">
                                <?=system_showText(LANG_LABEL_SITEMGR_SECTION);?>
                            </a>
                        <?php } ?>
                    </nav>
                    <?php if (empty($_SESSION[SM_LOGGEDIN])) { ?>
                        <nav class="navbar-links">
                            <a href="<?=DEFAULT_URL?>/" class="navbar-link">
                                <?=system_showText(LANG_LABEL_BACK_TO_SEARCH);?>
                            </a>
                            <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/help.php" class="navbar-link">
                                <?=system_showText(LANG_BUTTON_HELP)?>
                            </a>
                            <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/faq.php" class="navbar-link">
                                <?=system_showText(LANG_MENU_FAQ);?>
                            </a>
                            <a href="<?=DEFAULT_URL."/".MEMBERS_ALIAS."/logout.php"?>" class="navbar-link">
                                <?=system_showText(LANG_BUTTON_LOGOFF);?>
                            </a>
                        </nav>
                    <? } ?>
                <?php } ?>
            </div>
        </header>
        <main>
