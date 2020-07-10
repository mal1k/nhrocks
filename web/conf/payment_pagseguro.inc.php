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
    # * FILE: /conf/payment_pagseguro.inc.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # PAGSEGURO CONSTANTS
    # ----------------------------------------------------------------------------------------------------
    if (PAGSEGUROPAYMENT_FEATURE == "on") {
        $pagseguro_email = crypt_decrypt(setting_get('payment_pagseguro_email'));
        $pagseguro_token = crypt_decrypt(setting_get('payment_pagseguro_token'));
        define('PAYMENT_PAGSEGURO_EMAIL', $pagseguro_email);
        define('PAYMENT_PAGSEGURO_TOKEN', $pagseguro_token);

        define('PAGSEGURO_CURRENCY', PAYMENT_CURRENCY_CODE);
    }
