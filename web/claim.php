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
    # * FILE: /claim.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include("./conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # MAINTENANCE MODE
    # ----------------------------------------------------------------------------------------------------
    verify_maintenanceMode();

    # ----------------------------------------------------------------------------------------------------
    # VALIDATE FEATURE
    # ----------------------------------------------------------------------------------------------------
    if (CLAIM_FEATURE != "on") {
        exit;
    }

    if ($_GET["claim"]) {
        $db = db_getDBObject();
        $sql = "SELECT Listing.id as id FROM Listing WHERE Listing.friendly_url = " . db_formatString($_GET["claim"]) . " LIMIT 1";
        $result = $db->query($sql);
        $aux = mysqli_fetch_assoc($result);
        $_GET["claimlistingid"] = $aux["id"];
        if (!$_GET["claimlistingid"]) {
            header("Location: " . LISTING_DEFAULT_URL . "/");
            exit;
        }
    }

    extract($_POST);
    extract($_GET);

    $listingObject = new Listing($claimlistingid);
    if (!$listingObject->getNumber("id") || $listingObject->getNumber("id") <= 0 || is_numeric($listingObject->getNumber("account_id")) || $listingObject->getString("claim_disable") != "n") {
        header("Location: ".LISTING_DEFAULT_URL."/");
        exit;
    }

    if (sess_getAccountIdFromSession()) {
        $accountObj = new Account(sess_getAccountIdFromSession());
        $accountObj->changeMemberStatus(true);

        $accDomain = new Account_Domain($accountObj->getNumber("id"), SELECTED_DOMAIN_ID);
        $accDomain->Save();
        $accDomain->saveOnDomain($accountObj->getNumber("id"), $accountObj);

        $host = string_strtoupper(str_replace("www.", "", $_SERVER["HTTP_HOST"]));

        setcookie($host."_DOMAIN_ID_MEMBERS", "", time()+60*60*24*30, "".EDIRECTORY_FOLDER."/");
        setcookie($host."_DOMAIN_ID", "", time()+60*60*24*30, "".EDIRECTORY_FOLDER."/");
        unset($_SESSION[$host."_DOMAIN_ID_MEMBERS"], $_SESSION[$host."_DOMAIN_ID"]);

        header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/claim/getlisting.php?claimlistingid=".$claimlistingid);
        exit;
    }

    # ----------------------------------------------------------------------------------------------------
    # SUBMIT
    # ----------------------------------------------------------------------------------------------------
    if (($_SERVER['REQUEST_METHOD'] == "POST")) {

        $_POST["retype_password"] = $_POST["password"];

        $validate_account = validate_addAccount($_POST, $message_account);
        $validate_contact = validate_form("contact", $_POST, $message_contact);

        if ($validate_account && $validate_contact) {

            $account = new Account($_POST);
            $account->save();

            if ($_POST["claim"]) {
                $account->changeMemberStatus(true);
            }

            if ($_POST["newsletter"]) {
                $_POST["name"] = $_POST["first_name"]." ".$_POST["last_name"];
                $_POST["type"] = "sponsor";
                arcamailer_addSubscriber($_POST, $success, $account->getNumber("id"));
            }

            $contact = new Contact($_POST);
            $contact->setNumber("account_id", $account->getNumber("id"));
            $contact->save();

            $profileObj = new Profile(sess_getAccountIdFromSession());
            $profileObj->setNumber("account_id", $account->getNumber("id"));
            if (!$profileObj->getString("nickname")) {
                $profileObj->setString("nickname", $_POST["first_name"]." ".$_POST["last_name"]);
            }
            $profileObj->Save();

            $accDomain = new Account_Domain($account->getNumber("id"), SELECTED_DOMAIN_ID);
            $accDomain->Save();
            $accDomain->saveOnDomain($account->getNumber("id"), $account, $contact, $profileObj);

            /**************************************************************************************************/
            /*                                                                                                */
            /* E-mail notify                                                                                  */
            /*                                                                                                */
            /**************************************************************************************************/

            // sending e-mail to user //////////////////////////////////////////////////////////////////////////
            if ($emailNotificationObj = system_checkEmail(SYSTEM_CLAIM_SIGNUP)) {

                $linkActivation = system_getAccountActivationLink($account->getNumber("id"));

                $subject = $emailNotificationObj->getString("subject");
                $body = $emailNotificationObj->getString("body");
                $body = str_replace("ACCOUNT_NAME",$contact->getString("first_name").' '.$contact->getString("last_name"),$body);
                $login_info = trim(system_showText(LANG_LABEL_USERNAME)).": ".$_POST["username"];
                $login_info .= ($emailNotificationObj->getString("content_type") == "text/html"? "<br />": "\n");
                $login_info .= trim(system_showText(LANG_LABEL_PASSWORD)).": ".$_POST["password"];
                $body = str_replace("ACCOUNT_LOGIN_INFORMATION",$login_info, $body);
                $body = str_replace("LINK_ACTIVATE_ACCOUNT", $linkActivation, $body);
                $body = system_replaceEmailVariables($body, $listingObject->getNumber('id'), 'listing');
                $subject = system_replaceEmailVariables($subject, $listingObject->getNumber('id'), 'listing');
                $body = html_entity_decode($body);
                $subject = html_entity_decode($subject);
                $error = false;

                SymfonyCore::getContainer()->get('core.mailer')
                    ->newMail($subject, $body, $emailNotificationObj->getString( "content_type" ))
                    ->setTo($contact->getString( "email" ))
                    ->setBcc($emailNotificationObj->getString( "bcc" ))
                    ->send();
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////

            sess_registerAccountInSession($account->getString("username"));
            setcookie("username_members", $account->getString("username"), time()+60*60*24*30, "".EDIRECTORY_FOLDER."/");

            header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/claim/getlisting.php?claimlistingid=".$claimlistingid);
            exit;

        } else {
            // removing slashes added if required
            $_POST = format_magicQuotes($_POST);
            $_GET  = format_magicQuotes($_GET);
            extract($_POST);
            extract($_GET);
        }

    }

    unset($facebookEnabled, $googleEnabled, $cUserEnabled);

    setting_get("foreignaccount_google", $foreignaccount_google);
    if ($foreignaccount_google == "on") {
        $googleEnabled = true;
    }

    if (FACEBOOK_APP_ENABLED == "on") {
        $facebookEnabled = true;
    }

    if (sess_isAccountLogged() && SOCIALNETWORK_FEATURE == "on") {
        $cUserEnabled = true;
    }

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    $headertag_title = (($listingObject->getString("seo_title")) ? ($listingObject->getString("seo_title")) : ($listingObject->getString("title")))." - ".system_showText(LANG_LISTING_CLAIMTHIS);
    $headertag_description = (($listingObject->getString("seo_description")) ? ($listingObject->getString("seo_description")) : ($listingObject->getString("description")));
    $headertag_keywords = (($listingObject->getString("seo_keywords")) ? ($listingObject->getString("seo_keywords")) : (str_replace(" || ", ", ", $listingObject->getString("keywords"))));
    include(EDIRECTORY_ROOT."/frontend/header.php");
    $cover_title = system_showText(LANG_LISTING_CLAIMING) .' "'. $listingObject->getString("title") .'"';
    $cover_subtitle = string_strtoupper(system_showText(LANG_EASYANDFAST)) .' '.string_strtoupper(system_showText(LANG_THREESTEPS));
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>
    <div class="claim-signup-breadcrumb">
        <div class="breadcrumb-item" is-active="true">
            <strong>1:</strong> <?=system_showText(LANG_ACCOUNTSIGNUP)?>
        </div>
        <div class="breadcrumb-item">
            <strong>2:</strong> <?=system_showText(LANG_LISTINGUPDATE)?>
        </div>
        <?php if (PAYMENT_FEATURE === 'on') { ?>
			<div class="breadcrumb-item">
				<strong>3:</strong> <?=system_showText(LANG_CHECKOUT)?>
			</div>
		<?php } ?>
    </div>
    <div class="modal-default modal-sign keep-style modal-sign-claim" is-page="true">
        <div class="modal-content">
            <div class="modal-nav text-center">
                <a href="#" class="heading modal-nav-link active" data-tab="sign-in"><?=system_showText(LANG_BUTTON_SIGNIN)?></a>
                <a href="#" class="heading modal-nav-link" data-tab="sign-up"><?=system_showText(LANG_BUTTON_SIGNUP)?></a>
                <div class="selected-arrow"></div>
            </div>
            <div class="modal-body">
                <div class="content-tab content-sign-in active" id="sign-in">
                    <?php if ($foreignaccount_google == "on" || FACEBOOK_APP_ENABLED == "on") { ?>
                    <div class="modal-social">
                    <?php
                        $redirectURI_params = [
                            "destiny" => "claim",
                            "claimlistingid" => $claimlistingid
                        ];
                        if (FACEBOOK_APP_ENABLED == "on") {
                            $fbLabel = 'Facebook';
                            include(INCLUDES_DIR."/forms/form_facebooklogin.php");
                            unset($fbLabel);
                        }

                        if ($foreignaccount_google == "on") {
                            $goLabel = 'Google';
                            include(INCLUDES_DIR."/forms/form_googlelogin.php");
                            unset($goLabel);
                        }
                    ?>
                    </div>
                    <span class="heading or-label"><?= system_showText(LANG_OR_SIGNUPEMAIL); ?></span>
                    <? } ?>
                    <form role="form" class="modal-form" name="formDirectory" method="post" action="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/login.php?destiny=<?=EDIRECTORY_FOLDER?>/<?=MEMBERS_ALIAS?>/claim/getlisting.php&amp;query=claimlistingid=<?=$claimlistingid?>">
                        <input type="hidden" name="claim" value="yes" />
                        <?
                        $members_section = true;
                        include(INCLUDES_DIR."/forms/form_login.php"); ?>
                    </form>
                </div>
                <div class="content-tab content-sign-up" id="sign-up">
                    <?php if ($foreignaccount_google == "on" || FACEBOOK_APP_ENABLED == "on") { ?>
                    <div class="modal-social">
                    <?php
                        if (FACEBOOK_APP_ENABLED == "on") {
                            $fbLabel = 'Facebook';
                            include(INCLUDES_DIR."/forms/form_facebooklogin.php");
                            unset($fbLabel);
                        }

                        if ($foreignaccount_google == "on") {
                            $goLabel = 'Google';
                            include(INCLUDES_DIR."/forms/form_googlelogin.php");
                            unset($goLabel);
                        }
                    ?>
                    </div>
                    <span class="heading or-label"><?= system_showText(LANG_OR_SIGNUPEMAIL); ?></span>
                    <?php } ?>
                    <form role="form" class="modal-form" name="signup_claim" method="post" action="<?=system_getFormAction($_SERVER["REQUEST_URI"])?>">
                        <input type="hidden" name="claim" value="true" />
                        <input type="hidden" name="claimlistingid" id="claimlistingid" value="<?=$claimlistingid?>" />
                        <?php
                            $claimSection = true;
                            include(INCLUDES_DIR."/forms/form_addaccount.php");
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?


    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    $claimPage = true;
    include(EDIRECTORY_ROOT."/frontend/footer.php");
