<?

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
# * FILE: /includes/code/mailappsignup.php
# ----------------------------------------------------------------------------------------------------

$arcamailerService = SymfonyCore::getContainer()->get('arcamailer.service');

# ----------------------------------------------------------------------------------------------------
# SUBMIT
# ----------------------------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($disconnet === 'yes') {
        $arcamailerService->logout();

        header('Location: '.DEFAULT_URL.'/'.SITEMGR_ALIAS.'/promote/newsletter/index.php?messageDisconnect=1#account');
        exit;
    }

    if (validate_form('mailapp_signup', $_POST, $message_mailapp)) {

        //Create/Connect account
        if ($actionForm === 'newAcc') {

            try {
                if ($account_type === 'new') {
                    $arcamailerService->register($edir_name, $edir_email, $edir_country, $edir_timezone);
                } else {
                    $arcamailerService->login($arcamailer_username, $arcamailer_password);
                }

                header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/promote/newsletter/index.php?".($member_type == "existing" ? "messageConnect" : "messageSignup")."=1#account");
                exit;
            } catch (\Exception $e) {
                $message_mailapp = $e->getMessage();
            }
        } else {

            //Update list
            if ($edir_list_id) {

                header('Location: '.DEFAULT_URL.'/'.SITEMGR_ALIAS.'/promote/newsletter/index.php?messageUpdate=1#newsletter');
                exit;

                //Create list
            } else {

                try {
                    $arcamailerService->createList($edir_list);

                    header('Location: '.DEFAULT_URL.'/'.SITEMGR_ALIAS.'/promote/newsletter/index.php?messageNewList=1#newsletter');
                    exit;
                } catch (\Exception $e) {
                    $message_mailapp = $e->getMessage();
                }
            }
        }
    }

    // removing slashes added if required
    $_POST = format_magicQuotes($_POST);
    $_GET = format_magicQuotes($_GET);

    extract($_POST);
    extract($_GET);
}

# ----------------------------------------------------------------------------------------------------
# FORMS DEFINES
# ----------------------------------------------------------------------------------------------------
setting_get('arcamailer_customer_id', $edir_customer_id);
setting_get('arcamailer_customer_name', $edir_name);
setting_get('arcamailer_customer_email', $edir_email);
setting_get('arcamailer_customer_country', $edir_country);
setting_get('arcamailer_customer_timezone', $edir_timezone);
setting_get('arcamailer_customer_listname', $edir_list);
setting_get('arcamailer_customer_listid', $edir_list_id);

//Prepare dropdowns
$return = $arcamailerService->getInfo();

if (is_array($return['timezones'])) {
    foreach ($return['timezones'] as $timezone) {
        $timezoneOptions .= "\n<option value=\"".$timezone.'" '.($timezone == $edir_timezone ? 'selected="selected"' : '').">$timezone</option>";
    }
}

if (is_array($return['contries'])) {
    foreach ($return['contries'] as $country) {
        $countryOptions .= "\n<option value=\"".$country.'" '.($country == $edir_country ? 'selected="selected"' : '').">$country</option>";
    }
}

