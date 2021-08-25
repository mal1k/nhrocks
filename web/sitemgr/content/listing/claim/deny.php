<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/listing/claim/deny.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include '../../../../conf/loadconfig.inc.php';

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (CLAIM_FEATURE !== 'on') { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track('Denied a claim');

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST, null);
	extract($_GET, null);

	$url_search_params = system_getURLSearchParams(($_POST ? : $_GET));

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$errorPage = DEFAULT_URL.'/'.SITEMGR_ALIAS.'/content/'.LISTING_FEATURE_FOLDER.'/claim/index.php?message='.$message."&screen=$screen&letter=$letter".($url_search_params ? "&$url_search_params" : '');
	if ($id) {
		$claim = new Claim($id);
		if ((!$claim->getNumber('id')) || ($claim->getNumber('id') <= 0)) {
			header('Location: '.$errorPage);
			exit;
		}
		if (!$claim->canDeny()) {
			header('Location: '.$errorPage);
			exit;
		}
	} else {
		header('Location: '.$errorPage);
		exit;
	}

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$claim->setString('status', 'denied');
	$claim->Save();

	$listing = new Listing($claim->getNumber('listing_id'));
	$listing->setString('account_id', null);
    if ($listing->countDeals($claim->getNumber('listing_id')) > 0){
        $listing->removePromotionLinks();
    }

	setting_get('claim_deny', $claim_deny);
	if ($claim_deny) {
		$listing->setString('renewal_date', date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+5)));
		$listing->setString('status', 'A');
	} else {
		$listing->setString('renewal_date', '0000-00-00');
		$listing->setString('status', 'P');
	}

	$listing->setString('location_1', $claim->getNumber('old_location_1'));
	$listing->setString('location_2', $claim->getNumber('old_location_2'));
	$listing->setString('location_3', $claim->getNumber('old_location_3'));
	$listing->setString('location_4', $claim->getNumber('old_location_4'));
	$listing->setString('location_5', $claim->getNumber('old_location_5'));
	$listing->setString('title', $claim->getString('old_title', false));
	$listing->setString('friendly_url', $claim->getString('old_friendly_url', false));
	$listing->setString('email', $claim->getString('old_email', false));
	$listing->setString('url', $claim->getString('old_url', false));
	$listing->setString('display_url', '');
	$listing->setString('phone', $claim->getString('old_phone', false));
	$listing->setString('label_additional_phone', $claim->getString('old_label_additional_phone', false));
	$listing->setString('additional_phone', $claim->getString('old_additional_phone', false));
	$listing->setString('address', $claim->getString('old_address', false));
	$listing->setString('address2', $claim->getString('old_address2', false));
	$listing->setString('zip_code', $claim->getString('old_zip_code', false));
	$listing->setString('level', $claim->getNumber('old_level'));
	$listing->setString('listingtemplate_id', ($claim->getNumber('old_listingtemplate_id') ?: null));
    $listing->setString('description', $claim->getString('old_description', false));
    $listing->setString('long_description', $claim->getString('old_long_description', false));
    $listing->setString('keywords', $claim->getString('old_keywords', false));
    $listing->setString('locations', $claim->getString('old_locations', false));
    $listing->setString('features', $claim->getString('old_features', false));
    $listing->setString('hours_work', $claim->getString('old_hours_work', false));
    $listing->setString('seo_title', $claim->getString('old_seo_title', false));
    $listing->setString('seo_keywords', $claim->getString('old_seo_keywords', false));
    $listing->setString('seo_description', $claim->getString('old_seo_description', false));
    $listing->setString('social_network', $claim->getString('old_social_network', false));
    $listing->setNumber('latitude', $claim->getString('old_latitude', false));
    $listing->setNumber('longitude', $claim->getString('old_longitude', false));

	//Revert Categories
    $oldCategories = $claim->getString('old_categories', false);
    $arrayOldCategories = (array)json_decode($oldCategories);
    if (is_array($arrayOldCategories)) {
        $listing->setCategories($arrayOldCategories);
    }

    //Revert Additional Fields
    $oldAdditionalFields = $claim->getString('old_additional_fields', false);
    $arrayOldFields = (array)json_decode($oldAdditionalFields);
    if (is_array($arrayOldFields)) {
        foreach ($arrayOldFields as $key => $value) {
            $listing->setString($key, $value);
        }
    }

	$listing->Save();

	$domain = new Domain( SELECTED_DOMAIN_ID );

    setting_get( 'claim_denyemail', $claim_denyemail );

    if ( $claim_denyemail )
    {
        $contact = new Contact( $claim->getNumber('account_id') );

        if ( $emailNotificationObj = system_checkEmail( SYSTEM_CLAIM_DENIED ) )
        {
            $subject = $emailNotificationObj->getString('subject');
            $subject = system_replaceEmailVariables( $subject, $listing->getNumber( 'id' ), 'listing' );
            $subject = html_entity_decode( $subject );

            $body = $emailNotificationObj->getString('body');
            $body = str_replace( 'ACCOUNT_NAME', $contact->getString('first_name').' '.$contact->getString('last_name'), $body );
            $body = system_replaceEmailVariables( $body, $listing->getNumber( 'id' ), 'listing' );
            $body = str_replace( ['DEFAULT_URL', $_SERVER['HTTP_HOST']], array(DEFAULT_URL, $domain->getString('url')),
                $body );
            $body = html_entity_decode( $body );

            SymfonyCore::getContainer()->get('core.mailer')
                ->newMail($subject, $body, $emailNotificationObj->getString( "content_type" ))
                ->setTo($contact->getString( "email" ))
                ->setBcc($emailNotificationObj->getString( "bcc" ))
                ->send();
        }
    }

    $message = 0;
    header('Location: '.DEFAULT_URL.'/'.SITEMGR_ALIAS.'/content/'.LISTING_FEATURE_FOLDER.'/claim/index.php?message='.$message."&screen=$screen&letter=$letter".($url_search_params ? "&$url_search_params" : ''));
	exit;
