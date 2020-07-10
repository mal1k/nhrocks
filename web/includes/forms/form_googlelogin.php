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
	# * FILE: /includes/forms/form_googlelogin.php
	# ----------------------------------------------------------------------------------------------------

    setting_get('foreignaccount_google_clientid', $foreignaccount_google_clientid);
    setting_get('foreignaccount_google_clientsecret', $foreignaccount_google_clientsecret);

    if ($foreignaccount_google_clientid && $foreignaccount_google_clientsecret) {

        if (!$goLabel) {
            if (string_strpos($_SERVER['PHP_SELF'], 'order') !== false || string_strpos($_SERVER['REQUEST_URI'], ALIAS_CLAIM_URL_DIVISOR.'/') !== false) {
                $goLabel = 'Google';
            } else {
                $goLabel = system_showText(LANG_LOGINGOOGLEUSER);
            }
        }

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

        // Call Google API
        $gClient = new Google_Client();
        $gClient->setApplicationName(EDIRECTORY_TITLE);
        $gClient->setClientId($foreignaccount_google_clientid);
        $gClient->setClientSecret($foreignaccount_google_clientsecret);
        $gClient->setRedirectUri(DEFAULT_URL.'/'.MEMBERS_ALIAS.'/googleauth.php');
        $gClient->addScope(['profile', 'email']);
        $gClient->setState(json_encode($redirectURI_params));

        /* ModStores Hooks */
        HookFire( "formgooglelogin_after_build_redirect", [
            "urlRedirect" => &$redirectURI_params,
        ]);

        // Get login url
        $authUrl = $gClient->createAuthUrl();
?>
        <?php if($members){ ?>
            <div class="g-signin">
                <p class="text-center">
                    <a href="<?=$authUrl?>" class="social-modal-button google-button"><img src="/assets/images/g-icon.png"> <?=$goLabel?></a>
                </p>
            </div>
            <?=!$advertiseArea ? '<br/>' : ''?>
        <?php } else { ?>
            <a href="<?=$authUrl?>" class="social-modal-button google-button"><img src="/assets/images/g-icon.png"> <?=$goLabel?></a>
        <?php } ?>
<?php
    }
?>