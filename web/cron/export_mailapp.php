#!/usr/bin/php -q
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
# * FILE: /cron/export_mailapp.php
# ----------------------------------------------------------------------------------------------------

////////////////////////////////////////////////////////////////////////////////////////////////////
ini_set("html_errors", false);
////////////////////////////////////////////////////////////////////////////////////////////////////
define("EXPORT_MAIL_BLOCK", 50000);

////////////////////////////////////////////////////////////////////////////////////////////////////
define("EDIRECTORY_ROOT", __DIR__ . "/..");
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$_inCron = true;
$loadSitemgrLangs = true;
include_once(EDIRECTORY_ROOT . "/conf/config.inc.php");
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$host = _DIRECTORYDB_HOST;
$db = _DIRECTORYDB_NAME;
$user = _DIRECTORYDB_USER;
$pass = _DIRECTORYDB_PASS;
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$link = ($GLOBALS["___mysqli_ston"] = mysqli_connect($host,  $user,  $pass));
mysqli_query( $link, "SET NAMES 'utf8'");
mysqli_query( $link, 'SET character_set_connection=utf8');
mysqli_query( $link, 'SET character_set_client=utf8');
mysqli_query( $link, 'SET character_set_results=utf8');
mysqli_select_db($GLOBALS["___mysqli_ston"], $db);
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$sqlDomain = "	SELECT
						Domain.`id`,
                        Control_Export_MailApp.`last_exportlog`,
                        Control_Export_MailApp.`domain_id`
					FROM `Domain` AS Domain
                        LEFT OUTER JOIN `Control_Export_MailApp` AS Control_Export_MailApp ON (Control_Export_MailApp.`domain_id` = Domain.`id`)
					WHERE Control_Export_MailApp.`scheduled` = 'Y' AND
                          (Control_Export_MailApp.`running` = 'N' OR (ADDDATE(Control_Export_MailApp.`last_run_date`, INTERVAL 1 DAY) <= NOW() OR Control_Export_MailApp.`last_run_date` = '0000-00-00 00:00:00'))
                          AND Domain.`status` = 'A'
					ORDER BY
						IF (Control_Export_MailApp.`last_run_date` IS NULL, 0, 1),
						Control_Export_MailApp.`last_run_date`,
						Domain.`id`
					LIMIT 1";
$resDomain = mysqli_query( $link, $sqlDomain);

$sqlRunning = "SELECT `domain_id` FROM `Control_Export_MailApp` WHERE `running` = 'Y' LIMIT 1";
$resRunning = mysqli_query( $link, $sqlRunning);

if (mysqli_num_rows($resDomain) > 0 && mysqli_num_rows($resRunning) == 0) {
    $rowDomain = mysqli_fetch_assoc($resDomain);
    define("SELECTED_DOMAIN_ID", $rowDomain["id"]);

    $sqlUpdate = "UPDATE `Control_Export_MailApp` SET `scheduled` = 'N', `running` = 'Y', `last_run_date` = NOW() WHERE `domain_id` = " . $rowDomain["id"];
    mysqli_query( $link, $sqlUpdate);
    $last_export_log = $rowDomain["last_exportlog"];

} else {
    exit;
}

$_inCron = false;
include_once(EDIRECTORY_ROOT . "/conf/loadconfig.inc.php");
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());

    return ((float)$usec + (float)$sec);
}

$time_start = getmicrotime();
////////////////////////////////////////////////////////////////////////////////////////////////////

$mailAppObj = new MailAppList();
$mailAppObj->exportList(SELECTED_DOMAIN_ID, $last_export_log);

$time_end = getmicrotime();
$time = $time_end - $time_start;

print "Export MailApp on Domain " . SELECTED_DOMAIN_ID . " - " . date("Y-m-d H:i:s") . " - " . round($time,
        2) . " seconds.\n";
