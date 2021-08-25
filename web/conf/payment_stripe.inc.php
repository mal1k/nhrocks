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
	# * FILE: /conf/payment_stripe.inc.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# STRIPE CONSTANTS
	# ----------------------------------------------------------------------------------------------------
	if (STRIPEPAYMENT_FEATURE == "on") {
        $stripe_apikey = crypt_decrypt(setting_get('payment_stripe_apikey'));
        define("PAYMENT_STRIPE_APIKEY", $stripe_apikey);
	}
