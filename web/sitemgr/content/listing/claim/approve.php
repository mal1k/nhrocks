<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/listing/claim/approve.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (CLAIM_FEATURE != "on") { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track("Approved a claim");

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

	$url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$errorPage = DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/claim/index.php??message=".$message."&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : "");
	if ($id) {
		$claim = new Claim($id);
		if ((!$claim->getNumber("id")) || ($claim->getNumber("id") <= 0)) {
			header("Location: ".$errorPage);
			exit;
		}
		if (!$claim->canApprove()) {
			header("Location: ".$errorPage);
			exit;
		}
	} else {
		header("Location: ".$errorPage);
		exit;
	}

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$claim->setString("status", "approved");
	$claim->save();

	$listing = new Listing($claim->getNumber("listing_id"));

	$image_id = $listing->getNumber("image_id");
	$galleryArray = $listing->getGalleries();
	system_renameGalleryImages($image_id, $claim->getNumber("account_id"), $galleryArray[0]);

	$domain = new Domain(SELECTED_DOMAIN_ID);
	setting_get("claim_approveemail", $claim_approveemail);

    setting_get( "listing_approve_updated", $listing_approve_updated );
    if (!$listing->needToCheckOut() && !$listing_approve_updated)
    {
        $listing->setString( "status", "A" );
        $listing->save();
    }

	if ( $claim_approveemail )
    {
        $contact              = new Contact( $claim->getNumber( "account_id" ) );

        if ( $emailNotificationObj = system_checkEmail( SYSTEM_CLAIM_APPROVED ) )
        {
            $domain  = new Domain( SELECTED_DOMAIN_ID );

            $subject = $emailNotificationObj->getString( "subject" );
            $subject = system_replaceEmailVariables( $subject, $listing->getNumber( 'id' ), 'listing' );
            $subject = html_entity_decode( $subject );

            $body    = $emailNotificationObj->getString( "body" );
            $body    = system_replaceEmailVariables( $body, $listing->getNumber( 'id' ), 'listing' );
            $body    = str_replace( $_SERVER["HTTP_HOST"], $domain->getString( "url" ), $body );
            $body    = str_replace( "DEFAULT_URL", DEFAULT_URL, $body );
            $body    = str_replace( $_SERVER["HTTP_HOST"], $domain->getstring( "url" ), $body );
            $body    = html_entity_decode( $body );

            SymfonyCore::getContainer()->get('core.mailer')
                ->newMail($subject, $body, $emailNotificationObj->getString( "content_type" ))
                ->setTo($contact->getString( "email" ))
                ->setBcc($emailNotificationObj->getString( "bcc" ))
                ->send();
        }
    }

    $message = 1;
    header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/claim/index.php??message=".$message."&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : ""));
	exit;
