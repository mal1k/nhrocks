<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/mobile/appbuilder/index.php
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

mixpanel_track("Accessed Step 1 from App Builder section");

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
extract($_POST);
extract($_GET);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if (!$name) {
		$errorMessage .= ($errorMessage ? "<br />" : "").system_showText(LANG_SITEMGR_APPNAME_REQUIRED);
	} else {

        mixpanel_track("Updated App name on the App Builder section");

		if (!setting_set("appbuilder_app_name", $name)) {
			setting_new("appbuilder_app_name", $name);
		}

		if ( $next == "yes")
		{
			/* User has done step 2 successfully */
			setting_set("appbuilder_step_1", "done") or setting_new("appbuilder_step_1", "done");
		}

		header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/mobile/appbuilder/".($next == "yes" ? "step2.php" : "?success=1"));
		exit;
	}
}

setting_get("appbuilder_app_name", $name);
setting_get("appbuilder_icon_id", $appbuilder_icon_id);
setting_get("appbuilder_icon_extension", $appbuilder_icon_extension);
setting_get("appbuilder_build_done", $appbuilder_build_done);

extract($_POST);
extract($_GET);

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
include(SM_EDIRECTORY_ROOT."/layout/sidebar-mobile.php");

?>

	<main class="wrapper togglesidebar container-fluid">

		<section class=" row heading">
			<div class="container">
				<h1><?=system_showText(LANG_SITEMGR_APPBUILDER);?></h1>
				<p><?=system_showText(LANG_SITEMGR_CHOOSE_ICON2);?></p>
			</div>
		</section>

		<section class="row appbuilder">

			<div class="appbuilder-container">

				<?
				require(EDIRECTORY_ROOT."/".SITEMGR_ALIAS."/registration.php");
				require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");

				/*  Navbar  */
				include("navbar.php");
				?>

				<section class="container">

					<h4><?=system_showText(LANG_SITEMGR_CONFIGURE_APP_STEP_10)?></h4>
					<p><?=system_showText(LANG_SITEMGR_CONFIGURE_APP_STEP_10_MESSAGE)?></p>
					<p class="alert-tip"><?=system_showText(LANG_SITEMGR_CONFIGURE_APP_STEP_10_TIP)?></p>

					<p id="returnMessage" style="display:none;"></p>

					<? if ($errorMessage) { ?>
						<p id="errorMessage" class="errorMessage"><?=$errorMessage;?></p>
					<? } elseif ($success) { ?>
						<p id="successMessage" class="successMessage"><?=ucfirst(system_showText(LANG_SITEMGR_SETTINGSSUCCESSUPDATED));?></p>
					<? } ?>

					<form id="step1" name="step1" method="post" enctype="multipart/form-data" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>">

						<input type="hidden" name="next" id="next" value="no" />

						<div class="form-left">

							<h4><?=system_showText(LANG_SITEMGR_ICONPREVIEW);?></h4>
							<p><?=system_showText(LANG_SITEMGR_ICONPREVIEW_TIP);?></p>

							<div class="cover-preview-image device-apple-large">
								<div class="preview-icon" id="preview-icon">
									<? if (file_exists(EDIRECTORY_ROOT."/".IMAGE_APPBUILDER_PATH."/appbuilder_icon_{$appbuilder_icon_id}.{$appbuilder_icon_extension}")) { ?>
										<img src="<?=DEFAULT_URL."/".IMAGE_APPBUILDER_PATH."/appbuilder_icon_{$appbuilder_icon_id}.{$appbuilder_icon_extension}"?>" />
									<? } ?>
								</div>
								<div class="cover-device-icons"></div>
							</div>

						</div>

						<div class="form-right">

							<h4><?=system_showText(LANG_SITEMGR_BUILDER_CONFIG);?></h4>
							<p><?=system_showText(LANG_SITEMGR_ICON_CONFIG_TIP);?></p>

							<div>
								<label for="name"><?=system_showText(LANG_SITEMGR_APPNAME)?></label>
								<input class="form-control" type="text" name="name" id="name" value="<?=($name ? $name : EDIRECTORY_TITLE);?>" maxlength="40" />
								<p class="tip"><?=system_showText(LANG_SITEMGR_APPNAME_TIP)?></p>
							</div>

							<div>
								<label><?=system_showText(LANG_SITEMGR_ICONIMAGE)?> (1024 x 1024 pixels)</label>
								<div class="row">
									<div class="col-xs-6"> <input type="file" class="file-noinput" name="image" id="image" onchange="sendFile();" /> <br> </div>
									<div class="col-xs-6"> <div id="loading_image" class="loading-image hidden"><img src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/loading-32.gif" alt="<?=system_showText(LANG_SITEMGR_WAITLOADING);?>" title="<?=system_showText(LANG_SITEMGR_WAITLOADING);?>"/></div></div>
								</div>
								<p id="alert_img" class="alert alert-warning" style="display:none"><?=system_showText(LANG_SITEMGR_APPBUILDER_ABOUT_CONFIG_TIP2);?></p>
							</div>

						</div>

						<div class="action">
							<button type="button" class="btn btn-success" onclick="JS_submit(true);"><?=system_showText(LANG_SITEMGR_SAVENEXT)?></button>
						</div>

					</form>

				</section>

			</div>

		</section>

	</main>

<?
# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/appbuilder.php";
include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
