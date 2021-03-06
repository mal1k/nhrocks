<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/deal/report.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (PROMOTION_FEATURE != "on") { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track("Accessed Manage Deals Reports section");

	$url_redirect = DEFAULT_URL."/".SITEMGR_ALIAS."/content/".PROMOTION_FEATURE_FOLDER;
	$url_base = DEFAULT_URL."/".SITEMGR_ALIAS."";
	$sitemgr = 1;

	extract($_GET);
	extract($_POST);

    # ----------------------------------------------------------------------------------------------------
    # OBJECTS
    # ----------------------------------------------------------------------------------------------------
	if ($id) {
        $promotion = new Promotion($id);
	}	else {
		header($url_redirect);
		exit;
	}

    # ----------------------------------------------------------------------------------------------------
    # REPORT DATA
    # ----------------------------------------------------------------------------------------------------
    $reports = retrievePromotionReport($id);

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
	include(SM_EDIRECTORY_ROOT."/layout/sidebar-content.php");

?>

    <main class="wrapper togglesidebar container-fluid">

        <?php
        require(SM_EDIRECTORY_ROOT."/registration.php");
        require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
        ?>

       <section class="row heading">

            <div class="container">
                <div class="col-sm-8">
                    <h1><?=string_ucwords(system_showText(LANG_SITEMGR_REPORT_TRAFFICREPORT))?> - <i><?=$promotion->getString("name")?></i></h1>
                </div>
            </div>

        </section>

        <section class="row tab-options">

            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="container">
                        <div class="col-md-12 form-horizontal">
                            <div class="table-responsive">

                                <? if (count($reports) > 0) { ?>
                                    <? include(INCLUDES_DIR."/tables/table_promotion_reports.php"); ?>
                                <? } else { ?>
                                    <p class="alert alert-info"><?=system_showText(LANG_SITEMGR_REPORT_NORECORD)?></p>
                                <? } ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <section class="row footer-action">

            <div class="container">
                <div class="col-xs-12 text-right">
                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".PROMOTION_FEATURE_FOLDER."/"?>" class="btn btn-default btn-xs"><?=system_showText(LANG_LABEL_BACK)?></a>
                </div>
            </div>

        </section>

    </main>

<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/modules.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
