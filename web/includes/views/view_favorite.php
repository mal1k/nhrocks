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
    # * FILE: /includes/views/view_favorite.php
    # ----------------------------------------------------------------------------------------------------

    if ($module == "listing") {

        unset($listing);
        $listing = new Listing($favorite["id"]);
        $level = new ListingLevel(true);

        $listingAux = $listing;
        $listing = $listing->data_in_array;

        //Get fields according to level
        unset($array_fields);
        $array_fields = system_getFormFields("Listing", $listing['level']);

        $itemLink = LISTING_DEFAULT_URL."/".htmlspecialchars($listing["friendly_url"]).".html";

        unset($item_phone);
        if (htmlspecialchars($listing["phone"]) && is_array($array_fields) && in_array("phone", $array_fields)) {
            $item_phone = $listing["phone"];
        }

        unset($avgreview);
        if ($review_enabled == "on") {
            if ($levelsWithReview) {
                if (in_array($listing["level"], $levelsWithReview)) {
                    $avgreview = $listing["avg_review"];
                }
            }
        }

        $remove_favorites_click = "onclick=\"itemInQuicklist(this, 'remove', '".sess_getAccountIdFromSession()."', '".$listing["id"]."', 'listing');\"";

        $item_title = htmlspecialchars($listing["title"]);

    } elseif ($module == "classified") {

        $classified = new Classified($favorite["id"]);
        $level = new ClassifiedLevel(true);

        //Get fields according to level
        unset($array_fields);
        $array_fields = system_getFormFields("Classified", $classified->getNumber("level"));

        $itemLink = CLASSIFIED_DEFAULT_URL."/".$classified->getString("friendly_url").".html";

        unset($item_phone);
        if (is_array($array_fields) && in_array("contact_phone", $array_fields)){
            $item_phone = $classified->getString("phone");
        }

        $item_title = $classified->getString("title");

        $remove_favorites_click = "onclick=\"itemInQuicklist(this, 'remove', '".sess_getAccountIdFromSession()."', '".$classified->getNumber("id")."', 'classified');\"";

    } elseif ($module == "event") {

        $event = new Event($favorite["id"]);
        $level = new EventLevel(true);

        //Get fields according to level
        unset($array_fields);
        $array_fields = system_getFormFields("Event", $event->getNumber("level"));

        $itemLink = EVENT_DEFAULT_URL."/".$event->getString("friendly_url").".html";

        unset($item_phone);
        if (is_array($array_fields) && in_array("phone", $array_fields)){
            $item_phone = $event->getString("phone");
        }

        $item_title = $event->getString("title");

        $remove_favorites_click = "onclick=\"itemInQuicklist(this, 'remove', '".sess_getAccountIdFromSession()."', '".$event->getNumber("id")."', 'event');\"";

    } elseif ($module == "article") {

        $article = new Article($favorite["id"]);
        $level = new ArticleLevel(true);
        $itemLink = ARTICLE_DEFAULT_URL."/".$article->getString("friendly_url").".html";

        $item_title = $article->getString("title");
        $remove_favorites_click = "onclick=\"itemInQuicklist(this, 'remove', '".sess_getAccountIdFromSession()."', '".$article->getNumber("id")."', 'article');\"";

        unset($avgreview);

    }
    ?>
    <div class="favorite-item">
        <div class="favorite-content">
            <a href="<?=$itemLink?>" class="heading h-4 favorite-name"><?=$item_title;?></a>

            <?php if ($module != "article") { ?>
                <div class="paragraph p-3 favorite-address"><?=system_getItemAddressString(ucfirst($module), $favorite["id"]);?></div>
            <?php } ?>

            <?php if ($id == sess_getAccountIdFromSession()) { ?>
                <a href="javascript:void(0);" class="remove-favorite" rel="nofollow" <?=$remove_favorites_click?>><i class="fa fa-close"></i></a>
            <?php } ?>
        </div>
        <div class="favorite-footer">
            <div class="favorite-share">
                <a href="https://www.facebook.com/sharer.php?u=<?=$itemLink?>&amp;t=<?=urlencode($item_title);?>" class="social-link" rel="nofollow" target="_blank" title="<?=system_showText(LANG_ADDTO_SOCIALBOOKMARKING)." Facebook"?>"><i class="fa fa-facebook-square"></i></a>
                <a href="https://twitter.com/?status=<?=$itemLink?>" class="social-link" rel="nofollow" target="_blank" title="<?=system_showText(LANG_ADDTO_SOCIALBOOKMARKING)." Twitter"?>"><i class="fa fa-twitter-square"></i></a>
            </div>
            <?php if ($item_phone) { ?>
                <div class="favorite-phone"><?=$item_phone?></div>
            <?php } ?>
        </div>
    </div>
