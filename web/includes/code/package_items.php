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
	# * FILE: /includes/code/package_items.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (PAYMENT_FEATURE != "on") { exit; }
	if (CREDITCARDPAYMENT_FEATURE != "on" && PAYMENT_INVOICE_STATUS != "on") { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSession();
    
    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$packageItems = false;

	if (!$items && !$items_price) { exit; }

	$package = new Package($id);

	$packageItems = true;

	$packagePaymentItems = $items;
	$packagePaymentPrices = $items_price;

	$packagePaymentItems = explode("\n", $packagePaymentItems);
	$packagePaymentPrices = explode("\n", $packagePaymentPrices);

	$str_price = "";
	foreach($packagePaymentPrices as $price){
		$str_price .= PAYMENT_CURRENCY_SYMBOL." ".format_money($price)."<br />";
	}
?>
