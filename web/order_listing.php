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
    # * FILE: /order_listing.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include("./conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSessionFront();

    extract($_POST);
    extract($_GET);

    # ----------------------------------------------------------------------------------------------------
    # VALIDATE FEATURE
    # ----------------------------------------------------------------------------------------------------
    $listLevelObj = new ListingLevel();
    $listLevelValue = $listLevelObj->getValues();
    if (!in_array($level, $listLevelValue)) {
        header("Location: ".DEFAULT_URL."/".ALIAS_ADVERTISE_URL_DIVISOR."/");
        exit;
    }

    if (system_blockListingCreation()) {
        header("Location: ".DEFAULT_URL."/".ALIAS_CONTACTUS_URL_DIVISOR."/");
        exit;
    }

    if (sess_getAccountIdFromSession()) {

        $redirectFile = 'listing.php';
        if (LISTINGTEMPLATE_FEATURE == "on" && CUSTOM_LISTINGTEMPLATE_FEATURE == "on") {
            $dbObjLT = db_getDBObJect();
            $sql = "SELECT id FROM ListingTemplate WHERE status = 'enabled' AND editable ='n' LIMIT 1";
            $result = $dbObjLT->query($sql);
            $row = mysqli_fetch_assoc($result);
            $listingtemplate_id = $row["id"];

            $sql = "SELECT COUNT(id) AS total FROM ListingTemplate WHERE status = 'enabled' AND editable = 'y'";
            $result = $dbObjLT->query($sql);
            $row = mysqli_fetch_assoc($result);

            if ($row['total'] > 0) {
                $redirectFile = 'listinglevel.php';
            }
        }

        $accObj = new Account(sess_getAccountIdFromSession());
        $accObj->changeMemberStatus(true);

        header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/".LISTING_FEATURE_FOLDER."/".$redirectFile."?level=$level&listingtemplate_id=$listingtemplate_id");
        exit;
    }

    # ----------------------------------------------------------------------------------------------------
    # SUBMIT
    # ----------------------------------------------------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !system_isHoneypotFilled()) {

        $_POST["friendly_url"] = str_replace(".htm", "", $_POST["friendly_url"]);
        $_POST["friendly_url"] = str_replace(".html", "", $_POST["friendly_url"]);
        $_POST["friendly_url"] = trim($_POST["friendly_url"]);
        $_POST["friendly_url"] = system_denyInjections($_POST["friendly_url"]);

        if (!$_POST["friendly_url"]) {
            system_generateFriendlyURL($_POST["title"]);
        }

        $sqlFriendlyURL = "SELECT friendly_url FROM Listing WHERE friendly_url = ".db_formatString($_POST["friendly_url"])." LIMIT 1";

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObjFriendlyURL = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        $resultFriendlyURL = $dbObjFriendlyURL->query($sqlFriendlyURL);
        if (mysqli_num_rows($resultFriendlyURL) > 0) {
            $_POST["friendly_url"] = $_POST["friendly_url"].FRIENDLYURL_SEPARATOR.uniqid();
        }

        $friendly_url = $_POST["friendly_url"];
        $_POST["retype_password"] = $_POST["password"];

        $validate_account = validate_addAccount($_POST, $message_account);
        $validate_contact = validate_form("contact", $_POST, $message_contact);
        $tmpEMAIL = $_POST["email"];
        unset($_POST["email"]);

        $validate_listing = validate_form("listing", $_POST, $message_listing);

        $_POST["email"] = $tmpEMAIL;
        $validate_discount = is_valid_discount_code($_POST["discount_id"], "listing", $_POST["id"], $message_discount, $discount_error_num);

        if ($validate_account && $validate_contact && $validate_listing && $validate_discount) {

            $_POST['notify_traffic_listing'] = ($_POST['notify_traffic_listing'] ? 'y' : 'n');

            $account = new Account($_POST);
            $account->save();

            $account->changeMemberStatus(true);

            $contact = new Contact($_POST);
            $contact->setNumber("account_id", $account->getNumber("id"));
            $contact->save();

            $profileObj = new Profile($account->getNumber("id"));
            $profileObj->setNumber("account_id", $account->getNumber("id"));
            if (!$profileObj->getString("nickname")) {
                $profileObj->setString("nickname", $_POST["first_name"]." ".$_POST["last_name"]);
            }
            $profileObj->Save();

            $accDomain = new Account_Domain($account->getNumber("id"), SELECTED_DOMAIN_ID);
            $accDomain->Save();
            $accDomain->saveOnDomain($account->getNumber("id"), $account, $contact, $profileObj);

            if ($_POST["newsletter"]) {
                $_POST["name"] = $_POST["first_name"]." ".$_POST["last_name"];
                $_POST["type"] = "sponsor";
                arcamailer_addSubscriber($_POST, $success, $account->getNumber("id"));
            }

            unset($_POST["email"]);
            unset($_POST["phone"]);
            unset($_POST["address"]);
            unset($_POST["address2"]);
            $listing = new Listing($_POST);
            $listing->setNumber("account_id", $account->getNumber("id"));
            $status = new ItemStatus();
            $listing->setDate("renewal_date", "00/00/0000");

            /*
             * Used for package
             */
            if ($listing->getNumber("domain_id") == 0) {
                $listing->setNumber("domain_id", SELECTED_DOMAIN_ID);
            }

            $listing->Save();
            $return_categories_array = explode(",", $return_categories);
            $listing->setCategories($return_categories_array);

            $gallery = new Gallery($id);

            $aux = array("account_id" => 0, "title" => $_POST["title"], "entered" => "NOW()", "updated" => "now()");
            $gallery->makeFromRow($aux);
            $gallery->save();
            $listing->setGalleries($gallery->getNumber("id"));

            /**************************************************************************************************/
            /*                                                                                                */
            /* E-mail notify																				  */
            /*                                                                                                */
            /**************************************************************************************************/
            setting_get("sitemgr_send_email",$sitemgr_send_email);
            setting_get("sitemgr_email",$sitemgr_email);
            $sitemgr_emails = explode(",",$sitemgr_email);
            if ($sitemgr_emails[0]) $sitemgr_email = $sitemgr_emails[0];
            setting_get("sitemgr_account_email",$sitemgr_account_email);
            $sitemgr_account_emails = explode(",",$sitemgr_account_email);
            setting_get("sitemgr_listing_email",$sitemgr_listing_email);
            $sitemgr_listing_emails = explode(",",$sitemgr_listing_email);

            // sending e-mail to user //////////////////////////////////////////////////////////////////////////
            if ($emailNotificationObj = system_checkEmail(SYSTEM_LISTING_SIGNUP)) {
                $linkActivation = system_getAccountActivationLink($account->getNumber("id"));
                $subject = $emailNotificationObj->getString("subject");
                $body = $emailNotificationObj->getString("body");
                $login_info = trim(system_showText(LANG_LABEL_USERNAME)).": ".$_POST["username"];
                $login_info .= ($emailNotificationObj->getString("content_type") == "text/html"? "<br />": "\n");
                $login_info .= trim(system_showText(LANG_LABEL_PASSWORD)).": ".$_POST["password"];
                $body = str_replace("ACCOUNT_LOGIN_INFORMATION",$login_info,$body);
                $body = system_replaceEmailVariables($body, $listing->getNumber('id'), 'listing');
                $body = str_replace("LINK_ACTIVATE_ACCOUNT", $linkActivation, $body);
                $subject = system_replaceEmailVariables($subject, $listing->getNumber('id'), 'listing');
                $body = html_entity_decode($body);
                $subject = html_entity_decode($subject);
                $email = filter_var($contact->getString( "email" ), FILTER_VALIDATE_EMAIL);
                if($email){
                    SymfonyCore::getContainer()->get('core.mailer')
                        ->newMail($subject, $body, $emailNotificationObj->getString( "content_type" ))
                        ->setTo($email)
                        ->setBcc($emailNotificationObj->getString( "bcc" ))
                        ->send();
                }
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////

            // site manager warning message /////////////////////////////////////
            $emailSubject = "[".EDIRECTORY_TITLE."] ".system_showText(LANG_NOTIFY_SIGNUPLISTING);
            $sitemgr_msg = system_showText(LANG_LABEL_SITE_MANAGER).",<br /><br />".system_showText(LANG_NOTIFY_SIGNUPLISTING_1)."<br /><br />".system_showText(LANG_LABEL_ACCOUNT).":<br /><br />";
            $sitemgr_msg .= "<strong>".system_showText(LANG_LABEL_USERNAME2).": </strong>".system_showAccountUserName($account->getString("username"))."<br />";
            $sitemgr_msg .= "<strong>".system_showText(LANG_LABEL_FIRST_NAME).": </strong>".$contact->getString("first_name")."<br />";
            $sitemgr_msg .= "<strong>".system_showText(LANG_LABEL_LAST_NAME).": </strong>".$contact->getString("last_name")."<br />";
            $sitemgr_msg .= "<strong>".system_showText(LANG_LABEL_COMPANY).": </strong>".$contact->getString("company")."<br />";
            $sitemgr_msg .= "<strong>".system_showText(LANG_LABEL_ADDRESS).": </strong>".$contact->getString("address")." ".$contact->getString("address2")."<br />";
            $sitemgr_msg .= "<strong>".system_showText(LANG_LABEL_CITY).": </strong>".$contact->getString("city")."<br />";
            $sitemgr_msg .= "<strong>".system_showText(LANG_LABEL_STATE).": </strong>".$contact->getString("state")."<br />";
            $sitemgr_msg .= "<strong>".ucfirst(system_showText(ZIPCODE_LABEL)).": </strong>".$contact->getString("zip")."<br />";
            $sitemgr_msg .= "<strong>".system_showText(LANG_LABEL_COUNTRY).": </strong>".$contact->getString("country")."<br />";
            $sitemgr_msg .= "<strong>".system_showText(LANG_LABEL_PHONE).": </strong>".$contact->getString("phone")."<br />";
            $sitemgr_msg .= "<strong>".system_showText(LANG_LABEL_EMAIL).": </strong>".$contact->getString("email")."<br />";
            $sitemgr_msg .= "<br /><a href=\"".DEFAULT_URL."/".SITEMGR_ALIAS."/account/sponsor/sponsor.php?id=".$account->getNumber("id")."\" target=\"_blank\">".DEFAULT_URL."/".SITEMGR_ALIAS."/account/sponsor/sponsor.php?id=".$account->getNumber("id")."</a><br /><br />";
            $sitemgr_msg .= "".system_showText(LANG_LISTING_FEATURE_NAME).":<br /><br />";
            $sitemgr_msg .= "<strong>".system_showText(LANG_LABEL_TITLE).": </strong>".$listing->getString("title")."<br />";
            $sitemgr_msg .= "<br /><a href=\"".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/listing.php?id=".$listing->getNumber("id")."\" target=\"_blank\">".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/listing.php?id=".$listing->getNumber("id")."</a><br /><br />";

            setting_get("new_listing_email", $new_listing_email);

            if ($new_listing_email) {
                system_notifySitemgr($sitemgr_account_emails, $emailSubject, $sitemgr_msg, true, true, $sitemgr_listing_emails);
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////


            if ($checkout) $payment_method = "checkout";

            sess_registerAccountInSession($account->getString("username"));
            setcookie("username_members", $account->getString("username"), time()+60*60*24*30, "".EDIRECTORY_FOLDER."/");
            setcookie("automatic_login_members", "false", time()+60*60*24*30, "".EDIRECTORY_FOLDER."/");

            $host = string_strtoupper(str_replace("www.", "", $_SERVER["HTTP_HOST"]));

            setcookie($host."_DOMAIN_ID_MEMBERS", SELECTED_DOMAIN_ID, time()+60*60*24*30, "".EDIRECTORY_FOLDER."/");

            //Check if a package was bought
            $queryPackage = "";

            if ($_POST["using_package"] == "y") {

                //Check if exists package
                $packageObj = new Package();
                $array_package_offers = $packageObj->getPackagesByDomainID(SELECTED_DOMAIN_ID, "listing", $listing->level);

                if ((is_array($array_package_offers)) and (count($array_package_offers) > 0) and $array_package_offers[0]) {

                    unset($array_info_package);
                    $array_info_package["item_type"]		= "listing";
                    $array_info_package["item_id"]			= $listing->getNumber("id");
                    $array_info_package["item_name"]		= $listing->getString("title");
                    $array_info_package["item_friendly_ur"]	= $listing->getString("friendly_url");
                    $array_info_package["package_id"][0]	= $aux_package_id;
                    $package_id = package_buying_package($array_info_package, true);
                    $queryPackage = "&ispackage=true&package_id=$package_id";

                }
            }

            setting_get("listing_approve_free", $listing_approve_free);

            if ($payment_method == "checkout" && !$listing_approve_free){
                $listing->setString("status", "A");
                $listing->save();
            }

            if ($payment_method == "checkout") {
                $redirectURL = DEFAULT_URL."/".MEMBERS_ALIAS."/".LISTING_FEATURE_FOLDER."/listing.php?id=".$listing->getNumber("id")."&process=signup";
            } elseif ($payment_method == "invoice") {
                $redirectURL = DEFAULT_URL."/".MEMBERS_ALIAS."/signup/invoice.php".($queryPackage ? "?".$queryPackage : "");
            } else {
                $redirectURL = DEFAULT_URL."/".MEMBERS_ALIAS."/signup/payment.php?payment_method=".$payment_method.$queryPackage;
            }

            /* ModStores Hooks */
            HookFire("orderlisting_before_redirect", [
                "account"    => &$account,
                "contact"    => &$contact,
                "profileObj" => &$profileObj,
                "accDomain"  => &$accDomain,
                "listing"    => &$listing
            ]);

            header("Location: ".$redirectURL);
            exit;

        } else {

            if (($pos = string_strrpos($_POST["friendly_url"], FRIENDLYURL_SEPARATOR)) !== false) {
                $_POST["friendly_url"] = string_substr($_POST["friendly_url"], 0, $pos);
            }

            // removing slashes added if required
            $_POST = format_magicQuotes($_POST);
            $_GET  = format_magicQuotes($_GET);
            extract($_POST);
            extract($_GET);

        }

    }

    # ----------------------------------------------------------------------------------------------------
    # CODE
    # ----------------------------------------------------------------------------------------------------
    $dbObjLT = db_getDBObJect();

    if ($return_categories) {
        $return_categories_array = explode(",", $return_categories);
        if ($return_categories_array) {
            foreach ($return_categories_array as $each_category) {
                $categories[] = new ListingCategory($each_category);
            }
        }
    }

    $feedDropDown = "<select name=\"feed\" id=\"feed\" multiple=\"multiple\" size=\"5\">";
    $catSelected = 0;
    if ($categories) {

        $auxListing = new ListingCategory();

        foreach ($categories as $category) {

            if ($category instanceof $auxListing) {
                $name = $category->getString("title");
                $feedDropDown .= "<option value='".$category->getNumber("id")."'>$name</option>";
                $feedAjaxCategory[] = $category->getNumber("id");
                $catSelected++;
            }
        }
    } else {
        $feedDropDown .= "<option value='empty'>&nbsp;</option>";
    }

    $feedDropDown .= "</select>";

    $listingLevelObj = new ListingLevel();
    $levelValue = $listingLevelObj->getValues();

    $formloginaction = DEFAULT_URL."/".MEMBERS_ALIAS."/login.php?destiny=".EDIRECTORY_FOLDER."/".MEMBERS_ALIAS."/".LISTING_FEATURE_FOLDER."/listing.php";

    /*
     * TAX SECTION
     */
    setting_get("payment_tax_status", $payment_tax_status);
    setting_get("payment_tax_value", $payment_tax_value);
    setting_get("payment_tax_label", $payment_tax_label);

    unset($googleEnabled, $facebookEnabled);

    setting_get("foreignaccount_google", $foreignaccount_google);
    if ($foreignaccount_google == "on") {
        $googleEnabled = true;
    }

    if (FACEBOOK_APP_ENABLED == "on") {
        $facebookEnabled = true;
    }

    $unique_id = system_generatePassword();

    //Listing Type vars - JS function and Dropdown
    $jsVarsType = "";
    if (LISTINGTEMPLATE_FEATURE == "on" && CUSTOM_LISTINGTEMPLATE_FEATURE == "on") {
        $sqlLT = "SELECT id FROM ListingTemplate WHERE status = 'enabled' ORDER BY editable, title";
        $resultLT = $dbObjLT->query($sqlLT);
        $jsVarsType .= "var title_template_0 = '".system_showText(LANG_LABEL_TITLE)."';\n";
        while ($rowLT = mysqli_fetch_assoc($resultLT)) {
            $listingtemplate = new ListingTemplate($rowLT["id"]);
            $template_title_field = $listingtemplate->getListingTemplateFields("title");
            $jsVarsType .= "var title_template_".$listingtemplate->getNumber("id")." = '".addslashes(($template_title_field !== false) ? $template_title_field[0]["label"] : system_showText(LANG_LABEL_TITLE))."';\n";
        }
    }

    $checkoutpayment_class = "isHidden";
    $checkoutfree_class = "isHidden";

    $labelName = str_replace("[level]", $listingLevelObj->showLevel($level), LANG_ADVERTISE_LISTINGLEVEL);

    if (LISTINGTEMPLATE_FEATURE == "on" && CUSTOM_LISTINGTEMPLATE_FEATURE == "on") {

        if (!$listingtemplate_id) {
            $sql = "SELECT id FROM ListingTemplate WHERE status = 'enabled' AND editable ='n' LIMIT 1";
            $result = $dbObjLT->query($sql);
            $row = mysqli_fetch_assoc($result);
            $listingtemplate_id = $row["id"];
        }

        if (system_showListingTypeDropdown($listingtemplate_id)) {

            $listingTypeOptions = "";
            $sqlLT = "SELECT id FROM ListingTemplate WHERE status = 'enabled' ORDER BY editable, title";
            $resultLT = $dbObjLT->query($sqlLT);
            while ($rowLT = mysqli_fetch_assoc($resultLT)) {
                $listingtemplate = new ListingTemplate($rowLT["id"]);
                $listingTypeOptions .= "<option value=\"" . $listingtemplate->getNumber("id") . "\"";
                if ($listingtemplate_id == $listingtemplate->getNumber("id")) {
                    $listingTypeOptions .= " selected";
                }
                $listingTypeOptions .= ">" . $listingtemplate->getString("title");
                if ($listingtemplate->getString("price") > 0) {
                    $listingTypeOptions .= " (+" . PAYMENT_CURRENCY_SYMBOL . $listingtemplate->getString("price") . ")";
                }
                $listingTypeOptions .= "</option>";
            }

        }

        if ($listingtemplate_id) {
            $templateObj = new ListingTemplate($listingtemplate_id);
            if ($templateObj && $templateObj->getString("status")=="enabled") {
                $template_title_field = $templateObj->getListingTemplateFields("title");
            }
        } else {
            $template_title_field = false;
        }
    } else {
        $template_title_field = false;
    }

    $advertiseItem = "listing";

    //Check if exists package
    $packageObj = new Package();
    $array_package_offers = $packageObj->getPackagesByDomainID(SELECTED_DOMAIN_ID, "listing", $level);
    $hasPackage = false;
    if ((is_array($array_package_offers)) && (count($array_package_offers) > 0) && $array_package_offers[0]) {
        $hasPackage = true;
    }

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    $headertag_title = $headertagtitle;
    $headertag_description = $headertagdescription;
    $headertag_keywords = $headertagkeywords;
    include(EDIRECTORY_ROOT."/frontend/header.php");

    $cover_title = system_showText(LANG_MENU_ADVERTISE) .' - '. ucwords($labelName);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");

    include(INCLUDES_DIR."/forms/form_advertise.php");

    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/frontend/footer.php");