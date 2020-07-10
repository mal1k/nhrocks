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
    # * FILE: /frontend/widgets/navigation-left-logo-plus-social.php
    # ----------------------------------------------------------------------------------------------------

    //Links to twitter, facebook and linkedin
    $social = [];
    setting_get("setting_facebook_link", $social['facebook']);
    setting_get("setting_linkedin_link", $social['linkedin']);
    setting_get("twitter_account", $social['twitter']);
    setting_get("setting_instagram_link", $social['instagram']);
    setting_get("setting_pinterest_link", $social['pinterest']); 

    ?>
    <header class="header" data-type="2" is-sticky="<?=$widgetContent['stickyMenu'] === 'true' ? 'true' : 'false' ?>" is-inverse="<?=$widgetContent['backgroundColor'] === 'base' ? 'true' : 'false'?>" has-opacity="<?=$widgetContent['isTransparent'] === 'true' ? 'true' : 'false' ?>" has-mod="<?HookFire("header_check_dropdown")?>">
        <div class="header-content">
            <div class="container">
                <div class="wrapper">
                    <div class="content-left">
                        <a href="<?=DEFAULT_URL?>" target="_parent" title="<?=EDIRECTORY_TITLE?>" class="header-logo" style="background-image: url(<?=image_getLogoImagePath() . '?' . date('U');?>)"></a>
                        <nav class="header-navbar">
                            <?php include EDIRECTORY_ROOT.'/frontend/header_menu.php'; ?>

                            <div class="navbar-more">
                                <div class="more-label"><?=ucfirst(LANG_MORE);?> <i class="fa fa-angle-up"></i></div>
                                <div class="more-content"></div>
                            </div>
                        </nav>
                        <div class="content-mobile">
                            <button class="toggler-button navbar-toggler"><i class="fa fa-bars"></i></button>
                        </div>
                    </div>
                    <div class="content-right">
                        <a href="<?=DEFAULT_URL.'/'.ALIAS_ADVERTISE_URL_DIVISOR?>" class="button button-bg is-inverse"><?=$translator->trans($widgetContent['labelListWithUs'], [], 'widgets')?></a>
                        <?php if (sess_getAccountIdFromSession()) { ?>
                            <div class="bar-link user-button">
                                <?=$contactWelcome['nickname']?> <i class="fa fa-angle-down"></i>
                                <div class="user-content">
                                    <?php if ($contactWelcome['is_sponsor'] == 'y') { ?>
                                        <a href="<?=DEFAULT_URL.'/'.MEMBERS_ALIAS.'/'?>" class="user-link"><?=$translator->trans($widgetContent['labelDashboard'], [], 'widgets')?></a>
                                        <a href="<?=DEFAULT_URL.'/'.MEMBERS_ALIAS.'/faq.php'?>" class="user-link"><?=$translator->trans($widgetContent['labelFaq'], [], 'widgets')?></a>
                                        <a href="<?=DEFAULT_URL.'/'.MEMBERS_ALIAS.'/account/'?>" class="user-link"><?=$translator->trans($widgetContent['labelAccountPref'], [], 'widgets')?></a>

                                        <?php if ($contactWelcome['has_profile'] == 'y' && SOCIALNETWORK_FEATURE == 'on') { ?>
                                            <a href="<?=DEFAULT_URL.'/'.SOCIALNETWORK_FEATURE_NAME?>" class="user-link"><?=$translator->trans($widgetContent['labelProfile'], [], 'widgets')?></a>
                                        <?php } ?>

                                        <a href="<?=DEFAULT_URL.'/'.MEMBERS_ALIAS.'/logout.php'?>" class="user-link"><?=$translator->trans($widgetContent['labelLogOff'], [], 'widgets')?></a>
                                    <?php } ?>

                                    <?php if ($contactWelcome['is_sponsor'] == 'n' && $contactWelcome['has_profile'] == 'y' && SOCIALNETWORK_FEATURE == 'on') { ?>
                                        <a href="<?=DEFAULT_URL.'/'.SOCIALNETWORK_FEATURE_NAME?>" class="user-link"><?=$translator->trans($widgetContent['labelProfile'], [], 'widgets')?></a>
                                        <a href="<?=DEFAULT_URL.'/'.ALIAS_FAQ_URL_DIVISOR?>" class="user-link"><?=$translator->trans($widgetContent['labelFaq'], [], 'widgets')?></a>
                                        <a href="<?=DEFAULT_URL.'/'.SOCIALNETWORK_FEATURE_NAME.'/edit.php'?>" class="user-link"><?=$translator->trans($widgetContent['labelAccountPref'], [], 'widgets')?></a>
                                        <a href="<?=DEFAULT_URL.'/'.SOCIALNETWORK_FEATURE_NAME.'/logout.php'?>" class="user-link"><?=$translator->trans($widgetContent['labelLogOff'], [], 'widgets')?></a>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <a href="javascript:void(0);" data-modal="login" id="navbar-signin" class="button button-bg is-primary"><?=$translator->trans($widgetContent['labelSignIn'], [], 'widgets')?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="navbar-mobile">
            <?php if (sess_getAccountIdFromSession()) { ?>
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

                <nav class="navbar-links">
                    <?php if ($contactWelcome['is_sponsor'] == 'y') { ?>
                        <a href="<?=DEFAULT_URL.'/'.MEMBERS_ALIAS.'/'?>" class="navbar-link"><?=$translator->trans($widgetContent['labelDashboard'], [], 'widgets')?></a>
                        <a href="<?=DEFAULT_URL.'/'.MEMBERS_ALIAS.'/faq.php'?>" class="navbar-link"><?=$translator->trans($widgetContent['labelFaq'], [], 'widgets')?></a>
                        <a href="<?=DEFAULT_URL.'/'.MEMBERS_ALIAS.'/account/'?>" class="navbar-link"><?=$translator->trans($widgetContent['labelAccountPref'], [], 'widgets')?></a>

                        <?php if ($contactWelcome['has_profile'] == 'y' && SOCIALNETWORK_FEATURE == 'on') { ?>
                            <a href="<?=DEFAULT_URL.'/'.SOCIALNETWORK_FEATURE_NAME?>" class="navbar-link"><?=$translator->trans($widgetContent['labelProfile'], [], 'widgets')?></a>
                        <?php } ?>

                        <a href="<?=DEFAULT_URL.'/'.MEMBERS_ALIAS.'/logout.php'?>" class="navbar-link"><?=$translator->trans($widgetContent['labelLogOff'], [], 'widgets')?></a>
                    <?php } ?>

                    <?php if ($contactWelcome['is_sponsor'] == 'n' && $contactWelcome['has_profile'] == 'y' && SOCIALNETWORK_FEATURE == 'on') { ?>
                        <a href="<?=DEFAULT_URL.'/'.SOCIALNETWORK_FEATURE_NAME?>" class="navbar-link"><?=$translator->trans($widgetContent['labelProfile'], [], 'widgets')?></a>
                        <a href="<?=DEFAULT_URL.'/'.ALIAS_FAQ_URL_DIVISOR?>" class="navbar-link"><?=$translator->trans($widgetContent['labelFaq'], [], 'widgets')?></a>
                        <a href="<?=DEFAULT_URL.'/'.SOCIALNETWORK_FEATURE_NAME.'/edit.php'?>" class="navbar-link"><?=$translator->trans($widgetContent['labelAccountPref'], [], 'widgets')?></a>
                        <a href="<?=DEFAULT_URL.'/'.SOCIALNETWORK_FEATURE_NAME.'/logout.php'?>" class="navbar-link"><?=$translator->trans($widgetContent['labelLogOff'], [], 'widgets')?></a>
                    <?php } ?>
                </nav>
            <?php } else { ?>
                <nav class="navbar-links">
                    <a href="javascript:void(0);" data-modal="login" id="navbar-signin" class="button button-bg is-primary"><?=$translator->trans($widgetContent['labelSignIn'], [], 'widgets')?></a>
                </nav>
            <?php } ?>

            <nav class="navbar-links">
                <?php include EDIRECTORY_ROOT.'/frontend/header_menu.php'; ?>
            </nav>
            <nav class="navbar-links">
                <a href="<?=DEFAULT_URL.'/'.ALIAS_ADVERTISE_URL_DIVISOR?>" class="navbar-link">
                    <?=$translator->trans($widgetContent['labelListWithUs'], [], 'widgets')?>
                </a>
            </nav>
        </div>
    </header>
