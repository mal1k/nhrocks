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
	# * FILE: /sponsors/classified/classifiedlevel.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (CLASSIFIED_FEATURE != "on" || CUSTOM_CLASSIFIED_FEATURE != "on") { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSession();
	$acctId = sess_getAccountIdFromSession();

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_GET);
	extract($_POST);

	$url_redirect = "".DEFAULT_URL."/".MEMBERS_ALIAS;
	$url_base = "".DEFAULT_URL."/".MEMBERS_ALIAS."";
	$members = 1;

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	if ($id) {
		$classified = new Classified($id);
		if (sess_getAccountIdFromSession() != $classified->getNumber("account_id")) {
			header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
			exit;
		}
		$classified->extract();
	}

	$levelObj = new ClassifiedLevel();
	if ($level) {
		$levelArray[$levelObj->getLevel($level)] = $level;
	} else {
		$levelArray[$levelObj->getLevel($levelObj->getDefaultLevel())] = $levelObj->getDefaultLevel();
	}

	# ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		if (($id) && ($classified)) {
			if ($_POST["level"] && ($_POST["level"] != $classified->getNumber("level"))) {
				$status = new ItemStatus();
				$classified->setString("status", $status->getDefaultStatus());
				$classified->setDate("renewal_date", "00/00/0000");
			}
			$classified->setString("level", $_POST['level']);
			$classified->Save();
			header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
			exit;
		} else {
			/*
			 * Check if exists package
			 */
            if (!$level) {
                header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
                exit;
            }
			$packageObj = new Package();
			$array_package_offers = $packageObj->getPackagesByDomainID(SELECTED_DOMAIN_ID, "classified", $_POST["level"]);
			if ((is_array($array_package_offers)) and (count($array_package_offers)>0) and $array_package_offers[0]) {
				header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/".CLASSIFIED_FEATURE_FOLDER."/order_package.php?level=".$_POST["level"]);
			}else{
				header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/".CLASSIFIED_FEATURE_FOLDER."/classified.php?level=".$_POST["level"]);
			}
			exit;
		}
	}

    if (system_blockListingCreation($id)) {
        header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
        exit;
    }

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");

	# ----------------------------------------------------------------------------------------------------
	# NAVBAR
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/navbar.php");
	$cover_title = system_showText(LANG_ADD) ." ". system_showText(LANG_CLASSIFIED_FEATURE_NAME) ." - ". system_showText(LANG_LABEL_PRICE_PLURAL);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>

	<div class="custom-level-price" data-bg="neutral">
		<form name="classifiedlevel" action="<?= system_getFormAction($_SERVER["PHP_SELF"]) ?>" method="post" 	enctype="multipart/form-data">
			<input type="hidden" name="id" value="<?= $id ?>">
			<? include(INCLUDES_DIR."/forms/form_classifiedlevel.php"); ?>
		</form>
		<form action="<?= DEFAULT_URL ?>/<?= MEMBERS_ALIAS ?>/" method="get" class="level-price-actions">
			<div class="hidden">
				<button class="button button-md is-outline" type="submit"><?= system_showText(LANG_BUTTON_CANCEL) ?></button>
				<button class="button button-md is-primary" id="buttonContinue" type="button" onclick="document.classifiedlevel.submit();"><?= system_showText(LANG_BUTTON_CONTINUE) ?></button>
			</div>
		</form>
	</div>

    <?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
