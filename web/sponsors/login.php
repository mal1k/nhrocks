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
	# * FILE: /sponsors/login.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# DOMAIN COOKIE VALIDATION
	# ----------------------------------------------------------------------------------------------------
	if (!isset($_COOKIE["automatic_login_members"]) || $_COOKIE["automatic_login_members"] == "false") {
		$resetDomainSession = true;
	}

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/includes/code/login.php");

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");

    $cover_title = system_showText(LANG_LABEL_SPONSORAREA);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php"); 
?>

    <div class="modal-default modal-sign" is-page="true">
        <div class="modal-content">
            <div class="modal-body">
                <div class="content-tab content-sign-in">
                    <?php if ($foreignaccount_google == "on" || FACEBOOK_APP_ENABLED == "on") { ?>
                        <div class="modal-social">
                            <?php
                                $redirectURI_params = [
                                    "destiny" => "sponsors"
                                ];

                                if (FACEBOOK_APP_ENABLED == "on") {
									$fbLabel = 'Facebook';
                                    include(INCLUDES_DIR."/forms/form_facebooklogin.php");
                                }

                                if ($foreignaccount_google == "on" ) {
									$goLabel = 'Google';
                                    include(INCLUDES_DIR."/forms/form_googlelogin.php");
                                }
                            ?>
                        </div>
                        <span class="heading or-label"><?= system_showText(LANG_OR); ?></span>
                    <?php } ?>
                    <form name="formDirectory" method="post" action="<?=MEMBERS_LOGIN_PAGE;?>" class="modal-form">
                        <input type="hidden" name="advertise" value="<?=($_GET["advertise"] ? $_GET["advertise"] : $_POST["advertise"]);?>">
                        <input type="hidden" name="claim" value="<?=($_GET["claim"] ? $_GET["claim"] : $_POST["claim"]);?>">
                        <? include(INCLUDES_DIR."/forms/form_login.php"); ?>
                    </form>
                    <div class="not-member"><a href="<?=DEFAULT_URL?>/<?=ALIAS_ADVERTISE_URL_DIVISOR?>/" class="link"><?=system_showText(LANG_DOYOUWANT_ADVERTISEWITHUS)?></a></div>
                </div>
            </div>
        </div>
    </div>

<?
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
