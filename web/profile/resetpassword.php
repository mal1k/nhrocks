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
    # * FILE: /profile/resetpassword.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include("../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSession();

    # ----------------------------------------------------------------------------------------------------
    # SUBMIT
    # ----------------------------------------------------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] == "POST") {

        $accountObj = new Account(sess_getAccountIdFromSession());
        $member_username = $accountObj->getString("username");

        if ($_POST["password"]) {
            if (validate_MEMBERS_account($_POST, $message, sess_getAccountIdFromSession())) {
                $accountObj->setString("password", $_POST["password"]);
                $accountObj->updatePassword();
                $success_message = system_showText(LANG_MSG_PASSWORD_SUCCESSFULLY_UPDATED);
                $urlRedirect = SOCIALNETWORK_URL."/edit.php";
            }
        } else {
            $message = system_showText(LANG_MSG_PASSWORD_IS_REQUIRED);
        }

    }

    # ----------------------------------------------------------------------------------------------------
    # AUX
    # ----------------------------------------------------------------------------------------------------
    if ($_GET["key"]) {

        $forgotPasswordObj = new forgotPassword($_GET["key"]);

        if ($forgotPasswordObj->getString("unique_key") && ($forgotPasswordObj->getString("section") == "members")) {

            $accountObj = new Account($forgotPasswordObj->getString("account_id"));
            $member_username = $accountObj->getString("username");

            $forgotPasswordObj->Delete();

            if (!$member_username) {
                $error_message = system_showText(LANG_MSG_WRONG_ACCOUNT);
            }

        } else {
            $error_message = system_showText(LANG_MSG_WRONG_KEY);
        }

    } else {
        $error_message = system_showText(LANG_MSG_WRONG_KEY);
    }

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    $headertag_title = system_showText(LANG_LABEL_RESET_PASSWORD);
    include(EDIRECTORY_ROOT."/frontend/header.php");

    $cover_title = system_showText(LANG_LABEL_RESET_PASSWORD);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");

?>

    <div class="modal-default modal-sign" is-page="true">
        <div class="modal-content">
            <div class="modal-body">
                <div class="content-tab content-sign-in">
                    <?php 
                        $style = "";

                        if(!$success_message && !$error_message && !$message){
                            $style = "style='margin-top: 0;'";
                        }
                    ?>
                    <?php if ($success_message) { ?>
                        <div class="alert alert-success">
                            <i class="fa fa-check"></i>
                            <?=$success_message;?>
                            <a href="<?=$urlRedirect;?>"><?=system_showText(LANG_BUTTON_MANAGE_ACCOUNT)?></a>
                        </div>
                    <? } elseif ($error_message && !$message) { ?>
                        <div class="alert alert-danger">
                            <i class="fa fa-times"></i>
                            <?=$error_message;?><br>
                            <a href="<?=DEFAULT_URL?>/<?=SOCIALNETWORK_FEATURE_NAME?>/forgot.php"><?=system_showText(LANG_LABEL_FORGOTPASSWORD);?></a>
                        </div>
                    <? } else {
                        if ($message) { ?>
                            <div class="alert alert-danger">
                                <i class="fa fa-times"></i>
                                <?=$message;?>
                            </div>
                        <? } ?>
                        <form role="form" class="modal-form" name="forgotpassword" method="post" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" <?=$style?>>
                            <div class="form-group">
                                <label for="np-password"><?=system_showText(LANG_LABEL_PASSWORD)?></label>
                                <input type="password" class="input" name="password" maxlength="<?=PASSWORD_MAX_LEN?>" required class="form-control" id="np-password">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="np-passwordretype"><?=system_showText(LANG_LABEL_RETYPE_PASSWORD)?></label>
                                <input type="password" class="input" name="retype_password" required class="form-control" id="np-passwordretype">
                            </div>
                            <br>
                            <button type="submit" class="button button-bg is-primary" full-width="true" type="submit" value="<?=system_showText(LANG_BUTTON_SUBMIT);?>"><?=system_showText(LANG_BUTTON_SUBMIT);?></button>
                        </form>
                    <? } ?>
                </div>
            </div>
        </div>
    </div>

    <?
    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/frontend/footer.php");
