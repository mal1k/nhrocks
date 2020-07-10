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
# * FILE: /cron/rollback_import.php
# ----------------------------------------------------------------------------------------------------

////////////////////////////////////////////////////////////////////////////////////////////////////
ini_set("html_errors", false);
////////////////////////////////////////////////////////////////////////////////////////////////////
define("EDIRECTORY_ROOT", __DIR__."/..");
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$_inCron = true;
include_once(EDIRECTORY_ROOT."/conf/config.inc.php");
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

function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());

    return ((float)$usec + (float)$sec);
}

$time_start = getmicrotime();

////////////////////////////////////////////////////////////////////////////////////////////////////
$sqlDomain = "	SELECT
                            D.`id`, D.`database_host`, D.`database_port`, D.`database_username`, D.`database_password`, D.`database_name`, D.`url`
                        FROM `Domain` AS D
                        LEFT JOIN `Control_Cron` AS CC ON (CC.`domain_id` = D.`id`)
                        WHERE CC.`running` = 'N'
                        AND CC.`type` = 'rollback_import'
                        AND D.`status` = 'A'
                        ORDER BY
                            IF (CC.`last_run_date` IS NULL, 0, 1),
                            CC.`last_run_date`,
                            D.`id`
                        LIMIT 1";

$resDomain = mysqli_query( $link, $sqlDomain);

if (mysqli_num_rows($resDomain) > 0) {
    $rowDomain = mysqli_fetch_assoc($resDomain);
    define("SELECTED_DOMAIN_ID", $rowDomain["id"]);

    $domainHost = $rowDomain["database_host"].($rowDomain["database_port"] ? ":".$rowDomain["database_port"] : "");
    $domainUser = $rowDomain["database_username"];
    $domainPass = $rowDomain["database_password"];
    $domainDBName = $rowDomain["database_name"];
    $domainURL = $rowDomain["url"];

    $link_domain = ($GLOBALS["___mysqli_ston"] = mysqli_connect($domainHost,  $domainUser,  $domainPass));
    mysqli_query( $link_domain, "SET NAMES 'utf8'");
    mysqli_query( $link_domain, 'SET character_set_connection=utf8');
    mysqli_query( $link_domain, 'SET character_set_client=utf8');
    mysqli_query( $link_domain, 'SET character_set_results=utf8');
    mysqli_select_db($GLOBALS["___mysqli_ston"], $domainDBName);
    ////////////////////////////////////////////////////////////////////////////////////////////////////
} else {
    exit;
}
////////////////////////////////////////////////////////////////////////////////////////////////////

$_inCron = false;
include_once(EDIRECTORY_ROOT."/conf/loadconfig.inc.php");
////////////////////////////////////////////////////////////////////////////////////////////////////

$sqlIL = "SELECT `id` FROM `ImportLog` WHERE `status` = 'running' AND `module` = 'listing' ORDER BY `createdAt`";
$resIL = mysqli_query( $link_domain, $sqlIL);
if (mysqli_num_rows($resIL) <= 0) {
    $sqlIL = "SELECT `id` FROM `ImportLog` WHERE `status` = 'waitrollback' AND `module` = 'listing' ORDER BY `createdAt`";
    $resIL = mysqli_query( $link_domain, $sqlIL);
    if (mysqli_num_rows($resIL) > 0) {
        $sqlUpdate = "UPDATE `Control_Cron` SET `running` = 'Y', `last_run_date` = NOW() WHERE `domain_id` = ".SELECTED_DOMAIN_ID." AND `type` = 'rollback_import'";
        mysqli_query( $link, $sqlUpdate);

        $rowIL = mysqli_fetch_assoc($resIL);
        $importID = $rowIL["id"];

        $num_listings = 0;
        $sql = "SELECT id FROM Listing WHERE import_id = ".db_formatNumber($importID);
        $result = mysqli_query( $link_domain, $sql);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $listingObj = new Listing($row["id"]);
                if ($listingObj->getNumber("id") > 0) {
                    $listingObj->Delete(SELECTED_DOMAIN_ID);
                    $num_listings++;
                }
            }
        }

        $num_accounts = 0;
        $sql = "SELECT id FROM Account WHERE importID = ".db_formatNumber($importID)." AND domain_importID = ".db_formatNumber(SELECTED_DOMAIN_ID);
        $result = mysqli_query( $link, $sql);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $accountObj = new Account($row["id"]);
                if ($accountObj->getNumber("id") > 0) {
                    $accountObj->Delete();
                    $num_accounts++;
                }
            }
        }

        $sql = "UPDATE ImportLog SET status = ".db_formatString(\ArcaSolutions\ImportBundle\Entity\ImportLog::STATUS_UNDONE)." WHERE id = ".db_formatNumber($importID);
        mysqli_query( $link_domain, $sql);

        $sqlUpdate = "UPDATE `Control_Cron` SET `running` = 'N', `last_run_date` = NOW() WHERE `domain_id` = ".SELECTED_DOMAIN_ID." AND `type` = 'rollback_import'";
        mysqli_query( $link, $sqlUpdate);
    } else {
        $sqlUpdate = "UPDATE `Control_Cron` SET `running` = 'N', `last_run_date` = NOW() WHERE `domain_id` = ".SELECTED_DOMAIN_ID." AND `type` = 'rollback_import'";
        mysqli_query( $link, $sqlUpdate);
        exit;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////

$time_end = getmicrotime();
$time = $time_end - $time_start;

print "Roll Back Process on Domain ".SELECTED_DOMAIN_ID." - ".date("Y-m-d H:i:s")." - Listings Rolled Back: ".$num_listings." - Accounts Rolled Back: ".$num_accounts."\n";

if (!setting_set("last_datetime_rollback_import", date("Y-m-d H:i:s"))) {
    if (!setting_new("last_datetime_rollback_import", date("Y-m-d H:i:s"))) {
        print "last_datetime_rollback_import error - Domain - ".SELECTED_DOMAIN_ID." - ".date("Y-m-d H:i:s")."\n";
    }
}
