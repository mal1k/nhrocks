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
	# * FILE: /sponsors/event/event.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (EVENT_FEATURE != "on" || CUSTOM_EVENT_FEATURE != "on") { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSession();
	$acctId = sess_getAccountIdFromSession();
	$members = 1;
	$item_form    = 1;

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$url_redirect = "".DEFAULT_URL."/".MEMBERS_ALIAS;
	$url_base = "".DEFAULT_URL."/".MEMBERS_ALIAS."";

    if (system_blockListingCreation($id)) {
        header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
        exit;
    }

	include(EDIRECTORY_ROOT."/includes/code/event.php");

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");

	# ----------------------------------------------------------------------------------------------------
	# NAVBAR
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/navbar.php");
    $cover_title = system_showText($id ? LANG_LABEL_EDIT : LANG_ADD) ." ". system_showText(LANG_EVENT_FEATURE_NAME);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>

    <div class="members-page">
        <div class="container">
            <div class="members-wrapper">
                <div class="members-panel edit-panel">
                    <div class="panel-header">
                        <?=system_showText(LANG_EVENT_INFORMATION)?>
                    </div>
                    <div class="panel-body">
                        <form name="event" id="event" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post" enctype="multipart/form-data">

                            <input type="hidden" name="process" id="process" value="<?=$process?>">
                            <input type="hidden" name="id" id="id" value="<?=$id?>">
                            <input type="hidden" name="account_id" id="account_id" value="<?=$acctId?>">
                            <input type="hidden" name="level" id="level" value="<?=$level?>">
                            <input type="hidden" name="using_package" id="using_package" value="<?=($package_id ? "y" : "n")?>">
                            <input type="hidden" name="package_id" id="package_id" value="<?=$package_id?>">
                            <input type="hidden" name="gallery_hash" value="<?=$gallery_hash?>">

                            <? if ($message_event) { ?>
                            <div class="form-edit-alert">
                                <?=$message_event;?>
                            </div>
                            <? } ?>
                            
                            <div class="custom-edit-content" has-sidebar="true">
                                <? include(INCLUDES_DIR."/forms/form-event.php"); ?>
                            </div>
                        </form>
                        <form action="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/" method="get" class="form-action-sponsors">
                            <button class="button button-md is-outline" type="submit"><?=system_showText(LANG_BUTTON_CANCEL)?></button>
                            <button class="button button-md is-primary action-save" type="button" onclick="JS_submit()" data-loading-text="<?= LANG_LABEL_FORM_WAIT ?>">
                                <?=system_showText(LANG_MSG_SAVE_CHANGES)?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include(INCLUDES_DIR."/modals/modal-categoryselect.php");
    include(INCLUDES_DIR."/modals/modal-crop.php");
    if (!empty(UNSPLASH_ACCESS_KEY)) {
        include(INCLUDES_DIR . "/modals/modal-unsplash.php");
        JavaScriptHandler::registerFile(DEFAULT_URL . '/assets/js/lib/unsplash.js');
    }

# ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/modules.php";
    include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
