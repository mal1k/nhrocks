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
	# * FILE: /includes/forms/form_account_members.php
	# ----------------------------------------------------------------------------------------------------

    $accountID = sess_getAccountIdFromSession();

    $readonly = "";
    if (DEMO_LIVE_MODE && ($username == "demo@demodirectory.com")) {
        $readonly = "readonly";
    }

    $isForeignAcc = false;

    if ((string_strpos($username, "facebook::") !== false || string_strpos($username, "google::") !== false)) {
        $isForeignAcc = true;
    }

    $dropdown_protocol = html_protocolDropdown($url, "url_protocol", false, $protocol_replace);

    if ((string_strpos($username, "facebook::") === false && string_strpos($username, "google::") === false)) { ?>
    <div id="change-email">
        <h4 class="heading h-4"><?=system_showText(LANG_LABEL_ACCOUNT_USERNAME);?></h4>
        <div class="paragraph p-2"><?=system_showText(LANG_LABEL_ACCOUNT_USERNAME_TIP);?></div>
        <br>

        <div class="row custom-content-row">
            <div class="col-sm-4 col-sm-offset-2 well">
                <div class="form-group">
                    <div class="checking input-group">
                        <input id="username_mail" class="form-control" type="text" name="username" value="<?=$username?>" maxlength="<?=USERNAME_MAX_LEN?>" onblur="checkUsername(this.value, '<?=DEFAULT_URL;?>', 'members', <?=($accountID ? $accountID : 0);?>); populateField(this.value,'email');">
                        <?php if ($active != "y") { ?>
                            <span class="input-group-btn">
                                <button class="button button-md is-primary" type="button" onclick="sendEmailActivation(<?=$accountID?>);"><?=system_showText(LANG_LABEL_ACTIVATE_ACC);?></button>
                            </span>
                        <?php } ?>
                    </div>
                    <input type="hidden" name="active" value="<?=$active?>">
                    <p class="alert alert-success" id="messageEmail" style="display:none"><?=system_showText(LANG_LABEL_ACTIVATEEMAIL_SENT);?></p>
                    <p class="alert alert-warning" id="messageEmailError" style="display:none"></p>
                    <p class="alert alert-warning" id="checkUsername" style="display:none"></p>
                </div>
            </div>
        </div>
    </div>

    <? } else { ?>

    <input type="hidden" name="username" value="<?=$username?>">

    <? } ?>

    <? if (!$isForeignAcc) { ?>

    <div id="change-password">
        <h4 class="heading h-4"><?=system_showText(LANG_LABEL_ACCOUNT_CHANGEPASS);?></h4>
        <div class="paragraph p-2"><?=system_showText(LANG_LABEL_ACCOUNT_CHANGEPASS_TIP);?></div>
        <br>

        <div class="row default-row-biling">
            <div class="form-group col-md-4">
                <label for="currentPass"><?=system_showText(LANG_LABEL_CURRENT_PASSWORD)?></label>
                <div class="checking">
                    <input id="currentPass" type="password" name="current_password" class="form-control" <?=$readonly?>>
                </div>
            </div>
            <div class="form-group col-md-4">
                <label for="newPass"><?=system_showText(LANG_LABEL_NEW_PASSWORD);?> </label>
                <input id="newPass" class="form-control" type="password" name="password" maxlength="<?=PASSWORD_MAX_LEN?>" <?=$readonly?>>
            </div>
            <div class="form-group col-md-4">
                <label for="retypePass"><?=system_showText(LANG_LABEL_RETYPE_NEW_PASSWORD);?></label>
                <input id="retypePass" class="form-control" type="password" name="retype_password" <?=$readonly?>>
            </div>
        </div>
    </div>

    <? }
