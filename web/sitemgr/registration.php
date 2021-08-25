<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/registration.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
if (strpos($_SERVER["PHP_SELF"], "registration.php") !== false) {
    $resetDomainSession = true;
    include("../conf/loadconfig.inc.php");
}

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
if ((strpos($_SERVER["PHP_SELF"], "registration.php") !== false)) sess_validateSMSession();

# ----------------------------------------------------------------------------------------------------
# AUX
# ----------------------------------------------------------------------------------------------------
$edirectory_registration_file = "yes";
$edirectory_registration_aux = md5("499bb0ce1391c3d8497d79097726bfa7".session_id());

# ----------------------------------------------------------------------------------------------------
# FUNCTIONS
# ----------------------------------------------------------------------------------------------------
if (!function_exists("getKey")) {

    function getKey($domain){
        $domainsql = db_formatString(string_strtolower($domain));
        $dbObj = db_getDBObject(DEFAULT_DB,true);
        $sql = "SELECT * FROM Registration WHERE domain = $domainsql AND (name = 'a' or name = 'b')";
        $result = $dbObj->query($sql);
        if(mysqli_num_rows($result)){

            $aux_key_value = array();
            $aux_key_value[] = $domain;
            while($row = mysqli_fetch_assoc($result)){
                $aux_key_value[] = $row["value"];
            }
            $key_value = md5(implode("_",$aux_key_value));
            return $key_value;
        }else{
            return false;
        }

    }

}
if (!function_exists("getActivationCode")) {

    function getActivationCode($licensenumber, $domain, $version, $forceCheck = false) {

        $activationcode = "";

        $domaincheck = $_SERVER["HTTP_HOST"];
        if (string_strpos($domaincheck, "www.") !== false) {
            $domaincheck = str_replace("www.", "", $domaincheck);
        }
        if (defined('EDIRECTORY_MOBILE') && EDIRECTORY_MOBILE == "on") {
            if (string_strpos($domaincheck, EDIRECTORY_MOBILE_LABEL.".") !== false) {
                $domaincheck = str_replace(EDIRECTORY_MOBILE_LABEL.".", "", $domaincheck);
            }
        }
        if (((string_strpos(DEFAULT_URL, $domaincheck) !== false) && ($domaincheck == $domain)) || $forceCheck) {

            $versioncheck = VERSION;
            if ($versioncheck == $version) {

                $activationcode = $licensenumber.$domain.$version;
                for ($i=0, $iMax = string_strlen($activationcode); $i< $iMax; $i++) {
                    if (!($i%10)) {
                        $activationcode[$i] = '.';
                    }
                    $auxInt = ord($activationcode[$i]);
                    $auxInt += 2;
                    $auxInt %= 255;
                    $activationcode[$i] = chr($auxInt);
                }
                $activationcodemd5 = md5($activationcode);
                $activationcode = string_strtoupper($activationcodemd5);

            }

        }

        return $activationcode;

    }

}

if (!function_exists("softwareRegistration")) {

    function softwareRegistration($licensenumber, $domain, $version, $activationcode) {

        $domaincheck = $_SERVER["HTTP_HOST"];
        if (string_strpos($domaincheck, "www.") !== false) {
            $domaincheck = str_replace("www.", "", $domaincheck);
        }
        if ((string_strpos(DEFAULT_URL, $domaincheck) !== false) && ($domaincheck == $domain)) {

            $versioncheck = VERSION;
            if ($versioncheck == $version) {

                $activationcodecheck = getActivationCode($licensenumber, $domain, $version);
                if (($activationcodecheck) && ($activationcodecheck === $activationcode)) {

                    $dbObj = db_getDBObject(DEFAULT_DB,true);

                    $domainsql = db_formatString(string_strtolower($domain));

                    $date_time = db_formatString(date("Y-m-d H:i:s"));

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('a', $domainsql, $date_time, ".db_formatString(string_strtoupper($licensenumber)).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('b', $domainsql, $date_time, ".db_formatString(string_strtoupper($activationcode)).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('c', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($licensenumber.$activationcode))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('d', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($licensenumber.$domain))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('e', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($licensenumber.$version))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('f', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($licensenumber.$activationcode.$domain.$version))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('g', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode.BRANDED_PRINT))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('h', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."EVENT_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('i', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."BANNER_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('j', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."CLASSIFIED_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('k', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."ARTICLE_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('l', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."ZIPCODE_PROXIMITYoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('m', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."MODREWRITE_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('n', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."CUSTOM_INVOICE_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('o', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."CLAIM_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('p', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."LISTINGTEMPLATE_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('q', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."MOBILE_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('r', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."MULTILANGUAGE_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('s', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."PAYMENTSYSTEM_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('t', $domainsql, $date_time, ".db_formatString(string_strtoupper(md5($activationcode."SITEMAP_FEATUREoff"))).")";
                    $dbObj->query($sql);

                    return true;

                }

            }

        }

        return false;

    }

}

if (!function_exists("isRegistered")) {

    function isRegistered($domain = false, $id = false) {

        $dbObj = db_getDBObject(DEFAULT_DB,true);

        $forceCheck = false;
        if (!$domain) {
            $domain = $_SERVER["HTTP_HOST"];
        } else {
            $forceCheck = true;
        }

        if (string_strpos($domain, "www.") !== false) {
            $domain = str_replace("www.", "", $domain);
        }
        if (defined('EDIRECTORY_MOBILE') && EDIRECTORY_MOBILE == "on") {
            if (string_strpos($domain, EDIRECTORY_MOBILE_LABEL.".") !== false) {
                $domain = str_replace(EDIRECTORY_MOBILE_LABEL.".", "", $domain);
            }
        }
        if (string_strpos(DEFAULT_URL, $domain) === false && !$forceCheck) {
            $domain = "";
        }

        $version = VERSION;

        $domainsql = db_formatString(string_strtolower($domain));

        $date_time_check = "";
        $sql = "SELECT * FROM Registration WHERE domain = $domainsql ORDER BY date_time DESC LIMIT 20";
        $result = $dbObj->query($sql);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                if ((!$date_time_check) || ($date_time_check == $row["date_time"])) {
                    $registration[$row["name"]] = $row["value"];
                    $date_time_check = $row["date_time"];
                }
            }
        }

        if (($registration) && (count($registration) == 20)) {

            if ($registration["a"]) $licensenumber = $registration["a"];
            if ($licensenumber) {

                if ($registration["b"]) $activationcode = $registration["b"];
                if ($activationcode) {

                    if ($activationcode == getActivationCode($licensenumber, $domain, $version, $forceCheck)) {

                        $isregistered = true;

                        $licensenumber_1 = string_substr($licensenumber, 0, 20);
                        $licensenumber_2 = string_substr($licensenumber, 20, 5);
                        $licensenumber_3 = string_substr($licensenumber, 25, 7);

                        if (($licensenumber != $licensenumber_1.$licensenumber_2.string_substr(string_strtoupper(md5($licensenumber_1.$licensenumber_2)), 0, 7)) || ($licensenumber_3 != string_substr(string_strtoupper(md5($licensenumber_1.$licensenumber_2)), 0, 7))) {
                            $isregistered = false;
                        }

                        $features_string = decbin($licensenumber_2);
                        while (string_strlen($features_string) < 14) {
                            $features_string = "0".$features_string;
                        }

                        if ($registration["c"]) {
                            if ($registration["c"] != string_strtoupper(md5($licensenumber.$activationcode))) {
                                $isregistered = false;
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["d"]) {
                            if ($registration["d"] != string_strtoupper(md5($licensenumber.$domain))) {
                                $isregistered = false;
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["e"]) {
                            if ($registration["e"] != string_strtoupper(md5($licensenumber.$version))) {
                                $isregistered = false;
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["f"]) {
                            if ($registration["f"] != string_strtoupper(md5($licensenumber.$activationcode.$domain.$version))) {
                                $isregistered = false;
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["g"]) {
                            if ($forceCheck){
                                $reg_g = domain_findConstants("BRANDED_PRINT", $id);
                            }else{
                                $reg_g = BRANDED_PRINT;
                            }

                            if ($registration["g"] != string_strtoupper(md5($activationcode.$reg_g))) {
                                $isregistered = false;
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["h"]) {
                            if ($forceCheck) $reg_h = domain_findConstants("EVENT_FEATURE", $id);
                            else $reg_h = EVENT_FEATURE;

                            if ($features_string[13] == "0") {
                                if ($registration["h"] != string_strtoupper(md5($activationcode."EVENT_FEATURE".$reg_h))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["i"]) {
                            if ($forceCheck) $reg_i = domain_findConstants("BANNER_FEATURE", $id);
                            else $reg_i = BANNER_FEATURE;

                            if ($features_string[12] == "0") {
                                if ($registration["i"] != string_strtoupper(md5($activationcode."BANNER_FEATURE".$reg_i))) {
                                    $isregistered = false;
                                }
                            }
                        } else {

                            $isregistered = false;
                        }

                        if ($registration["j"]) {
                            if ($forceCheck) $reg_j = domain_findConstants("CLASSIFIED_FEATURE", $id);
                            else $reg_j = CLASSIFIED_FEATURE;

                            if ($features_string[11] == "0") {
                                if ($registration["j"] != string_strtoupper(md5($activationcode."CLASSIFIED_FEATURE".$reg_j))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["k"]) {
                            if ($forceCheck) $reg_k = domain_findConstants("ARTICLE_FEATURE", $id);
                            else $reg_k = ARTICLE_FEATURE;

                            if ($features_string[10] == "0") {
                                if ($registration["k"] != string_strtoupper(md5($activationcode."ARTICLE_FEATURE".$reg_k))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["l"]) {
                            if ($forceCheck) $reg_l = domain_findConstants("ZIPCODE_PROXIMITY", $id);
                            else $reg_l = ZIPCODE_PROXIMITY;

                            if ($features_string[6] == "0") {
                                if ($registration["l"] != string_strtoupper(md5($activationcode."ZIPCODE_PROXIMITY".$reg_l))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["m"]) {
                            if ($forceCheck) $reg_m = domain_findConstants("MODREWRITE_FEATURE", $id);
                            else $reg_m = defined('MODREWRITE_FEATURE') ? MODREWRITE_FEATURE : '';

                            if ($features_string[4] == "0") {
                                if ($registration["m"] != string_strtoupper(md5($activationcode."MODREWRITE_FEATURE".$reg_m))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["n"]) {
                            if ($forceCheck) $reg_n = domain_findConstants("CUSTOM_INVOICE_FEATURE", $id);
                            else $reg_n = CUSTOM_INVOICE_FEATURE;

                            if ($features_string[3] == "0") {
                                if ($registration["n"] != string_strtoupper(md5($activationcode."CUSTOM_INVOICE_FEATURE".$reg_n))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["o"]) {
                            if ($forceCheck) $reg_o = domain_findConstants("CLAIM_FEATURE", $id);
                            else $reg_o = CLAIM_FEATURE;

                            if ($features_string[2] == "0") {
                                if ($registration["o"] != string_strtoupper(md5($activationcode."CLAIM_FEATURE".$reg_o))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["p"]) {
                            if ($forceCheck) $reg_p = domain_findConstants("LISTINGTEMPLATE_FEATURE", $id);
                            else $reg_p = LISTINGTEMPLATE_FEATURE;

                            if ($features_string[9] == "0") {
                                if ($registration["p"] != string_strtoupper(md5($activationcode."LISTINGTEMPLATE_FEATURE".$reg_p))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["q"]) {
                            if ($forceCheck) $reg_q = domain_findConstants("MOBILE_FEATURE", $id);
                            else $reg_q = MOBILE_FEATURE;

                            if ($features_string[8] == "0") {
                                if ($registration["q"] != string_strtoupper(md5($activationcode."MOBILE_FEATURE".$reg_q))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["r"]) {
                            if ($forceCheck) $reg_r = domain_findConstants("MULTILANGUAGE_FEATURE", $id);
                            else $reg_r = MULTILANGUAGE_FEATURE;

                            if ($features_string[7] == "0") {
                                if ($registration["r"] != string_strtoupper(md5($activationcode."MULTILANGUAGE_FEATURE".$reg_r))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["s"]) {
                            if ($forceCheck) $reg_s = domain_findConstants("PAYMENTSYSTEM_FEATURE", $id);
                            else $reg_s = PAYMENTSYSTEM_FEATURE;

                            if ($features_string[1] == "0") {
                                if ($registration["s"] != string_strtoupper(md5($activationcode."PAYMENTSYSTEM_FEATURE".$reg_s))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($registration["t"]) {
                            if ($forceCheck) $reg_t = domain_findConstants("SITEMAP_FEATURE", $id);
                            else $reg_t = SITEMAP_FEATURE;

                            if ($features_string[0] == "0") {
                                if ($registration["t"] != string_strtoupper(md5($activationcode."SITEMAP_FEATURE".$reg_t))) {
                                    $isregistered = false;
                                }
                            }
                        } else {
                            $isregistered = false;
                        }

                        if ($isregistered) {
                            $sql = "SELECT * FROM Registration WHERE name = 'extra' AND domain = '' AND date_time = '0000-00-00 00:00:00' LIMIT 1";
                            $result = $dbObj->query($sql);
                            if ($result) {
                                if ($row = mysqli_fetch_assoc($result)) {
                                    if ($row["value"] != string_strtoupper(md5($version."SITEMAP_FEATUREon"))) {
                                        $sql = "UPDATE Registration SET value = ".db_formatString(string_strtoupper(md5($version."SITEMAP_FEATUREon")))." WHERE name = 'extra' AND domain = '' AND date_time = '0000-00-00 00:00:00' LIMIT 1";
                                        $dbObj->query($sql);
                                    }
                                } else {
                                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('extra', '', '0000-00-00 00:00:00', ".db_formatString(string_strtoupper(md5($version."SITEMAP_FEATUREon"))).")";
                                    $dbObj->query($sql);
                                }
                            } else {
                                $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('extra', '', '0000-00-00 00:00:00', ".db_formatString(string_strtoupper(md5($version."SITEMAP_FEATUREon"))).")";
                                $dbObj->query($sql);
                            }
                            return true;
                        }

                    }

                }

            }

        }

        if ($_SERVER["HTTP_HOST"]) {
            $sql = "SELECT * FROM Registration WHERE name = 'extra' AND domain = '' AND date_time = '0000-00-00 00:00:00' LIMIT 1";
            $result = $dbObj->query($sql);
            if ($result) {
                if ($row = mysqli_fetch_assoc($result)) {
                    if ($row["value"] != string_strtoupper(md5($version."SITEMAP_FEATUREoff"))) {
                        $sql = "UPDATE Registration SET value = ".db_formatString(string_strtoupper(md5($version."SITEMAP_FEATUREoff")))." WHERE name = 'extra' AND domain = '' AND date_time = '0000-00-00 00:00:00' LIMIT 1";
                        $dbObj->query($sql);
                    }
                } else {
                    $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('extra', '', '0000-00-00 00:00:00', ".db_formatString(string_strtoupper(md5($version."SITEMAP_FEATUREoff"))).")";
                    $dbObj->query($sql);
                }
            } else {
                $sql = "INSERT INTO Registration (name, domain, date_time, value) VALUES ('extra', '', '0000-00-00 00:00:00', ".db_formatString(string_strtoupper(md5($version."SITEMAP_FEATUREoff"))).")";
                $dbObj->query($sql);
            }
        }

        return false;

    }

}

# ----------------------------------------------------------------------------------------------------
# SUBMIT
# ----------------------------------------------------------------------------------------------------
if ((string_strpos($_SERVER["PHP_SELF"], "registration.php") !== false)) {

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (!isRegistered()) {

            $licensenumber = string_strtoupper(preg_replace('/[^0-9a-zA-Z]/i', '', $_POST["license_field"]));

            if (string_strlen($licensenumber) != 32) {

                $message = "Wrong license number!";
                $activationerror = 1;

            } else {

                $licensenumber_1 = string_substr($licensenumber, 0, 20);
                $licensenumber_2 = string_substr($licensenumber, 20, 5);
                $licensenumber_3 = string_substr($licensenumber, 25, 7);

                if (($licensenumber != $licensenumber_1.$licensenumber_2.string_substr(string_strtoupper(md5($licensenumber_1.$licensenumber_2)), 0, 7)) || ($licensenumber_3 != string_substr(string_strtoupper(md5($licensenumber_1.$licensenumber_2)), 0, 7))) {

                    $message = "Wrong license number!";
                    $activationerror = 1;

                } else {

                    $features_string = decbin($licensenumber_2);
                    while (string_strlen($features_string) < 14) {
                        $features_string = "0".$features_string;
                    }
                    $message = "";
                    if (($features_string[13] == "0") && (EVENT_FEATURE == "on")) {
                        $message .= "Wrong event feature constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[12] == "0") && (BANNER_FEATURE == "on")) {
                        $message .= "Wrong banner feature constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[11] == "0") && (CLASSIFIED_FEATURE == "on")) {
                        $message .= "Wrong classified feature constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[10] == "0") && (ARTICLE_FEATURE == "on")) {
                        $message .= "Wrong article feature constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[9] == "0") && (LISTINGTEMPLATE_FEATURE == "on")) {
                        $message .= "Wrong listing template feature constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[8] == "0") && (MOBILE_FEATURE == "on")) {
                        $message .= "Wrong mobile feature constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[7] == "0") && (MULTILANGUAGE_FEATURE == "on")) {
                        $message .= "Wrong multilanguage feature constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[6] == "0") && (ZIPCODE_PROXIMITY == "on")) {
                        $message .= "Wrong zip proximity constant!<br />";
                        $activationerror = 1;
                    }
                    if ((($features_string[5] == "0") && (BRANDED_PRINT == "on")) || (($features_string[5] == "1") && (BRANDED_PRINT != "on"))) {
                        $message .= "Wrong branded print constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[4] == "0") && (MODREWRITE_FEATURE == "on")) {
                        $message .= "Wrong mod rewrite feature constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[3] == "0") && (CUSTOM_INVOICE_FEATURE == "on")) {
                        $message .= "Wrong custom invoice feature constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[2] == "0") && (CLAIM_FEATURE == "on")) {
                        $message .= "Wrong claim feature constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[1] == "0") && (PAYMENTSYSTEM_FEATURE == "on")) {
                        $message .= "Wrong payment system feature constant!<br />";
                        $activationerror = 1;
                    }
                    if (($features_string[0] == "0") && (SITEMAP_FEATURE == "on")) {
                        $message .= "Wrong sitemap feature constant!<br />";
                        $activationerror = 1;
                    }

                    if (!$activationerror) {

                        $domain = $_SERVER["HTTP_HOST"];
                        if (string_strpos($domain, "www.") !== false) {
                            $domain = str_replace("www.", "", $domain);
                        }
                        if (string_strpos(DEFAULT_URL, $domain) === false) {
                            $domain = "";
                        }
                        $domainsql = db_formatString(string_strtolower($domain));

                        $dbObj = db_getDBObject(DEFAULT_DB,true);
                        $sql = "SELECT * FROM Registration WHERE domain = $domainsql AND name = 'a'";
                        $result = $dbObj->query($sql);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                if ($row["value"] == $licensenumber) {
                                    $sql = "DELETE FROM Registration WHERE domain = $domainsql";
                                    $dbObj->query($sql);
                                }
                            }
                        }

                    }

                }

                if (!$activationerror) {

                    if ($_POST["activation_by_phone"]) {

                        $activationcode = string_strtoupper(preg_replace('/[^0-9a-zA-Z]/i', '', $_POST["activation_field"]));

                    } else {

                        ####################################################################################################
                        ### GETTING ACTIVATION CODE BY CLIENT URL
                        ####################################################################################################

                        $ch = curl_init();
                        if ($ch) {

                            $curl_setopt_count_error = 0;

                            if (DEMO_LIVE_MODE || DEMO_DEV_MODE) {
                                $autoregistrationdomain = "http://activationdev.arcasolutions.com.br";
                            } else {
                                $autoregistrationdomain = "http://activation.arcasolutions.com";
                            }
                            if (!curl_setopt($ch, CURLOPT_URL, $autoregistrationdomain."/autoregistration.php")) {
                                $message = "Software Registration Error: 30500";
                                $activationerror = 1;
                                $curl_setopt_count_error++;
                            }

                            if (!curl_setopt($ch, CURLOPT_FAILONERROR, 1)) {
                                $message = "Software Registration Error: 30501";
                                $activationerror = 1;
                                $curl_setopt_count_error++;
                            }

                            if (!curl_setopt($ch, CURLOPT_TIMEOUT, 600)) {
                                $message = "Software Registration Error: 30502";
                                $activationerror = 1;
                                $curl_setopt_count_error++;
                            }

                            if (!curl_setopt($ch, CURLOPT_REFERER, "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"])) {
                                $message = "Software Registration Error: 30503";
                                $activationerror = 1;
                                $curl_setopt_count_error++;
                            }

                            if (!curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1)) {
                                $message = "Software Registration Error: 30504";
                                $activationerror = 1;
                                $curl_setopt_count_error++;
                            }

                            if (!curl_setopt($ch, CURLOPT_POST, 1)) {
                                $message = "Software Registration Error: 30505";
                                $activationerror = 1;
                                $curl_setopt_count_error++;
                            }

                            $postfields = array();
                            $postfields[] = "licensenumber=".urlencode(trim($licensenumber));
                            $postfields[] = "project=eDirectory";
                            $domain = $_SERVER["HTTP_HOST"];
                            if (string_strpos($domain, "www.") !== false) {
                                $domain = str_replace("www.", "", $domain);
                            }
                            if (string_strpos(DEFAULT_URL, $domain) === false) {
                                $domain = "";
                            }
                            $postfields[] = "domain=".urlencode(trim($domain));
                            $postfields[] = "version=".urlencode(trim(VERSION));
                            if (!curl_setopt($ch, CURLOPT_POSTFIELDS, implode("&", $postfields))) {
                                $message = "Software Registration Error: 30506";
                                $activationerror = 1;
                                $curl_setopt_count_error++;
                            }

                            if (!$curl_setopt_count_error) {

                                $curl_response = curl_exec($ch);
                                if (curl_errno($ch) || curl_error($ch)) {
                                    $message = "Software Registration Error: 30100<br />(".curl_errno($ch).") ".curl_error($ch)."";
                                    $activationerror = 1;
                                } else {

                                    curl_close($ch);

                                    if ($curl_response) {

                                        if (string_strpos($curl_response, "success=") !== false) {

                                            $curl_response = str_replace("success=", "", $curl_response);
                                            $activationcode = $curl_response;

                                        } elseif (string_strpos($curl_response, "fail=") !== false) {

                                            $curl_response = str_replace("fail=", "", $curl_response);
                                            $message = $curl_response;
                                            $activationerror = 1;

                                        } else {
                                            $message = "Software Registration Error: 30300";
                                            $activationerror = 1;
                                        }

                                    } else {
                                        $message = "Software Registration Error: 30200";
                                        $activationerror = 1;
                                    }

                                }

                            }

                        } else {
                            $message = "Software Registration Error: 30000";
                            $activationerror = 1;
                        }

                        ####################################################################################################

                    }

                    if (!$activationerror) {

                        if (string_strlen($activationcode) != 32) {

                            $message = "Wrong activation code!";
                            $activationerror = 1;

                        }

                    }

                }

            }

            if (!$activationerror) {

                $domain = $_SERVER["HTTP_HOST"];
                if (string_strpos($domain, "www.") !== false) {
                    $domain = str_replace("www.", "", $domain);
                }
                if (string_strpos(DEFAULT_URL, $domain) === false) {
                    $domain = "";
                }

                if ((!$activationcode) || ($activationcode != getActivationCode($licensenumber, $domain, VERSION))) {

                    $message = "Wrong license number and/or activation code!";
                    $activationerror = 1;

                } else {

                    if (!softwareRegistration($licensenumber, $domain, VERSION, $activationcode)) {

                        $message = "Wrong registration! Please try again!";
                        $activationerror = 1;

                    } else {

                        $activationdone = 1;

                    }

                }

            }

            if ($activationdone) {
                $domainObj = new Domain(SELECTED_DOMAIN_ID);
                $domainObj->changeActivationStatus();
                header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/registration.php?activationdone=".$activationdone."");
            }
            elseif ($message) header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/registration.php?message=".urlencode($message)."");
            else header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/registration.php");
            exit;

        }

    }

}

# ----------------------------------------------------------------------------------------------------
# HEADER
# ----------------------------------------------------------------------------------------------------
if ((string_strpos($_SERVER["PHP_SELF"], "registration.php") !== false)) include(SM_EDIRECTORY_ROOT."/layout/header.php");

# ----------------------------------------------------------------------------------------------------
# HTML
# ----------------------------------------------------------------------------------------------------
if (string_strpos($_SERVER["PHP_SELF"], "registration.php") !== false) {

    # ----------------------------------------------------------------------------------------------------
    # Registration Main Page
    # ----------------------------------------------------------------------------------------------------

    include(SM_EDIRECTORY_ROOT."/layout/navbar.php");
    ?>

    <div class="container">
        <div class="container-fluid row">
            <div class="col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2">
                <br>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Welcome to the Software Registration page</h1>
                    </div>
                    <div class="panel-body">
                        <? if ($_GET["message"]) { ?>
                            <div class="alert alert-warning">
                                <p><b><?=$_GET["message"]?></b> <a href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/registration.php" class="text-warning">Click here to try again.</a></p>
                            </div>
                        <? } else {

                            if (!$_GET["activationdone"]) {

                                if (!isRegistered()) { ?>

                                    <form action="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/registration.php" role="form" method="post">

                                        <p>This copy of eDirectory is licensed for:
                                            <mark>
                                                <?
                                                $domain = $_SERVER["HTTP_HOST"];
                                                if (string_strpos($domain, "www.") !== false) {
                                                    $domain = str_replace("www.", "", $domain);
                                                }
                                                if (string_strpos(DEFAULT_URL, $domain) === false) {
                                                    $domain = "";
                                                }
                                                echo $domain;
                                                ?>
                                            </mark>
                                        </p>
                                        <h2>To activate your software follow one of these processes:</h2>
                                        <br>
                                        <p>Insert your license number in the "License Number" text field and click the "Activate" button to activate your software.</p>
                                        <p class="text-center">Or</p><br>
                                        <p>Click "Activate by phone" checkbox, insert your license number in the "License Number" text field, insert your activation code (phone and obtain the code) in the "Activation Code" text field and click the "Activate" button to activate your software.</p>
                                        <br>
                                        <br>

                                        <div class="row">
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <label for="license_field">License Number</label>
                                                    <?
                                                    $dbObj = db_getDBObject(DEFAULT_DB,true);
                                                    $sql = "SELECT * FROM Registration WHERE name = 'license' AND domain = '' AND date_time = '0000-00-00 00:00:00' LIMIT 1";
                                                    $result = $dbObj->query($sql);
                                                    if ($result && ($row = mysqli_fetch_assoc($result))) { ?>
                                                        <input class="form-control" type="text" id="license_field" name="license_field" value="<?=$row["value"];?>" maxlength="39" />
                                                    <? } else { ?>
                                                        <input class="form-control" type="text" id="license_field" name="license_field"  maxlength="39" />
                                                    <? } ?>
                                                </div>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" id="activation_by_phone" name="activation_by_phone" class="inputCheck" onClick="activationByPhone()" /> <b>Activate by phone</b>
                                                    </label>
                                                </div>
                                                <div id="table_activation" class="form-group hidden">
                                                    <label for="activation_field">Activation Code</label>
                                                    <input class="form-control" type="text" id="activation_field" name="activation_field" maxlength="39" >
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <button type="submit" name="submit_button"  class="btn btn-primary">Activate</button>
                                            </div>
                                        </div>

                                    </form>

                                <? } else { ?>
                                    <h2>eDirectory has been already Registered!</h2>
                                    <a href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/">Click here to go back.</a>
                                <? } ?>

                            <? } else { ?>
                                <h2>eDirectory has been Activated!</h2>
                                <a href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/">Click here to go back.</a>
                            <? } ?>

                        <? } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?

} elseif (string_strpos($_SERVER["PHP_SELF"], "about.php") !== false) {

    # ----------------------------------------------------------------------------------------------------
    # About Modal
    # ----------------------------------------------------------------------------------------------------
    ?>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="ROBOTS" content="index, follow">
        <meta name="author" content="Arca Solutions">
        <?
        setting_get("header_title", $headertag_title);
        $headertag_title = (($headertag_title) ? ($headertag_title) : (EDIRECTORY_TITLE));
        ?>
        <title><?=$headertag_title?></title>
        <link href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/layout/general_sitemgr.css" rel="stylesheet" type="text/css" />
        <link href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/layout/sitemgr_registration.css" rel="stylesheet" type="text/css" />

    </head>
    <body>
    <div class="wrapper aboutWrapper">
        <div class="header2" style="width: auto;">
            <span class="logoLink">&nbsp;</span>
        </div>
        <div class="content">

            <?
            include(EDIRECTORY_ROOT."/custom/domain/domain.inc.php");
            foreach ($domainInfo as $_url=>$_id) {
                if ($_id == SELECTED_DOMAIN_ID) {
                    $selectedDomain["url"] = $_url;
                    $selectedDomain["id"] = $_id;
                    break;
                }
            }
            unset($domainInfo, $_url, $_id);

            if (!isRegistered($selectedDomain["url"], $selectedDomain["id"])) { ?>
                <h1>Activation is required to use <span>eDirectory!</span></h1>
            <? } ?>

            <p class="copyright">
                Copyright &copy; <?=date("Y");?> <a href="http://www.arcasolutions.com" target="_blank">Arca Solutions Inc</a>.
                <br />
                Powered by <a href="http://www.edirectory.com<?=(string_strpos($_SERVER["HTTP_HOST"], ".com.br") !== false ? ".br" : "")?>" target="_blank">eDirectory Cloud Service</a>&trade;.
                <br />
                All Rights Reserved.
            </p>

            <?
            $dbObj = db_getDBObject(DEFAULT_DB,true);
            $domain = $selectedDomain["url"];
            if (string_strpos($domain, "www.") !== false) {
                $domain = str_replace("www.", "", $domain);
            }
            //						if (string_strpos(DEFAULT_URL, $domain) === false) {
            //							$domain = "";
            //						}
            $domainsql = db_formatString(string_strtolower($domain));
            $date_time_check = "";
            $sql = "SELECT * FROM Registration WHERE domain = $domainsql ORDER BY date_time DESC LIMIT 20";
            $result = $dbObj->query($sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if ((!$date_time_check) || ($date_time_check == $row["date_time"])) {
                        $registration[$row["name"]] = $row["value"];
                        $date_time_check == $row["date_time"];
                    }
                }
            }
            if ($registration["a"] && $registration["b"] && $registration["c"] && $registration["d"] && $registration["e"] && $registration["f"] && $registration["g"] && $registration["h"] && $registration["i"] && $registration["j"] && $registration["k"] && $registration["l"] && $registration["m"] && $registration["n"] && $registration["o"] && $registration["p"] && $registration["q"] && $registration["r"] && $registration["s"] && $registration["t"]) {
                $version_label = VERSION;
                if ($registration["e"] != string_strtoupper(md5($registration["a"].$version_label))) {
                    $version_label = "v.?.?.??";
                }
                ?>
                <p>
                    <span>This copy of eDirectory (<?=$version_label;?>) is licensed for domain:</span>
                    <?=$domain;?>
                    <br />
                    <span>License Number:</span>
                    <?
                    $licensenumber = $registration["a"];
                    echo string_substr($licensenumber, 0, 4)."-".string_substr($licensenumber, 4, 4)."-".string_substr($licensenumber, 8, 4)."-".string_substr($licensenumber, 12, 4)."-".string_substr($licensenumber, 16, 4)."-".string_substr($licensenumber, 20, 4)."-".string_substr($licensenumber, 24, 4)."-".string_substr($licensenumber, 28, 4);
                    ?>
                    <br />
                    <span>Activation Code:</span>
                    <?
                    $activationcode = $registration["b"];
                    echo string_substr($activationcode, 0, 4)?>-<?=string_substr($activationcode, 4, 4)?>-<?=string_substr($activationcode, 8, 4)?>-<?=string_substr($activationcode, 12, 4)?>-<?=string_substr($activationcode, 16, 4)?>-<?=string_substr($activationcode, 20, 4)?>-<?=string_substr($activationcode, 24, 4)?>-<?=string_substr($activationcode, 28, 4);
                    ?>
                </p>
                <?
                if ($version_label == "v.?.?.??") {
                    ?>
                    <div class="notAvailable">
                        <p>
                            <span>Version Number:</span>
                        </p>
                        <ul>
                            <li><strong>Wrong Version Number</strong></li>
                        </ul>
                    </div>
                    <?
                }
                $features_string = string_substr($licensenumber, 20, 5);
                $features_string = decbin($features_string);
                while (string_strlen($features_string) < 14) {
                    $features_string = "0".$features_string;
                }
                if ($features_string[13] == "1") {
                    $available_features[] = "Event";
                }
                if ($features_string[12] == "1") {
                    $available_features[] = "Banner";
                }
                if ($features_string[11] == "1") {
                    $available_features[] = "Classified";
                }
                if ($features_string[10] == "1") {
                    $available_features[] = "Article";
                }
                if ($features_string[9] == "1") {
                    $available_features[] = "Listing Template";
                }
                if ($features_string[8] == "1") {
                    $available_features[] = "Mobile";
                }
                if ($features_string[7] == "1") {
                    $available_features[] = "Multilanguage";
                }
                if ($features_string[6] == "1") {
                    $available_features[] = "Zip Proximity";
                }
                if ($features_string[5] == "1") {
                    if (BRANDED_PRINT == "on") $available_features[] = "Branded";
                    else $available_features[] = "<strong>Branded (Wrong Setup)</strong>";
                }
                if ($features_string[4] == "1") {
                    $available_features[] = "Mod Rewrite";
                }
                if ($features_string[3] == "1") {
                    $available_features[] = "Custom Invoice";
                }
                if ($features_string[2] == "1") {
                    $available_features[] = "Claim";
                }
                if ($features_string[1] == "1") {
                    $available_features[] = "Payment System";
                }
                if ($features_string[0] == "1") {
                    $available_features[] = "Sitemap";
                }
                if ($available_features && (count($available_features) > 0)) {
                    ?>
                    <div class="available">
                        <p>
                            <span>Available Features:</span>
                        </p>
                        <ul>
                            <?
                            foreach ($available_features as $available_feature) {
                                echo "<li>".$available_feature."</li>";
                            }
                            ?>
                        </ul>
                    </div>
                    <?
                }
                if (($features_string[13] == "0") && (domain_findConstants("EVENT_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Event";
                }
                if (($features_string[12] == "0") && (domain_findConstants("BANNER_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Banner";
                }
                if (($features_string[11] == "0") && (domain_findConstants("CLASSIFIED_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Classified";
                }
                if (($features_string[10] == "0") && (domain_findConstants("ARTICLE_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Article";
                }
                if (($features_string[9] == "0") && (domain_findConstants("LISTINGTEMPLATE_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Listing Template";
                }
                if (($features_string[8] == "0") && (domain_findConstants("MOBILE_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Mobile";
                }
                if (($features_string[7] == "0") && (domain_findConstants("MULTILANGUAGE_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Multilanguage";
                }
                if (($features_string[6] == "0") && (domain_findConstants("ZIPCODE_PROXIMITY", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Zip Proximity";
                }
                if (($features_string[5] == "0") && (domain_findConstants("BRANDED_PRINT", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Branded";
                }
                if (($features_string[4] == "0") && (domain_findConstants("MODREWRITE_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Mod Rewrite";
                }
                if (($features_string[3] == "0") && (domain_findConstants("CUSTOM_INVOICE_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Custom Invoice";
                }
                if (($features_string[2] == "0") && (domain_findConstants("CLAIM_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Claim";
                }
                if (($features_string[1] == "0") && (domain_findConstants("PAYMENTSYSTEM_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Payment System";
                }
                if (($features_string[0] == "0") && (domain_findConstants("SITEMAP_FEATURE", SELECTED_DOMAIN_ID) == "on")) {
                    $notavailable_features[] = "Sitemap";
                }
                if ($notavailable_features && (count($notavailable_features) > 0)) {
                    ?>
                    <div class="notAvailable">
                        <p>
                            <span>Not Available Features:</span>
                        </p>
                        <ul>
                            <?
                            foreach ($notavailable_features as $notavailable_feature) {
                                echo "<li>".$notavailable_feature."</li>";
                            }
                            ?>
                        </ul>
                    </div>
                    <?
                }
            }
            ?>

        </div>
    </div>
    </body>
    </html>
    <?

} else {

    # ----------------------------------------------------------------------------------------------------
    # Registration Warning Alert
    # ----------------------------------------------------------------------------------------------------
    if (!(($activation_warning == "yes") && ($activation_warning_aux == md5("b74bd7de6420911ac59ea1896da8457c".session_id())))) {
        include(EDIRECTORY_ROOT."/custom/domain/domain.inc.php");
        foreach ($domainInfo as $_url => $_id) {
            if ($_id == SELECTED_DOMAIN_ID) {
                $selectedDomain["url"] = $_url;
                $selectedDomain["id"] = $_id;
                break;
            }
        }
        unset($domainInfo, $_url, $_id);
        if (!isRegistered($selectedDomain["url"], $selectedDomain["id"])) {
            if ((string_strpos($_SERVER["PHP_SELF"],
                        "/".SITEMGR_ALIAS."") !== false) || (string_strpos($_SERVER["PHP_SELF"],
                        "/".MEMBERS_ALIAS."") !== false)) {

                include(EDIRECTORY_ROOT."/custom/domain/domain.inc.php");
                foreach ($domainInfo as $_url => $_id) {
                    if ($_id == $_GET["domain_id"]) {
                        $selectedDomain["url"] = $_url;
                        $selectedDomain["id"] = $_id;
                        break;
                    }
                }
                unset($domainInfo, $_url, $_id);

                if (string_strpos($_SERVER["PHP_SELF"], "/".SITEMGR_ALIAS."") !== false) { ?>

                    <div class="modal modal-danger fade" id="aux-ed-modal">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                            class="sr-only">Close</span></button>
                                    <h4 class="modal-title">Activation</h4>
                                </div>

                                <? if (!isRegistered($selectedDomain["url"], $selectedDomain["id"])) { ?>

                                    <div class="modal-body text-center">
                                        <h3>Oh, wait!</h3>
                                        <h2>This eDirectory is not activated!</h2>
                                        <p>Activation is required to use this software.</p>
                                        <br><br><br>
                                    </div> <!-- Close modal-body-->

                                    <? if (string_strpos($_SERVER["PHP_SELF"], "/".SITEMGR_ALIAS."") !== false) {
                                        $urlMODE = "http://";
                                        if (SSL_ENABLED == "on" && FORCE_SITEMGR_SSL == "on") {
                                            $urlMODE = "https://";
                                        }
                                        $redirectUrl = $urlMODE.$selectedDomain["url"].EDIRECTORY_FOLDER;
                                        ?>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Later
                                            </button>
                                            <button type="button" class="btn btn-danger"
                                                    onclick="self.parent.location='<?= $redirectUrl; ?>/<?= SITEMGR_ALIAS ?>/registration.php';">
                                                Activate Now
                                            </button>
                                        </div>

                                    <? } else { ?>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Got it!
                                                I'll contact the site manager.
                                            </button>
                                        </div>

                                    <? } ?>

                                <? } else { ?>

                                    <div class="modal-body text-center">
                                        <h2>eDirectory already has been activated!</h2>
                                        <p>Thank you for choosen eDirectory!</p>
                                        <br><br><br>
                                    </div> <!-- Close modal-body-->

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                        </button>
                                    </div>

                                <? } ?>

                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <script>var aux_force_showModal = true;</script>

                    <?
                    $urlMODE = "http://";
                    if (SSL_ENABLED == "on" && FORCE_SITEMGR_SSL == "on") {
                        $urlMODE = "https://";
                    }
                    $redirectUrl = $urlMODE.$selectedDomain["url"].EDIRECTORY_FOLDER;

                    if ($_SERVER["HTTP_HOST"] != $selectedDomain["url"]) {
                        $sessUID = sess_getSMIdFromSession();
                        if (!$sessUID) {
                            setting_get("complementary_info", $sessCInfo);
                        } else {
                            $smAObj = new SMAccount($sessUID);
                            $sessCInfo = $smAObj->getString("complementary_info");
                            unset($smAObj);
                        }
                    }
                    ?>

                    <div class="footer-message">
                        <div class="alert alert-warning  alert-dismissible" role="alert">
                            <p><strong>eDirectory activation required!</strong> <a
                                    href="<?= $redirectUrl; ?>/<?= SITEMGR_ALIAS ?>/registration.php"
                                    class="text-danger">Click here to activate it.</a></p>
                        </div>
                    </div>
                <? } else { ?>
                    <div class="footer-message">
                        <div class="alert alert-warning alert-dismissible" role="alert">
                            <p><strong>eDirectory activation required!</strong> Please, contact the site manager.</p>
                        </div>
                    </div>
                    <?
                }
            }
        }
    }
}

# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
if ((string_strpos($_SERVER["PHP_SELF"], "registration.php") !== false)) {
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/general.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
}
