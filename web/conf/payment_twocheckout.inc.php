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
	# * FILE: /conf/payment_twocheckout.inc.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# TWOCHECKOUT CONSTANTS
	# ----------------------------------------------------------------------------------------------------
	if (TWOCHECKOUTPAYMENT_FEATURE == "on") {
		if (REALTRANSACTION == "on") {
			define("TWOCHECKOUT_DEMO",  "N");
		} else {
			define("TWOCHECKOUT_DEMO",  "Y");
		}

        $twocheckout_login = crypt_decrypt(setting_get('payment_twocheckout_login'));
        define("PAYMENT_TWOCHECKOUT_LOGIN", $twocheckout_login);

		define("TWOCHECKOUT_POST_URL",  "https://www.2checkout.com/checkout/spurchase");
		define("TWOCHECKOUT_LANG",      "en");
		define("TWOCHECKOUT_CURRENCY",  PAYMENT_CURRENCY_CODE);
	}
