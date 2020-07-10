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
	# * FILE: /includes/forms/form_eventlevel.php
	# ----------------------------------------------------------------------------------------------------

    if ((!$event) || (($event) && ($event->needToCheckOut())) || (string_strpos($url_base, "/".SITEMGR_ALIAS."")) || (($event) && ($event->getPrice('monthly') <= 0 && $event->getPrice('yearly') <= 0))) {

        $signupItem = "event";
        include(INCLUDES_DIR."/code/moduleslevel.php");

    } else { ?>

		<p>
			<?=string_ucwords($levelObj->getLevel($level));?>
		</p>

	<? } ?>

    <input type="hidden" id="level" name="level" value="<?=$level?>">
