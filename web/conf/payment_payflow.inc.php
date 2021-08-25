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
	# * FILE: /conf/payment_payflow.inc.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# PAYFLOW CONSTANTS
	# ----------------------------------------------------------------------------------------------------
	if (PAYFLOWPAYMENT_FEATURE == "on") {

        $payflow_login = crypt_decrypt(setting_get('payment_payflow_login'));
        $payflow_partner = crypt_decrypt(setting_get('payment_payflow_partner'));
        define("PAYMENT_PAYFLOW_LOGIN",     $payflow_login);
        define("PAYMENT_PAYFLOW_PARTNER",   $payflow_partner);
        define("PAYFLOW_POST_URL",  "https://payflowlink.paypal.com");
		define("PAYFLOW_CURRENCY",  PAYMENT_CURRENCY_CODE);
	}
