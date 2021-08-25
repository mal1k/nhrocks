<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/export/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include '../../../conf/loadconfig.inc.php';

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track('Accessed Export section');

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include INCLUDES_DIR.'/code/export.php';

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include SM_EDIRECTORY_ROOT.'/layout/header.php';

    # ----------------------------------------------------------------------------------------------------
	# NAVBAR
	# ----------------------------------------------------------------------------------------------------
	include SM_EDIRECTORY_ROOT.'/layout/navbar.php';

    # ----------------------------------------------------------------------------------------------------
	# SIDEBAR
	# ----------------------------------------------------------------------------------------------------
	include SM_EDIRECTORY_ROOT.'/layout/sidebar-content.php';

?>

    <main class="wrapper togglesidebar container-fluid">

        <?php
        require SM_EDIRECTORY_ROOT.'/registration.php';
        require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
        ?>

        <section class="heading">
            <h1><?=system_showText(LANG_SITEMGR_CONTENT_EXPORT)?></h1>
        </section>

        <div class="tab-options">

            <div class="row tab-content">
                <div class="col-xs-12">
                    <section class="tab-pane active" id="import-tool">

                        <div class="row">

                            <div class="col-sm-12">

                                <div id="export_loading" class="alert alert-loading alert-block text-center hidden">
                                    <p><?=system_showText(LANG_SITEMGR_EXPORT_WAITING_CRON);?></p>
                                    <img src="<?=DEFAULT_URL;?>/<?=SITEMGR_ALIAS?>/assets/img/loading-64.gif">
                                </div>

                                <p id="exportMessage" class="alert <?=$messageStyle?: ''; ?>" style="<?=$messageStyle? '' : 'display: none;'; ?>">
                                    <?=$exportMessage?: ''; ?>
                                </p>

                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group selectize">
                                    <label for="exampleInput"><?=system_showText(LANG_SITEMGR_EXPORT_EDIRECTORY)?></label>
                                    <select class="form-control form-control--select" id="select_export_edirectory">
                                        <option value=""><?=system_showText(LANG_SITEMGR_EXPORT_SELECT)?></option>
                                        <option value="listing"><?=system_showText(ucfirst(LANG_SITEMGR_LISTING_PLURAL))?></option>
                                        <?php if (EVENT_FEATURE === 'on' && CUSTOM_EVENT_FEATURE === 'on') { ?>
                                        <option value="event"><?=system_showText(ucfirst(LANG_SITEMGR_EVENT_PLURAL))?></option>
                                        <?php } ?>
                                    </select>
                                    <button class="btn btn-primary" onclick="showForm($('#select_export_edirectory option:selected').val());"><?=system_showText(LANG_SITEMGR_CONTENT_EXPORT)?></button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group selectize">
                                    <label for="exampleInput"><?=system_showText(LANG_SITEMGR_EXPORT_BACKUP)?></label>
                                    <select class="form-control form-control--select" id="select_export_backup">
                                        <option value=""><?=system_showText(LANG_SITEMGR_EXPORT_SELECT)?></option>

                                        <option value="Listing"><?=system_showText(ucfirst(LANG_SITEMGR_LISTING_PLURAL))?></option>

                                        <option value="ListingCategory"><?=system_showText(LANG_SITEMGR_NAVBAR_LISTINGCATEGORIES)?></option>

                                        <?php if (EVENT_FEATURE === 'on' && CUSTOM_EVENT_FEATURE === 'on') { ?>

                                            <option value="Event"><?=system_showText(ucfirst(LANG_SITEMGR_EVENT_PLURAL))?></option>

                                            <option value="EventCategory"><?=system_showText(LANG_SITEMGR_NAVBAR_EVENTCATEGORIES)?></option>

                                        <?php } ?>

                                        <?php if (CLASSIFIED_FEATURE === 'on' && CUSTOM_CLASSIFIED_FEATURE === 'on') { ?>

                                            <option value="Classified"><?=system_showText(ucfirst(LANG_SITEMGR_CLASSIFIED_PLURAL))?></option>

                                            <option value="ClassifiedCategory"><?=system_showText(LANG_SITEMGR_NAVBAR_CLASSIFIEDCATEGORIES)?></option>

                                        <?php } ?>

                                        <?php if (ARTICLE_FEATURE === 'on' && CUSTOM_ARTICLE_FEATURE === 'on') { ?>

                                        <option value="Article"><?=system_showText(ucfirst(LANG_SITEMGR_ARTICLE_PLURAL))?></option>

                                        <option value="ArticleCategory"><?=system_showText(LANG_SITEMGR_NAVBAR_ARTICLECATEGORIES)?></option>

                                        <?php } ?>

                                        <?php if (BANNER_FEATURE === 'on' && CUSTOM_BANNER_FEATURE === 'on') { ?>

                                            <option value="Banner"><?=system_showText(ucfirst(LANG_SITEMGR_BANNER_PLURAL))?></option>

                                        <?php } ?>

                                        <option value="Location"><?=system_showText(LANG_SITEMGR_NAVBAR_LOCATIONS)?></option>

                                        <option value="Account"><?=system_showText(LANG_SITEMGR_NAVBAR_ACCOUNTS)?></option>
                                    </select>
                                    <button class="btn btn-primary" onclick="exportFile()"><?=system_showText(LANG_SITEMGR_CONTENT_EXPORT)?></button>
                                </div>
                            </div>

                            <div class="col-sm-6 hidden exporting" id="exporting-form-listing">
                                <div class="panel panel-default">
                                    <div class="panel-heading"><?=system_showText(LANG_SITEMGR_EXPORT_TITLEEXPORTLISTINGSAMEFORMAT)?></div>
                                    <div class="panel-body">
                                        <p>
                                            <span id="export_message">
                                            <?php if ($aux_export_running['finished'] === 'Y' || LISTING_SCALABILITY_OPTIMIZATION === 'off') { ?>

                                                <a href="javascript:startExport();" class="btn btn-primary">
                                                    <?=system_showText(LANG_SITEMGR_EXPORT_CLICKHERETOSTART)?>
                                                </a>

                                            <?php } ?>
                                            </span>
                                        </p>

                                        <p id="download_file" class="hidden">
                                            <a href="<?=$url_redirect?>?action=downFile&file=<?=$exportFileListing?>&displayName=<?=$exportFileListing;?>"><?=system_showText(LANG_MSG_CLICK_TO_DOWNLOAD_THIS_FILE)?></a>
                                        </p>

                                        <p>
                                            <span id="export_progress">&nbsp;</span>
                                            <span id="export_progress_percentage">&nbsp;</span>
                                        </p>

                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 hidden exporting" id="exporting-form-event">
                                <div class="panel panel-default">
                                    <div class="panel-heading"><?=system_showText(LANG_SITEMGR_EXPORT_TITLEEXPORTEVENTSAMEFORMAT)?></div>
                                    <div class="panel-body">
                                        <p>
                                            <span id="export_messageEvent">
                                            <?php if ($aux_export_runningEvent['finished'] === 'Y' || EVENT_SCALABILITY_OPTIMIZATION === 'off') { ?>

                                                <a href="javascript:startExportEvent();" class="btn btn-primary">
                                                    <?=system_showText(LANG_SITEMGR_EXPORT_CLICKHERETOSTART)?>
                                                </a>

                                            <?php } ?>
                                            </span>
                                        </p>

                                        <p id="download_fileEvent" class="hidden">
                                            <a href="<?=$url_redirect?>?action=downFile&file=<?=$exportFileEvent?>&displayName=<?=$exportFileEvent;?>"><?=system_showText(LANG_MSG_CLICK_TO_DOWNLOAD_THIS_FILE)?></a>
                                        </p>

                                        <p>
                                            <span id="export_progressEvent">&nbsp;</span>
                                            <span id="export_progress_percentageEvent">&nbsp;</span>
                                        </p>

                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div id="exportlisting" class="panel panel-default" style="<?=(LISTING_SCALABILITY_OPTIMIZATION === 'on' && $export['finished'] === 'N' ? '' : 'display:none');?>">
                                    <div class="panel-body">
                                        <input type="hidden" id="nextFileName" value="<?=$exportFile?>" />
                                        <p>
                                            <strong><?=system_showText(LANG_SITEMGR_EXPORT_EXPORTITEM_AFTEREXPORTDONE)?></strong> <?=$exportFilePath?>
                                        </p>
                                        <p>
                                            <strong><?=system_showText(LANG_SITEMGR_EXPORT_EXPORTITEM_FILENAME)?></strong> <span id="showFileName"><?=$exportFile?></span>
                                        </p>
                                        <p  class="text-center" id="export_cron_loading" style="<?=$export['finished'] === 'N' ? '' : 'display: none;' ?>">
                                            <?=system_showText(LANG_SITEMGR_EXPORT_EXPORTINGPLEASEWAIT);?>
                                            <img src="<?=DEFAULT_URL;?>/<?=SITEMGR_ALIAS?>/assets/img/preloader-32.gif">
                                        </p>
                                        <p class="text-center" id="export_progress_backup" style="<?=$export['finished'] === 'N' ? '' : 'display: none;' ?>">&nbsp;</p>
                                        <p class="text-center" id="export_link_start" style="<?=$export['finished'] === 'N' ? 'display: none;' : '';?>">
                                            <a href="javascript:void(0);" onclick="scheduleExport();" class="btn btn-link" style="margin: 0; padding: 0; font-size: 14px;">
                                                <?=system_showText(LANG_SITEMGR_EXPORT_CLICKHERETOSTART)?>
                                            </a>
                                        </p>
                                        <p class="text-center" id="file_link" style="<?=$exportedFileName? '' : 'display: none; ' ?>">
                                            <?php if ($exportedFileName){ ?>
                                                <a class="btn btn-primary" href="<?=$_SERVER['PHP_SELF'].'?action=cron&download='.$exportedFileName;?>"><?=system_showText(LANG_SITEMGR_EXPORT_LASTFILE_MESSAGE)?></a>
                                            <?php } ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php include INCLUDES_DIR.'/tables/table-export.php'; ?>

                    </section>
                </div>
            </div>

        </div>

    </main>

<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	$customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/export.php';
    include SM_EDIRECTORY_ROOT.'/layout/footer.php';
