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
	# * FILE: /includes/forms/form_classifiedlevel.php
	# ----------------------------------------------------------------------------------------------------

	if ((!$classified) || (($classified) && ($classified->needToCheckOut())) || (string_strpos($url_base, "/".SITEMGR_ALIAS."")) || (($classified) && ($classified->getPrice('monthly') <= 0 && $classified->getPrice('yearly') <= 0))) {

        $signupItem = "classified";
        include(INCLUDES_DIR."/code/moduleslevel.php");

    } else { ?>

		<p>
            <?=string_ucwords($levelObj->getLevel($level));?>

        </p>

	<? } ?>

    <input type="hidden" name="level" id="level" value="<?=$level?>">
