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
	# * FILE: /includes/forms/form-listing-extra-fields.php
	# ----------------------------------------------------------------------------------------------------

	if ($templateObj && $templateObj->getString("status") == "enabled") {
        $template_fields = $templateObj->getListingTemplateFields("");
		if ($template_fields !== false) {
            system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_EXTRA_FIELDS), "tour-additional");
        ?>
        <div class="panel panel-form" id="tour-additional">

            <div class="panel-heading">
                <?= system_showText(LANG_EXTRA_FIELDS); ?>
            </div>

            <div class="panel-body">
            <? foreach ($template_fields as $row) {
                $row["form_value"] = ${$row["field"]};
                template_CreateDynamicField($row);
            } ?>
            </div>
        </div>
        <?php
		}
	}