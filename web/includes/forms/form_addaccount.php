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

    <p class="alert alert-warning hidden" id="validation">
    </p>

    <?=system_getHoneypotInput();?>
    <div class="form-box">
        <input class="input custom-input-size" type="text" name="first_name" id="first_name" value="<?=$first_name?>" placeholder="<?=system_showText(LANG_LABEL_FIRST_NAME);?>" />
        <input class="input custom-input-size" type="text" name="last_name" id="last_name" value="<?=$last_name?>" placeholder="<?=system_showText(LANG_LABEL_LAST_NAME);?>" />
        <input class="input custom-input-size username" type="email" name="username" id="username<?=($claimSection ? "_claim" : "")?>" value="<?=$username?>" maxlength="<?=USERNAME_MAX_LEN?>" onblur="populateField(this.value,'email');" placeholder="<?=system_showText(LANG_LABEL_USERNAME);?>" />
        <input type="hidden" name="email" id="email" value="<?=$email?>" />

        <input class="input custom-input-size password signup_secondary hidden" placeholder="<?=system_showText(LANG_LABEL_PASSWORD);?>" id="password<?=($claimSection ? "_claim" : "")?>" type="password" name="password" maxlength="<?=PASSWORD_MAX_LEN?>" />

        <?php if (!$advertise_section) { ?>
            <div id="upload_section" class="signup_secondary hidden">
                <div style="border: 1px solid rgba(62,69,94,.25); padding: 5px; margin-top: 5px; border-radius: 3px;">
                    <span>Required: Upload photo of NH drivers license</span>
                    <input class="custom-input-size" type="file" name="fileToUpload" id="fileToUpload" accept="image/*">
                </div>
                <p style="color: #FFAB3E; text-align: center; font-weight: bold;"><small>We use this to verify residency. We delete this data upon verification/payment. <br> We promise to never sell or distribute your information to anyone, ever.</small></p>
            </div>
        <?php } ?>
    </div>

    <div id="continue_button_section" class="form-button signup_primary">
        <button class="button button-bg is-primary" type="button" id="continue_button">Continue</button>
    </div>

    <?php if ($showNewsletter) { ?>

        <div class="checkbox">
            <label>
                <input type="checkbox" name="newsletter" value="y" <?=($newsletter || (!$newsletter && $_SERVER["REQUEST_METHOD"] != "POST")) ? "checked" : ""?> />
                <?=$signupLabel?>
            </label>
        </div>

    <?php } ?>

    <div id="form_captcha" class="signup_secondary hidden">
       <?php echo(new \reCAPTCHA())->render(); ?>
    </div>

    <div id="signup_button" class="form-button signup_secondary hidden">
        <?php if ($advertise_section) { ?>
            <?php if (PAYMENT_FEATURE == "on" && ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on"))) { ?>
                <button class="button button-bg is-primary" id="check_out_payment_2" type="submit" name="continue" value=""><?=system_showText(LANG_BUTTON_SUBMIT)?></button>
            <?php } ?>
            <button class="button button-bg is-primary" id="check_out_free_2" type="submit" name="checkout" value="<?=system_showText(LANG_BUTTON_CONTINUE)?>"><?=system_showText(LANG_BUTTON_SUBMIT)?></button>
        <?php } else { ?>
            <button class="button button-bg is-primary" type="submit" value="Submit" id="standard_submit"><?=system_showText(LANG_BUTTON_SIGNUP)?></button>
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

    <?php if (!$advertise_section) { ?>
    <script>
        var errors = [];
        var validation = document.getElementById('validation');
        var first_name = document.getElementById('first_name');
        var last_name = document.getElementById('last_name');
        var username = document.getElementsByClassName('username')[0];
        var password = document.getElementsByClassName('password')[0];
        var photo_upload = document.getElementById('fileToUpload');
        var submit_button = document.getElementById('standard_submit');
        var continue_button = document.getElementById('continue_button');
        var signup_secondary = document.getElementsByClassName('signup_secondary');

        continue_button.addEventListener("click", function(event){
            errors = [];

            if( !first_name.value ){
                errors.push("First Name: Required");
            }

            if( !last_name.value ){
                errors.push("Last Name: Required");
            }

            if( !username.value ){
                errors.push("Username: Required");
            }

            if(errors.length > 0){
                event.preventDefault();
                validation.classList.remove("hidden");

                var existing_validations = document.querySelectorAll('.validation_item');
                var existing_validations_br = document.querySelectorAll('#validation br');
                for (var x = 0; x < existing_validations.length; x++) {
                    existing_validations[x].parentNode.removeChild(existing_validations[x]);
                    existing_validations_br[x].parentNode.removeChild(existing_validations_br[x]);
                }

                for (var i = 0; i < errors.length; i++) {
                    var item = document.createElement('span');
                    item.classList.add('validation_item');
                    item.innerText = (errors[i]);
                    validation.appendChild(item);
                    validation.appendChild(document.createElement('br'));
                }

                return;
            } else {
                validation.classList.add("hidden");
            }

            var xhttp = new XMLHttpRequest();
            xhttp.responseType = 'json';
            xhttp.onreadystatechange = function() {
                if (this.readyState === 4) {
                    console.log(xhttp.response);
                    Array.from(signup_secondary).forEach(
                        function(element, index, array) {
                            element.classList.remove("hidden");
                        }
                    );
                    continue_button.classList.add('hidden')
                }
            };
            xhttp.open("POST", "/profile/add_to_airtable.php", true);
            xhttp.setRequestHeader('Content-Type', 'application/json');
            xhttp.send(JSON.stringify({
                'first_name': first_name.value,
                'last_name': last_name.value,
                'email': username.value
            }));
        });

        submit_button.addEventListener("click", function(event){
            errors = [];

            if( !first_name.value ){
                errors.push("First Name: Required");
            }

            if( !last_name.value ){
                errors.push("Last Name: Required");
            }

            if( !username.value ){
                errors.push("Username: Required");
            }

            if( !password.value ){
                errors.push("Password: Required");
            }

            if( photo_upload.files.length === 0 ){
                errors.push("Drivers license: Required");
            }else{
                var file = photo_upload.files[0];
                if(file && file.size > (1024*1000*5)) { // 5 MB (this size is in bytes)
                    errors.push("Drivers license: Image too large");
                }
            }

            if(errors.length > 0){
                event.preventDefault();
                validation.classList.remove("hidden");

                var existing_validations = document.querySelectorAll('.validation_item');
                var existing_validations_br = document.querySelectorAll('#validation br');
                for (var x = 0; x < existing_validations.length; x++) {
                    existing_validations[x].parentNode.removeChild(existing_validations[x]);
                    existing_validations_br[x].parentNode.removeChild(existing_validations_br[x]);
                }

                for (var i = 0; i < errors.length; i++) {
                    var item = document.createElement('span');
                    item.classList.add('validation_item');
                    item.innerText = (errors[i]);
                    validation.appendChild(item);
                    validation.appendChild(document.createElement('br'));
                }
            }
        });
    </script>
    <?php } ?>