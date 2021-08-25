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
# * FILE: /includes/code/language.php
# ----------------------------------------------------------------------------------------------------
unset($domainObj);
$domainObj = new Domain(SELECTED_DOMAIN_ID);

$registeredDomain = $domainObj->getString("url");
$registeredDomainID = SELECTED_DOMAIN_ID;

if ((strpos($_SERVER["PHP_SELF"], "registration.php") === false) && (strpos($_SERVER["PHP_SELF"], "about.php") === false)) {

    $activation_warning = "yes";
    $activation_warning_aux = md5("b74bd7de6420911ac59ea1896da8457c".session_id());

    require(EDIRECTORY_ROOT."/".SITEMGR_ALIAS."/registration.php");
    require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");

    unset($activation_warning);
    unset($activation_warning_aux);

    $edir_default_language_aux = $edir_default_language;
    $edir_default_languagenumber_aux = $edir_default_languagenumber;
    $edir_languages_aux = $edir_languages;
    $edir_languagenames_aux = $edir_languagenames;
    $edir_languagenumbers_aux = $edir_languagenumbers;
    $edir_language_aux = $edir_language;

    $edirlanguages = explode("," , $edir_languages_aux);
    $edirlanguagenames = explode("," , $edir_languagenames_aux);
    $edir_languages_aux = "";
    $edir_languagenames_aux = "";
    for ($ediri=0, $ediriMax = count($edirlanguages); $ediri< $ediriMax; $ediri++) {
        if (!$edirlanguages[$ediri]) {
            $edirlanguages[$ediri] = $edir_default_language_aux;
        }
        if (!$edirlanguagenames[$ediri]) {
            $edirlanguagenames[$ediri] = $edirlanguages[$ediri];
        }
        if ($edirlanguages[$ediri] != $edir_default_language_aux) {
            $edir_languages_aux = $edir_languages_aux.$edirlanguages[$ediri].",";
            $edir_languagenames_aux = $edir_languagenames_aux.$edirlanguagenames[$ediri].",";
        } else {
            $edir_languages_aux = $edirlanguages[$ediri].",".$edir_languages_aux;
            $edir_languagenames_aux = $edirlanguagenames[$ediri].",".$edir_languagenames_aux;
        }
    }
    if (strpos($edir_languages_aux, $edir_default_language_aux) === false) {
        $edir_languages_aux = $edir_default_language_aux.",".$edir_languages_aux;
        $edir_languagenames_aux = $edir_default_language_aux.",".$edir_languagenames_aux;
    }
    $edir_languages_aux = substr($edir_languages_aux, 0, strlen($edir_languages_aux)-1);
    $edir_languagenames_aux = substr($edir_languagenames_aux, 0, strlen($edir_languagenames_aux)-1);
    unset($edirlanguages);
    unset($edirlanguagenames);
    unset($ediri);

    $isregisteredBin = isRegistered();
    if (
        (MULTILANGUAGE_FEATURE == "on")
        && isRegistered($registeredDomain,$registeredDomainID)
        && ($edirectory_registration_file == "yes")
        && ($edirectory_registration_aux == md5("499bb0ce1391c3d8497d79097726bfa7".session_id()))
        && ($edirectory_checkregistration_file == "yes")
        && ($edirectory_checkregistration_aux == md5("217413e28563be686aa871241300624a".session_id())))
    {
        $checkMultilanguageBin = true;
    }

    if (!$isregisteredBin || !$checkMultilanguageBin) {

        $edir_default_language_aux = $edir_default_language_aux;
        $edir_languages_aux = $edir_default_language_aux;
        if (strpos($edir_languagenames_aux, ",") !== false) {
            $edir_languagenames_aux = substr($edir_languagenames_aux, 0, strpos($edir_languagenames_aux, ","));
        } else {
            $edir_languagenames_aux = $edir_languagenames_aux;
        }
        $edir_language_aux = $edir_default_language_aux;

        if (strpos($edir_languagenumbers_aux, ",") !== false) {
            $edir_languagenumbers_aux = substr($edir_languagenumbers_aux, 0, strpos($edir_languagenumbers_aux, ","));
        } else {
            $edir_languagenumbers_aux = $edir_languagenumbers_aux;
        }

    }

    define("EDIR_DEFAULT_LANGUAGE", $edir_default_language_aux);
    define("EDIR_DEFAULT_LANGUAGENUMBER", $edir_default_languagenumber_aux);
    define("EDIR_LANGUAGES", $edir_languages_aux);
    define("EDIR_LANGUAGENAMES", $edir_languagenames_aux);
    define("EDIR_LANGUAGENUMBERS", $edir_languagenumbers_aux);

    $isSitemgrLang = false;
    $isAppLang = false;

    if ((strpos($_SERVER["PHP_SELF"], "/".SITEMGR_ALIAS."") !== false) || $loadSitemgrLangs) {
        $isSitemgrLang = true;
        setting_get("sitemgr_language", $sitemgr_language_aux);
    } elseif (strpos($_SERVER["PHP_SELF"], "api") !== false && ($_GET["edir_lang"] || $_POST["edir_lang"])) {
        $isAppLang = true;
        $app_language_aux = ($_GET["edir_lang"] ? $_GET["edir_lang"] : $_POST["edir_lang"]);
    }

    $langPath = language_getFilePath($isSitemgrLang ? $sitemgr_language_aux : ($isAppLang ? $app_language_aux : $edir_language_aux));
    $langPathDefault = language_getFilePath($edir_default_language_aux);

    if (file_exists($langPath)) {
        define("EDIR_LANGUAGE", ($isAppLang ? $app_language_aux : $edir_language_aux));
        $langPart = explode("_", EDIR_LANGUAGE);
        include($langPath);
    } else {
        define("EDIR_LANGUAGE", $edir_default_language_aux);
        $langPart = explode("_", EDIR_LANGUAGE);
        include($langPathDefault);
    }
    if ($isSitemgrLang || $isAppLang) {

        $langPath = language_getFilePath(($isSitemgrLang ? $sitemgr_language_aux : $app_language_aux), false, false, true);
        $langPathDefault = language_getFilePath($edir_default_language_aux, false, false, true);

        if (file_exists($langPath)) {
            include($langPath);
        } else {
            include($langPathDefault);
        }
    }

    unset($edir_default_language_aux);
    unset($edir_languages_aux);
    unset($edir_languagenames_aux);
    unset($edir_language_aux);
    unset($sitemgr_language_aux);
    unset($app_language_aux);

}


/**
 * Preparing Variable to Listing Results and Listing Detail
 */
if (function_exists("isRegistered")) {
    $EDIR_isregisteredBin = isRegistered();
}
