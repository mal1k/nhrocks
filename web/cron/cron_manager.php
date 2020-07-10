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
# * FILE: /cron/cron_manager.php
# ----------------------------------------------------------------------------------------------------
////////////////////////////////////////////////////////////////////////////////////////////////////
ini_set("html_errors", false);
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
define("EDIRECTORY_ROOT", __DIR__ . "/..");
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$_inCron = true;
include_once(EDIRECTORY_ROOT . "/conf/config.inc.php");

////////////////////////////////////////////////////////////////////////////////////////////////////
function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());

    return ((float)$usec + (float)$sec);
}

$time_start = getmicrotime();

/**
 * Files to Cron manager
 */
$files[] = "daily_maintenance.php";
$files[] = "email_traffic.php";
$files[] = "renewal_reminder.php";
$files[] = "report_rollup.php";
$files[] = "sitemap.php";
$files[] = "statisticreport.php";
$files[] = "export_listings.php";
$files[] = "export_events.php";
$files[] = "export_mailapp.php";
$files[] = "rollback_import.php";
$files[] = "rollback_import_events.php";

/*
 * Save information about cron running
 */
$host = _DIRECTORYDB_HOST;
$db = _DIRECTORYDB_NAME;
$user = _DIRECTORYDB_USER;
$pass = _DIRECTORYDB_PASS;

$link = ($GLOBALS["___mysqli_ston"] = mysqli_connect($host,  $user,  $pass));
mysqli_query( $link, "SET NAMES 'utf8'");
mysqli_query( $link, 'SET character_set_connection=utf8');
mysqli_query( $link, 'SET character_set_client=utf8');
mysqli_query( $link, 'SET character_set_results=utf8');
mysqli_select_db($GLOBALS["___mysqli_ston"], $db);

$sql = "SELECT value FROM Setting WHERE name = 'running_cron_manager'";
$result = mysqli_query( $link, $sql);

if (mysqli_num_rows($result)) {
    $row = mysqli_fetch_assoc($result);

    if ($row["value"] == "n") {

        $sql_update = "UPDATE Setting SET value = 'y' WHERE name = 'running_cron_manager'";
        mysqli_query( $link, $sql_update);

        for ($i = 0; $i < count($files); $i++) {
            if (is_file(EDIRECTORY_ROOT . "/cron/" . $files[$i])) {
                system("php -f " . EDIRECTORY_ROOT . "/cron/" . $files[$i]);
            }
        }

        $sql_update = "UPDATE Setting SET value = 'n' WHERE name = 'running_cron_manager'";
        mysqli_query( $link, $sql_update);
    }

} else {
    $sql = "INSERT INTO Setting (name, value) VALUES ('running_cron_manager', 'n');";
    $result = mysqli_query( $link, $sql);
}

////////////////////////////////////////////////////////////////////////////////////////////////////
$time_end = getmicrotime();
$time = $time_end - $time_start;
print "Cron Manager - " . date("Y-m-d H:i:s") . " - " . round($time, 2) . " seconds.\n";
