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
    # * FILE: /frontend/widgets/footer-nav-with-contactus.php
    # ----------------------------------------------------------------------------------------------------

    setting_get('footer_copyright', $footer_copyright);
?>

    <!-- Footer Begin -->
    <footer class="footer" data-type="1" is-inverse="<?=$widgetContent['backgroundColor'] === 'base' ? 'true' : 'false'?>">
        <div class="footer-content">
            <div class="container">
                <div class="wrapper">
                    <?php include EDIRECTORY_ROOT.'/frontend/footer_menu.php'; ?>

                    <?php if ($contact_address || $contact_phone) { ?>
                        <div class="footer-item" data-content="contact-content">
                            <div class="heading footer-item-title">
                                <?=$translator->trans($widgetContent['labelContactUs'], [], 'widgets')?>
                            </div>
                            <div class="footer-item-content">
                                <?php if(!empty($contact_address)) { ?>
                                    <div class="footer-info">
                                        <div class="icon icon-md"><i class="fa fa-map-marker"></i></div>
                                        <?=$contact_address?>
                                    </div>
                                <?php } ?>
                                <?php if(!empty($contact_phone)) { ?>
                                    <div class="footer-info">
                                        <div class="icon icon-md"><i class="fa fa-phone"></i></div>
                                        <?=$contact_phone?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($setting_twitter_link || $setting_facebook_link || $setting_linkedin_link || $setting_instagram_link || $setting_pinterest_link ) { ?>
                        <div class="footer-item" data-content="social-content">
                            <div class="heading footer-item-title">
                                <?=$translator->trans($widgetContent['labelFollowUs'], [], 'widgets')?>
                            </div>
                            <div class="footer-item-content">
                                <?php if ($setting_facebook_link) { ?>
                                    <a href="<?=$setting_facebook_link?>" rel="noopener noreferrer" target="_blank" class="link-footer">
                                        <div class="icon icon-md">
                                            <i class="fa fa-facebook"></i>
                                        </div>
                                        <span>Facebook</span>
                                    </a>
                                <?php } ?>
                                <?php if ($setting_linkedin_link) { ?>
                                    <a href="<?=$setting_linkedin_link?>" rel="noopener noreferrer" target="_blank" class="link-footer">
                                        <div class="icon icon-md">
                                            <i class="fa fa-linkedin"></i>
                                        </div>
                                        <span>LinkedIn</span>
                                    </a>
                                <?php } ?>
                                <?php if ($setting_twitter_link) { ?>
                                    <a href="<?=$setting_twitter_link?>" rel="noopener noreferrer" target="_blank" class="link-footer">
                                        <div class="icon icon-md">
                                            <i class="fa fa-twitter"></i>
                                        </div>
                                        <span>Twitter</span>
                                    </a>
                                <?php } ?>
                                <?php if ($setting_instagram_link) { ?>
                                    <a href="<?=$setting_instagram_link?>" rel="noopener noreferrer" target="_blank" class="link-footer">
                                        <div class="icon icon-md">
                                            <i class="fa fa-instagram"></i>
                                        </div>
                                        <span>Instagram</span>
                                    </a>
                                <?php } ?>
                                <?php if ($setting_pinterest_link) { ?>
                                    <a href="<?=$setting_pinterest_link?>" rel="noopener noreferrer" target="_blank" class="link-footer">
                                        <div class="icon icon-md">
                                            <i class="fa fa-pinterest"></i>
                                        </div>
                                        <span>Pinterest</span>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>

                    <?php
                        $flags = 0;
                        $widgetContent['linkPlayStore'] and $flags |= 64;
                        $widgetContent['AppStoreLabel'] and $flags |= 128;
                    ?>

                    <?php if ($flags & 192) { ?>
                        <div class="footer-item" data-content="apps-content">
                            <?php if ($flags & 64) { ?>
                                <a href="<?=$widgetContent['linkPlayStore']?>" rel="noopener noreferrer" target="_blank" class="link-store">
                                    <div class="app-icon"><i class="fa fa-android"></i></div>
                                    <div class="app-info">
                                        <span><?=$widgetContent['playStoreLabel']?></span>
                                    </div>
                                </a>
                            <?php } ?>

                            <?php if($flags & 128) { ?>
                                <a href="<?=$widgetContent['linkAppleStore']?>" rel="noopener noreferrer" target="_blank" class="link-store">
                                    <div class="app-icon"><i class="fa fa-apple"></i></div>
                                    <div class="app-info">
                                        <span><?=$widgetContent['AppStoreLabel']?></span>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php if (BRANDED_PRINT === 'on' || $footer_copyright) { ?>
            <div class="footer-bar">
                <div class="container">
                    <div class="wrapper">
                        <div class="footer-copyright"><?=$footer_copyright?></div>

                        <?php  if (BRANDED_PRINT === 'on') { ?>
                        <div class="footer-powered">
                            <?=LANG_POWEREDBY?>
                            <a href="http://www.edirectory.com<?=(string_strpos($_SERVER['HTTP_HOST'], '.com.br') !== false ? '.br' : '')?>" class="edirectory-link">
                                <img src="/assets/images/<?php echo($widgetContent['backgroundColor'] === 'base' ? 'edirectory-logo-inverse' : 'edirectory-logo'); ?>.svg" alt="eDirectory Cloud Service &trade;">
                            </a>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </footer>
    <!-- Footer End -->
