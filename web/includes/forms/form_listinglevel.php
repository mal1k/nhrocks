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
	# * FILE: /includes/forms/form_listinglevel.php
	# ----------------------------------------------------------------------------------------------------

    if (Listing::enableCategorySelection($listing, $url_base, true)) {

        $signupItem = "listing";
        include(INCLUDES_DIR."/code/moduleslevel.php");
        

        if (LISTINGTEMPLATE_FEATURE == "on" && CUSTOM_LISTINGTEMPLATE_FEATURE == "on" && system_showListingTypeDropdown($listingtemplate_id)) { ?>
            
            <div class="container" style="margin-top: 48px;">
                <div class="heading h-3 text-center"><?=system_showText(LANG_LISTING_TEMPLATE);?></div>
                <br>
                <div class="text-center">
                    <select id="listingtemplate_id" name="listingtemplate_id" class="input cutom-select-appearence" style="width: 300px;">
                        <?php
                            $dbMain = db_getDBObject(DEFAULT_DB, true);
                            $dbObjLT = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
                            $sqlLT = "SELECT id FROM ListingTemplate WHERE status = 'enabled' ORDER BY editable, title";
                            $resultLT = $dbObjLT->query($sqlLT);
                            while ($rowLT = mysqli_fetch_assoc($resultLT)) {
                                $listingtemplate = new ListingTemplate($rowLT["id"]);
                                echo "<option value=\"".$listingtemplate->getNumber("id")."\"";
                                if ($listingtemplate_id == $listingtemplate->getNumber("id")) {
                                    echo " selected";
                                }
                                echo ">".$listingtemplate->getString("title");
                                if ($listingtemplate->getString("price") > 0) echo " (+".PAYMENT_CURRENCY_SYMBOL.$listingtemplate->getString("price").")";
                                echo "</option>";
                            }
                        ?>
                    </select>
                </div>
            </div>
            <br>
        <? } else { ?>
            <input type="hidden" name="listingtemplate_id" value="<?=$listingtemplate_id?>">
        <? } ?>

        <input type="hidden" name="level" id="level" value="<?=$levelArray[$levelObj->getLevel($levelvalue)]?>">

    <? } else {

        if (LISTINGTEMPLATE_FEATURE == "on" && CUSTOM_LISTINGTEMPLATE_FEATURE == "on" && system_showListingTypeDropdown($listingtemplate_id)) { ?>

            <?=system_showText(LANG_LISTING_TEMPLATE)?>:

            <?
            $listingtemplate = new ListingTemplate($listing->getNumber("listingtemplate_id"));
            if (($listingtemplate) && ($listingtemplate->getNumber("id") > 0)) {
                echo $listingtemplate->getString("title");
            } else {
                echo system_showText(LANG_LABEL_DEFAULT);
            }
            if ($listingtemplate->getString("price") > 0) echo " (+".PAYMENT_CURRENCY_SYMBOL.$listingtemplate->getString("price").")";
            else echo " (".system_showText(LANG_LABEL_FREE).")";
            ?>

        <? } ?>

        <input type="hidden" name="listingtemplate_id" value="<?=$listingtemplate_id?>">
        <br>
        <?=system_showText(LANG_LISTING_LEVEL);?> :
        <?=string_ucwords($levelObj->getLevel($level));?>
        <input type="hidden" name="level" value="<?=$level?>">

    <? } ?>
