<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/support/import.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# THIS PAGE IS ONLY USED BY THE SUPPORT TEAM TO SET THE CONTROL CRON TABLES WITH DFAULT VALUES
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include("../../conf/loadconfig.inc.php");

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();

if (!permission_hasSMPermSection(SITEMGR_PERMISSION_SUPERADMIN)) {
    header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/");
    exit;
}

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------

function import_getLogTip($status) {


    switch ($status) {
        case "pending": $tip = "Import on queue to be processed";
            break;
        case "running": $tip = "Import being processed";
            break;
        case "aborted": $tip = "Import canceled";
            break;
        case "done": $tip = "Data persisted on the database";
            break;
        case "waitrollback": $tip = "Data waiting to be rolled back";
            break;
        case "undone": $tip = "Import rolled back";
            break;
        case "sync": $tip = "Import waiting to be syncronized with Es";
            break;
        case "completed": $tip = "Import finished";
            break;
        case "error": $tip = "Error on creating the temporary Elasticsearch index or persisting to the database";
            break;
        default:  $tip = "Invalid Status";

    }

    return $tip;
}

function getImports($type = "listing") {
    $dbMain = db_getDBObject(DEFAULT_DB, true);
    $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
    unset($dbMain);
    $sql = "SELECT * FROM ImportLog WHERE status <> 'D' AND module = '$type' ORDER BY id DESC";
    $result = $dbObj->query($sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $logarray[] = $row;
        }
        return $logarray;
    } else return NULL;
}

$dbMain = db_getDBObject(DEFAULT_DB, true);
$success = 0;
if ($_GET["cron"]) {
    $event = false;

    if ($_GET["cron"] == "rollback") {
        if ($_GET["running"] == "N") {
            $sql = "UPDATE Control_Cron SET running = 'Y' WHERE type = 'rollback_import' AND domain_id = ".SELECTED_DOMAIN_ID;
            $dbMain->query($sql);
        } else {
            if ($_GET["running"] == "Y") {
                $sql = "UPDATE Control_Cron SET running = 'N' WHERE type = 'rollback_import' AND domain_id = ".SELECTED_DOMAIN_ID;
                $dbMain->query($sql);
            }
        }
    } elseif ($_GET["cron"] == "rollback_event") {
        $event = true;
        if ($_GET["running"] == "N") {
            $sql = "UPDATE Control_Cron SET running = 'Y' WHERE type = 'rollback_import_events' AND domain_id = ".SELECTED_DOMAIN_ID;
            $dbMain->query($sql);
        } else {
            if ($_GET["running"] == "Y") {
                $sql = "UPDATE Control_Cron SET running = 'N' WHERE type = 'rollback_import_events' AND domain_id = ".SELECTED_DOMAIN_ID;
                $dbMain->query($sql);
            }
        }
    }

    if (!$dbMain->mysql_error) {
        if ($event) {
            $successEvent = 1;
        } else {
            $success = 1;
        }
    } else {
        if ($event) {
            $successEvent = 2;
        } else {
            $success = 2;
        }
    }
}

# ----------------------------------------------------------------------------------------------------
# FORMS DEFINES
# ----------------------------------------------------------------------------------------------------
$sql = "SELECT running, last_run_date FROM Control_Cron WHERE type = 'rollback_import' AND domain_id = ".SELECTED_DOMAIN_ID;
$row = mysqli_fetch_assoc($dbMain->query($sql));

$rollbackImport_running = $row["running"];
$rollbackImport_last_run_date = $row["last_run_date"];

$importsListing = getImports("listing");

$sql = "SELECT running, last_run_date FROM Control_Cron WHERE type = 'rollback_import_events' AND domain_id = ".SELECTED_DOMAIN_ID;
$row = mysqli_fetch_assoc($dbMain->query($sql));

$rollbackImport_running_event = $row["running"];
$rollbackImport_last_run_date_event = $row["last_run_date"];

$importsEvent = getImports("event");

# ----------------------------------------------------------------------------------------------------
# HEADER
# ----------------------------------------------------------------------------------------------------
include(SM_EDIRECTORY_ROOT."/layout/header.php");

# ----------------------------------------------------------------------------------------------------
# NAVBAR
# ----------------------------------------------------------------------------------------------------
include(SM_EDIRECTORY_ROOT."/layout/navbar.php");

# ----------------------------------------------------------------------------------------------------
# SIDEBAR
# ----------------------------------------------------------------------------------------------------
include(SM_EDIRECTORY_ROOT."/layout/sidebar-support.php");

?>

    <main class="wrapper-dashboard togglesidebar container-fluid">

        <?
        require(EDIRECTORY_ROOT."/".SITEMGR_ALIAS."/registration.php");
        require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
        ?>

        <section class="heading">
            <h1> Config Checker: Import</h1>
            <? if ($_GET["message"] == 1) { ?>
                <p class="alert alert-success">ImportLog successfully updated!</p>
            <? } ?>
        </section>

        <section class="row section-form">

            <div class="col-md-8">
                <h3>Control Cron Tables Status - Listing</h3>
                <? if ($success != 0) { ?>
                    <div id="logMessages">
                        <p class=<?= ($success == 1 ? "alert alert-success" : "alert alert-danger") ?>><?= ($success == 1 ? "Cron setting successfully changed!" : "Error trying to change the cron setting, please try again.") ?></p>
                    </div>
                <? } ?>

                <? include(INCLUDES_DIR."/forms/form-support-importlisting.php"); ?>

                <h3>Control Cron Tables Status - Event</h3>
                <? if ($successEvent != 0) { ?>
                    <div id="logMessages">
                        <p class=<?= ($successEvent == 1 ? "alert alert-success" : "alert alert-danger") ?>><?= ($successEvent == 1 ? "Cron setting successfully changed!" : "Error trying to change the cron setting, please try again.") ?></p>
                    </div>
                <? } ?>
                <? include(INCLUDES_DIR."/forms/form-support-importevent.php"); ?>
            </div>

            <div class="col-md-4 small">
                <div class="panel panel-default">
                    <div class="panel-heading">Description of the field <i>Status</i>:</div>
                    <div class="panel-body">
                        <p>pending: Import on queue to be processed</p>
                        <p>running: Import in progress</p>
                        <p>aborted: Import aborted by user</p>
                        <p>done: Import data successfully persisted on the database and wait to be syncronized within ElasticSearch</p>
                        <p>waitrollback: Import undone by user. Imported items will be deleted, except for categories and locations</p>
                        <p>undone: Import undone by user already completed. Imported items have been deleted</p>
                        <p>sync: Import being syncronized within ElasticSearch</p>
                        <p>completed: Import process finished</p>
                        <p>error: Error on creating the temporary Elasticsearch index or persisting to the database</p>
                    </div>
                </div>

            </div>

        </section>

    </main>

<?php
# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
include(SM_EDIRECTORY_ROOT."/layout/footer.php");
