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
    # * FILE: /includes/form/form_addaccount.php
    # ----------------------------------------------------------------------------------------------------

    if ((string_strlen(trim($message_account)) > 0) || (string_strlen(trim($message_contact)) > 0) ) { ?>
        <p class="alert alert-warning">
            <?php if (string_strlen(trim($message_contact)) > 0) { ?>
                <?=$message_contact?>
            <?php } ?>
            <?php if ((string_strlen(trim($message_contact)) > 0) && (string_strlen(trim($message_account)) > 0)) { ?>
                <br />
            <?php } ?>
            <?php if (string_strlen(trim($message_account)) > 0) { ?>
                <?=$message_account?>
            <?php } ?>
        </p>
    <?php } ?>

    <?=system_getHoneypotInput();?>
    <div class="form-box">
        <input class="input custom-input-size" type="text" name="first_name" id="first_name" value="<?=$first_name?>" placeholder="<?=system_showText(LANG_LABEL_FIRST_NAME);?>" />
        <input class="input custom-input-size" type="text" name="last_name" id="last_name" value="<?=$last_name?>" placeholder="<?=system_showText(LANG_LABEL_LAST_NAME);?>" />
        <input class="input custom-input-size" type="email" name="username" id="username<?=($claimSection ? "_claim" : "")?>" value="<?=$username?>" maxlength="<?=USERNAME_MAX_LEN?>" onblur="populateField(this.value,'email');" placeholder="<?=system_showText(LANG_LABEL_USERNAME);?>" />
        <input type="hidden" name="email" id="email" value="<?=$email?>" />
        <input class="input custom-input-size" placeholder="<?=system_showText(LANG_LABEL_PASSWORD);?>" id="password<?=($claimSection ? "_claim" : "")?>" type="password" name="password" maxlength="<?=PASSWORD_MAX_LEN?>" />
    </div>

    <?php if ($showNewsletter) { ?>

        <div class="checkbox">
            <label>
                <input type="checkbox" name="newsletter" value="y" <?=($newsletter || (!$newsletter && $_SERVER["REQUEST_METHOD"] != "POST")) ? "checked" : ""?> />
                <?=$signupLabel?>
            </label>
        </div>

    <?php } ?>
    
    <?php echo(new \reCAPTCHA())->render(); ?>

    <div class="form-button">
        <?php if ($advertise_section) { ?>
            <?php if (PAYMENT_FEATURE == "on" && ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on"))) { ?>
                <button class="button button-bg is-primary" id="check_out_payment_2" type="submit" name="continue" value=""><?=system_showText(LANG_BUTTON_SUBMIT)?></button>
            <?php } ?>
            <button class="button button-bg is-primary" id="check_out_free_2" type="submit" name="checkout" value="<?=system_showText(LANG_BUTTON_CONTINUE)?>"><?=system_showText(LANG_BUTTON_SUBMIT)?></button>
        <?php } else { ?>
            <button class="button button-bg is-primary" type="submit" value="Submit"><?=system_showText(LANG_BUTTON_SIGNUP)?></button>
        <?php } ?>
    </div>

    <?php
    /* ModStores Hooks */
    HookFire("formsignup_after_render_newsletter");
    ?>

    <small class="privacy-policy">
        <?=sprintf(LANG_SIGNUP_TERMS,
            "<a rel=\"nofollow\" href=\"".DEFAULT_URL."/".ALIAS_TERMS_URL_DIVISOR."\" target=\"_blank\">",
            "</a>",
            "<a rel=\"nofollow\" href=\"".DEFAULT_URL."/".ALIAS_PRIVACY_URL_DIVISOR."\" target=\"_blank\">",
            "</a>"
        );?>
    </small>
