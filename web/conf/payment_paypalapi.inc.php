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
	# * FILE: /conf/payment_paypalapi.inc.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# PAYPALAPI CONSTANTS
	# ----------------------------------------------------------------------------------------------------
	if (PAYPALAPIPAYMENT_FEATURE == "on") {
		if (REALTRANSACTION == "on") {
			define("PAYPALAPI_ENDPOINT",    "https://api-3t.paypal.com/nvp");
		} else {
			define("PAYPALAPI_ENDPOINT",    "https://api-3t.sandbox.paypal.com/nvp");
		}

        $paypalapi_username = crypt_decrypt(setting_get('payment_paypalapi_username'));
        $paypalapi_password = crypt_decrypt(setting_get('payment_paypalapi_password'));
        $paypalapi_signature = crypt_decrypt(setting_get('payment_paypalapi_signature'));

        define("PAYMENT_PAYPALAPI_USERNAME",    $paypalapi_username);
        define("PAYMENT_PAYPALAPI_PASSWORD",    $paypalapi_password);
        define("PAYMENT_PAYPALAPI_SIGNATURE",   $paypalapi_signature);

		define("PAYPALAPI_USE_PROXY",   FALSE);
		define("PAYPALAPI_PROXY_HOST",  "127.0.0.1");
		define("PAYPALAPI_PROXY_PORT",  "808");
		define("PAYPALAPI_VERSION",     "2.3");
		define("PAYPALAPI_CURRENCY",    PAYMENT_CURRENCY_CODE);
	}
