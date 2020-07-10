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
    # * FILE: /profile/add.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include("../conf/loadconfig.inc.php");

    if (sess_getAccountIdFromSession() and !$_GET['userperm']) {
        header("Location: ".DEFAULT_URL."/".SOCIALNETWORK_FEATURE_NAME."/");
        exit;
    }

    # ----------------------------------------------------------------------------------------------------
    # MAINTENANCE MODE
    # ----------------------------------------------------------------------------------------------------
    verify_maintenanceMode();

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSessionFront();

    # ----------------------------------------------------------------------------------------------------
    # VALIDATION
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/includes/code/validate_querystring.php");

    if (sess_isAccountLogged()) {
        header("Location: ".SOCIALNETWORK_URL."/");
        exit;
    }

    # ----------------------------------------------------------------------------------------------------
    # SUBMIT
    # ----------------------------------------------------------------------------------------------------
    include(INCLUDES_DIR."/code/add_account.php");

    if (SOCIALNETWORK_FEATURE == "off" && !isset($_GET["userperm"])) { exit; }

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    $headertag_title = $headertagtitle;
    $headertag_description = $headertagdescription;
    $headertag_keywords = $headertagkeywords;
    include(EDIRECTORY_ROOT."/frontend/header.php");

    # ----------------------------------------------------------------------------------------------------
    # BODY
    # ----------------------------------------------------------------------------------------------------
    include(INCLUDES_DIR."/code/newsletter.php");
    setting_get("foreignaccount_google", $foreignaccount_google);

    /*
     * Workaround for pin a bookmark without login
     */
    if ($_GET['bookmark_remember']) {
        $bookmarkQueryString = '&bookmark_remember=' . $_GET['bookmark_remember'];
    }

    /*
     * Workaround for make a redeem without login
     */
    if ($_GET['redeem_remember']) {
        $redeemQueryString = '&redeem_remember=' . $_GET['redeem_remember'];
    }

    if ($_GET['userperm']) {
        $userpermQueryString = '&userperm=' . $_GET['userperm'];
    }

    $cover_title = system_showText(LANG_JOIN_PROFILE);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>

    <div class="modal-default modal-sign" is-page="true">
        <div class="modal-content">
            <div class="modal-body">
                <div class="content-tab content-sign-in">
                    <?php if ($foreignaccount_google == "on" || FACEBOOK_APP_ENABLED == "on") { ?>
                        <div class="modal-social">
                            <?php
                                $redirectURI_params = [
                                    "destiny" => "referer",
                                    'referer' => $_SERVER['HTTP_REFERER']
                                ];
                                if (FACEBOOK_APP_ENABLED == "on") {
                                    $fbLabel = 'Facebook';
                                    include(INCLUDES_DIR."/forms/form_facebooklogin.php");
                                }

                                if ($foreignaccount_google == "on") {
                                    $goLabel = 'Google';
                                    include(INCLUDES_DIR."/forms/form_googlelogin.php");
                                }
                            ?>
                        </div>
                        <span class="heading or-label"><?= system_showText(LANG_OR); ?></span>
                    <?php } ?>
                    <form role="form" class="modal-form" name="add_account" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?><?=sprintf('?%s%s%s', $bookmarkQueryString, $redeemQueryString, $userpermQueryString)?>" method="post" autocomplete="off">

                        <?php include(INCLUDES_DIR."/forms/form_addaccount.php"); ?>

                        <?php if (isset($_POST['referer']) || $_SERVER['HTTP_REFERER']) { ?>
                            <input type="hidden" name="referer" value="<?=isset($_POST['referer']) ? $_POST['referer'] : $_SERVER['HTTP_REFERER']?>">
                        <?php } ?>
                    </form>
                    <div class="not-member"><a href="<?=SOCIALNETWORK_URL?>/login.php" class="link"><?=system_showText(LANG_LABEL_ALREADY_MEMBER);?></a></div>
                </div>
            </div>
        </div>
    </div>

<?

    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/frontend/footer.php");
