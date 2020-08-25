<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2014 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/content/import/index.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include("../../../conf/loadconfig.inc.php");

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();
permission_hasSMPerm();

# ----------------------------------------------------------------------------------------------------
# AUX
# ----------------------------------------------------------------------------------------------------

$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER;
$url_base 	  = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
$url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));
$sitemgr 	  = 1;

extract($_POST);
extract($_GET);

if ($id) {
    $listing = new Listing( $id );
    $listing->makeFromRow( $_POST );
    $levelObj = new ListingLevel( true );
}

$container = SymfonyCore::getContainer();

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

# ----------------------------------------------------------------------------------------------------
# VIEW
# ----------------------------------------------------------------------------------------------------
?>
    <main class="wrapper togglesidebar container-fluid">

        <?
        require(SM_EDIRECTORY_ROOT."/registration.php");
        require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");


        if ($id) {
            $arrayCompletion = system_gamefyItems("listing", $listing);
            ?>

            <div class="row">
                <div class="progress">
                    <div class="progress-bar"
                         data-placement="bottom"
                         data-toggle="tooltip"
                         data-original-title="<?=$arrayCompletion["total"]?>% <?=ucfirst(LANG_LABEL_COMPLETED)?>"
                         role="progressbar"
                         aria-valuenow="<?=$arrayCompletion["total"]?>"
                         aria-valuemin="0"
                         aria-valuemax="100"
                         style="width: <?=$arrayCompletion["total"]?>%;">
                        <span class="sr-only"><?=$arrayCompletion["total"]?>% <?=ucfirst(LANG_LABEL_COMPLETED)?></span>
                    </div>
                </div>
            </div>

        <? } ?>

        <form role="form" name="promotion" method="post" enctype="multipart/form-data" action="<?= $container->get('router')->generate('eventassociation_savelisting')?>">

            <input type="hidden" name="listing_id" value="<?=$id?>">
            <input type="hidden" name="letter" value="<?=$letter?>" />
            <input type="hidden" name="screen" value="<?=$screen?>" />
            <?=system_getFormInputSearchParams((($_POST)?($_POST):($GET)));?>

            <section class="row heading">

                <div class="container">
                    <div class="col-sm-8">
                        <? if ($id) { ?>
                            <h1><?=string_ucwords(system_showText(LANG_SITEMGR_EDIT))." ".system_showText(LANG_SITEMGR_LISTING_SING)?> <i><?=$listing->getString("title")?></i></h1>
                        <? } else { ?>
                            <h1><?=string_ucwords(system_showText(LANG_SITEMGR_ADD))." ".system_showText(LANG_SITEMGR_LISTING_SING)?></h1>
                        <? } ?>
                    </div>
                </div>

            </section>

            <section class="row tab-options">

                <div class="container">
                    <? include(SM_EDIRECTORY_ROOT."/layout/nav-tabs-content-listing.php"); ?>

                    <? if (CUSTOM_EVENT_FEATURE == "on") { ?>
                        <div class="pull-right top-actions">
                            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/"?>"
                               class="btn btn-default btn-xs">
                                <?=system_showText(LANG_CANCEL)?>
                            </a>
                            <span class="separator"> <?=system_showText(LANG_OR)?>  </span>
                            <button type="submit"
                                    value="Submit"
                                    class="btn btn-primary action-save"
                                    data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>">
                                <?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?>
                            </button>
                        </div>
                    <? } ?>
                </div>

                <? echo SymfonyCore::forward('EventAssociationListingBundle:Default:formListingEvent', $_POST, $_GET)->getContent(); ?>

            </section>

            <? if (CUSTOM_EVENT_FEATURE == "on") { ?>
                <section class="row footer-action">

                    <div class="container">
                        <div class="col-xs-12 text-right">
                            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/"?>"
                               class="btn btn-default btn-xs">
                                <?=system_showText(LANG_CANCEL)?>
                            </a>
                            <span class="separator"> <?=system_showText(LANG_OR)?>  </span>
                            <button type="submit"
                                    value="Submit"
                                    class="btn btn-primary action-save"
                                    data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>">
                                <?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?>
                            </button>
                        </div>
                    </div>

                </section>
            <? } else { ?>
                <p class="alert alert-info"><?=system_showText(LANG_SITEMGR_MODULE_UNAVAILABLE)?></p>
            <? } ?>

        </form>

    </main>

<?
# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/modules.php";
include(SM_EDIRECTORY_ROOT."/layout/footer.php");