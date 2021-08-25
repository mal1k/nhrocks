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
	# * FILE: /conf/payment_worldpay.inc.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# WORLDPAY CONSTANTS
	# ----------------------------------------------------------------------------------------------------
	if (WORLDPAYPAYMENT_FEATURE == "on") {
		if (REALTRANSACTION == "on") {
			define("WORLDPAY_TESTMODE", "0");
            define("WORLDPAY_HOST",     "https://secure.worldpay.com/wcc/purchase");
		} else {
			define("WORLDPAY_TESTMODE", "100");
            define("WORLDPAY_HOST",     "https://secure-test.worldpay.com/wcc/purchase");
		}
        $worldpay_instid = crypt_decrypt(setting_get('payment_worldpay_installationid'));
        define("PAYMENT_WORLDPAY_INSTID",   $worldpay_instid);
		define("WORLDPAY_LANG",     "en");
		define("WORLDPAY_CURRENCY", PAYMENT_CURRENCY_CODE);
	}
