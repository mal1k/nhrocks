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
# * FILE: /cron/report_rollup.php
# ----------------------------------------------------------------------------------------------------

////////////////////////////////////////////////////////////////////////////////////////////////////
// Reports:
// - Article
// - Banner
// - Classified
// - Event
// - Listing
// - Promotion
// - Blog
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
ini_set('html_errors', false);
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
define('EDIRECTORY_ROOT', __DIR__ .'/..');
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$_inCron = true;
include_once EDIRECTORY_ROOT .'/conf/config.inc.php';

////////////////////////////////////////////////////////////////////////////////////////////////////
function getmicrotime()
{
    list($usec, $sec) = explode(' ', microtime());

    return ((float)$usec + (float)$sec);
}

$time_start = getmicrotime();
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$host = _DIRECTORYDB_HOST;
$db = _DIRECTORYDB_NAME;
$user = _DIRECTORYDB_USER;
$pass = _DIRECTORYDB_PASS;
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$link = ($GLOBALS['___mysqli_ston'] = mysqli_connect($host,  $user,  $pass));
mysqli_query( $link, "SET NAMES 'utf8'");
mysqli_query( $link, 'SET character_set_connection=utf8');
mysqli_query( $link, 'SET character_set_client=utf8');
mysqli_query( $link, 'SET character_set_results=utf8');
mysqli_select_db($GLOBALS['___mysqli_ston'], $db);
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$sqlDomain = "  SELECT
                        D.`id`, D.`database_host`, D.`database_port`, D.`database_username`, D.`database_password`, D.`database_name`, D.`url`
                    FROM `Domain` AS D
                    LEFT JOIN `Control_Cron` AS CC ON (CC.`domain_id` = D.`id`)
                    WHERE ((CC.`running` = 'N' AND ADDDATE(CC.`last_run_date`, INTERVAL 1 DAY) <= NOW() OR CC.`last_run_date` = '0000-00-00 00:00:00') OR (ADDDATE(CC.`last_run_date`, INTERVAL 1 DAY) <= NOW() OR CC.`last_run_date` = '0000-00-00 00:00:00'))
                    AND CC.`type` = 'report_rollup'
                    AND D.`status` = 'A'
                    ORDER BY
                        IF (CC.`last_run_date` IS NULL, 0, 1),
                        CC.`last_run_date`,
                        D.`id`
                    LIMIT 1";

$resDomain = mysqli_query( $link, $sqlDomain);

if (mysqli_num_rows($resDomain) > 0) {
    $rowDomain = mysqli_fetch_assoc($resDomain);
    define('SELECTED_DOMAIN_ID', $rowDomain['id']);

    $sqlUpdate = "UPDATE `Control_Cron` SET `running` = 'Y', `last_run_date` = NOW() WHERE `domain_id` = " . SELECTED_DOMAIN_ID . " AND `type` = 'report_rollup'";
    mysqli_query( $link, $sqlUpdate);

    ////////////////////////////////////////////////////////////////////////////////////////////////////
    $domainHost = $rowDomain['database_host'] . ($rowDomain['database_port'] ? ':'. $rowDomain['database_port'] : '');
    $domainUser = $rowDomain['database_username'];
    $domainPass = $rowDomain['database_password'];
    $domainDBName = $rowDomain['database_name'];
    $domainURL = $rowDomain['url'];

    $linkDomain = ($GLOBALS['___mysqli_ston'] = mysqli_connect($domainHost,  $domainUser,  $domainPass));
    mysqli_query( $linkDomain, "SET NAMES 'utf8'");
    mysqli_query( $linkDomain, 'SET character_set_connection=utf8');
    mysqli_query( $linkDomain, 'SET character_set_client=utf8');
    mysqli_query( $linkDomain, 'SET character_set_results=utf8');
    mysqli_select_db($GLOBALS['___mysqli_ston'], $domainDBName);
    ////////////////////////////////////////////////////////////////////////////////////////////////////
} else {
    exit;
}
////////////////////////////////////////////////////////////////////////////////////////////////////

$_inCron = false;
include_once EDIRECTORY_ROOT .'/conf/loadconfig.inc.php';
////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////
// Removing Reports from deleted items
// Before generating the new Reports this code prevent unnecessary queries for deleted data
////////////////////////////////////////////////////////////////////////////////////////////////////
# ----------------------------------------------------------------------------------------------------
# ARTICLE
# ----------------------------------------------------------------------------------------------------
$sql = "DELETE FROM Report_Article WHERE article_id NOT IN (SELECT id FROM Article)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Article_Daily WHERE article_id NOT IN (SELECT id FROM Article)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Article_Monthly WHERE article_id NOT IN (SELECT id FROM Article)";
mysqli_query($linkDomain, $sql);

# ----------------------------------------------------------------------------------------------------
# BLOG
# ----------------------------------------------------------------------------------------------------
$sql = "DELETE FROM Report_Post WHERE post_id NOT IN (SELECT id FROM Post)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Post_Daily WHERE post_id NOT IN (SELECT id FROM Post)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Post_Monthly WHERE post_id NOT IN (SELECT id FROM Post)";
mysqli_query($linkDomain, $sql);

# ----------------------------------------------------------------------------------------------------
# BANNER
# ----------------------------------------------------------------------------------------------------
$sql = "DELETE FROM Report_Banner WHERE banner_id NOT IN (SELECT id FROM Banner)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Banner_Daily WHERE banner_id NOT IN (SELECT id FROM Banner)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Banner_Monthly WHERE banner_id NOT IN (SELECT id FROM Banner)";
mysqli_query($linkDomain, $sql);

# ----------------------------------------------------------------------------------------------------
# CLASSIFIED
# ----------------------------------------------------------------------------------------------------
$sql = "DELETE FROM Report_Classified WHERE classified_id NOT IN (SELECT id FROM Classified)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Classified_Daily WHERE classified_id NOT IN (SELECT id FROM Classified)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Classified_Monthly WHERE classified_id NOT IN (SELECT id FROM Classified)";
mysqli_query($linkDomain, $sql);

# ----------------------------------------------------------------------------------------------------
# EVENT
# ----------------------------------------------------------------------------------------------------
$sql = "DELETE FROM Report_Event WHERE event_id NOT IN (SELECT id FROM Event)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Event_Daily WHERE event_id NOT IN (SELECT id FROM Event)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Event_Monthly WHERE event_id NOT IN (SELECT id FROM Event)";
mysqli_query($linkDomain, $sql);

# ----------------------------------------------------------------------------------------------------
# LISTING
# ----------------------------------------------------------------------------------------------------
$sql = "DELETE FROM Report_Listing WHERE listing_id NOT IN (SELECT id FROM Listing)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Listing_Daily WHERE listing_id NOT IN (SELECT id FROM Listing)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Listing_Monthly WHERE listing_id NOT IN (SELECT id FROM Listing)";
mysqli_query($linkDomain, $sql);

# ----------------------------------------------------------------------------------------------------
# PROMOTION
# ----------------------------------------------------------------------------------------------------
$sql = "DELETE FROM Report_Promotion WHERE promotion_id NOT IN (SELECT id FROM Promotion)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Promotion_Daily WHERE promotion_id NOT IN (SELECT id FROM Promotion)";
mysqli_query($linkDomain, $sql);
$sql = "DELETE FROM Report_Promotion_Monthly WHERE promotion_id NOT IN (SELECT id FROM Promotion)";
mysqli_query($linkDomain, $sql);

//////////////////////////////////////////////////////////////////////////////////////////////////
// Daily rollup
//////////////////////////////////////////////////////////////////////////////////////////////////
// Create and Update totals from Reports and Delete processed data
// In this section, we have all process of generation of totals and generations of reports
//////////////////////////////////////////////////////////////////////////////////////////////////

$modules = [
    'Article',
    'Post',
    'Banner',
    'Classified',
    'Event',
    'Listing',
    'Promotion'
];

foreach($modules as $module) {
    $sql = "SELECT min(date) AS mindate FROM Report_{$module} WHERE Report_{$module}.date < CURRENT_DATE()";
    $resDate = mysqli_query($linkDomain, $sql);

    $minDate = mysqli_fetch_assoc($resDate);

    if (!empty($minDate['mindate'])) {
        try {
            $rowDate = new DateTime($minDate['mindate']);
        } catch (Exception $e) {
            print $e->getMessage();
            exit;
        }

        $rowDate->modify('first day of next month');

        $sql = "SELECT Report_{$module}.date FROM Report_$module WHERE Report_{$module}.date < '{$rowDate->format('Y-m-d')}' GROUP BY Report_{$module}.date";
        $dateResult = mysqli_query($linkDomain, $sql);
        if (mysqli_num_rows($dateResult) > 0) {
            $itemsToDelete = [];
            $moduleName = lcfirst($module);

            $insertValue = '';
            while ($date = mysqli_fetch_array($dateResult)) {
                if($date[0] !== date('Y-m-d')) {
                    $sql = "SELECT {$moduleName}_id, CONCAT('{', GROUP_CONCAT(CONCAT('\"',report_type, '\"', ':', '\"', report_amount, '\"') ORDER BY report_type), '}') as amounts FROM Report_$module WHERE DATE_FORMAT(date, '%Y-%m-%d') = '" . $date[0] . "' GROUP BY {$moduleName}_id ORDER BY {$moduleName}_id;";
                    $result = mysqli_query($linkDomain, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_array($result)) {
                            if (!empty($insertValue)) {
                                $insertValue .= ',';
                            }

                            $itemsToDelete[] = $row[$moduleName . '_id'];

                            $amounts = json_decode($row['amounts'], true);

                            if ($module !== 'Listing' && $module !== 'Banner') {
                                $summaryView = !empty($amounts[REPORT_SUMMARY_VIEW]) ? $amounts[REPORT_SUMMARY_VIEW] : 0;
                                $detailView = !empty($amounts[REPORT_DETAIL_VIEW]) ? $amounts[REPORT_DETAIL_VIEW] : 0;
                                $insertValue .= "('" . $row[$moduleName . '_id'] . "', '" . $date[0] . "', " . $summaryView . ', ' . $detailView . ')';
                            } elseif ($module === 'Banner') {
                                $view = !empty($amounts[BANNER_REPORT_VIEW]) ? $amounts[BANNER_REPORT_VIEW] : 0;
                                $clickThru = !empty($amounts[BANNER_REPORT_CLICK_THRU]) ? $amounts[BANNER_REPORT_CLICK_THRU] : 0;
                                $insertValue .= "('" . $row['banner_id'] . "', '" . $date[0] . "', " . $view . ', ' . $clickThru . ')';
                            } elseif ($module === 'Listing') {
                                $summaryView = !empty($amounts[LISTING_REPORT_SUMMARY_VIEW]) ? $amounts[LISTING_REPORT_SUMMARY_VIEW] : 0;
                                $detailView = !empty($amounts[LISTING_REPORT_DETAIL_VIEW]) ? $amounts[LISTING_REPORT_DETAIL_VIEW] : 0;
                                $clickThru = !empty($amounts[LISTING_REPORT_CLICK_THRU]) ? $amounts[LISTING_REPORT_CLICK_THRU] : 0;
                                $emailSent = !empty($amounts[LISTING_REPORT_EMAIL_SENT]) ? $amounts[LISTING_REPORT_EMAIL_SENT] : 0;
                                $phoneView = !empty($amounts[LISTING_REPORT_PHONE_VIEW]) ? $amounts[LISTING_REPORT_PHONE_VIEW] : 0;
                                $additionalPhoneView = !empty($amounts[LISTING_REPORT_ADDITIONAL_PHONE_VIEW]) ? $amounts[LISTING_REPORT_ADDITIONAL_PHONE_VIEW] : 0;
                                $insertValue .= "('" . $row['listing_id'] . "', '" . $date[0] . "', " . $summaryView . ', ' . $detailView . ', ' . $clickThru . ', ' . $emailSent . ', ' . $phoneView . ', ' . $additionalPhoneView . ')';
                            }
                        }
                    }
                }
            }

            if (!empty($insertValue)) {
                if ($module !== 'Listing' && $module !== 'Banner') {
                    $sql = "INSERT INTO Report_{$module}_Daily ({$moduleName}_id, day, summary_view, detail_view) VALUES $insertValue;";
                } elseif ($module === 'Banner') {
                    $sql = "INSERT INTO Report_{$module}_Daily (banner_id, day, view, click_thru) VALUES $insertValue;";
                } elseif ($module === 'Listing') {
                    $sql = "INSERT INTO Report_{$module}_Daily (listing_id, day, summary_view, detail_view, click_thru, email_sent, phone_view, additional_phone_view) VALUES $insertValue;";
                }

                mysqli_query($linkDomain, $sql);
            }

            if (!empty(mysqli_error($linkDomain))) {
                print 'Database error : ' . mysqli_error($linkDomain) . PHP_EOL;
                exit;
            }

            if (!empty($itemsToDelete)) {
                $sql = "DELETE FROM Report_$module WHERE {$moduleName}_id IN (" . implode(',', $itemsToDelete) . ") AND Report_{$module}.date < '{$rowDate->format('Y-m-d')}'";
                mysqli_query($linkDomain, $sql);
            }

            if (!empty(mysqli_error($linkDomain))) {
                print 'Database error : ' . mysqli_error($linkDomain) . PHP_EOL;
                exit;
            }

            ////////////////////////////////////////////////////////////////////////////////////////////////////
            // Move completed months to Report_[module]_Monthly
            ////////////////////////////////////////////////////////////////////////////////////////////////////
            $itemsToDelete = [];
            $insertValue = '';

            if ($module !== 'Listing' && $module !== 'Banner') {
                $sql = "SELECT {$moduleName}_id , CONCAT(YEAR(day), '-' , MONTH(day), '-', '1') AS period , SUM(summary_view) AS summary , SUM(detail_view) AS detail FROM Report_{$module}_Daily WHERE ((MONTH(day) < MONTH(NOW()) AND YEAR(day) = YEAR(NOW())) OR (YEAR(day) < YEAR(NOW()))) GROUP BY {$moduleName}_id , period  ORDER BY day DESC";
                $results = mysqli_query($linkDomain, $sql);
                while ($row = mysqli_fetch_array($results)) {
                    if (!empty($insertValue)) {
                        $insertValue .= ',';
                    }

                    $itemsToDelete[] = $row[$moduleName . '_id'];

                    $insertValue .= '(' . $row[$moduleName . '_id'] . ",'" . $row['period'] . "'," . $row['summary'] . ',' . $row['detail'] . ')';
                }
            } elseif ($module === 'Banner') {
                $sql = "SELECT banner_id , CONCAT(YEAR(day), '-' , MONTH(day), '-', '1') AS period , SUM(view) AS view, SUM(click_thru) AS click FROM Report_Banner_Daily WHERE ((MONTH(day) < MONTH(NOW()) AND YEAR(day) = YEAR(NOW())) OR (YEAR(day) < YEAR(NOW()))) GROUP BY banner_id, period  ORDER BY day DESC";
                $results = mysqli_query($linkDomain, $sql);
                while ($row = mysqli_fetch_array($results)) {
                    if (!empty($insertValue)) {
                        $insertValue .= ',';
                    }

                    $insertValue .= '(' . $row['banner_id'] . ",'" . $row['period'] . "'," . $row['view'] . ',' . $row['click'] . ')';

                    $itemsToDelete[] = $row['banner_id'];
                }
            } elseif ($module === 'Listing') {
                $sql = "SELECT listing_id , CONCAT(YEAR(day), '-' , MONTH(day), '-', '1') AS period , SUM(summary_view) AS summary , SUM(detail_view) AS detail , SUM(click_thru) AS click , SUM(email_sent) AS email , SUM(phone_view) AS phone, SUM(additional_phone_view) AS additional_phone FROM Report_Listing_Daily WHERE ((MONTH(day) < MONTH(NOW()) AND YEAR(day) = YEAR(NOW())) OR (YEAR(day) < YEAR(NOW()))) GROUP BY listing_id , period  ORDER BY day DESC";
                $results = mysqli_query($linkDomain, $sql);
                while ($row = mysqli_fetch_array($results)) {
                    if (!empty($insertValue)) {
                        $insertValue .= ',';
                    }

                    $itemsToDelete[] = $row['listing_id'];

                    $insertValue .= '(' . $row['listing_id'] . ",'" . $row['period'] . "'," . $row['summary'] . ',' . $row['detail'] . ',' . $row['click'] . ',' . $row['email'] . ',' . $row['phone'] . ',' . $row['additional_phone'] . ')';
                }
            }

            if (!empty($insertValue)) {
                $sqlInsert = "INSERT INTO Report_{$module}_Monthly VALUES $insertValue;";
                mysqli_query($linkDomain, $sqlInsert);
            }

            if (!empty(mysqli_error($linkDomain))) {
                print 'Database error : ' . mysqli_error($linkDomain) . PHP_EOL;
                exit;
            }

            if (!empty($itemsToDelete)) {
                $sqlDelete = "DELETE FROM Report_{$module}_Daily WHERE {$moduleName}_id IN (" . implode(',', $itemsToDelete) . ') AND ((MONTH(day) < MONTH(NOW()) AND YEAR(day) = YEAR(NOW())) OR (YEAR(day) < YEAR(NOW())))';
                mysqli_query($linkDomain, $sqlDelete);
            }

            if (!empty(mysqli_error($linkDomain))) {
                print 'Database error : ' . mysqli_error($linkDomain) . PHP_EOL;
                exit;
            }
        }
    }
}
////////////////////////////////////////////////////////////////////////////////////////////////////

$sqlUpdate = "UPDATE `Control_Cron` SET `running` = 'N' WHERE `domain_id` = " . SELECTED_DOMAIN_ID . " AND `type` = 'report_rollup'";
mysqli_query( $link, $sqlUpdate);

////////////////////////////////////////////////////////////////////////////////////////////////////
$time_end = getmicrotime();
$time = $time_end - $time_start;
print 'Report Rollup on Domain '. SELECTED_DOMAIN_ID .' - '. date('Y-m-d H:i:s') .' - '. round($time,
        2) . " seconds.\n";
if (!setting_set('last_datetime_reportrollup', date('Y-m-d H:i:s'))) {
    if (!setting_new('last_datetime_reportrollup', date('Y-m-d H:i:s'))) {
        print 'last_datetime_reportrollup error - Domain - '. SELECTED_DOMAIN_ID .' - '. date('Y-m-d H:i:s') . "\n";
    }
}
