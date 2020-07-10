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
	# * FILE: /sponsors/googleauth.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include '../conf/loadconfig.inc.php';

    setting_get('foreignaccount_google_clientid', $foreignaccount_google_clientid);
    setting_get('foreignaccount_google_clientsecret', $foreignaccount_google_clientsecret);

    if (isset($_GET['code']) && $foreignaccount_google_clientid && $foreignaccount_google_clientsecret) {

        // Call Google API
        $gClient = new Google_Client();
        $gClient->setApplicationName(EDIRECTORY_TITLE);
        $gClient->setClientId($foreignaccount_google_clientid);
        $gClient->setClientSecret($foreignaccount_google_clientsecret);
        $gClient->setRedirectUri(DEFAULT_URL.'/'.MEMBERS_ALIAS.'/googleauth.php');
        $gClient->addScope(['profile', 'email']);

        $gClient->fetchAccessTokenWithAuthCode($_GET['code']);

        $google_oauthV2 = new Google_Service_Oauth2($gClient);

        if ($gClient->getAccessToken()) {

            // Get user profile data from google
            $gpUserProfile = $google_oauthV2->userinfo->get();

            unset($userInfo);
            $userInfo['first_name'] = !empty($gpUserProfile['given_name']) ? $gpUserProfile['given_name'] : '';
            $userInfo['last_name'] = !empty($gpUserProfile['family_name']) ? $gpUserProfile['family_name'] : '';
            $userInfo['email'] = !empty($gpUserProfile['email']) ? $gpUserProfile['email'] : '';

            $URI_params = json_decode($_GET['state']);

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

            if (system_registerForeignAccount($userInfo, 'google', false, $email_notification)) {
                setcookie('uid', sess_getAccountIdFromSession(), time()+60*60*24*30, ''.EDIRECTORY_FOLDER.'/');
                if ($URI_params->destiny === 'claim' || $URI_params->destiny === 'advertise' || SOCIALNETWORK_FEATURE === 'off') {
                    $accObj = new Account(sess_getAccountIdFromSession());
                    if ($accObj->getString('is_sponsor') === 'n') {
                        $accObj->changeMemberStatus(true);
                    }

                    if ($URI_params->destiny === 'advertise') {
                        $itemID = $URI_params->item_id;
                        $item = $URI_params->advertise_item;
                        $redirectUrl = DEFAULT_URL.'/'.MEMBERS_ALIAS.'/'.constant(strtoupper($item).'_FEATURE_FOLDER')."/$item.php";

                        $level              = $_SESSION["go_{$item}_level_{$itemID}"];
                        $template           = $_SESSION["go_{$item}_template_id_{$itemID}"];
                        $title              = $_SESSION["go_{$item}_title_{$itemID}"];
                        $discount_id        = $_SESSION["go_{$item}_discount_id_{$itemID}"];
                        $return_categories  = $_SESSION["go_{$item}_return_categories_{$itemID}"];
                        $caption            = $_SESSION["go_{$item}_caption_{$itemID}"];
                        $package_id         = $_SESSION["go_{$item}_package_id_{$itemID}"];
                        $start_date         = $_SESSION["go_{$item}_start_date_{$itemID}"];
                        $end_date           = $_SESSION["go_{$item}_end_date_{$itemID}"];

                        unset(
                            $_SESSION["go_{$item}_level"],
                            $_SESSION["go_{$item}_template_id"],
                            $_SESSION["go_{$item}_title"],
                            $_SESSION["go_{$item}_discount_id"],
                            $_SESSION["go_{$item}_return_categories"],
                            $_SESSION["go_{$item}_caption"],
                            $_SESSION["go_{$item}_start_date"],
                            $_SESSION["go_{$item}_end_date"],
                            $_SESSION["go_{$item}_package_id"]
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

                /*
                 * Workaround for make a redeem without login
                 */
                if (isset($URI_params->review)) {
                    // Sets a cookie to use in font JS
                    setcookie('open_review', $URI_params->review, time() + 60 * 60, '/');
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

            }
        }
    }

    header('Location: '.DEFAULT_URL.'/'.MEMBERS_ALIAS.'/login.php?googleerror=cancel');
    exit;

