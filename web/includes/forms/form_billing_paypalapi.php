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
	# * FILE: /includes/forms/form_billing_paypalapi.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# INCLUDE
	# ----------------------------------------------------------------------------------------------------
	include(EDIRECTORY_ROOT."/conf/payment_paypalapi.inc.php");

	setting_get("payment_tax_status", $payment_tax_status);
	setting_get("payment_tax_value", $payment_tax_value);

	if ( PAYPALAPIPAYMENT_FEATURE == "on" )
    {

        $countries = [
            "ALBANIA" => "AL",
            "ALGERIA" => "DZ",
            "ANDORRA" => "AD",
            "ANGOLA" => "AO",
            "ANGUILLA" => "AI",
            "ANTIGUA & BARBUDA" => "AG",
            "ARGENTINA" => "AR",
            "ARMENIA" => "AM",
            "ARUBA" => "AW",
            "AUSTRALIA" => "AU",
            "AUSTRIA" => "AT",
            "AZERBAIJAN" => "AZ",
            "BAHAMAS" => "BS",
            "BAHRAIN" => "BH",
            "BARBADOS" => "BB",
            "BELARUS" => "BY",
            "BELGIUM" => "BE",
            "BELIZE" => "BZ",
            "BENIN" => "BJ",
            "BERMUDA" => "BM",
            "BHUTAN" => "BT",
            "BOLIVIA" => "BO",
            "BOSNIA & HERZEGOVINA" => "BA",
            "BOTSWANA" => "BW",
            "BRASIL" => "BR",
            "BRITISH VIRGIN ISLANDS" => "VG",
            "BRUNEI" => "BN",
            "BULGARIA" => "BG",
            "BURKINA FASO" => "BF",
            "BURUNDI" => "BI",
            "CAMBODIA" => "KH",
            "CAMEROON" => "CM",
            "CANADA" => "CA",
            "CAPE VERDE" => "CV",
            "CAYMAN ISLANDS" => "KY",
            "CHAD" => "TD",
            "CHILE" => "CL",
            "CHINA" => "C2",
            "COLOMBIA" => "CO",
            "COMOROS" => "KM",
            "CONGO - BRAZZAVILLE" => "CG",
            "CONGO - KINSHASA" => "CD",
            "COOK ISLANDS" => "CK",
            "COSTA RICA" => "CR",
            "CÔTE D’IVOIRE" => "CI",
            "CROATIA" => "HR",
            "CYPRUS" => "CY",
            "CZECH REPUBLIC" => "CZ",
            "DENMARK" => "DK",
            "DJIBOUTI" => "DJ",
            "DOMINICA" => "DM",
            "DOMINICAN REPUBLIC" => "DO",
            "ECUADOR" => "EC",
            "EGYPT" => "EG",
            "EL SALVADOR" => "SV",
            "ERITREA" => "ER",
            "ESTONIA" => "EE",
            "ETHIOPIA" => "ET",
            "FALKLAND ISLANDS" => "FK",
            "FAROE ISLANDS" => "FO",
            "FIJI" => "FJ",
            "FINLAND" => "FI",
            "FRANCE" => "FR",
            "FRENCH GUIANA" => "GF",
            "FRENCH POLYNESIA" => "PF",
            "GABON" => "GA",
            "GAMBIA" => "GM",
            "GEORGIA" => "GE",
            "GERMANY" => "DE",
            "GIBRALTAR" => "GI",
            "GREECE" => "GR",
            "GREENLAND" => "GL",
            "GRENADA" => "GD",
            "GUADELOUPE" => "GP",
            "GUATEMALA" => "GT",
            "GUINEA" => "GN",
            "GUINEA-BISSAU" => "GW",
            "GUYANA" => "GY",
            "HONDURAS" => "HN",
            "HONG KONG SAR CHINA" => "HK",
            "HUNGARY" => "HU",
            "ICELAND" => "IS",
            "INDIA" => "IN",
            "INDONESIA" => "ID",
            "IRELAND" => "IE",
            "ISRAEL" => "IL",
            "ITALY" => "IT",
            "JAMAICA" => "JM",
            "JAPAN" => "JP",
            "JORDAN" => "JO",
            "KAZAKHSTAN" => "KZ",
            "KENYA" => "KE",
            "KIRIBATI" => "KI",
            "KUWAIT" => "KW",
            "KYRGYZSTAN" => "KG",
            "LAOS" => "LA",
            "LATVIA" => "LV",
            "LESOTHO" => "LS",
            "LIECHTENSTEIN" => "LI",
            "LITHUANIA" => "LT",
            "LUXEMBOURG" => "LU",
            "MACEDONIA" => "MK",
            "MADAGASCAR" => "MG",
            "MALAWI" => "MW",
            "MALAYSIA" => "MY",
            "MALDIVES" => "MV",
            "MALI" => "ML",
            "MALTA" => "MT",
            "MARSHALL ISLANDS" => "MH",
            "MARTINIQUE" => "MQ",
            "MAURITANIA" => "MR",
            "MAURITIUS" => "MU",
            "MAYOTTE" => "YT",
            "MEXICO" => "MX",
            "MICRONESIA" => "FM",
            "MOLDOVA" => "MD",
            "MONACO" => "MC",
            "MONGOLIA" => "MN",
            "MONTENEGRO" => "ME",
            "MONTSERRAT" => "MS",
            "MOROCCO" => "MA",
            "MOZAMBIQUE" => "MZ",
            "NAMIBIA" => "NA",
            "NAURU" => "NR",
            "NEPAL" => "NP",
            "NETHERLANDS" => "NL",
            "NEW CALEDONIA" => "NC",
            "NEW ZEALAND" => "NZ",
            "NICARAGUA" => "NI",
            "NIGER" => "NE",
            "NIGERIA" => "NG",
            "NIUE" => "NU",
            "NORFOLK ISLAND" => "NF",
            "NORWAY" => "NO",
            "OMAN" => "OM",
            "PALAU" => "PW",
            "PANAMA" => "PA",
            "PAPUA NEW GUINEA" => "PG",
            "PARAGUAY" => "PY",
            "PERU" => "PE",
            "PHILIPPINES" => "PH",
            "PITCAIRN ISLANDS" => "PN",
            "POLAND" => "PL",
            "PORTUGAL" => "PT",
            "QATAR" => "QA",
            "RÉUNION" => "RE",
            "ROMANIA" => "RO",
            "RUSSIA" => "RU",
            "RWANDA" => "RW",
            "SAMOA" => "WS",
            "SAN MARINO" => "SM",
            "SÃO TOMÉ & PRÍNCIPE" => "ST",
            "SAUDI ARABIA" => "SA",
            "SENEGAL" => "SN",
            "SERBIA" => "RS",
            "SEYCHELLES" => "SC",
            "SIERRA LEONE" => "SL",
            "SINGAPORE" => "SG",
            "SLOVAKIA" => "SK",
            "SLOVENIA" => "SI",
            "SOLOMON ISLANDS" => "SB",
            "SOMALIA" => "SO",
            "SOUTH AFRICA" => "ZA",
            "SOUTH KOREA" => "KR",
            "SPAIN" => "ES",
            "SRI LANKA" => "LK",
            "ST. HELENA" => "SH",
            "ST. KITTS & NEVIS" => "KN",
            "ST. LUCIA" => "LC",
            "ST. PIERRE & MIQUELON" => "PM",
            "ST. VINCENT & GRENADINES" => "VC",
            "SURINAME" => "SR",
            "SVALBARD & JAN MAYEN" => "SJ",
            "SWAZILAND" => "SZ",
            "SWEDEN" => "SE",
            "SWITZERLAND" => "CH",
            "TAIWAN" => "TW",
            "TAJIKISTAN" => "TJ",
            "TANZANIA" => "TZ",
            "THAILAND" => "TH",
            "TOGO" => "TG",
            "TONGA" => "TO",
            "TRINIDAD & TOBAGO" => "TT",
            "TUNISIA" => "TN",
            "TURKMENISTAN" => "TM",
            "TURKS & CAICOS ISLANDS" => "TC",
            "TUVALU" => "TV",
            "UGANDA" => "UG",
            "UKRAINE" => "UA",
            "UNITED ARAB EMIRATES" => "AE",
            "UNITED KINGDOM" => "GB",
            "UNITED STATES" => "US",
            "URUGUAY" => "UY",
            "VANUATU" => "VU",
            "VATICAN CITY" => "VA",
            "VENEZUELA" => "VE",
            "VIETNAM" => "VN",
            "WALLIS & FUTUNA" => "WF",
            "YEMEN" => "YE",
            "ZAMBIA" => "ZM",
            "ZIMBABWE" => "ZW",
        ];

        if ( !PAYMENT_PAYPALAPI_USERNAME || !PAYMENT_PAYPALAPI_PASSWORD || !PAYMENT_PAYPALAPI_SIGNATURE )
        {
            echo "<p class=\"alert alert-warning\">".system_showText( LANG_GATEWAY_NO_AVAILABLE )." <a href=\"".DEFAULT_URL."/".MEMBERS_ALIAS."/help.php\" class=\"billing-contact\">".system_showText( LANG_LABEL_ADMINISTRATOR )."</a>.</p>";
        }
        else
        {

            if ( $bill_info["listings"] )
            {
                foreach ( $bill_info["listings"] as $id => $info )
                {
                    $cart_items .= "
					<input type=\"hidden\" name=\"listing_id[]\" value=\"$id\" />
					<input type=\"hidden\" name=\"listing_price[]\" value=\"".$info["total_fee"]."\" />";
                }
            }

            if ( $bill_info["events"] )
            {
                foreach ( $bill_info["events"] as $id => $info )
                {
                    $cart_items .= "
					<input type=\"hidden\" name=\"event_id[]\" value=\"$id\" />
					<input type=\"hidden\" name=\"event_price[]\" value=\"".$info["total_fee"]."\" />";
                }
            }

            if ( $bill_info["banners"] )
            {
                foreach ( $bill_info["banners"] as $id => $info )
                {
                    $cart_items .= "
					<input type=\"hidden\" name=\"banner_id[]\" value=\"$id\" />
					<input type=\"hidden\" name=\"banner_price[]\" value=\"".$info["total_fee"]."\" />";
                }
            }

            if ( $bill_info["classifieds"] )
            {
                foreach ( $bill_info["classifieds"] as $id => $info )
                {
                    $cart_items .= "
					<input type=\"hidden\" name=\"classified_id[]\" value=\"$id\" />
					<input type=\"hidden\" name=\"classified_price[]\" value=\"".$info["total_fee"]."\" />";
                }
            }

            if ( $bill_info["articles"] )
            {
                foreach ( $bill_info["articles"] as $id => $info )
                {
                    $cart_items .= "
					<input type=\"hidden\" name=\"article_id[]\" value=\"$id\" />
					<input type=\"hidden\" name=\"article_price[]\" value=\"".$info["total_fee"]."\" />";
                }
            }

            if ( $bill_info["custominvoices"] )
            {
                foreach ( $bill_info["custominvoices"] as $id => $info )
                {
                    $customInvoiceTitle = system_showTruncatedText( $info["title"], 25 );
                    $cart_items .= "
					<input type=\"hidden\" name=\"custominvoice_id[]\" value=\"$id\" />
					<input type=\"hidden\" name=\"custominvoice_price[]\" value=\"".$info["subtotal"]."\" />";
                }
            }

            $paypalapi_amount     = str_replace( ",", ".", $bill_info["total_bill"] );
            $contactObj           = new Contact( sess_getAccountIdFromSession() );
            $paypalapi_first_name = $contactObj->getString( "first_name" );
            $paypalapi_last_name  = $contactObj->getString( "last_name" );
?>

			<form name="paypalapiform" target="_self" action="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/<?=$payment_process?>/processpayment.php?payment_method=<?=$payment_method?>" method="post" class="custom-edit-content">

				<div style="display: none;">

                    <?
                        $paypalapi_subtotal   = $paypalapi_amount;
                        if ( $payment_tax_status == "on" )
                        {
                            $paypalapi_tax    = $payment_tax_value;
                            $paypalapi_amount = payment_calculateTax( $paypalapi_subtotal, $payment_tax_value );
                            $taxAmount        = payment_calculateTax( $paypalapi_subtotal, $payment_tax_value, true, false );
                        }
                        else
                        {
                            $paypalapi_tax = 0;
                        }
                    ?>

					<input type="hidden" name="paypalapi_tax" value="<?=$paypalapi_tax?>" />
					<input type="hidden" name="paypalapi_subtotal" value="<?=$paypalapi_subtotal?>" />
					<input type="hidden" name="amount" value="<?=$paypalapi_amount?>" />
					<input type="hidden" name="currency" value="<?=PAYPALAPI_CURRENCY?>" />
					<input type="hidden" name="paymentType" value="Sale" />
					<input type="hidden" name="paypalapi_package_id" value="<?=$package_id?>" />

					<?=$cart_items?>

					<input type="hidden" name="pay" value="1" />

				</div>
                
                <h4 class="heading h-4"><?=system_showText(LANG_LABEL_BILLING_INFO);?></h4>
                <br>

                <div class="row default-row-biling">
                    <div class="form-group col-md-4">
                        <label><?=system_showText(LANG_LABEL_CARD_TYPE);?>:</label>
                        <select class="form-control cutom-select-appearence" name="creditCardType" required class="payment-cardtype">
                            <option value="" disabled selected><?=system_showText(LANG_CHOOSE_PERIOD);?></option>
                            <option value="Visa">Visa</option>
                            <option value="MasterCard">MasterCard</option>
                            <option value="Discover">Discover</option>
                            <option value="Amex">American Express</option>
                        </select>
                    </div>
                    <div class="form-group col-md-8">
                        <label><?=system_showText(LANG_LABEL_CARD_NUMBER);?>:</label>
                        <input class="form-control" type="text" name="creditCardNumber" required value="" />
                    </div>
                </div>

                <div class="row default-row-biling">
                    <div class="form-group col-md-4">
                        <label><?=system_showText(LANG_LABEL_CARD_EXPIRE_DATE);?>:</label>
                        <select class="form-control cutom-select-appearence" name="expdate_month" required class="payment-datemonth">
                            <option value="" disabled selected><?=system_showText(LANG_MONTH);?></option>
                            <option value="1">01</option>
                            <option value="2">02</option>
                            <option value="3">03</option>
                            <option value="4">04</option>
                            <option value="5">05</option>
                            <option value="6">06</option>
                            <option value="7">07</option>
                            <option value="8">08</option>
                            <option value="9">09</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>&nbsp;</label>
                        <select class="form-control cutom-select-appearence" name="expdate_year" required class="payment-dateyear">
                            <option value="" disabled selected><?=system_showText(LANG_YEAR);?></option>
                            <? for ($i=date("Y"); $i<date("Y")+10; $i++) {
                                echo "<option value=\"".$i."\">".$i."</option>";
                            }?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label><?=system_showText(LANG_LABEL_CARD_VERIFICATION_NUMBER);?>:</label>
                        <input class="form-control" type="text" name="cvv2Number" required value="" />
                    </div>
                </div>

                <h4 class="heading h-4"><?=system_showText(LANG_LABEL_CUSTOMER_INFO);?></h4>
                <br>

                <div class="row default-row-biling">
                    <div class="form-group col-md-6">
                        <label><?=system_showText(LANG_LABEL_FIRST_NAME);?>:</label>
                        <input class="form-control" type="text" name="firstName" required value="<?=$paypalapi_first_name?>" />
                    </div>
                    <div class="form-group col-md-6">
                        <label><?=system_showText(LANG_LABEL_LAST_NAME);?>:</label>
                        <input class="form-control" type="text" name="lastName" required value="<?=$paypalapi_last_name?>" />
                    </div>
                </div>

                <div class="row default-row-biling">
                    <div class="form-group col-md-8">
                        <label><?=system_showText(LANG_LABEL_ADDRESS);?>:</label>
                        <input class="form-control" type="text" name="address1" required value="" />
                    </div>
                    <div class="form-group col-md-4">
                        <label><?=system_showText(LANG_LABEL_COUNTRY)?>:</label>
                        <select class="form-control cutom-select-appearence" id="country" name="country">
                            <option value="" disabled selected><?=system_showText(LANG_MSG_SELECT_A_COUNTRY)?></option>
                            <?php foreach ($countries as $Countryname => $CodeISO) { ?>
                                <option value="<?=$CodeISO?>" <?=($country == $CodeISO ? "selected=\"selected\"" : "")?>><?=ucwords(strtolower($Countryname))?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="row default-row-biling">
                    <div class="form-group col-md-4">
                        <label><?=system_showText(LANG_LABEL_STATE)?> / <?=system_showText(LANG_LABEL_PROVINCE)?> :</label>
                        <input class="form-control"  type="text" name="state" required value="" />
                    </div>
                    <div class="form-group col-md-4">
                        <label><?=system_showText(LANG_LABEL_CITY)?>:</label>
                        <input class="form-control"  type="text" name="city"  required value="" />
                    </div>
                    <div class="form-group col-md-4">
                        <label><?=string_ucwords(system_showText(LANG_LABEL_ZIP));?>:</label>
                        <input class="form-control" type="text" name="zip" required value="" />
                    </div>
                </div>

                <div class="payment-action">
                    <button class="button button-md is-outline" type="button" onclick="javascript:history.back(-1);"><?=system_showText(LANG_LABEL_BACK);?></button>

                    <?php if ($payment_process == "signup") {
                        $buttonGateway = "<button class=\"button button-md is-primary action-save\"  type=\"submit\" data-loading-text=\"".LANG_LABEL_FORM_WAIT."\">".system_highlightWords(system_showText(LANG_LABEL_PLACE_ORDER_CONTINUE))."</button>";
                    } else { ?>
                        <button class="button button-md is-primary action-save" type="submit" data-loading-text="<?= LANG_LABEL_FORM_WAIT ?>"><?=system_showText(LANG_BUTTON_PAY_BY_CREDIT_CARD);?></button>
                    <?php } ?>
                </div>
			</form>
			<?
		}
	}
