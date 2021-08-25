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
	# * FILE: /sponsors/event/order_package.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSession();

	extract($_GET);
	extract($_POST);

	if (!$level) {
        header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
        exit;
    }

	$url_redirect = "".DEFAULT_URL."/".MEMBERS_ALIAS;
	$url_base = "".DEFAULT_URL."/".MEMBERS_ALIAS."";
	$members = 1;

	/*
	 * Get packages
	 */
	$packageObj = new Package();
	$array_package_offers = $packageObj->getPackagesByDomainID(SELECTED_DOMAIN_ID, "event", $level);
	$next_page = $url_base."/".EVENT_FEATURE_FOLDER."/event.php?level=".$level;

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");

	# ----------------------------------------------------------------------------------------------------
	# NAVBAR
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/navbar.php");
    $cover_title = system_showText(LANG_INCREASE_VISIBILITY);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>
	<div class="members-page order-package-page">
        <div class="container">
            <div class="members-wrapper">
				<form name="package" method="get" action="<?=$next_page?>" style="width: 100%;">
					<? include(EDIRECTORY_ROOT."/includes/forms/form_orderpackage.php") ?>
				</div>
			</div>
		</div>
	</div>

<?
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
