<?
/* ==================================================================*\
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
  \*================================================================== */

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/code/paymentgateway.php
# ----------------------------------------------------------------------------------------------------

require_once(CLASSES_DIR.'/class_StripeInterface.php');

# ----------------------------------------------------------------------------------------------------
# SUBMIT
# ----------------------------------------------------------------------------------------------------
extract($_POST);
extract($_GET);

$dbMain = db_getDBObject(DEFAULT_DB, true);
$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

/**
 * This function converts data into an older format to allow
 * old functions to work. Yeah, we are looking at YOU, system_updateFormFields()
 * @param array $data an array containing the option's table names as keys and an array with each level associated with their values as value
 * @return array the modified array which will be fed into system_updateFormFields()
 */
function createItemLevelArray($data)
{
    foreach ($data as $key => $value) {
        /* On images we have a special case.
         * If the user sets zero images for a level, the level has no main image and no gallery
         * If the user sets one or more images, one image will be the main image and the rest will
         * be allocated in a gallery
         */
        if ($key === 'images') {
            foreach ($value as $level => &$amount) {
                $amount = max([$amount, 0]);

                if ($amount > 0) {
                    $amount--;
                    $data['itemLevel_main_image'][$level] = true;
                }
            }
        }

        $data["itemLevel_{$key}"] = $value;
        unset($data[$key]);
    }

    return $data;
}

/**
 * Treats and validates all information regarding Payment gateways and perform
 * the necessary database changes. Also makes coffee.
 * @todo This should be moved into a class of its own along with its auxiliary functions, person of the future.
 * @param mysql $dbObj
 */
function handleGatewayPost($dbObj)
{
    $recurring = $_POST['gateway']['recurring'];
    setting_set('payment_recurring_status', $recurring ? 'on' : 'off');

    $gateway_config = [];
    // creating array to append in gateway file
    $gateway_config += [
        'recurring' => ($recurring ? 'on' : 'off'),
    ];

    $enabled = null;

    foreach ($_POST['gateway'] as $gateway => $formData) {
        switch ($gateway) {
            case 'stripe':
                $enabled = ($formData['payment_stripeStatus'] === 'on' ? 'on' : 'off');
                $stripe_apikey = crypt_encrypt(trim($formData['payment_stripe_apikey']));

                if ($enabled === 'on' && !$stripe_apikey) {
                    MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_STRIPE));
                } else {

                    //Get existing API key in case the sitemgr is changing it
                    setting_get('payment_stripe_apikey', $stripe_apikey_saved);

                    setting_set('payment_stripe_status', $enabled);
                    setting_set('payment_stripe_apikey', $stripe_apikey);

                    if ($enabled === 'on') {
                        //Create plans
                        setting_get('stripe_planscreated', $stripe_planscreated);

                        //Create the plans in case they were never synchronized before, or if the API key was changed
                        if (!$stripe_planscreated || ($stripe_apikey_saved && $stripe_apikey_saved != $stripe_apikey)) {
                            $response = StripeInterface::StripeRequest('createplans',
                                $formData['payment_stripe_apikey']);
                        }

                        if (strlen($response)) {
                            MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_STRIPE_PLANS)."<br>".sprintf(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR), "<br><br>".$response."<br><br>"));
                        }

                        //Create discount codes
                        setting_get('stripe_couponscreated', $stripe_couponscreated);

                        //Create the coupons in case they were never synchronized before, or if the API key was changed
                        if (!$stripe_couponscreated || ($stripe_apikey_saved && $stripe_apikey_saved != $stripe_apikey)) {
                            $response = StripeInterface::StripeRequest('createcoupons',
                                $formData['payment_stripe_apikey']);
                        }

                        if (strlen($response)) {
                            MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_STRIPE_COUPONS)."<br>".sprintf(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR), "<br><br>".$response."<br><br>"));
                        }

                    }
                }

                // creating array to append in gateway file
                $gateway_config += [
                    'stripe.status' => $enabled,
                    'stripe.apikey' => $stripe_apikey,
                ];

                break;
            case 'paypal':
                $enabled = ($formData['payment_paypalStatus'] === 'on' ? 'on' : 'off');
                $account = crypt_encrypt(trim($formData['payment_paypal_account']));

                if ($enabled === 'on' && !$account) {
                    MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_PAYPAL));
                } else {
                    setting_set('payment_paypal_status', $enabled);
                    setting_set('payment_paypal_account', $account);
                }

                // creating array to append in gateway file
                $gateway_config += [
                    'paypal.status'  => $enabled,
                    'paypal.account' => $account,
                ];

                break;
            case 'paypalAPI':
                $enabled = ($formData['payment_paypalapiStatus'] === 'on' ? 'on' : 'off');
                $username = crypt_encrypt(trim($formData['payment_paypalapi_username']));
                $password = crypt_encrypt(trim($formData['payment_paypalapi_password']));
                $signature = crypt_encrypt(trim($formData['payment_paypalapi_signature']));

                if ($enabled === 'on' && (!$username || !$password || !$signature)) {
                    MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_PAYPALAPI));
                } else {
                    setting_set('payment_paypalapi_status', $enabled);
                    setting_set('payment_paypalapi_username', $username);
                    setting_set('payment_paypalapi_password', $password);
                    setting_set('payment_paypalapi_signature', $signature);
                }

                // creating array to append in gateway file
                $gateway_config += [
                    'paypalapi.status'    => $enabled,
                    'paypalapi.username'  => $username,
                    'paypalapi.password'  => $password,
                    'paypalapi.signature' => $signature,
                ];

                break;
            case 'pagseguro':
                $enabled = ($formData['payment_pagseguroStatus'] === 'on' ? 'on' : 'off');
                $email = crypt_encrypt(trim($formData['payment_pagseguro_email']));
                $token = crypt_encrypt(trim($formData['payment_pagseguro_token']));

                $payment_currency = setting_get('payment_currency_code');

                if ($enabled === 'on' && (!$email || !$token)) {
                    MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_PAGSEGURO));
                } else {
                    if ($enabled === 'on' && $payment_currency !== 'BRL') {
                        MessageHandler::registerError(LANG_MSG_CURRENCY_PAGSEGURO);
                    } else {
                        setting_set('payment_pagseguro_status', $enabled);
                        setting_set('payment_pagseguro_email', $email);
                        setting_set('payment_pagseguro_token', $token);
                    }
                }

                // creating array to append in gateway file
                $gateway_config += [
                    'pagseguro.status' => $enabled,
                    'pagseguro.email'  => $email,
                    'pagseguro.token'  => $token,
                ];

                break;
            case 'twoCheckout':
                $enabled = ($formData['payment_twocheckoutStatus'] === 'on' ? 'on' : 'off');
                $login = crypt_encrypt(trim($formData['payment_twocheckout_login']));

                if ($enabled === 'on' && !$login) {
                    MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_TWOCHECKOUT));
                } else {
                    setting_set('payment_twocheckout_status', $enabled);
                    setting_set('payment_twocheckout_login', $login);
                }

                // creating array to append in gateway file
                $gateway_config += [
                    'twocheckout.status' => $enabled,
                    'twocheckout.login'  => $login,
                ];

                break;
            case 'authorize':
                $enabled = ($formData['payment_authorizeStatus'] === 'on' ? 'on' : 'off');
                $login = crypt_encrypt(trim($formData['payment_authorize_login']));
                $transactionKey = crypt_encrypt(trim($formData['payment_authorize_transactionkey']));

                if ($enabled === 'on' && (!$login || !$transactionKey)) {
                    MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_AUTHORIZE));
                } else {
                    setting_set('payment_authorize_status', $enabled);
                    setting_set('payment_authorize_login', $login);
                    setting_set('payment_authorize_transactionkey', $transactionKey);
                }

                // creating array to append in gateway file
                $gateway_config += [
                    'authorize.status' => $enabled,
                    'authorize.login'  => $login,
                    'authorize.txnkey' => $transactionKey,
                ];

                break;
            case 'payflow':
                $enabled = ($formData['payment_payflowStatus'] === 'on' ? 'on' : 'off');
                $login = crypt_encrypt(trim($formData['payment_payflow_login']));
                $partner = crypt_encrypt(trim($formData['payment_payflow_partner']));

                if ($enabled === 'on' && (!$login || !$partner)) {
                    MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_PAYFLOW));
                } else {
                    setting_set('payment_payflow_status', $enabled);
                    setting_set('payment_payflow_login', $login);
                    setting_set('payment_payflow_partner', $partner);
                }

                // creating array to append in gateway file
                $gateway_config += [
                    'payflow.status'  => $enabled,
                    'payflow.login'   => $login,
                    'payflow.partner' => $partner,
                ];

                break;
            case 'worldpay':
                $enabled = ($formData['payment_worldpayStatus'] === 'on' ? 'on' : 'off');
                $installID = crypt_encrypt(trim($formData['payment_worldpay_installationid']));

                if ($enabled === 'on' && !$installID) {
                    MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_WORLDPAY));
                } else {
                    setting_set('payment_worldpay_status', $enabled);
                    setting_set('payment_worldpay_installationid', $installID);
                }

                // creating array to append in gateway file
                $gateway_config += [
                    'worldpay.status' => $enabled,
                    'worldpay.instid' => $installID,
                ];
                break;
        }

        $gatewaysMixpanel[ucfirst($gateway)] = $enabled === 'on' ? 'enabled' : 'disabled';
    }

    $gatewaysMixpanel['Recurring'] = ($recurring ? 'on' : 'off');

    mixpanel_track('Payment gateways updated', $gatewaysMixpanel);

    if (!MessageHandler::haveErrors()) {
        MessageHandler::registerSuccess(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_SAVED));
    }
}

/**
 * Creates the config file with database saved values
 * @param type $dbObj
 */
function createConfigFile($dbObj)
{
    $array_PaymentSetting = [
        'payment_recurring'         => setting_get('payment_recurring_status'),
        'payment_stripeStatus'      => setting_get('payment_stripe_status'),
        'payment_paypalStatus'      => setting_get('payment_paypal_status'),
        'payment_paypalapiStatus'   => setting_get('payment_paypalapi_status'),
        'payment_payflowStatus'     => setting_get('payment_payflow_status'),
        'payment_twocheckoutStatus' => setting_get('payment_twocheckout_status'),
        'payment_worldpayStatus'    => setting_get('payment_worldpay_status'),
        'payment_authorizeStatus'   => setting_get('payment_authorize_status'),
        'payment_pagseguroStatus'   => setting_get('payment_pagseguro_status'),
        'payment_currency_code'     => setting_get('payment_currency_code'),
        'payment_currency_symbol'   => setting_get('payment_currency_symbol'),
        'invoice_payment'           => setting_get('payment_invoice_status'),
        'manual_payment'            => setting_get('payment_manual_status'),
    ];

    payment_writeSettingPaymentFile($array_PaymentSetting);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !DEMO_LIVE_MODE) {
    /* The action post is defined by which button the user has clicked. */
    switch ($_POST['action']) {
        case 'gateways':
            if (PAYMENTSYSTEM_FEATURE === 'on') {
                handleGatewayPost($dbObj);
                createConfigFile($dbObj);
            }
            break;
        case 'currencyOptions':
            /* Filters data*/
            $currencySymbol = mysqli_real_escape_string($GLOBALS["___mysqli_ston"],
                strip_tags(trim($_POST['payment_currency_symbol'])));

            /* Error Handling */
            !$currencySymbol and MessageHandler::registerError(LANG_MSG_CURRENCY_SYMBOL_IS_REQUIRED);

            setting_set('payment_currency_symbol', $currencySymbol);
            if (PAYMENTSYSTEM_FEATURE === 'on') {
                $paymentCurrency = string_strtoupper($_POST['payment_currency_code']);

                $paymentTaxStatus = $_POST['payment_tax_status'] === 'on' ? 'on' : 'off';
                $paymentTaxLabel = mysqli_real_escape_string($GLOBALS["___mysqli_ston"],
                    strip_tags(trim($_POST['payment_tax_label'])));
                /* Replaces , with . and attempts to convert to a float with two decimal positions */
                $paymentTaxValue = sprintf('%.2f', str_replace(',', '.', $_POST['payment_tax_value']));

                /* Data filtering*/
                $invoicePayment = $_POST['invoice_payment'] === 'on' ? 'on' : 'off';
                $manualPayment = $_POST['manual_payment'] === 'on' ? 'on' : 'off';

                if (!$paymentCurrency) {
                    MessageHandler::registerError(LANG_MSG_PAYMENT_CURRENCY_IS_REQUIRED);
                } else {
                    $filteredPaymentCurrency = preg_replace('/[^a-zA-Z]/', '', $paymentCurrency);

                    if (string_strlen($filteredPaymentCurrency) != 3) {
                        MessageHandler::registerError(LANG_MSG_PAYMENT_CURRENCY_MUST_CONTAIN_THREE_CHARS);
                    }

                    if ($filteredPaymentCurrency != $paymentCurrency) {
                        MessageHandler::registerError(LANG_MSG_PAYMENT_CURRENCY_MUST_BE_ONLY_LETTERS);
                    }

                    if (setting_get('payment_pagseguro_status') === 'on' && $paymentCurrency !== 'BRL') {
                        MessageHandler::registerError(LANG_MSG_CURRENCY_PAGSEGURO);
                    }

                    $paymentCurrency = $filteredPaymentCurrency;

                    /* Check if Needs to Send to Stripe */
                    $oldPaymentCurrency = setting_get('payment_currency_code');
                    $currencyNeedsUpdate = false;
                    if ($filteredPaymentCurrency != $oldPaymentCurrency) {
                        $currencyNeedsUpdate = true;
                    }
                }

                if ($paymentTaxStatus === 'on') {
                    !$paymentTaxLabel and MessageHandler::registerError(LANG_SITEMGR_MSG_MAINLANGUAGE_REQUIRED);

                    if (!$paymentTaxValue && $paymentTaxValue != 0) {
                        MessageHandler::registerError(LANG_SITEMGR_MSG_VALUE_REQUIRED);
                    } else {
                        is_numeric($paymentTaxValue) or MessageHandler::registerError(LANG_SITEMGR_MSG_VALUE_MUST_BE_NUMERIC);
                        $paymentTaxValue > 0 or MessageHandler::registerError(LANG_SITEMGR_MSG_MIN_VALUE);
                    }
                }

                if (!MessageHandler::haveErrors()) {
                    setting_get('payment_tax_status', $old_payment_tax_status);
                    if (!$old_payment_tax_status) {
                        $old_payment_tax_status = 'off';
                    }

                    setting_get('payment_invoice_status', $old_invoicepayment_status);
                    if (!$old_invoicepayment_status) {
                        $old_invoicepayment_status = 'off';
                    }

                    setting_get('payment_manual_status', $old_manualpayment_status);
                    if (!$old_manualpayment_status) {
                        $old_manualpayment_status = 'off';
                    }

                    /* Sets if exists, creates if doesn't */
                    if (!setting_set('payment_tax_status', $payment_tax_status)) {
                        setting_new('payment_tax_status', $payment_tax_status);
                    }
                    if (!setting_set('payment_tax_value', $payment_tax_value)) {
                        setting_new('payment_tax_value', $payment_tax_value);
                    }
                    if (!setting_set('payment_tax_label', $payment_tax_label)) {
                        setting_new('payment_tax_label', $payment_tax_label);
                    }
                    if (!setting_set('invoice_header', $invoice_header)) {
                        setting_new('invoice_header', $invoice_header);
                    }
                    if (!setting_set('invoice_footer', $invoice_footer)) {
                        setting_new('invoice_footer', $invoice_footer);
                    }

                    setting_set('payment_currency_code', $filteredPaymentCurrency);
                    setting_set('payment_invoice_status', $invoicePayment);
                    setting_set('payment_manual_status', $manualPayment);

                    if (RECURRING_FEATURE === 'on' && STRIPEPAYMENT_FEATURE === 'on' && $currencyNeedsUpdate) {
                        //Update plans on Stripe
                        $stripekey = crypt_decrypt(setting_get('payment_stripe_apikey'));
                        $response = StripeInterface::StripeRequest('createplans', $stripekey, '',
                            $filteredPaymentCurrency);

                        if (strlen($response)) {
                            MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_STRIPE_PLANS)."<br>".sprintf(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR),
                                    "<br><br>".$response."<br><br>"));
                        } else {
                            $response = StripeInterface::StripeRequest('createcoupons', $stripekey, '',
                                $filteredPaymentCurrency);

                            if (strlen($response)) {
                                MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_STRIPE_COUPONS)."<br>".sprintf(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR),
                                        "<br><br>".$response."<br><br>"));
                            }
                        }
                    }
                }

                if (!MessageHandler::haveErrors()) {
                    MessageHandler::registerSuccess(system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_CURRENCY_SAVED));
                }

                if ($old_payment_tax_status != $paymentTaxStatus) {
                    mixpanel_track('Tax '.($paymentTaxStatus === 'on' ? 'enabled' : 'disabled'));
                }
                if ($old_invoicepayment_status != $invoicePayment) {
                    mixpanel_track('Invoice payment '.($invoicePayment === 'on' ? 'enabled' : 'disabled'));
                }
                if ($old_manualpayment_status != $manualPayment) {
                    mixpanel_track('Manual payment '.($manualPayment === 'on' ? 'enabled' : 'disabled'));
                }

                createConfigFile($dbObj);
            }
            break;
        case 'levels':
            $stripeData = [];
            mixpanel_track('Levels information updated');
            foreach ($_POST['level'] as $type => $data) {
                switch ($type) {
                    case 'listing' :
                        $levelObj = new ListingLevel(true);
                        $levelsArray = $levelObj->getLevelValues();
                        $levelOptionData = $_POST['levelOption']['listing'];

                        //We have no deals unless proven otherwise by the following foreach
                        $hasPromotionCheck = false;

                        foreach ($levelsArray as $levelValue) {
                            /* Check Level Name is empty */
                            if ((PAYMENTSYSTEM_FEATURE === 'on' || $levelValue < 30) && empty($data['name'][$levelValue])) {
                                MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_LEVELS_NAMES_EMPTY));
                                break 2;
                            }

                            /* Data filtering*/
                            $name = string_strtolower(mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $data['name'][$levelValue]));
                            $active = (empty($data['active'][$levelValue]) ? 'n' : 'y');
                            $popular = ($data['popular'] == $levelValue ? 'y' : 'n');
                            $featured = (empty($data['featured'][$levelValue]) ? 'n' : 'y');

                            if (!empty($levelOptionData['deals'][$levelValue]) && (int)$levelOptionData['deals'][$levelValue] > 0) {
                                $hasPromotionCheck = true;
                            }

                            $hasReview = (empty($levelOptionData['has_review'][$levelValue]) ? 'n' : 'y');
                            $detail = (empty($levelOptionData['detail'][$levelValue]) ? 'n' : 'y');
                            $hasCoverImage = (empty($levelOptionData['has_cover_image'][$levelValue]) ? 'n' : 'y');
                            $hasLogoImage = (empty($levelOptionData['has_logo_image'][$levelValue]) ? 'n' : 'y');
                            $images = (empty($levelOptionData['images'][$levelValue]) ? 0 : (int)$levelOptionData['images'][$levelValue]);
                            $classified_quantity_association = filter_var($levelOptionData['classified_quantity_association'][$levelValue],
                                FILTER_SANITIZE_NUMBER_INT);
                            $classified_quantity_association = !$classified_quantity_association ? 0 : $classified_quantity_association;

                            /* Check if Needs to Send to Stripe */
                            $needsUpdate = false;
                            if ($name != string_strtolower($levelObj->getName($levelValue)) || (float)$data['price'][$levelValue] != (float)$levelObj->getPrice($levelValue)
                                || (float)$data['price_yearly'][$levelValue] != (float)$levelObj->getPrice($levelValue,
                                    'yearly') ||
                                (int)$data['trial'][$levelValue] != (int)$levelObj->getTrial($levelValue)) {
                                $needsUpdate = true;
                            }

                            /*Saving to DB*/
                            if (PAYMENTSYSTEM_FEATURE === 'on' || $levelValue < 30) {
                                $levelObj->updateValues($name, $active, '', '', '', '', '', $levelValue, 'names',
                                    $popular,
                                    '');
                                $levelObj->updateValues('', '', $hasReview, $detail, $images,
                                    $hasCoverImage, $hasLogoImage, $levelValue, 'fields', '', $classified_quantity_association);
                                $levelObj->updateFeatured($featured, $levelValue);

                                $levelObj->updatePricing('price',
                                    (empty($data['price'][$levelValue]) ? 0 : (float)$data['price'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('price_yearly',
                                    (empty($data['price_yearly'][$levelValue]) ? 0 : (float)$data['price_yearly'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('trial',
                                    (empty($data['trial'][$levelValue]) ? 0 : (int)$data['trial'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('category_price',
                                    (empty($data['category_price'][$levelValue]) ? 0 : (float)$data['category_price'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('free_category',
                                    (empty($data['free_category'][$levelValue]) ? 0 : (int)$data['free_category'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('deals',
                                    (empty($levelOptionData['deals'][$levelValue]) ? 0 : (int)$levelOptionData['deals'][$levelValue]),
                                    $levelValue);

                                /* ModStores Hooks */
                                HookFire("paymentgateway_after_save_listinglevel", [
                                    "data"            => &$data,
                                    "levelValue"      => &$levelValue,
                                    "levelOptionData" => &$levelOptionData,
                                    "levelObj"        => &$levelObj
                                ]);

                                /* Sets array to Update Stripe Plans, if needed */
                                if ($needsUpdate) {
                                    $stripeData[] = [
                                        'price'    => StripeInterface::normalizePrice(number_format((float)$data['price'][$levelValue],
                                            2)),
                                        'interval' => 'month',
                                        'name'     => @constant('LANG_'.strtoupper($type).'_FEATURE_NAME').' '.$name.' - '.system_showText(LANG_MONTHLY),
                                        'currency' => PAYMENT_CURRENCY_CODE,
                                        'trial'    => (int)$data['trial'][$levelValue],
                                        'id'       => 'monthly_'.$type.'_'.$levelValue.'_'.SELECTED_DOMAIN_ID,
                                    ];

                                    $stripeData[] = [
                                        'price'    => StripeInterface::normalizePrice(number_format((float)$data['price_yearly'][$levelValue],
                                            2)),
                                        'interval' => 'year',
                                        'name'     => @constant('LANG_'.strtoupper($type).'_FEATURE_NAME').' '.$name.' - '.system_showText(LANG_YEARLY),
                                        'currency' => PAYMENT_CURRENCY_CODE,
                                        'trial'    => (int)$data['trial'][$levelValue],
                                        'id'       => 'yearly_'.$type.'_'.$levelValue.'_'.SELECTED_DOMAIN_ID,
                                    ];
                                }
                            }
                        }

                        // this mimics old form post structure for old functions to work.
                        $createItemLevelArray = createItemLevelArray($levelOptionData);

                        //Updates values for table ListingLevel_Field
                        system_updateFormFields($createItemLevelArray, 'Listing');

                        //Updates promotion setting
                        if ($hasPromotionCheck) {
                            setting_set('custom_has_promotion', 'on') or setting_new('custom_has_promotion',
                                'on') or MessageHandler::registerError(LANG_SITEMGR_SETTINGS_LEVELS_ERROR);
                        } else {
                            setting_set('custom_has_promotion', '') or setting_new('custom_has_promotion',
                                '') or MessageHandler::registerError(LANG_SITEMGR_SETTINGS_LEVELS_ERROR);
                        }

                        break;
                    case 'event' :
                        $levelObj = new EventLevel(true);
                        $levelsArray = $levelObj->getLevelValues();
                        $levelOptionData = $_POST['levelOption']['event'];

                        foreach ($levelsArray as $levelValue) {
                            /* Check Level Name is empty */
                            if ((PAYMENTSYSTEM_FEATURE === 'on' || $levelValue < 30) && empty($data['name'][$levelValue])) {
                                MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_LEVELS_NAMES_EMPTY));
                                break 2;
                            }

                            /* Data filtering*/
                            $name = string_strtolower(mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $data['name'][$levelValue]));
                            $active = (empty($data['active'][$levelValue]) ? 'n' : 'y');
                            $popular = ($data['popular'] == $levelValue ? 'y' : 'n');
                            $featured = (empty($data['featured'][$levelValue]) ? 'n' : 'y');

                            $detail = (empty($levelOptionData['detail'][$levelValue]) ? 'n' : 'y');
                            $hasCoverImage = (empty($levelOptionData['has_cover_image'][$levelValue]) ? 'n' : 'y');
                            $images = (empty($levelOptionData['images'][$levelValue]) ? 0 : (int)$levelOptionData['images'][$levelValue]);

                            /* Check if Needs to Send to Stripe */
                            $needsUpdate = false;
                            if ($name != string_strtolower($levelObj->getName($levelValue)) || (float)$data['price'][$levelValue] != (float)$levelObj->getPrice($levelValue)
                                || (float)$data['price_yearly'][$levelValue] != (float)$levelObj->getPrice($levelValue,
                                    'yearly') ||
                                (int)$data['trial'][$levelValue] != (int)$levelObj->getTrial($levelValue)) {
                                $needsUpdate = true;
                            }

                            /*Saving*/
                            if (PAYMENTSYSTEM_FEATURE === 'on' || $levelValue < 30) {
                                $levelObj->updateValues($name, $active, '', '', '', $levelValue, 'names', $popular);
                                $levelObj->updateValues('', '', $detail, $images, $hasCoverImage, $levelValue,
                                    'fields');
                                $levelObj->updateFeatured($featured, $levelValue);

                                $levelObj->updatePricing('price',
                                    (empty($data['price'][$levelValue]) ? 0 : (float)$data['price'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('price_yearly',
                                    (empty($data['price_yearly'][$levelValue]) ? 0 : (float)$data['price_yearly'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('trial',
                                    (empty($data['trial'][$levelValue]) ? 0 : (int)$data['trial'][$levelValue]),
                                    $levelValue);

                                /* ModStores Hooks */
                                HookFire("paymentgateway_after_save_eventlevel", [
                                    "data"            => &$data,
                                    "levelValue"      => &$levelValue,
                                    "levelOptionData" => &$levelOptionData,
                                    "levelObj"        => &$levelObj
                                ]);

                                /* Sets array to Update Stripe Plans, if needed */
                                if ($needsUpdate) {
                                    $stripeData[] = [
                                        'price'    => StripeInterface::normalizePrice(number_format((float)$data['price'][$levelValue],
                                            2)),
                                        'interval' => 'month',
                                        'name'     => @constant('LANG_'.strtoupper($type).'_FEATURE_NAME').' '.$name.' - '.system_showText(LANG_MONTHLY),
                                        'currency' => PAYMENT_CURRENCY_CODE,
                                        'trial'    => (int)$data['trial'][$levelValue],
                                        'id'       => 'monthly_'.$type.'_'.$levelValue.'_'.SELECTED_DOMAIN_ID,
                                    ];

                                    $stripeData[] = [
                                        'price'    => StripeInterface::normalizePrice(number_format((float)$data['price_yearly'][$levelValue],
                                            2)),
                                        'interval' => 'year',
                                        'name'     => @constant('LANG_'.strtoupper($type).'_FEATURE_NAME').' '.$name.' - '.system_showText(LANG_YEARLY),
                                        'currency' => PAYMENT_CURRENCY_CODE,
                                        'trial'    => (int)$data['trial'][$levelValue],
                                        'id'       => 'yearly_'.$type.'_'.$levelValue.'_'.SELECTED_DOMAIN_ID,
                                    ];
                                }
                            }
                        }

                        if (isset($levelOptionData['start_time'])) {
                            $levelOptionData['time'] = $levelOptionData['start_time'];
                            unset($levelOptionData['start_time']);
                        }

                        // this mimics old form post structure for old functions to work.
                        $createItemLevelArray = createItemLevelArray($levelOptionData);

                        //Updates values for table ListingLevel_Field
                        system_updateFormFields($createItemLevelArray, 'Event');

                        break;
                    case 'banner' :
                        $levelObj = new BannerLevel(true);
                        $levelsArray = $levelObj->getLevelValues();
                        $levelOptionData = $_POST['levelOption']['banner'];

                        foreach ($levelsArray as $levelValue) {
                            /* Check Level Name is empty */
                            if ('' == $data['name'][$levelValue] or is_null($data['name'][$levelValue])) {
                                MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_LEVELS_NAMES_EMPTY));
                                break 2;
                            }

                            $name = string_strtolower(mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $data['name'][$levelValue]));
                            $active = (empty($data['active'][$levelValue]) ? 'n' : 'y');
                            $popular = ($data['popular'] == $levelValue ? 'y' : 'n');

                            /* Check if Needs to Send to Stripe */
                            $needsUpdate = false;
                            if ($name != string_strtolower($levelObj->getName($levelValue)) || (float)$data['price'][$levelValue] != (float)$levelObj->getPrice($levelValue)
                                || (float)$data['price_yearly'][$levelValue] != (float)$levelObj->getPrice($levelValue,
                                    'yearly') ||
                                (int)$data['trial'][$levelValue] != (int)$levelObj->getTrial($levelValue)) {
                                $needsUpdate = true;
                            }

                            $levelObj->updateValues($name, $active, $levelValue, $popular);
                            if (PAYMENTSYSTEM_FEATURE === 'on') {
                                $levelObj->updatePricing('price',
                                    (empty($data['price'][$levelValue]) ? 0 : (float)$data['price'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('price_yearly',
                                    (empty($data['price_yearly'][$levelValue]) ? 0 : (float)$data['price_yearly'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('trial',
                                    (empty($data['trial'][$levelValue]) ? 0 : (int)$data['trial'][$levelValue]),
                                    $levelValue);

                                /* ModStores Hooks */
                                HookFire("paymentgateway_after_save_bannerlevel", [
                                    "data"            => &$data,
                                    "levelValue"      => &$levelValue,
                                    "levelOptionData" => &$levelOptionData,
                                    "levelObj"        => &$levelObj
                                ]);

                                /* Sets array to Update Stripe Plans, if needed */
                                if ($needsUpdate) {
                                    $stripeData[] = [
                                        'price'    => StripeInterface::normalizePrice(number_format((float)$data['price'][$levelValue],
                                            2)),
                                        'interval' => 'month',
                                        'name'     => @constant('LANG_'.strtoupper($type).'_FEATURE_NAME').' '.$name.' - '.system_showText(LANG_MONTHLY),
                                        'currency' => PAYMENT_CURRENCY_CODE,
                                        'trial'    => (int)$data['trial'][$levelValue],
                                        'id'       => 'monthly_'.$type.'_'.$levelValue.'_'.SELECTED_DOMAIN_ID,
                                    ];

                                    $stripeData[] = [
                                        'price'    => StripeInterface::normalizePrice(number_format((float)$data['price_yearly'][$levelValue],
                                            2)),
                                        'interval' => 'year',
                                        'name'     => @constant('LANG_'.strtoupper($type).'_FEATURE_NAME').' '.$name.' - '.system_showText(LANG_YEARLY),
                                        'currency' => PAYMENT_CURRENCY_CODE,
                                        'trial'    => (int)$data['trial'][$levelValue],
                                        'id'       => 'yearly_'.$type.'_'.$levelValue.'_'.SELECTED_DOMAIN_ID,
                                    ];
                                }
                            }
                        }

                        break;
                    case 'classified' :
                        $levelObj = new ClassifiedLevel(true);
                        $levelsArray = $levelObj->getLevelValues();
                        $levelOptionData = $_POST['levelOption']['classified'];

                        foreach ($levelsArray as $levelValue) {
                            /* Check Level Name is empty */
                            if ((PAYMENTSYSTEM_FEATURE === 'on' || $levelValue < 30) && empty($data['name'][$levelValue])) {
                                MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_LEVELS_NAMES_EMPTY));
                                break 2;
                            }

                            $name = string_strtolower(mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $data['name'][$levelValue]));
                            $active = (empty($data['active'][$levelValue]) ? 'n' : 'y');
                            $popular = ($data['popular'] == $levelValue ? 'y' : 'n');
                            $featured = (empty($data['featured'][$levelValue]) ? 'n' : 'y');

                            $detail = (empty($levelOptionData['detail'][$levelValue]) ? 'n' : 'y');
                            $video = (empty($levelOptionData['video'][$levelValue]) ? 'n' : 'y');
                            $images = (empty($levelOptionData['images'][$levelValue]) ? 0 : (int)$levelOptionData['images'][$levelValue]);
                            $hasCoverImage = (empty($levelOptionData['has_cover_image'][$levelValue]) ? 'n' : 'y');
                            $additional_files = (empty($levelOptionData['additional_files'][$levelValue]) ? 'n' : 'y');

                            /* Check if Needs to Send to Stripe */
                            $needsUpdate = false;
                            if ($name != string_strtolower($levelObj->getName($levelValue)) || (float)$data['price'][$levelValue] != (float)$levelObj->getPrice($levelValue)
                                || (float)$data['price_yearly'][$levelValue] != (float)$levelObj->getPrice($levelValue,
                                    'yearly') ||
                                (int)$data['trial'][$levelValue] != (int)$levelObj->getTrial($levelValue)) {
                                $needsUpdate = true;
                            }

                            if (PAYMENTSYSTEM_FEATURE === 'on' || $levelValue < 30) {
                                $levelObj->updateValues($name, $active, '', '', '', $levelValue, '', '', 'names',
                                    $popular);
                                $levelObj->updateValues('', '', $detail, $images, $hasCoverImage, $levelValue, $video,
                                    $additional_files,
                                    'fields');
                                $levelObj->updateFeatured($featured, $levelValue);

                                $levelObj->updatePricing('price',
                                    (empty($data['price'][$levelValue]) ? 0 : (float)$data['price'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('price_yearly',
                                    (empty($data['price_yearly'][$levelValue]) ? 0 : (float)$data['price_yearly'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('trial',
                                    (empty($data['trial'][$levelValue]) ? 0 : (int)$data['trial'][$levelValue]),
                                    $levelValue);

                                /* ModStores Hooks */
                                HookFire("paymentgateway_after_save_classifiedlevel", [
                                    "data"            => &$data,
                                    "levelValue"      => &$levelValue,
                                    "levelOptionData" => &$levelOptionData,
                                    "levelObj"        => &$levelObj
                                ]);

                                /* Sets array to Update Stripe Plans, if needed */
                                if ($needsUpdate) {
                                    $stripeData[] = [
                                        'price'    => StripeInterface::normalizePrice(number_format((float)$data['price'][$levelValue],
                                            2)),
                                        'interval' => 'month',
                                        'name'     => @constant('LANG_'.strtoupper($type).'_FEATURE_NAME').' '.$name.' - '.system_showText(LANG_MONTHLY),
                                        'currency' => PAYMENT_CURRENCY_CODE,
                                        'trial'    => (int)$data['trial'][$levelValue],
                                        'id'       => 'monthly_'.$type.'_'.$levelValue.'_'.SELECTED_DOMAIN_ID,
                                    ];

                                    $stripeData[] = [
                                        'price'    => StripeInterface::normalizePrice(number_format((float)$data['price_yearly'][$levelValue],
                                            2)),
                                        'interval' => 'year',
                                        'name'     => @constant('LANG_'.strtoupper($type).'_FEATURE_NAME').' '.$name.' - '.system_showText(LANG_YEARLY),
                                        'currency' => PAYMENT_CURRENCY_CODE,
                                        'trial'    => (int)$data['trial'][$levelValue],
                                        'id'       => 'yearly_'.$type.'_'.$levelValue.'_'.SELECTED_DOMAIN_ID,
                                    ];
                                }
                            }
                        }

                        // this mimics old form post structure for old functions to work.
                        $createItemLevelArray = createItemLevelArray($levelOptionData);

                        //Updates values for table ListingLevel_Field
                        system_updateFormFields($createItemLevelArray, 'Classified');

                        break;
                    case 'article' :
                        $levelObj = new ArticleLevel(true);
                        $levelsArray = $levelObj->getLevelValues();
                        $levelOptionData = $_POST['levelOption']['article'];

                        foreach ($levelsArray as $levelValue) {
                            /* Check Level Name is empty */
                            if ('' == $data['name'][$levelValue] or is_null($data['name'][$levelValue])) {
                                MessageHandler::registerError(system_showText(LANG_SITEMGR_SETTINGS_LEVELS_NAMES_EMPTY));
                                break 2;
                            }

                            $name = string_strtolower(mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $data['name'][$levelValue]));
                            $active = (empty($data['active'][$levelValue]) ? 'n' : 'y');

                            /* Check if Needs to Send to Stripe */
                            $needsUpdate = false;
                            if ($name != string_strtolower($levelObj->getName($levelValue)) || (float)$data['price'][$levelValue] != (float)$levelObj->getPrice($levelValue)
                                || (float)$data['price_yearly'][$levelValue] != (float)$levelObj->getPrice($levelValue,
                                    'yearly') ||
                                (int)$data['trial'][$levelValue] != (int)$levelObj->getTrial($levelValue)) {
                                $needsUpdate = true;
                            }

                            $levelObj->updateValues($name, $active, '', $levelValue);
                            if (PAYMENTSYSTEM_FEATURE === 'on') {
                                $levelObj->updatePricing('price',
                                    (empty($data['price'][$levelValue]) ? 0 : (float)$data['price'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('price_yearly',
                                    (empty($data['price_yearly'][$levelValue]) ? 0 : (float)$data['price_yearly'][$levelValue]),
                                    $levelValue);
                                $levelObj->updatePricing('trial',
                                    (empty($data['trial'][$levelValue]) ? 0 : (int)$data['trial'][$levelValue]),
                                    $levelValue);

                                /* ModStores Hooks */
                                HookFire("paymentgateway_after_save_articlelevel", [
                                    "data"            => &$data,
                                    "levelValue"      => &$levelValue,
                                    "levelOptionData" => &$levelOptionData,
                                    "levelObj"        => &$levelObj
                                ]);

                                /* Sets array to Update Stripe Plans, if needed */
                                if ($needsUpdate) {
                                    $stripeData[] = [
                                        'price'    => StripeInterface::normalizePrice(number_format((float)$data['price'][$levelValue],
                                            2)),
                                        'interval' => 'month',
                                        'name'     => @constant('LANG_'.strtoupper($type).'_FEATURE_NAME').' '.$name.' - '.system_showText(LANG_MONTHLY),
                                        'currency' => PAYMENT_CURRENCY_CODE,
                                        'trial'    => (int)$data['trial'][$levelValue],
                                        'id'       => 'monthly_'.$type.'_'.$levelValue.'_'.SELECTED_DOMAIN_ID,
                                    ];

                                    $stripeData[] = [
                                        'price'    => StripeInterface::normalizePrice(number_format((float)$data['price_yearly'][$levelValue],
                                            2)),
                                        'interval' => 'year',
                                        'name'     => @constant('LANG_'.strtoupper($type).'_FEATURE_NAME').' '.$name.' - '.system_showText(LANG_YEARLY),
                                        'currency' => PAYMENT_CURRENCY_CODE,
                                        'trial'    => (int)$data['trial'][$levelValue],
                                        'id'       => 'yearly_'.$type.'_'.$levelValue.'_'.SELECTED_DOMAIN_ID,
                                    ];
                                }
                            }
                        }

                        break;
                }

                /* ModStores Hooks */
                HookFire("paymentgateway_after_save_levels", [
                    "type"            => &$type,
                    "levelOptionData" => &$levelOptionData
                ]);
            }

            if (RECURRING_FEATURE === 'on' && STRIPEPAYMENT_FEATURE === 'on' && $_POST['save-pricing'] === 'yes' && !empty($stripeData) && PAYMENTSYSTEM_FEATURE === 'on') {

                //Update plans on Stripe
                $stripekey = crypt_decrypt(setting_get('payment_stripe_apikey'));
                $response = StripeInterface::StripeRequest('updateplans', $stripekey, $stripeData);

            }

            MessageHandler::haveErrors() or MessageHandler::registerSuccess(LANG_SITEMGR_SETTINGS_PAYMENTS_LEVELS_SAVED);
            break;
    }

    $_SESSION['PaymentOptions']['type'] = $_POST['action'];

    if (MessageHandler::haveErrors()) {
        /* Loads post information into the forms */
        $currency_symbol = $_POST['payment_currency_symbol'];
        $payment_currency = $_POST['payment_currency_code'];
        $payment_tax_status = $_POST['payment_tax_status'];
        $payment_tax_value = $_POST['payment_tax_value'];
        $payment_tax_label = $_POST['payment_tax_label'];
        $invoice_payment = $_POST['invoice_payment'];
        $manual_payment = $_POST['manual_payment'];
        $gatewayInfo = $_POST['gateway'];
        $invoice_header = $_POST['invoice_header'];
        $invoice_footer = $_POST['invoice_footer'];
    } else {
        /* Since we use the header to reload the page (to clear post data)
         * we need to save the messages in the session for them not to be lost
         * this is basically what this function does */
        MessageHandler::serialize();

        /* This is used on the next page to set which tab will be displayed */
        header("Location: http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
        exit;
    }
} else {
    /* Down here we prepare the "default" data which will be shown for each pane
     * These are the user's current settings, in other words. */

    /* Currency Defaults */
    $currency_symbol = setting_get('payment_currency_symbol');
    $payment_currency = setting_get('payment_currency_code');

    /* Tax Defaults */

    setting_get('payment_tax_status', $payment_tax_status);
    setting_get('payment_tax_value', $payment_tax_value);
    setting_get('payment_tax_label', $payment_tax_label);

    /* Invoice Defaults */
    $invoice_payment = setting_get('payment_invoice_status');
    $manual_payment = setting_get('payment_manual_status');
    $invoice_header = setting_get('invoice_header');
    $invoice_footer = setting_get('invoice_footer');

    /* Payment Gateways Defaults */
    /* Let's make sure it's empty */
    $gatewayInfo = null;

    $gatewayInfo['recurring'] = setting_get('payment_recurring_status');

    /* Stripe */
    $sql = "SELECT * FROM Setting WHERE name LIKE 'payment_stripe_%'";
    $result = $dbObj->query($sql);

    while ($row = mysqli_fetch_assoc($result)) {
        switch ($row['name']) {
            case 'payment_stripe_apikey'        :
                $gatewayInfo['stripe']['payment_stripe_apikey'] = crypt_decrypt($row['value']);
                break;
            case 'payment_stripe_status'         :
                $gatewayInfo['stripe']['payment_stripeStatus'] = $row['value'];
                break;
        }
    }

    /* Paypal */
    $sql = "SELECT * FROM Setting WHERE name LIKE 'payment_paypal_%'";
    $result = $dbObj->query($sql);

    while ($row = mysqli_fetch_assoc($result)) {
        switch ($row['name']) {
            case 'payment_paypal_account'        :
                $gatewayInfo['paypal']['payment_paypal_account'] = crypt_decrypt($row['value']);
                break;
            case 'payment_paypal_status'         :
                $gatewayInfo['paypal']['payment_paypalStatus'] = $row['value'];
                break;
        }
    }

    $sql = "SELECT * FROM Setting WHERE name LIKE 'payment_paypalapi_%'";
    $result = $dbObj->query($sql);

    while ($row = mysqli_fetch_assoc($result)) {
        switch ($row['name']) {
            case 'payment_paypalapi_status'    :
                $gatewayInfo['paypalAPI']['payment_paypalapiStatus'] = $row['value'];
                break;
            case 'payment_paypalapi_username'  :
                $gatewayInfo['paypalAPI']['payment_paypalapi_username'] = crypt_decrypt($row['value']);
                break;
            case 'payment_paypalapi_password'  :
                $gatewayInfo['paypalAPI']['payment_paypalapi_password'] = crypt_decrypt($row['value']);
                break;
            case 'payment_paypalapi_signature' :
                $gatewayInfo['paypalAPI']['payment_paypalapi_signature'] = crypt_decrypt($row['value']);
                break;
        }

    }

    $sql = "SELECT * FROM Setting WHERE name LIKE 'payment_payflow_%'";
    $result = $dbObj->query($sql);

    while ($row = mysqli_fetch_assoc($result)) {
        switch ($row['name']) {
            case 'payment_payflow_status'  :
                $gatewayInfo['payflow']['payment_payflowStatus'] = $row['value'];
                break;
            case 'payment_payflow_login'   :
                $gatewayInfo['payflow']['payment_payflow_login'] = crypt_decrypt($row['value']);
                break;
            case 'payment_payflow_partner' :
                $gatewayInfo['payflow']['payment_payflow_partner'] = crypt_decrypt($row['value']);
                break;
        }
    }

    $sql = "SELECT * FROM Setting WHERE name LIKE 'payment_twocheckout_%'";
    $result = $dbObj->query($sql);

    while ($row = mysqli_fetch_assoc($result)) {
        switch ($row['name']) {
            case 'payment_twocheckout_status' :
                $gatewayInfo['twoCheckout']['payment_twocheckoutStatus'] = $row['value'];
                break;
            case 'payment_twocheckout_login'  :
                $gatewayInfo['twoCheckout']['payment_twocheckout_login'] = crypt_decrypt($row['value']);
                break;
        }
    }

    $sql = "SELECT * FROM Setting WHERE name LIKE 'payment_worldpay_%'";
    $result = $dbObj->query($sql);

    while ($row = mysqli_fetch_assoc($result)) {
        switch ($row['name']) {
            case 'payment_worldpay_status' :
                $gatewayInfo['worldpay']['payment_worldpayStatus'] = $row['value'];
                break;
            case 'payment_worldpay_installationid' :
                $gatewayInfo['worldpay']['payment_worldpay_installationid'] = crypt_decrypt($row['value']);
                break;
        }
    }

    $sql = "SELECT * FROM Setting WHERE name LIKE 'payment_authorize_%'";
    $result = $dbObj->query($sql);

    while ($row = mysqli_fetch_assoc($result)) {
        switch ($row['name']) {
            case 'payment_authorize_login'           :
                $gatewayInfo['authorize']['payment_authorize_login'] = crypt_decrypt($row['value']);
                break;
            case 'payment_authorize_transactionkey'          :
                $gatewayInfo['authorize']['payment_authorize_transactionkey'] = crypt_decrypt($row['value']);
                break;
            case 'payment_authorize_status'          :
                $gatewayInfo['authorize']['payment_authorizeStatus'] = $row['value'];
                break;
        }
    }

    $sql = "SELECT * FROM Setting WHERE name LIKE 'payment_pagseguro_%'";
    $result = $dbObj->query($sql);

    while ($row = mysqli_fetch_assoc($result)) {
        switch ($row['name']) {
            case 'payment_pagseguro_email'  :
                $gatewayInfo['pagseguro']['payment_pagseguro_email'] = crypt_decrypt($row['value']);
                break;
            case 'payment_pagseguro_token'  :
                $gatewayInfo['pagseguro']['payment_pagseguro_token'] = crypt_decrypt($row['value']);
                break;
            case 'payment_pagseguro_status' :
                $gatewayInfo['pagseguro']['payment_pagseguroStatus'] = $row['value'];
                break;
        }
    }

}

/* Available Modules */
/* Each module's defaults are loaded separatedly inside includes/forms/form-payment-pricing.php */
$availableModules['event'] = [
    'active' => (EVENT_FEATURE === 'on'),
    'name'   => system_showText(LANG_SITEMGR_NAVBAR_EVENT),
];
$availableModules['classified'] = [
    'active' => (CLASSIFIED_FEATURE === 'on'),
    'name'   => system_showText(LANG_SITEMGR_NAVBAR_CLASSIFIED),
];
$availableModules['banner'] = [
    'active' => (BANNER_FEATURE === 'on'),
    'name'   => system_showText(LANG_SITEMGR_NAVBAR_BANNER),
];
$availableModules['article'] = [
    'active' => (ARTICLE_FEATURE === 'on'),
    'name'   => system_showText(LANG_SITEMGR_NAVBAR_ARTICLE),
];
