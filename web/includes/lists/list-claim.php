<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/lists/list-claim.php
	# ----------------------------------------------------------------------------------------------------

    if (is_numeric($message) && isset($msg_claim[$message])) { ?>
        <p class="alert alert-success"><?=$msg_claim[$message]?></p>
    <? } ?>

    <section id="list-claim">

        <ul class="list-content-item list no-bulk">

        <?
        $cont = 0;
        $level = new ListingLevel(true);
        foreach($claims as $claim) {
            $cont++;

            $previewClaim[$cont]["id"] = $claim->getNumber("id");
            if ($claim->getNumber("account_id")) {
                $previewClaim[$cont]["account_id"] = $claim->getNumber("account_id");
            }
            $previewClaim[$cont]["username"] = $claim->getString("username");
            if ($claim->getNumber("listing_id")) {
                $previewClaim[$cont]["listing_id"] = $claim->getNumber("listing_id");

				$listing = new Listing($claim->getNumber("listing_id"));
                $listingHasDetail = $level->getDetail($listing->getNumber("level"));
                $previewClaim[$cont]["preview_url"] = $listing->getFriendlyURL(LISTING_DEFAULT_URL);
            }
            $previewClaim[$cont]["listing_title"] = $claim->getString("listing_title");
            $previewClaim[$cont]["old_title"] = $claim->getString("old_title");
            $previewClaim[$cont]["new_title"] = $claim->getString("new_title");
            $previewClaim[$cont]["old_friendly_url"] = $claim->getString("old_friendly_url");
            $previewClaim[$cont]["new_friendly_url"] = $claim->getString("new_friendly_url");
            $previewClaim[$cont]["old_email"] = $claim->getString("old_email");
            $previewClaim[$cont]["new_email"] = $claim->getString("new_email");
            $previewClaim[$cont]["old_url"] = $claim->getString("old_url");
            $previewClaim[$cont]["new_url"] = $claim->getString("new_url");
            $previewClaim[$cont]["old_phone"] = $claim->getString("old_phone");
            $previewClaim[$cont]["new_phone"] = $claim->getString("new_phone");
            $previewClaim[$cont]["old_label_additional_phone"] = $claim->getString("old_label_additional_phone");
            $previewClaim[$cont]["new_label_additional_phone"] = $claim->getString("new_label_additional_phone");
            $previewClaim[$cont]["old_additional_phone"] = $claim->getString("old_additional_phone");
            $previewClaim[$cont]["new_additional_phone"] = $claim->getString("new_additional_phone");
            $previewClaim[$cont]["old_address"] = $claim->getString("old_address");
            $previewClaim[$cont]["new_address"] = $claim->getString("new_address");
            $previewClaim[$cont]["old_address2"] = $claim->getString("old_address2");
            $previewClaim[$cont]["new_address2"] = $claim->getString("new_address2");
            $previewClaim[$cont]["old_description"] = $claim->getString("old_description");
            $previewClaim[$cont]["new_description"] = $claim->getString("new_description");
            $previewClaim[$cont]["old_long_description"] = $claim->getString("old_long_description");
            $previewClaim[$cont]["new_long_description"] = $claim->getString("new_long_description");
            $previewClaim[$cont]["old_keywords"] = $claim->getString("old_keywords");
            $previewClaim[$cont]["new_keywords"] = $claim->getString("new_keywords");
            $previewClaim[$cont]["old_locations"] = $claim->getString("old_locations");
            $previewClaim[$cont]["new_locations"] = $claim->getString("new_locations");
            $previewClaim[$cont]["old_features"] = $claim->getString("old_features");
            $previewClaim[$cont]["new_features"] = $claim->getString("new_features");
            $previewClaim[$cont]["old_hours_work"] = $claim->getString("old_hours_work");
            $previewClaim[$cont]["new_hours_work"] = $claim->getString("new_hours_work");
            $previewClaim[$cont]["old_seo_title"] = $claim->getString("old_seo_title");
            $previewClaim[$cont]["new_seo_title"] = $claim->getString("new_seo_title");
            $previewClaim[$cont]["old_seo_keywords"] = $claim->getString("old_seo_keywords");
            $previewClaim[$cont]["new_seo_keywords"] = $claim->getString("new_seo_keywords");
            $previewClaim[$cont]["old_seo_description"] = $claim->getString("old_seo_description");
            $previewClaim[$cont]["new_seo_description"] = $claim->getString("new_seo_description");

            $oldSocialNetworks = (array)json_decode($claim->getString("old_social_network"));
            if (is_array($oldSocialNetworks)) {
                foreach ($oldSocialNetworks as $key => $value) {
                    $previewClaim[$cont]["old_social_network"] .= '<b>'.ucfirst($key).'</b>: '.$value.'<br>';
                }
            }

            $newSocialNetworks = (array)json_decode($claim->getString("new_social_network"));
            if (is_array($newSocialNetworks)) {
                foreach ($newSocialNetworks as $key => $value) {
                    $previewClaim[$cont]["new_social_network"] .= '<b>'.ucfirst($key).'</b>: '.$value.'<br>';
                }
            }

            //Categories
            $categories = (array)json_decode($claim->getString("old_categories"));
            if (is_array($categories)) {
                foreach ($categories as $category) {
                    $listCateg = new ListingCategory($category);
                    if ($listCateg->getNumber('id')) {
                        $previewClaim[$cont]["old_categories"] .= $listCateg->getString('title').'<br>';
                    }
                }
            }

            $categories = (array)json_decode($claim->getString("new_categories"));
            if (is_array($categories)) {
                foreach ($categories as $category) {
                    $listCateg = new ListingCategory($category);
                    if ($listCateg->getNumber('id')) {
                        $previewClaim[$cont]["new_categories"] .= $listCateg->getString('title').'<br>';
                    }
                }
            }

            $_locations = explode(",", EDIR_LOCATIONS);
            foreach ($_locations as $_location_level) {
                $previewClaim[$cont]["old_location_".$_location_level] = $claim->getString("old_location_".$_location_level);
                $previewClaim[$cont]["new_location_".$_location_level] = $claim->getString("new_location_".$_location_level);
            }

            $previewClaim[$cont]["old_zipcode"] = $claim->getString("old_zip_code");
            $previewClaim[$cont]["new_zipcode"] = $claim->getString("new_zip_code");

            $oldlistingtemplate = new ListingTemplate($claim->getString("old_listingtemplate_id"));
            $newlistingtemplate = new ListingTemplate($claim->getString("new_listingtemplate_id"));

            $previewClaim[$cont]["old_listingtemplate"] = $oldlistingtemplate->getString("title");
            $previewClaim[$cont]["new_listingtemplate"] = $newlistingtemplate->getString("title");

            $previewClaim[$cont]["old_level"] = $level->showLevel($claim->getString("old_level"));;
            $previewClaim[$cont]["new_level"] = $level->showLevel($claim->getString("new_level"));;

            $previewClaim[$cont]["canApprove"] = $claim->canApprove();
            $previewClaim[$cont]["canDeny"] = $claim->canDeny();

            ?>

            <li class="content-item" data-id="<?=$claim->getNumber("id")?>">
                <div class="status"><span class="status-<?=($claim->getString("status") == "approved" ? "active" : ($claim->getString("status") == "denied" ? "pending" : "suspended"))?>"></span></div>
                <div class="item">
                    <h3 class="item-title">
                        <?
                        if ($claim->getString("old_title") == $claim->getString("new_title")) {
                            echo $claim->getString("listing_title");
                        } else {
                            echo $claim->getString("new_title")." (".$claim->getString("old_title").")";
                        }
                        ?>
                    </h3>

                    <p>
                        <span class="item-author">
                            <?=string_ucwords(system_showText(LANG_SITEMGR_ACCOUNT))?>: <?=system_showAccountUserName($claim->getString("username"));?>
                        </span>
                    </p>
                    <p>
                        <span class="pull-left"><?=system_showText(LANG_LABEL_DATE)?>: <?=format_date($claim->getString("date_time"), DEFAULT_DATE_FORMAT, "datetime")." - ".format_getTimeString($claim->getNumber("date_time"));?></span>
                        <span class="pull-right">
                            <span class="status-<?=($claim->getString("status") == "approved" ? "active" : ($claim->getString("status") == "denied" ? "pending" : "suspended"))?>">
                                <?=@system_showText(constant("LANG_SITEMGR_CLAIM_STATUS_".string_strtoupper($claim->getString("status"))))?>
                            </span>
                        </span>
                    </p>
                </div>
            </li>

        <? } ?>

        </ul>

    </section>
