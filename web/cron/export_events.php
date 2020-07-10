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
# * FILE: /cron/export_events.php
# ----------------------------------------------------------------------------------------------------

////////////////////////////////////////////////////////////////////////////////////////////////////
ini_set("html_errors", false);
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
define("EDIRECTORY_ROOT", __DIR__ . "/..");
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$_inCron = true;
$loadSitemgrLangs = true;
include_once(EDIRECTORY_ROOT . "/conf/config.inc.php");

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

$sql_check_export = "SELECT
							D.`database_host`, D.`database_port`, D.`database_username`, D.`database_password`, D.`database_name`, CEL.`id`, CEL.`type`, CEL.`filename`, CEL.`domain_id`, CEL.`block`, CEL.`total_event_exported`
						FROM `Domain` AS D
						LEFT JOIN `Control_Export_Event` AS CEL ON (CEL.`domain_id` = D.`id`)
						WHERE CEL.`scheduled` = 'Y'
						AND D.`status` = 'A'
						ORDER BY
							IF (CEL.`last_run_date` IS NULL, 0, 1),
							CEL.`last_run_date`,
							D.`id`
						LIMIT 1";

$result_check_export = mysqli_query( $link, $sql_check_export);
if (mysqli_num_rows($result_check_export)) {
    $row = mysqli_fetch_array($result_check_export);
    $type = $row["type"];
    $filename = $row["filename"];
    $domain_id = $row["domain_id"];

    $db_host = $row["database_host"] . ($row["database_port"] ? $row["database_port"] : "");
    $db_username = $row["database_username"];
    $db_password = $row["database_password"];
    $db_name = $row["database_name"];

    $linkDomain = ($GLOBALS["___mysqli_ston"] = mysqli_connect($db_host,  $db_username,  $db_password));
    mysqli_query( $linkDomain, "SET NAMES 'utf8'");
    mysqli_query( $linkDomain, 'SET character_set_connection=utf8');
    mysqli_query( $linkDomain, 'SET character_set_client=utf8');
    mysqli_query( $linkDomain, 'SET character_set_results=utf8');
    mysqli_select_db($GLOBALS["___mysqli_ston"], $db_name);

    $limit = $row["block"];
    $start = $row["total_event_exported"];
    $end = $limit;

    $exportFilePath = EDIRECTORY_ROOT . "/custom/domain_$domain_id/export_files";

    define("SELECTED_DOMAIN_ID", $domain_id);

    $files = glob($exportFilePath . "/export_*.progress");
    if (is_array($files)) {
        foreach ($files as $file) {
            if (strrpos($file, "export_" . str_replace(".zip", ".progress", $filename)) === false) {
                unlink($file);
            }
        }
    }

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

if ($type == "csv") { //working only for csv (import format) export
    export_ExportToCSV("event", false, false, $domain_id);
}

$time_end = getmicrotime();
$time = $time_end - $time_start;
print "Export Events on Domain " . SELECTED_DOMAIN_ID . " - " . date("Y-m-d H:i:s") . " - " . round($time,
        2) . " seconds.\n";
