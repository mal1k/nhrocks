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
# * FILE: /sponsors/facebookauth.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include '../conf/loadconfig.inc.php';

$URI_params = json_decode($_GET['state']);

require_once CLASSES_DIR.'/class_FacebookLogin.php';
$fbLogin = new FacebookLogin();

try {
    $accessToken = $fbLogin->helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    $fbLogin->handleError('Graph returned an error: ' . $e->getMessage());
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    $fbLogin->handleError('Facebook SDK returned an error: ' . $e->getMessage());
}

if (! isset($accessToken)) {
    if ($fbLogin->helper->getError()) {
        header('HTTP/1.0 401 Unauthorized');
        $errorMsg = 'Error: '. $fbLogin->helper->getError() . "\n";
        $errorMsg .=  'Error Code: '. $fbLogin->helper->getErrorCode() . "\n";
        $errorMsg .=   'Error Reason: '. $fbLogin->helper->getErrorReason() . "\n";
        $errorMsg .=   'Error Description: '. $fbLogin->helper->getErrorDescription() . "\n";
        $fbLogin->handleError($errorMsg);
    } else {
        $fbLogin->handleError('Bad request', true);
    }
}

// The OAuth 2.0 client handler helps us manage access tokens
$oAuth2Client = $fbLogin->fb->getOAuth2Client();

// Get the access token metadata from /debug_token
$tokenMetadata = $oAuth2Client->debugToken($accessToken);

// Validation (these will throw FacebookSDKException's when they fail)
$tokenMetadata->validateAppId(FACEBOOK_API_ID);

$tokenMetadata->validateExpiration();

if (! $accessToken->isLongLived()) {
    // Exchanges a short-lived access token for a long-lived one
    try {
        $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        echo '<p>Error getting long-lived access token: '. $e->getMessage() . "</p>\n\n";
        exit;
    }
}

$_SESSION['fb_access_token'] = (string) $accessToken;

$fbLogin->getUserInfo($userInfo);

/*
 * Attach existing account to a Facebook account
 */

if ($URI_params->destiny === 'attach_account') {
    $sql = 'SELECT account_id FROM Profile WHERE facebook_uid = '. db_formatNumber($userInfo['uid']) .' AND account_id <> '. $URI_params->edir_account;
    $db = db_getDBObject(DEFAULT_DB, true);
    $result = $db->query($sql);
    $enableAttach = true;

    $denyUrl = EDIRECTORY_FOLDER .'/'. SOCIALNETWORK_FEATURE_NAME .'/index.php?error=disableAttach';

    if (mysqli_num_rows($result) > 0) {
        $redirectUrl = $denyUrl;
        $enableAttach = false;
    } else {
        $userInfo['account_id'] = $URI_params->edir_account;
        $userInfo['facebook_action'] = 'facebook_import';
    }
}

/*
* Get user id (sitemgr section)
*/
if ($URI_params->destiny === 'sitemgr') {
    $redirectUrl = DEFAULT_URL.'/'.SITEMGR_ALIAS.'/configuration/networking/index.php?user_id='. $userInfo['uid'];
} elseif (($URI_params->destiny === 'attach_account' && $enableAttach) || $URI_params->destiny !== 'attach_account') {

    if ($URI_params->destiny === 'claim' || $URI_params->destiny === 'advertise') {
        if ($URI_params->advertise_item === LISTING_FEATURE_FOLDER) {
            $email_notification = SYSTEM_LISTING_SIGNUP;
        } else if ($URI_params->advertise_item === ARTICLE_FEATURE_FOLDER) {
            $email_notification = SYSTEM_ARTICLE_SIGNUP;
        } else if ($URI_params->advertise_item === EVENT_FEATURE_FOLDER) {
            $email_notification = SYSTEM_EVENT_SIGNUP;
        } else if ($URI_params->advertise_item === CLASSIFIED_FEATURE_FOLDER) {
            $email_notification = SYSTEM_CLASSIFIED_SIGNUP;
        } else if ($URI_params->advertise_item === BANNER_FEATURE_FOLDER) {
            $email_notification = SYSTEM_BANNER_SIGNUP;
        } else if ($URI_params->advertise_item === ALIAS_CLAIM_URL_DIVISOR) {
            $email_notification = SYSTEM_CLAIM_SIGNUP;
        } else {
            $email_notification = SYSTEM_NEW_PROFILE;
        }
    } else {
        $email_notification = SYSTEM_NEW_PROFILE;
    }

    if (system_registerForeignAccount($userInfo, 'facebook', ($URI_params->destiny === 'attach_account' ? true : false), $email_notification)) {
        setcookie('uid', sess_getAccountIdFromSession(), time() + 60 * 60 * 24 * 30, ''.EDIRECTORY_FOLDER.'/');
        if ($URI_params->destiny === 'claim' || $URI_params->destiny === 'advertise' || SOCIALNETWORK_FEATURE === 'off') {
            $accObj = new Account(sess_getAccountIdFromSession());
            if ($accObj->getString('is_sponsor') === 'n') {
                $accObj->changeMemberStatus(true);
            }

            if ($URI_params->destiny === 'advertise') {
                $itemID = $URI_params->item_id;
                $item = $URI_params->advertise_item;
                $redirectUrl = DEFAULT_URL.'/'.MEMBERS_ALIAS.'/'.constant(strtoupper($item).'_FEATURE_FOLDER')."/$item.php";

                $level = $_SESSION["fb_{$item}_level_{$itemID}"];
                $template = $_SESSION["fb_{$item}_template_id_{$itemID}"];
                $title = $_SESSION["fb_{$item}_title_{$itemID}"];
                $discount_id = $_SESSION["fb_{$item}_discount_id_{$itemID}"];
                $return_categories = $_SESSION["fb_{$item}_return_categories_{$itemID}"];
                $caption = $_SESSION["fb_{$item}_caption_{$itemID}"];
                $package_id = $_SESSION["fb_{$item}_package_id_{$itemID}"];
                $start_date = $_SESSION["fb_{$item}_start_date_{$itemID}"];
                $end_date = $_SESSION["fb_{$item}_end_date_{$itemID}"];

                unset(
                    $_SESSION["fb_{$item}_level"],
                    $_SESSION["fb_{$item}_template_id"],
                    $_SESSION["fb_{$item}_title"],
                    $_SESSION["fb_{$item}_discount_id"],
                    $_SESSION["fb_{$item}_return_categories"],
                    $_SESSION["fb_{$item}_caption"],
                    $_SESSION["fb_{$item}_start_date"],
                    $_SESSION["fb_{$item}_end_date"],
                    $_SESSION["fb_{$item}_package_id"]
                );

                if ($item === 'banner') {
                    $redirectUrl .= '?type='.$level;
                    $redirectUrl .= '&caption='.$caption;
                } elseif ($item === 'listing') {
                    $redirectUrl .= '?level='.$level;
                    if ($template) {
                        $redirectUrl .= '&listingtemplate_id='.$template;
                    }
                    if ($return_categories) {
                        $redirectUrl .= '&return_categories='.$return_categories;
                    }
                } elseif ($item === 'event') {
                    $redirectUrl .= '?level='.$level;
                    if ($start_date) {
                        $redirectUrl .= '&start_date='.$start_date;
                    }
                    if ($end_date) {
                        $redirectUrl .= '&end_date='.$end_date;
                    }
                } else {
                    $redirectUrl .= '?level='.$level;
                }

                if ($title) {
                    $redirectUrl .= '&title='.$title;
                }
                if ($discount_id) {
                    $redirectUrl .= '&discount_id='.$discount_id;
                }
                if ($package_id) {
                    $redirectUrl .= '&package_id='.$package_id;
                }
            }
        }
    }
}

/*
 * Workaround to pin a bookmark without login
 */
if (isset($URI_params->bookmark_remember)) {
    // Sets a cookie to use in font JS
    setcookie('open_bookmark', $URI_params->bookmark_remember, time() + 60 * 60, '/');
}

/*
 * Workaround for make a redeem without login
 */
if (isset($URI_params->redeem_remember)) {
    // Sets a cookie to use in font JS
    setcookie('open_redeem', $URI_params->redeem_remember, time() + 60 * 60, '/');
}

// Opens modal automatically
$_SESSION['_sf2_attributes']['modal'] = 1;

if (empty($redirectUrl)) {

    switch ($URI_params->destiny) {
        case 'profile':
            $redirectUrl = DEFAULT_URL . '/' . SOCIALNETWORK_FEATURE_NAME;
            break;
        case 'claim':
            $redirectUrl = DEFAULT_URL.'/'.MEMBERS_ALIAS.'/claim/getlisting.php?claimlistingid='.$URI_params->claimlistingid;
            break;
        case 'attach_account':
            $redirectUrl = DEFAULT_URL.'/'.SOCIALNETWORK_FEATURE_NAME.'/index.php?facebookattached';
            break;
        case 'referer':
            $redirectUrl = ($URI_params->referer ?: DEFAULT_URL . '/' . SOCIALNETWORK_FEATURE_NAME);
            break;
        case 'sponsors':
            $redirectUrl = DEFAULT_URL.'/'.MEMBERS_ALIAS.'/';
            break;
        default: $redirectUrl = DEFAULT_URL . '/' . SOCIALNETWORK_FEATURE_NAME;
    }
}

header('Location: '. $redirectUrl);
exit;
