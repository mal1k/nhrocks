<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/classified/classified.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (CLASSIFIED_FEATURE != "on") { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
    permission_hasSMPerm();

    $url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".CLASSIFIED_FEATURE_FOLDER;
    $url_base 	  = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
    $url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));
    $sitemgr 	  = 1;

    # ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

    mixpanel_track(($id ? "Edited an existing classified" : "Added a new classified"));

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include(EDIRECTORY_ROOT."/includes/code/classified.php");

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

    if (system_blockListingCreation($id)) { ?>
        <main class="wrapper togglesidebar container-fluid">
            <?php include INCLUDES_DIR.'/views/upgrade_plan_banner.php'; ?>
        </main>
    <?php } else {

        ?>

        <main class="wrapper togglesidebar container-fluid">

            <?php
            require(SM_EDIRECTORY_ROOT."/registration.php");
            require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");

            if ($id) {
                $arrayCompletion = system_gamefyItems("classified", $classified);
                ?>

                <div class="row">
                    <div class="progress">
                        <div class="progress-bar" data-placement="bottom" data-toggle="tooltip"
                             data-original-title="<?= $arrayCompletion["total"] ?>% <?= ucfirst(LANG_LABEL_COMPLETED) ?>"
                             role="progressbar" aria-valuenow="<?= $arrayCompletion["total"] ?>" aria-valuemin="0"
                             aria-valuemax="100" style="width: <?= $arrayCompletion["total"] ?>%;">
                            <span class="sr-only"><?= $arrayCompletion["total"] ?>% <?= ucfirst(LANG_LABEL_COMPLETED) ?></span>
                        </div>
                    </div>
                </div>

            <? } ?>

            <form role="form" name="classified" id="classified"
                  action="<?= system_getFormAction($_SERVER["PHP_SELF"]) ?>" method="post"
                  enctype="multipart/form-data">

                <input type="hidden" name="sitemgr" id="sitemgr" value="<?= $sitemgr ?>">
                <input type="hidden" name="id" id="id" value="<?= $id ?>">
                <?= system_getFormInputSearchParams((($_POST) ? ($_POST) : ($_GET))); ?>
                <input type="hidden" name="letter" value="<?= $letter ?>">
                <input type="hidden" name="screen" value="<?= $screen ?>">

                <section class="row heading">

                    <div class="container">
                        <? include(SM_EDIRECTORY_ROOT."/layout/back-navigation.php"); ?>
                        <div class="col-sm-8">
                            <? if ($id) { ?>
                                <h1><?= string_ucwords(system_showText(LANG_SITEMGR_EDIT))." ".system_showText(LANG_SITEMGR_CLASSIFIED_SING) ?>
                                    <i><?= $classified->getString("title") ?></i></h1>
                            <? } else { ?>
                                <h1><?= string_ucwords(system_showText(LANG_SITEMGR_ADD))." ".system_showText(LANG_SITEMGR_CLASSIFIED_SING) ?></h1>
                            <? } ?>
                        </div>
                        <div class="col-sm-4 text-right">
                            <br><br>
                            <a href="javascript:void(0);" data-tour
                               class="text-info tutorial-text hidden-xs hidden-sm"><?= system_showText(LANG_LABEL_TUTORIAL); ?>
                                <i class="icon-help8"></i></a>
                        </div>

                        <? if ($message_classified) { ?>
                            <div class="col-sm-12 alert alert-warning" role="alert">
                                <p><?= $message_classified; ?></p>
                            </div>
                        <? } ?>
                    </div>

                </section>

                <section class="row tab-options">

                    <div class="container">
                        <div class="pull-right top-actions">
                            <a href="<?= DEFAULT_URL."/".SITEMGR_ALIAS."/content/".CLASSIFIED_FEATURE_FOLDER."/" ?>"
                               class="btn btn-default btn-xs"><?= system_showText(LANG_CANCEL) ?></a>
                            <span class="separator"> <?= system_showText(LANG_OR) ?>  </span>
                            <button type="button" name="submit_button" value="Submit"
                                    class="btn btn-primary action-save"
                                    data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"
                                    onclick="JS_submit();"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="container">
                                <? include(INCLUDES_DIR."/forms/form-classified.php"); ?>
                            </div>
                        </div>
                    </div>

                </section>

                <section class="row footer-action">

                    <div class="container">
                        <div class="col-xs-12 text-right">
                            <a href="<?= DEFAULT_URL."/".SITEMGR_ALIAS."/content/".CLASSIFIED_FEATURE_FOLDER."/" ?>"
                               class="btn btn-default btn-xs"><?= system_showText(LANG_CANCEL) ?></a>
                            <span class="separator"> <?= system_showText(LANG_OR) ?> </span>
                            <button type="button" name="submit_button" value="Submit"
                                    class="btn btn-primary action-save"
                                    data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"
                                    onclick="JS_submit();"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                        </div>
                    </div>

                </section>

            </form>

            <aside class="tutorial-tour">
                <h1><?= system_showText(LANG_LABEL_TUTORIAL_FIELDS); ?></h1>
                <div class="nano">
                    <ul class="list-unstyled nano-content">
                        <? foreach ($arrayTutorial as $key => $title) { ?>
                            <li><span class="tour-step <?= (!$key ? "active" : "") ?>" data-step="<?= $key ?>"><i
                                            class="icon-chevron15"></i> <?= $title["field"] ?></span></li>
                        <? } ?>
                        <li><span class="tour-step-end"><?= system_showText(LANG_LABEL_TUTORIAL_END) ?></span></li>
                    </ul>
                </div>
            </aside>

        </main>

        <?php
        include(INCLUDES_DIR."/modals/modal-categoryselect.php");
        include(INCLUDES_DIR."/modals/modal-crop.php");
        if (!empty(UNSPLASH_ACCESS_KEY)) {
            include(INCLUDES_DIR . "/modals/modal-unsplash.php");
            JavaScriptHandler::registerFile(DEFAULT_URL . '/assets/js/lib/unsplash.js');
        }

        $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/modules.php";
    }
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
