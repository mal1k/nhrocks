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
	# * FILE: /sponsors/claim/listinglevel.php
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

	if (!is_array($listingObject->getGalleries())) {
		$gallery = new Gallery();
		$aux = array("account_id"=>0,"title"=>$listingObject->getString("title"),"entered"=>"NOW()","updated"=>"now()");
		$gallery->makeFromRow($aux);
		$gallery->save();
		$listingObject->setGalleries($gallery->getNumber("id"));
	}

	
	$db = db_getDBObject(DEFAULT_DB, true);
	$dbObjClaim = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $db);
	$sqlClaim = "SELECT id FROM Claim WHERE account_id = '".$acctId."' AND listing_id = '".$claimlistingid."' AND status = 'progress' AND step = 'a' ORDER BY date_time DESC LIMIT 1";
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
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
	if ($_SERVER['REQUEST_METHOD'] == "POST") {

        if (!$level) {
            header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
            exit;
        }

		$status = new ItemStatus();
		$listingObject->setDate("renewal_date", "00/00/0000");
		$listingObject->setString("status", $status->getDefaultStatus());
		$listingObject->setString("level", $_POST["level"]);
		$listingObject->setNumber("listingtemplate_id", $_POST["listingtemplate_id"]);
		$listingObject->save();
		$claimObject->setString("step", "b");
		$claimObject->save();
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/claim/listing.php?claimlistingid=".$claimlistingid);
		exit;
	}

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$listing = $listingObject;
	$listing->extract();
	$levelObj = new ListingLevel();
    if ($level) {
		$levelArray[$levelObj->getLevel($level)] = $level;
	} else {
		$levelArray[$levelObj->getLevel($levelObj->getDefaultLevel())] = $levelObj->getDefaultLevel();
	}

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");
	$cover_title = system_showText(LANG_LISTING_CLAIMING) .' "'. $listingObject->getString("title") .'"';
    $cover_subtitle = string_strtoupper(system_showText(LANG_EASYANDFAST)) .' '.string_strtoupper(system_showText(LANG_THREESTEPS));
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>
	<div class="custom-level-price" data-bg="neutral">
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
		<br>
		<br>
		<form name="listinglevel" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
			<input type="hidden" name="claimlistingid" value="<?=$claimlistingid?>">
			<? include(INCLUDES_DIR."/forms/form_listinglevel.php"); ?>
			<br>
			<div class="level-price-actions">
				<button class="button button-md is-primary" id="buttonContinue" type="submit"><?=system_showText(LANG_BUTTON_NEXT)?></button>
			</div>
		</form>
	</div>
<?
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
