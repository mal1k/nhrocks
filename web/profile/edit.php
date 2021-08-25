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
    # * FILE: /profile/edit.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include("../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # MAINTENANCE MODE
    # ----------------------------------------------------------------------------------------------------
    verify_maintenanceMode();

    # ----------------------------------------------------------------------------------------------------
    # VALIDATION
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/includes/code/validate_querystring.php");

    if (SOCIALNETWORK_FEATURE == "off") { exit; }

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSessionFront();

    if ($_SERVER['REQUEST_METHOD'] == "POST") {

        if ($_POST["ajax"] || $_GET["ajax"]) {

            if ($_POST["action"] == "changeStatus") {

                if ($_POST['has_profile'] == "on" || $_POST['has_profile'] == "true") {
                    $has_profile = true;
                } else {
                    $has_profile = false;
                }

                $accObj = new Account();
                $accObj->setNumber("id", $_POST["account_id"]);
                $accObj->changeProfileStatus($has_profile);

                $accDomain = new Account_Domain($accObj->getNumber("id"), SELECTED_DOMAIN_ID);
                $accDomain->Save();
                $accDomain->saveOnDomain($accObj->getNumber("id"), $accObj);

            } elseif ($_POST["action"] == "removePhoto") {

                $profileObj = new Profile($_POST["account_id"]);

                $idm = $profileObj->getNumber("image_id");
                $image = new Image($idm, true);
                if ($image) $image->Delete();

                $profileObj->setNumber("image_id", 0);
                $profileObj->setString("facebook_image", "");

                $profileObj->Save();

            } elseif ($_GET["action"] === "uploadPhoto") {
                $error = false;
                if (file_exists($_FILES['image']['tmp_name'])) {
                    if (!image_upload_check($_FILES["image"]["tmp_name"])) {
                        $error = true;
                        $return = system_showText(LANG_MSG_INVALID_IMAGE_TYPE)."<br />";
                    } else {

                        $imageArray = image_uploadForItem($_FILES['image']['tmp_name'], sess_getAccountIdFromSession()."_", 200, 200, true);
                        if ($imageArray["success"]) {
                            $profileObj = new Profile(sess_getAccountIdFromSession());
                            $profileObj->setString('facebook_image', '');
                            $oldImage = $profileObj->getNumber('image_id');
                            if ($oldImage) {
                                $imageAux = new Image($oldImage, true);
                                if ($imageAux) {
                                    $imageAux->Delete();
                                }
                            }
                            $profileObj->setNumber('image_id', $imageArray["image_id"]);
                            $profileObj->Save();
                            $imageObj = new Image($imageArray["image_id"], true);
                            $return = $imageObj->getTag(true, PROFILE_MEMBERS_IMAGE_WIDTH, PROFILE_MEMBERS_IMAGE_HEIGHT, '', '', 'Profile Image');
                        } else {
                            $error = true;
                            $return = system_showText(LANG_LABEL_ERRORLOGIN);
                        }

                    }
                } else {
                    $error = true;
                    $return = system_showText(LANG_MSG_MAX_FILE_SIZE . ': ' . UPLOAD_MAX_SIZE . 'MB.');
                }
                echo ($error ? 'error' : 'ok') . '||' . $return;
            }
            exit;
        }

        extract($_POST);

        $accObj = new Account($_POST["account_id"]);
        $profileObj = new Profile($_POST["account_id"]);

        if ($_POST["facebook_image"]) {
            $_POST["image_id"] = "";
        }

        if (!trim($_POST["nickname"])) {
            $message_profile .= "&#149;&nbsp;".system_showText(LANG_MSG_NICKANAME_REQUIRED)."<br />";
            $error = 1;
        }

        if (!$friendly_url) {
            $message_profile = "&#149;&nbsp;".system_showText(LANG_LABEL_YOURURL_REQUIRED)."<br />";
            $error = 1;
        } else {
            if (!preg_match(FRIENDLYURL_REGULAREXPRESSION, $friendly_url)) {
                $message_profile = "&#149;&nbsp;".system_showText(LANG_MSG_FRIENDLY_URL_INVALID_CHARS)."<br />";
                $error = 1;
            }
        }

        if ($profileObj->fUrl_Exists($_POST["friendly_url"])) {
            $message_profile .= "&#149;&nbsp;".system_showText(LANG_MSG_PAGE_URL_IN_USE);
            $error = 1;
        }

        if (!$error) {
            $profileObj->makeFromRow($_POST);
            $profileObj->Save();

            $accDomain = new Account_Domain($profileObj->getNumber("account_id"), SELECTED_DOMAIN_ID);
            $accDomain->Save();
            $accDomain->saveOnDomain($profileObj->getNumber("account_id"), false, false, $profileObj);

            $message = system_showText(LANG_MSG_PROFILE_SUCCESSFULLY_UPDATED);
            $message_style = "successMessage";

            $profileObj = new Profile($account_id);
            $profileObj->extract();
        }
    } else {
        if (string_strpos($_SERVER["PHP_SELF"], "/".SOCIALNETWORK_FEATURE_NAME."/") !== false && $_GET["tab"] != "tab_2") {
            $accObj = new Account(sess_getAccountIdFromSession());
            $contactObj = new Contact($accObj->getNumber("id"));
            if (!$contactObj->getString("email")) {
                header("Location: ".SOCIALNETWORK_URL."/edit.php?tab=tab_2");
                exit;
            }
        }

        $profileObj = new Profile(sess_getAccountIdFromSession());
        $profileObj->extract();
    }

    if (MAIL_APP_FEATURE == "on") {
        arcamailer_checkSubscriber();
    }

    # ----------------------------------------------------------------------------------------------------
    # SUBMIT
    # ----------------------------------------------------------------------------------------------------
    // Default CSS class for message box
    $message_style = "warning";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (SOCIALNETWORK_FEATURE == "off") {
            $_POST["publish_contact"] = "n";
        } else {
            if ($_POST["publish_contact"] == "on") {
                $_POST["publish_contact"] = "y";
            } else {
                $_POST["publish_contact"] = "n";
            }
        }

        if ((string_strlen($_POST["password"])) || (string_strlen($_POST["retype_password"]))) {
            $validate_membercurrentpassword = validate_memberCurrentPassword($_POST, sess_getAccountIdFromSession(), $message_member);
        } else {
            $validate_membercurrentpassword = true;
        }

        $account = new Account($account_id);
        $validate_account = validate_MEMBERS_account($_POST, $message_account, sess_getAccountIdFromSession());
        $validate_contact = validate_form("contact", $_POST, $message_contact);

        if ($validate_membercurrentpassword && $validate_account && $validate_contact && !$message_profile) {
            $account = new Account($account_id);
            $lastNewsletter = $account->getString("newsletter");

            $notifyUser = false;
            if ($_POST["password"]) {
                $notifyUser = true;
                $account->setString("password", $_POST["password"]);
                $account->updatePassword();
            }
            if ($_POST["username"]) {
                if ($account->getString("username") != $_POST["username"]) {
                    $notifyUser = true;
                }
                $account->setString("username", $_POST["username"]);
            }
            $account->setString("publish_contact", $_POST["publish_contact"]);

            if ($_POST["newsletter"]) {
                $actualNewsletter = "y";
            } else {
                $actualNewsletter = "n";
            }

            $account->setString("newsletter", $actualNewsletter);
            $account->Save();

            $contact = new Contact($_POST);
            $contact->Save();

            if ($actualNewsletter != $lastNewsletter) {

                //Subscribe
                if ($actualNewsletter == "y") {

                    $fields["name"] = $contact->getString("first_name")." ".$contact->getString("last_name");
                    $fields["type"] = "profile";
                    $fields["email"] = $contact->getString("email");
                    arcamailer_addSubscriber($fields, $success, $account->getNumber("id"));

                    //Unsubscribe
                } else {
                    arcamailer_Unsubscribe($contact->getString("email"), $account->getNumber("id"));
                }

            }

            $accDomain = new Account_Domain($account->getNumber("id"), SELECTED_DOMAIN_ID);
            $accDomain->Save();
            $accDomain->saveOnDomain($account->getNumber("id"), $account, $contact);

            if (system_checkEmail(SYSTEM_VISITOR_ACCOUNT_UPDATE) && $_POST["tab"] == "tab_2" && $notifyUser) {
                system_sendPassword(SYSTEM_VISITOR_ACCOUNT_UPDATE, $_POST["email"], $_POST["username"], $_POST["password"], $_POST["first_name"]." ".$_POST["last_name"]);
            }

            $message = system_showText(LANG_MSG_ACCOUNT_SUCCESSFULLY_UPDATED);
            $message_style = "success";
        } else {
            $message = "";
            $message_style = "";
        }


        // removing slashes added if required
        $_POST = format_magicQuotes($_POST);
        $_GET  = format_magicQuotes($_GET);

        extract($_GET);
        extract($_POST);
    }

    # ----------------------------------------------------------------------------------------------------
    # MODE REWRITE
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/".SOCIALNETWORK_FEATURE_NAME."/mod_rewrite.php");

    unset($info);
    $info = socialnetwork_retrieveInfoProfile($id);

    # ----------------------------------------------------------------------------------------------------
    # AUX
    # ----------------------------------------------------------------------------------------------------
    extract($_GET);
    extract($_POST);

    # ----------------------------------------------------------------------------------------------------
    # FORMS DEFINES
    # ----------------------------------------------------------------------------------------------------
    if (sess_getAccountIdFromSession()) {
        $accountObj = new Account(sess_getAccountIdFromSession());
        $contactObj = new Contact(sess_getAccountIdFromSession());
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            $accountObj->extract();
            $contactObj->extract();
        }
    } else {
        header("Location: ".DEFAULT_URL."/".SOCIALNETWORK_FEATURE_NAME."/index.php");
        exit;
    }

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    $headertag_title = $headertagtitle;
    $headertag_description = $headertagdescription;
    $headertag_keywords = $headertagkeywords;
    include(EDIRECTORY_ROOT."/frontend/header.php");

    $cover_title = system_showText(LANG_JOIN_PROFILE);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");

    if ($_GET["id"]) {
        $account = $_GET["id"];
    } else {
        $account = sess_getAccountIdFromSession();
    }

    $account = new Account($account);
    $contactObj = new Contact($account->getNumber("id"));

    # ----------------------------------------------------------------------------------------------------
    # BODY
    # ----------------------------------------------------------------------------------------------------
    ?>

    <div class="members-page profile-page">
        <div class="container">
            <?php
                include(INCLUDES_DIR."/forms/form_members_messages.php");

                if (!$contactObj->getString("email")) {
                    echo '<p class="alert alert-warning">'.system_showText(LANG_MSG_FOREIGNACCOUNTWARNING).'</p>';
                }
            ?>

            <p id="returnMessage" class="alert alert-warning" style="display:none;"></p>

            <form name="account" id="account" method="post" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" enctype="multipart/form-data">
                <input type="hidden" name="tab" id="tab" value="<?=$tab ? $tab: "tab_1";?>" />
                <input type="hidden" name="account_id" value="<?=$account_id?>" />

                <?php
                    $accountID = sess_getAccountIdFromSession();

                    include(INCLUDES_DIR."/forms/form_profile.php");
                ?>
                <br>
                <div class="members-panel edit-panel">
                    <div class="panel-header">
                        <?=system_showText(LANG_LABEL_ACCOUNT_SETTINGS)?>
                    </div>
                    <div class="panel-body">
                        <div class="custom-edit-content">
                            <?php
                                include(INCLUDES_DIR."/forms/form_account_members.php");
                                include(INCLUDES_DIR."/forms/form_contact_members.php");
                            ?>
                        </div>
                    </div>
                </div>
                <br>
                <div class="text-center">
                    <button class="button button-md is-success action-save" type="button" onclick="document.account.submit();"><?=system_showText(LANG_SAVE_CHANGES)?></button>
                </div>
            </form>
        </div>
    </div>

    <?php
    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/frontend/footer.php");
