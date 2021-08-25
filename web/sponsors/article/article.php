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
	# * FILE: /sponsors/article/article.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (ARTICLE_FEATURE != "on" || CUSTOM_ARTICLE_FEATURE != "on") { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSession();
	$acctId = sess_getAccountIdFromSession();

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_GET);
	extract($_POST);

	$url_redirect = "".DEFAULT_URL."/".MEMBERS_ALIAS;
	$url_base = "".DEFAULT_URL."/".MEMBERS_ALIAS."";
	$members = 1;
	$item_form = 1;

	/*
	 * Check if need show package
	 */
	if (!$id && !$show_package && !$package_id && $_SERVER['REQUEST_METHOD'] != "POST") {
		/*
		 * Check if exists package
		 */
		$articleLevelObj = new ArticleLevel();
		$level = $articleLevelObj->getDefault();

		$packageObj = new Package();
		$array_package_offers = $packageObj->getPackagesByDomainID(SELECTED_DOMAIN_ID, "article", $level);
		$hasPackage = false;
		if ((is_array($array_package_offers)) and (count($array_package_offers) > 0) and $array_package_offers[0]) {
			header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/".ARTICLE_FEATURE_FOLDER."/order_package.php?show_package=1&level=".$level);
			exit;
		}
	}

	if (system_blockListingCreation($id)) {
        header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
        exit;
    }

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include(EDIRECTORY_ROOT."/includes/code/article.php");

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");

	# ----------------------------------------------------------------------------------------------------
	# NAVBAR
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/navbar.php");
	$cover_title = system_showText($id ? LANG_LABEL_EDIT : LANG_ADD) ." ". system_showText(LANG_ARTICLE_FEATURE_NAME);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>

	<div class="members-page">
        <div class="container">
            <div class="members-wrapper">
                <div class="members-panel edit-panel">
                    <div class="panel-header">
                        <?=system_showText(LANG_ARTICLE_INFORMATION)?>
                    </div>
                    <div class="panel-body">
						<form name="article" id="article" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post" enctype="multipart/form-data">
							<? /* Microsoft IE Bug (When the form contain a field with a special char like &#8213; and the enctype is multipart/form-data and the last textfield is empty the first transmitted field is corrupted) */ ?>

							<input type="hidden" name="ieBugFix" value="1">

							<? /* Microsoft IE Bug */ ?>

							<input type="hidden" name="process" id="process" value="<?=$process?>">
							<input type="hidden" name="id" id="id" value="<?=$id?>">
							<input type="hidden" name="account_id" id="account_id" value="<?=$acctId?>">
							<input type="hidden" name="level" id="level" value="<?=$level?>">
							<input type="hidden" name="using_package" id="using_package" value="<?=($package_id ? "y" : "n")?>">
							<input type="hidden" name="package_id" id="package_id" value="<?=$package_id?>">
							<input type="hidden" name="gallery_hash" value="<?=$gallery_hash?>">

							<? if ($message_article) { ?>
							<div class="form-edit-alert">
								<?=$message_article;?>
							</div>
							<? } ?>

							<div class="custom-edit-content" has-sidebar="true">
								<? include(INCLUDES_DIR."/forms/form-article.php"); ?>
							</div>

							<? /* Microsoft IE Bug (When the form contain a field with a special char like &#8213; and the enctype is multipart/form-data and the last textfield is empty the first transmitted field is corrupted)*/ ?>

							<input type="hidden" name="ieBugFix2" value="1">

							<? /* Microsoft IE Bug */ ?>
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
