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
    # * FILE: /profile/login.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------

    include '../conf/loadconfig.inc.php';

    if (sess_getAccountIdFromSession() and !$_GET['userperm']) {
        header('Location: '.DEFAULT_URL.'/'.SOCIALNETWORK_FEATURE_NAME.'/');
        exit;
    }

    # ----------------------------------------------------------------------------------------------------
    # MAINTENANCE MODE
    # ----------------------------------------------------------------------------------------------------
    verify_maintenanceMode();

    # ----------------------------------------------------------------------------------------------------
    # VALIDATION
    # ----------------------------------------------------------------------------------------------------
    include EDIRECTORY_ROOT.'/includes/code/validate_querystring.php';

    if (SOCIALNETWORK_FEATURE == 'off' && !isset($_GET['userperm'])) { exit; }

    include EDIRECTORY_ROOT.'/includes/code/profile_login.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_GET['facebookerror'] || $_GET['googleerror'] || $_GET['key'] || $_GET['activation_key']) {
        include EDIRECTORY_ROOT.'/includes/code/login.php';
    }

    /* these var are, also, used in login modal */
    $bookmarkQueryString = $redeemQueryString = $userpermQueryString = '';
    /*
     * Workaround for pin a bookmark without login
     */
    if ($_GET['bookmark_remember']) {
        $bookmarkQueryString = '&bookmark_remember=' . $_GET['bookmark_remember'];
        $url .= $bookmarkQueryString;
    }

    /*
     * Workaround for make a redeem without login
     */
    if ($_GET['redeem_remember']) {
        $redeemQueryString = '&redeem_remember=' . $_GET['redeem_remember'];
        $url .= $redeemQueryString;
    }

    if ($_GET['userperm']) {
        $userpermQueryString = '&userperm=' . $_GET['userperm'];
    }

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    include EDIRECTORY_ROOT.'/frontend/header.php';

    # ----------------------------------------------------------------------------------------------------
    # BODY
    # ----------------------------------------------------------------------------------------------------

    $cover_title = system_showText(LANG_BUTTON_SIGNIN);
    include EDIRECTORY_ROOT.'/frontend/coverimage.php';
?>

    <div class="modal-default modal-sign profile-login-modal" is-page="true">
        <div class="modal-content">
            <div class="modal-body">
                <div class="content-tab content-sign-in">
                    <?php if ($foreignaccount_google == 'on' || FACEBOOK_APP_ENABLED == 'on') { ?>
                        <div class="modal-social">
                            <?php
                                $redirectURI_params = [
                                    'destiny' => 'referer',
                                    'referer' => $_SERVER['HTTP_REFERER']
                                ];

                                if (FACEBOOK_APP_ENABLED == 'on') {
                                    $fbLabel = 'Facebook';
                                    include INCLUDES_DIR.'/forms/form_facebooklogin.php';
                                }

                                if ($foreignaccount_google == 'on') {
                                    $goLabel = 'Google';
                                    include INCLUDES_DIR.'/forms/form_googlelogin.php';
                                }
                            ?>
                        </div>
                        <span class="heading or-label"><?= system_showText(LANG_OR); ?></span>
                    <?php } ?>
                    <form role="form" class="modal-form" name="login" method="post" action="<?=DEFAULT_URL?><?= $url ?>">
                        <?php
                            $members_section = true;
                            include INCLUDES_DIR.'/forms/form_login.php';
                        ?>
                    </form>
                    <div class="not-member"><a href="<?= SOCIALNETWORK_URL ?>/add.php?<?=sprintf('%s%s%s', $bookmarkQueryString, $redeemQueryString, $userpermQueryString)?>" class="link"><?= system_showText(LANG_LABEL_SIGNUPNOW); ?></a></div>
                </div>
            </div>
        </div>
    </div>

<?php

    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    include EDIRECTORY_ROOT.'/frontend/footer.php';