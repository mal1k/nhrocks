<?

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
	# * FILE: /includes/forms/form-support-importevent.php
	# ----------------------------------------------------------------------------------------------------

?>
    <table class="table table-bordered">
        <tr>
            <th>Cron</th>
            <th>Last Run Date</th>
            <th>Running</th>
        </tr>
        <tr>
            <td>
                Roll Back Import
            </td>
            <td>
                <?
                if ($rollbackImport_last_run_date_event != "0000-00-00 00:00:00") {
                    echo format_date($rollbackImport_last_run_date_event, DEFAULT_DATE_FORMAT, "datetime")." - ".format_getTimeString($rollbackImport_last_run_date_event);
                } else {
                    echo "0000-00-00 00:00:00";
                }
                ?>
            </td>
            <td>
                <a title="<?=($rollbackImport_running_event == 'Y' ? "Running" : "Not Running")?>" href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/support/import.php?cron=rollback_event&running=<?=$rollbackImport_running_event?>"><i class="<?=$rollbackImport_running_event == 'Y' ? 'icon-ion-ios7-checkmark-outline' : 'icon-ion-ios7-close-outline text-warning'?>" ></i></a>
            </td>
        </tr>
    </table>

    <h3>Import Log - Event</h3>

    <? if (is_array($importsEvent) && $importsEvent[0]) { ?>
        <table class="table table-bordered">
            <tr>
                <th>ID</th>
                <th>Date/Time</th>
                <th>Filename</th>
                <th>Status</th>
                <th>&nbsp;</th>
            </tr>
            <? foreach ($importsEvent as $import) {
                    include (INCLUDES_DIR."/tables/table_import_support.php");
                }
            ?>
        </table>
    <? } else { ?>
        <p class="alert alert-warning">No records found.</p>
    <? } ?>
