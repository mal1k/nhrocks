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
	# * FILE: /sponsors/transactions/view.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (PAYMENT_FEATURE != "on") { exit; }
	if ((CREDITCARDPAYMENT_FEATURE != "on") && (PAYMENT_MANUAL_STATUS != "on")) { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSession();
	$acctId = sess_getAccountIdFromSession();

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	if ($cart_id = $_GET['id']) {

		$url_redirect = "".DEFAULT_URL."/".MEMBERS_ALIAS."/transactions";
		$url_base = "".DEFAULT_URL."/".MEMBERS_ALIAS."";

		include(INCLUDES_DIR."/code/transaction.php");

	} else {
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/transactions/index.php");
		exit;
	}

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");
	$cover_title = system_showText(LANG_TRANSACTION_INFO);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>

	<section class="block">
		<div class="container">

			<? include(MEMBERS_EDIRECTORY_ROOT."/layout/navbar.php"); ?>

			<div class="well">

				<?
				if (is_array($transaction) && (is_array($transaction_listing_log) || is_array($transaction_event_log) || is_array($transaction_banner_log) || is_array($transaction_classified_log) || is_array($transaction_article_log) || is_array($transaction_custominvoice_log) || is_array($transaction_package_log))) {
					include_once(EDIRECTORY_ROOT."/includes/views/view_transaction_detail.php");
				} else { ?>
				<p class="alert alert-info"><?=system_showText(LANG_TRANSACTION_NOT_FOUND_FOR_ACCOUNT)?><p>
					<? } ?>

			</div>
		</div>
	</section>

<?
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
