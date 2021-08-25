<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/article/article.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # VALIDATE FEATURE
    # ----------------------------------------------------------------------------------------------------
    if (PAYMENT_FEATURE != "on") { header("Location:".DEFAULT_URL."/".SITEMGR_ALIAS.""); exit; }
    if ((CREDITCARDPAYMENT_FEATURE != "on") && (PAYMENT_INVOICE_STATUS != "on")) { header("Location:".DEFAULT_URL."/".SITEMGR_ALIAS.""); exit; }
    if (CUSTOM_INVOICE_FEATURE != "on") { header("Location:".DEFAULT_URL."/".SITEMGR_ALIAS.""); exit; }


	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
    permission_hasSMPerm();

    # ----------------------------------------------------------------------------------------------------
    # AUX
    # ----------------------------------------------------------------------------------------------------
    extract($_GET);
    extract($_POST);

    $url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/activity/custominvoices";
    $url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
    $sitemgr = 1;

    $url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));
    # ----------------------------------------------------------------------------------------------------
    # CODE
    # ----------------------------------------------------------------------------------------------------
    include(INCLUDES_DIR."/code/custominvoice.php");

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
	include(SM_EDIRECTORY_ROOT."/layout/sidebar-activity.php");


?>

    <main class="wrapper togglesidebar container-fluid">

        <?php
        require(SM_EDIRECTORY_ROOT."/registration.php");
        require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
        ?>

        <form role="form" role="form" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">

            <section class="row heading">

	           	<div class="container">
                    <div class="col-sm-8">
                        <? if ($id) {
                            mixpanel_track("Edited a custom invoice");
                            ?>
                        <h1><?=string_ucwords(system_showText(LANG_SITEMGR_EDIT))." ".string_ucwords(system_showText(LANG_SITEMGR_CUSTOMINVOICE))?> <i><?= $customInvoice->getString("title")?></i></h1>
                        <? } else {
                            mixpanel_track("Added a custom invoice")
                            ?>
                        <h1><?=string_ucwords(system_showText(LANG_SITEMGR_ADD))." ".string_ucwords(system_showText(LANG_SITEMGR_CUSTOMINVOICE_PLURAL))?></h1>
                        <? } ?>
                    </div>
                </div>

            </section>

			<section class="row tab-options">
                <div class="container">
                    <div class="pull-right top-actions">
                        <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/activity/custominvoices/"?>" class="btn btn-default"><?=system_showText(LANG_CANCEL)?></a>
                        <button type="submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_NEXT);?></button>
                    </div>
                </div>

                <div class="tab-content">
                    <div class="tab-pane active">
                        <div class="container">
                            <?php
                                include(INCLUDES_DIR."/forms/form-custominvoice.php");
                            ?>
                        </div>
                    </div>
                </div>

            </section>

            <section class="row footer-action">

           		<div class="container">
	           		<div class="col-xs-12 text-right">
		           		<a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/activity/custominvoices"?>" class="btn btn-default"><?=system_showText(LANG_CANCEL)?></a>
                        <button type="submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_NEXT);?></button>
					</div>
				</div>

            </section>

        </form>


    </main>



<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
