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
	# * FILE: /sponsors/billing/pay.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (PAYMENT_FEATURE != "on") { exit; }
	if ((CREDITCARDPAYMENT_FEATURE != "on") && (PAYMENT_INVOICE_STATUS != "on")) { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSession();
	$acctId = sess_getAccountIdFromSession();
	$url_redirect = "".DEFAULT_URL."/".MEMBERS_ALIAS."/billing";
	$url_base = "".DEFAULT_URL."/".MEMBERS_ALIAS."";

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$second_step = $_POST["second_step"] ? $_POST["second_step"] : $_GET["second_step"];
	if (!$second_step) {
		header("Location: ".$url_base."/billing/index.php");
		exit;
	}

	setting_get("payment_tax_status", $payment_tax_status);
	setting_get("payment_tax_value", $payment_tax_value);
	setting_get("payment_tax_label", $payment_tax_label);

	include(INCLUDES_DIR."/code/billing.php");

    /* ModStores Hooks */
    HookFire("billingpay_before_render_page", [
        "bill_info" => &$bill_info,
    ]);

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");
	$cover_title = system_showText(LANG_MENU_CHECKOUT);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>

	<div class="members-page">
        <div class="container">
            <div class="members-wrapper">
                <div class="members-panel edit-panel">
                    <div class="panel-header">
                        <?=system_showText(LANG_LISTING_INFORMATION)?>
                    </div>
                    <div class="panel-body">
					<?php if ($paymentSystemError) { ?>
						<div class="form-edit-alert">
							<?=$payment_message?>
						</div>
					<?php } elseif ($payment_message) { ?>
						<div class="form-edit-alert">
							<?=system_showText(LANG_MSG_PROBLEMS_WERE_FOUND)?>:<br>
							<?=$payment_message?>
						</div>
					<?php } elseif ((!$bill_info["listings"]) && (!$bill_info["events"]) && (!$bill_info["banners"]) && (!$bill_info["classifieds"]) && (!$bill_info["articles"]) && (!$bill_info["custominvoices"])) { ?>
						<div class="form-edit-alert">
							<?=system_showText(LANG_MSG_NO_ITEMS_SELECTED_REQUIRING_PAYMENT)?>
						</div>
					<?php } else { ?>
						<div class="custom-biling">
							<?php include(INCLUDES_DIR."/tables/table_billing_second_step.php"); ?>
						</div>
					<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
