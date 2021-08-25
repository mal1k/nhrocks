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
	# * FILE: /conf/payment_paypal.inc.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# PAYPAL CONSTANTS
	# ----------------------------------------------------------------------------------------------------
	if (PAYPALPAYMENT_FEATURE == "on") {
		if (REALTRANSACTION == "on") {
			define("PAYPAL_URL",        "www.paypal.com");
		} else {
			define("PAYPAL_URL",        "www.sandbox.paypal.com");
		}

        $paypal_account = crypt_decrypt(setting_get('payment_paypal_account'));
        define("PAYMENT_PAYPAL_ACCOUNT",    $paypal_account);

		define("PAYPAL_URL_FOLDER", "/cgi-bin/webscr");
		define("PAYPAL_LC",         "US");
		define("PAYPAL_CURRENCY",   PAYMENT_CURRENCY_CODE);
	}
