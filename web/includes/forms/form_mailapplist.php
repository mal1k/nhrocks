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
	# * FILE: /includes/forms/form_mailapplist.php
	# ----------------------------------------------------------------------------------------------------

    if ($message_mailapp) {
        echo "<p class=\"alert alert-warning\">";
            echo $message_mailapp;
        echo "</p>";
    }
    $modulesStr = LISTING_FEATURE_NAME_PLURAL.(EVENT_FEATURE == "on" && FORCE_DISABLE_EVENT_FEATURE != "on" ? ", ".EVENT_FEATURE_NAME_PLURAL : "" ).(CLASSIFIED_FEATURE == "on" && FORCE_DISABLE_CLASSIFIED_FEATURE != "on" ? ", ".CLASSIFIED_FEATURE_NAME_PLURAL : "").(ARTICLE_FEATURE == "on" && FORCE_DISABLE_ARTICLE_FEATURE != "on" ? ", ".ARTICLE_FEATURE_NAME_PLURAL : "");
    ?>

    <form name="mailapp" role="form" id="mailapp" method="post" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>">
        <input type="hidden" name="return_categories_all" value="">

        <div class="panel panel-default">
            <div class="panel-heading">
                1. <?=system_showText(LANG_SITEMGR_MAILAPP_CLIENTEXPORTER_STEP_1);?>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label><?=system_showText(LANG_SITEMGR_MAILAPP_CLIENTEXPORTER_STEP_1_TIP);?></label>
                    <div class="row">
                        <div class="col-sm-6 selectize">
                            <select id="moduleSelector" name="module" onchange="showCategoriesByModule(this.value);">
                                <?=system_showText($modulesDropdownOptions);?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default" id="div_step_2" <?=($module == "all" ? "style=\"display: none;\"" : "");?>>
            <div class="panel-heading">
                2. <?=system_showText(LANG_SITEMGR_MAILAPP_CLIENTEXPORTER_STEP_2);?>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label><?=system_showText(LANG_SITEMGR_MAILAPP_CLIENTEXPORTER_STEP_2_TIP);?></label>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="categories" id="divCategories" <?=($module ? "" : "style=\"display: none;\"")?>>
                                <p class="help-block"><?=system_showText(LANG_MSG_CLICKADDTOSELECTCATEGORIES);?></p>
                                <? foreach ($availableModules as $avModule) { ?>

                                    <div id="categories_<?=ucfirst($avModule);?>" <?=($module == ucfirst($avModule) ? "" : "style=\"display: none;\"")?>>
                                        <ul id="<?=$avModule?>_categorytree_id_0" class="categoryTreeview">&nbsp;</ul>
                                        <div style="display: none">
                                            <?=${"feedDropDown_".$avModule};?>
                                        </div>
                                        <input type="hidden" name="return_categories_<?=$avModule?>" value="">
                                    </div>

                                <? } ?>
                            </div>
                        </div>
                        <div class="col-sm-6" id="divAll">
                            <div class="categories-field">
                                <?=$feed_all;?>
                            </div>
                            <div class="optionbuttons" id="removeCategoriesButton" style="display:none;">
                                <button class="btn btn-info" type="button" value="<?=system_showText(LANG_BUTTON_REMOVESELECTEDCATEGORY)?>" onclick="removeCategory(document.mailapp.feed_all);"><?=system_showText(LANG_BUTTON_REMOVESELECTEDCATEGORY)?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default" id="div_step_3">
            <div class="panel-heading">
                <span id="label_step_3"><?=($module == "all" ? "2" : "3");?></span>. <?=system_showText(LANG_SITEMGR_MAILAPP_CLIENTEXPORTER_STEP_5);?>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label><?=system_showText(LANG_SITEMGR_MAILAPP_CLIENTEXPORTER_STEP_5_TIP);?></label>
                    <div class="row">
                        <div class="col-sm-6">
                            <?php include(EDIRECTORY_ROOT."/includes/code/load_location.php"); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <span id="label_step_4"><?=($module == "all" ? "3" : "4");?></span>. <?=system_showText(LANG_SITEMGR_MAILAPP_CLIENTEXPORTER_STEP_3);?>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="title"><?=system_showText(LANG_SITEMGR_MAILAPP_CLIENTEXPORTER_STEP_3_TIP);?></label>
                    <div class="row">
                        <div class="col-sm-6">
                             <input type="text" name="title" id="title" class="form-control" value="<?=$title;?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <span id="label_step_5"><?=($module == "all" ? "4" : "5");?></span>. <?=system_showText(LANG_SITEMGR_MAILAPP_CLIENTEXPORTER_STEP_4);?>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label><?=system_showText(LANG_SITEMGR_MAILAPP_CLIENTEXPORTER_STEP_4_TIP);?></label>
                    <button type="button" onclick="JS_submit();" class="btn btn-info action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_EXPORT_SUBMIT);?></button>
                </div>
            </div>
        </div>

    </form>
