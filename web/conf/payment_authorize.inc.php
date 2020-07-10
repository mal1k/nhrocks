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
	# * FILE: /conf/payment_authorize.inc.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# AUTHORIZE CONSTANTS
	# ----------------------------------------------------------------------------------------------------
	if (AUTHORIZEPAYMENT_FEATURE == "on") {

        $authorize_login = crypt_decrypt(setting_get('payment_authorize_login'));
        $authorize_txnkey = crypt_decrypt(setting_get('payment_authorize_transactionkey'));
        define("PAYMENT_AUTHORIZE_LOGIN",   $authorize_login);
        define("PAYMENT_AUTHORIZE_TXNKEY",  $authorize_txnkey);

		if (REALTRANSACTION == "on") {
			if (RECURRING_FEATURE == "on") {
				define("AUTHORIZE_POST_URL",    "https://api.authorize.net/xml/v1/request.api");
			} else {
				define("AUTHORIZE_POST_URL",    "https://secure.authorize.net/gateway/transact.dll");
			}
		} else {
			if (RECURRING_FEATURE == "on") {
				define("AUTHORIZE_POST_URL",    "https://apitest.authorize.net/xml/v1/request.api");
			} else {
				define("AUTHORIZE_POST_URL",    "https://test.authorize.net/gateway/transact.dll");
			}
		}
		define("AUTHORIZE_CURRENCY", PAYMENT_CURRENCY_CODE);
	}
