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
	# * FILE: /sponsors/help.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSession();
	$acctId = sess_getAccountIdFromSession();
	$url_base = DEFAULT_URL."/".MEMBERS_ALIAS."";

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_GET);
	extract($_POST);

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$contactObj = new Contact($acctId);
	$name       = $contactObj->getString("first_name")." ".$contactObj->getString("last_name");
	$email      = $contactObj->getString("email");

	if ($_POST) {

		extract($_POST);
		$validate_help = validate_form("help", $_POST, $message_help);

		if ($validate_help) {

			$text = $_POST["text"];
			$text = str_replace("\r\n", "\n", $text);
			$text = str_replace("\n", "\r\n", $text);

            setting_get("sitemgr_support_email", $sitemgr_support_email);
			$sitemgr_support_emails = explode(",", $sitemgr_support_email);

			$emailSubject = "[".EDIRECTORY_TITLE."] ".system_showText(LANG_NOTIFY_MEMBERSHELP);

            $sitemgr_msg = system_showText(LANG_LABEL_SITE_MANAGER).",<br><br>".system_showText(LANG_NOTIFY_MEMBERSHELP_1)."<br><br>".system_showText(LANG_NOTIFY_MEMBERSHELP_2).": ".$name."<br><br>"."".system_showText(LANG_NOTIFY_MEMBERSHELP_3).": ".$email."<br><br>".system_showText(LANG_NOTIFY_MEMBERSHELP_4).": <br><br>".nl2br($text)."";

            system_notifySitemgr($sitemgr_support_emails, $emailSubject, $sitemgr_msg);

            $success = true;
            $message_help = system_showText(LANG_CONTACTMSGSUCCESS);

		}

		// removing slashes added if required
		$_POST = format_magicQuotes($_POST);
		$_GET  = format_magicQuotes($_GET);
		extract($_POST);
		extract($_GET);

	}

	$cover_title = system_showText(LANG_LABEL_HELP);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>
	<div class="members-page">
        <div class="container">
            <div class="members-wrapper">
                <div class="members-panel edit-panel">
                    <div class="panel-header">
                        <?=system_showText(LANG_HELP_MESSAGE)?>
                    </div>
                    <div class="panel-body">
						<form name="help" method="post" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>">
							<?php if ($message_help){ ?>
								<?php if($success){ ?>
									<div class="form-edit-alert alert-success">
										<?=$message_help?>
									</div>
								<?php } else { ?>
									<div class="form-edit-alert">
										<?=$message_help?>
									</div>
								<?php } ?>
							<?php } ?>
							
							<div class="custom-edit-content" has-sidebar="false">
								<div class="form-group">
									<label for="name"><?=system_showText(LANG_LABEL_NAME)?></label>
									<input class="form-control" id="name" type="text" name="name" value="<?=$name?>" />
								</div>
								<div class="form-group">
									<label for="email"><?=system_showText(LANG_LABEL_EMAIL)?></label>
									<input class="form-control" id="email" type="text" name="email" value="<?=$email?>" />
								</div>
								<div class="form-group">
									<label for="textarea"><?=system_showText(LANG_LABEL_MESSAGE)?></label>
									<textarea class="form-control" id="textarea" name="text" value="<?=$text?>" rows="6"></textarea>
								</div>

								<button type="submit" class="button button-md is-primary"><?=system_showText(LANG_BUTTON_SEND)?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
