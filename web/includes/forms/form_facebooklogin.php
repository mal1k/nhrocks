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
	# * FILE: /includes/forms/form_facebooklogin.php
	# ----------------------------------------------------------------------------------------------------

	/*
	 * Workaround to pin a bookmark without login
	 */
	if ($_GET['bookmark_remember']) {
        $redirectURI_params = array_merge($redirectURI_params, ["bookmark_remember" => $_GET['bookmark_remember']]);
	}

	/*
	 * Workaround for make a redeem without login
	 */
	if ($_GET['redeem_remember']) {
        $redirectURI_params = array_merge($redirectURI_params, ["redeem_remember" => $_GET['redeem_remember']]);
	}

    /* ModStores Hooks */
    HookFire( "formfacebooklogin_after_build_redirect", [
        "urlRedirect" => &$redirectURI_params,
    ]);

    if (!$fbLabel) {
        if (string_strpos($_SERVER["PHP_SELF"], "order") !== false || string_strpos($_SERVER["REQUEST_URI"], ALIAS_CLAIM_URL_DIVISOR."/") !== false) {
            $fbLabel = "Facebook";
        } else {
            $fbLabel = system_showText(LANG_LOGINFACEBOOKUSER);
        }
    }

    require_once(CLASSES_DIR."/class_FacebookLogin.php");
	$fbLogin = new FacebookLogin();
    $loginUrl = $fbLogin->getFBLoginURL($redirectURI_params);
?>
<?php if ($linkAttachFB) { ?>
	<a href="<?=$loginUrl;?>" class="button button-sm is-primary"><i class="fa fa-facebook-official"></i> <?=system_showText(LANG_LABEL_LINK_FACEBOOK);?></a>
<?php } else { ?>
	<?php if (isset($_GET["facebookerror"])) { ?>
		<div class="form-edit-alert"><?=system_showText(LANG_LABEL_ERRORLOGIN)?></div>
	<?php } ?>

	<a href="<?=$loginUrl;?>" <?=($isPopUP ? "target=\"_top\"" : "")?> class="social-modal-button facebook-button"><i class="fa fa-facebook-official"></i> <?=$fbLabel?></a>
<?php } ?>