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
	# * FILE: /sponsors/layout/navbar.php
	# ----------------------------------------------------------------------------------------------------
?>
    <?php
        if (string_strpos($_SERVER["PHP_SELF"], MEMBERS_ALIAS."/index.php") !== false) {
            $blockListingCreation = system_blockListingCreation();
    ?>
        <div class="new-content">
            <button class="button button-md is-primary new-content-toggler" full-width="true"><?=system_showText(LANG_ADD_NEW_CONTENT);?></button>
            <div class="new-content-list">
                <? if (!$blockListingCreation) { ?>
                    <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/<?=LISTING_FEATURE_FOLDER;?>/listinglevel.php" class="new-content-item"><?=system_showText(LANG_LISTING_FEATURE_NAME);?></a>
                <? } ?>
                <? if (BANNER_FEATURE == "on" && CUSTOM_BANNER_FEATURE == "on") { ?>
                    <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/<?=BANNER_FEATURE_FOLDER;?>/banner.php" class="new-content-item"><?=system_showText(LANG_BANNER_FEATURE_NAME);?></a>
                <? } ?>
                <? if (EVENT_FEATURE == "on" && CUSTOM_EVENT_FEATURE == "on" && !$blockListingCreation) { ?>
                    <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/<?=EVENT_FEATURE_FOLDER;?>/eventlevel.php" class="new-content-item"><?=system_showText(LANG_EVENT_FEATURE_NAME);?></a>
                <? } ?>
                <? if (CLASSIFIED_FEATURE == "on" && CUSTOM_CLASSIFIED_FEATURE == "on" && !$blockListingCreation) { ?>
                    <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/<?=CLASSIFIED_FEATURE_FOLDER;?>/classifiedlevel.php" class="new-content-item"><?=system_showText(LANG_CLASSIFIED_FEATURE_NAME);?></a>
                <? } ?>
                <? if (ARTICLE_FEATURE == "on" && CUSTOM_ARTICLE_FEATURE == "on" && !$blockListingCreation) { ?>
                    <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/<?=ARTICLE_FEATURE_FOLDER;?>/article.php" class="new-content-item"><?=system_showText(LANG_ARTICLE_FEATURE_NAME);?></a>
                <? } ?>
            </div>
        </div>
    <? } ?>

    <? if (count($sponsorItems) > 0) { ?>
        <?php
            if (count($arrayForms)) {
                foreach ($arrayForms as $form) {
        ?>
            <div style="display:none">
                <form name="delete_<?=$form;?>" id="delete_<?=$form;?>" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                    <input type="hidden" name="hiddenValue">
                    <input type="hidden" name="module" value="<?=$form;?>">
                </form>
            </div>
        <?php
                }
            }
        ?>

    <?php
        $deals = array_filter($sponsorItems, function($item){
            return !empty($item['label']) && $item['label'] === 'Deal';
        });

        $sponsorItems = array_map(function($item) use ($deals){
            if (!empty($item['label']) && $item['label'] === 'Listing') {
                $filteredDeals = array_filter($deals, function($d) use($item){
                    return !empty($d['listing_id']) && $d['listing_id'] === $item['id'];
                });
                $item['deals'] = $filteredDeals;
            }
            return $item;
        }, $sponsorItems);

        $disableEditStyle = 'pointer-events: none; color: grey;'
    ?>
        <div class="members-cards">
        <?php foreach ($sponsorItems as $item) { ?>
            <div class="members-item" id="<?=ucfirst($item["module"])."_".$item["id"]?>" is-active="<?=$item["class"] == 'active' ? 'true': 'false';?>">
                <div class="cards-content" <?=$item["clickFunction"]?>>
                    <h5 class="heading h-5 card-title"><?=$item["title"];?></h5>
                    <div class="paragraph p-3 card-type"><i class="fa <?=$item["icon"];?>"></i> <?=$item["label"]." ".$item["level"];?></div>
                    <? if ($item["status_label"]) { ?>
                        <div class="card-status"><?=$item["status_style"];?></div>
                    <? } ?>
                </div>
                <div class="cards-actions">
                    <a href="javascript:void(0)" <?=$item["clickFunction"]?> class="action-link"><?=system_showText(LANG_LABEL_STATS);?></a>
                    <a href="<?=$item["link_edit"];?>" class="action-link"
                       style="<?= (($item['label'] === 'Listing' && count($item['deals']) > 0) || '(Bronze)' === $item['level'] || $item['label'] !== 'Listing') ? '' : $disableEditStyle ?>"
                    ><?=system_showText(LANG_LABEL_EDIT);?></a>
                    <? if ($item["link_preview"]) { ?>
                        <a href="<?=$item["link_preview"];?>" target="_blank" class="action-link"><?=system_showText(LANG_LABEL_PREVIEW);?></a>
                    <? } ?>
                    <? if ($item["link_promotion"] && !$isBronze) { ?>
                        <a href="<?=$item["link_promotion"];?>" class="action-link"><?=ucfirst(system_showText(LANG_LISTING_ADDPROMOTION));?></a>
                    <? } ?>
                    <? if ($item["link_remove"]) { ?>
                        <a href="javascript:void(0);" onclick="<?=$item["link_remove"]?>" class="action-link"><?=system_showText(LANG_LABEL_REMOVE);?></a>
                    <? } ?>
                </div>
            </div>
        <? } ?>
        </div>
    <? } ?>