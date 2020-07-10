<?

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
	# * FILE: /includes/forms/form_login.php
	# ----------------------------------------------------------------------------------------------------

    if (!$advertise_section) { ?>

        <input type="hidden" name="destiny" value="<?=$destiny?>" />
        <input type="hidden" name="query" value="<?=urlencode($query)?>" />

    <? }

    $style = ($message_login) ? "display:visible;" : "display:none;";

    $defaultusername = $username;
    $defaultpassword = "";
    if (DEMO_DEV_MODE) {
        if ($members_section || $advertise_section) {

            if (string_strpos($_SERVER["PHP_SELF"], "/".SOCIALNETWORK_FEATURE_NAME."/login.php") !== false) {
                $defaultusername = "profile@demodirectory.com";
                $defaultpassword = "abc123";
                $forgotLink = DEFAULT_URL."/".SOCIALNETWORK_FEATURE_NAME."/forgot.php";
            } else {
                $defaultusername = "demo@demodirectory.com";
                $defaultpassword = "abc123";
            }

        } elseif ($sitemgr_section) {
            $defaultusername = "sitemgr@demodirectory.com";
            $defaultpassword = "abc123";
        }
    }

    if (string_strpos($_SERVER["PHP_SELF"], "/".SOCIALNETWORK_FEATURE_NAME."/login.php") !== false) {
        $forgotLink = DEFAULT_URL."/".SOCIALNETWORK_FEATURE_NAME."/forgot.php";
    } else {
        $forgotLink = DEFAULT_URL."/".MEMBERS_ALIAS."/forgot.php";
    }

    if (!$sitemgr_section) { ?>

        <? if ($message_login) { ?>
            <div class="alert alert-warning"><?=$message_login?></div>
        <? } ?>

        <input type="email" name="<?=$advertise_section ? "dir_" : ""?>username" id="<?=$advertise_section ? "dir_" : ""?>username" value="<?=$defaultusername?>" placeholder="<?=system_showText(LANG_LABEL_EMAIL_ADDRESS);?>" class="input" style="margin-top: 0;">
        <input type="password" name="<?=$advertise_section ? "dir_" : ""?>password" id="<?=$advertise_section ? "dir_" : ""?>password" value="<?=$defaultpassword?>" placeholder="<?=system_showText(LANG_LABEL_PASSWORD);?>" class="input">
        <div class="form-actions">
            <label class="form-remember">
                <input type="checkbox" name="automatic_login" value="1" <?=$checked?>>
                <?=system_showText(LANG_AUTOLOGIN);?>
            </label>
            <? if (system_checkEmail(SYSTEM_FORGOTTEN_PASS)) { ?>
                <div class="form-lost-password">
                    <a href="<?=$forgotLink;?>" rel="nofollow" class="link">
                        <?=system_showText(LANG_LABEL_FORGOTPASSWORD);?>
                    </a>
                </div>
            <? } ?>
        </div>
        <div class="form-button">
            <button class="button button-bg is-primary" <?=($advertise_section ? "type=\"button\"  onclick=\"submitForm();\"" : "type=\"submit\"")?>><?=system_showText(LANG_BUTTON_LOGIN);?></button>
        </div>
        <small class="privacy-policy">
            <?=sprintf(LANG_SIGNUP_TERMS,
                "<a rel=\"nofollow\" href=\"".DEFAULT_URL."/".ALIAS_TERMS_URL_DIVISOR."\" target=\"_blank\">",
                "</a>",
                "<a rel=\"nofollow\" href=\"".DEFAULT_URL."/".ALIAS_PRIVACY_URL_DIVISOR."\" target=\"_blank\">",
                "</a>"
            );?>
        </small>

	<? } else { ?>

		<div class="form-login">

			<h2><?=system_showText(LANG_SITEMGR_LOGIN_ACCOUNT);?></h2>

			<? if ($message_login) { ?>
                <p class="errorMessage" style="<?=$style?>"><?=$message_login?></p>
            <? } ?>

			<div class="form-box">

				<div>
					<input type="email" name="username" id="username" value="<?=$defaultusername?>" placeholder="<?=system_showText(LANG_SITEMGR_EMAIL_ADDRESS);?>" />
				</div>

				<div>
					<input type="password" name="password" id="password" value="<?=$defaultpassword?>" placeholder="<?=system_showText(LANG_LABEL_PASSWORD);?>" />
				</div>

				<? if (DEMO_DEV_MODE) { ?>
                    <div class="text-center warning">Test Password: abc123</div>
                <? } ?>

                <label class="automaticLogin">
                    <?=system_showText(LANG_AUTOLOGIN);?>
                    <input type="checkbox" name="automatic_login" value="1" <?=$checked?> class="inputAuto" />
                </label>

            	<button type="submit" class="stmgr-btn success"><?=system_showText(LANG_BUTTON_LOGIN);?></button>
			</div>

            <p class="linkLogin">
                <a href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/forgot.php" rel="nofollow"><?=system_showText(LANG_SITEMGR_FORGOTPASS_FORGOTYOURPASSWORD)?></a>
			</p>

		</div>
	<? } ?>
