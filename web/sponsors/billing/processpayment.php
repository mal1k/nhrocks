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
	# * FILE: /sponsors/billing/processpayment.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include '../../conf/loadconfig.inc.php';

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (PAYMENT_FEATURE != 'on') { exit; }
	if (CREDITCARDPAYMENT_FEATURE != 'on') { exit; }

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_GET);
	extract($_POST);

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	if ($payment_method != 'payflow' && !$_POST['USER1']) {
		sess_validateSession();
	}

	$acctId = sess_getAccountIdFromSession();
	$url_redirect = ''.DEFAULT_URL.'/'.MEMBERS_ALIAS.'/billing';
	$url_base = ''.DEFAULT_URL.'/'.MEMBERS_ALIAS.'';

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include INCLUDES_DIR.'/code/billing_'.$payment_method.'.php';

    /* ModStores Hooks */
    HookFire( 'billingprocesspayment_before_render_page', [
        'payment_success' => &$payment_success,
        'payment_amount'  => &$payment_amount,
    ]);

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include MEMBERS_EDIRECTORY_ROOT.'/layout/header.php';
	$cover_title = system_showText(LANG_LABEL_TRANSACTION_STATUS);
    include EDIRECTORY_ROOT.'/frontend/coverimage.php';
?>

	<div class="members-page">
        <div class="container">
			<?php
				if ($payment_message){
					echo urldecode($payment_message);
				}
			?>
		</div>
	</div>

<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include MEMBERS_EDIRECTORY_ROOT.'/layout/footer.php';
