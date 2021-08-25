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
	# * FILE: /sponsors/claim/listing.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_GET);
	extract($_POST);

    /* This was added here from code/listing.php because image cropping
     * was failing to bypass the Validate Feature session below. */


    if ( $_SERVER['REQUEST_METHOD'] == "POST" )
    {
        $url_base = "".DEFAULT_URL."/".MEMBERS_ALIAS."";
        NewImageUploader::treatPost($url_base, "Listing");
    }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSession();
	$acctId = sess_getAccountIdFromSession();
	$url_redirect = "".DEFAULT_URL."/".MEMBERS_ALIAS."/claim";
	$url_base = "".DEFAULT_URL."/".MEMBERS_ALIAS."";
	$members = 1;

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (CLAIM_FEATURE != "on") { exit; }
	if (!$claimlistingid) {
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
		exit;
	}
	$listingObject = new Listing($claimlistingid);
	if (!$listingObject->getNumber("id") || ($listingObject->getNumber("id") <= 0)) {
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
		exit;
	}
	if ($listingObject->getNumber("account_id") != $acctId) {
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
		exit;
	}

	$db = db_getDBObject(DEFAULT_DB, true);
	$dbObjClaim = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $db);
	$sqlClaim = "SELECT id FROM Claim WHERE account_id = '".$acctId."' AND listing_id = '".$claimlistingid."' AND status = 'progress' AND step = 'b' ORDER BY date_time DESC LIMIT 1";
	$resultClaim = $dbObjClaim->query($sqlClaim);
	if ($rowClaim = mysqli_fetch_assoc($resultClaim)) $claimID = $rowClaim["id"];
	if (!$claimID) {
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
		exit;
	}
	$claimObject = new Claim($claimID);
	if (!$claimObject->getNumber("id") || ($claimObject->getNumber("id") <= 0)) {
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
		exit;
	}

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$_POST["id"] = $claimlistingid;
	include(EDIRECTORY_ROOT."/includes/code/listing.php");

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");
	$cover_title = system_showText(LANG_LISTING_CLAIMING) .' "'. $listingObject->getString("title") .'"';
    $cover_subtitle = string_strtoupper(system_showText(LANG_EASYANDFAST)) .' '.string_strtoupper(system_showText(LANG_THREESTEPS));
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
 ?>
	<div class="members-page">
        <div class="container">
			<div class="claim-signup-breadcrumb">
				<div class="breadcrumb-item">
					<strong>1:</strong> <?=system_showText(LANG_LABEL_ACCOUNT_SIGNUP);?>
				</div>
				<div class="breadcrumb-item" is-active="true">
					<strong>2:</strong> <?=system_showText(LANG_LISTING_UPDATE);?>
				</div>
				<?php if (PAYMENT_FEATURE === 'on') { ?>
					<div class="breadcrumb-item">
						<strong>3:</strong> <?=system_showText(LANG_LABEL_CHECKOUT);?>
					</div>
				<?php } ?>
			</div>
			<br><br>
            <div class="members-wrapper">
                <div class="members-panel edit-panel">
                    <div class="panel-header">
                        <?=system_showText(LANG_LISTING_INFORMATION)?>
                    </div>
                    <div class="panel-body">
						<form name="listing" id="listing" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post" enctype="multipart/form-data">

							<input type="hidden" name="ieBugFix" value="1">

							<input type="hidden" name="process" id="process" value="claim">
							<input type="hidden" name="id" id="id" value="<?=$id?>">
							<input type="hidden" name="claimlistingid" id="claimlistingid" value="<?=$claimlistingid?>">
							<input type="hidden" name="claim_id" id="claim_id" value="<?=$claimID?>">
							<input type="hidden" name="listingtemplate_id" id="listingtemplate_id" value="<?=$listingtemplate_id?>">
							<input type="hidden" name="account_id" id="account_id" value="<?=$acctId?>">
							<input type="hidden" name="level" id="level" value="<?=$level?>">
							<input type="hidden" name="gallery_hash" value="<?=$gallery_hash?>">

							<? if ($message_listing) { ?>
								<div class="form-edit-alert">
									<?=$message_listing;?>
								</div>
							<? } ?>
							
							<div class="custom-edit-content" has-sidebar="true">
                                <? include(INCLUDES_DIR."/forms/form-listing.php"); ?>
							</div>
							
							<input type="hidden" name="ieBugFix2" value="1">

							<button class="button button-md is-primary action-save" type="button" onclick="JS_submit()">
								<?=system_showText(LANG_BUTTON_NEXT)?>
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

<?
	include(INCLUDES_DIR."/modals/modal-categoryselect.php");
	include(INCLUDES_DIR."/modals/modal-crop.php");

	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/modules.php";
	include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
