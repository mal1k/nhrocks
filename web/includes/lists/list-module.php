<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/lists/list-module.php
	# ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	setting_get("commenting_edir", $commenting_edir);
	setting_get("commenting_fb", $commenting_fb);
	setting_get("review_{$manageModule}_enabled", $review_enabled);
    $moduleObj = ucfirst($manageModule);
    $domain = new Domain(SELECTED_DOMAIN_ID);
    $domainURL = (SSL_ENABLED == "on" ? "https://" : "http://") . $domain->getString("url") . "/";

    if ($manageModule == "listing") {
        $levelsWithReview = system_retrieveLevelsWithInfoEnabled("has_review");
    }

    switch ($manageModule) {
        case "listing":     $level = new ListingLevel(true);
                            $msgSucessUpdate = LANG_MSG_LISTING_SUCCESSFULLY_UPDATE;
                            $msgSuccessDelete = LANG_MSG_LISTING_SUCCESSFULLY_DELETE;
                            $itemsList = $listings;
                            $moduleDefaultURL =  $domainURL . ALIAS_LISTING_MODULE;
                            $summaryfield = "description";
                            $titleField = "title";
                            break;

        case "banner":      $level = new BannerLevel(true);
                            $msgSucessUpdate = LANG_MSG_BANNER_SUCCESSFULLY_UPDATE;
                            $msgSuccessDelete = LANG_MSG_BANNER_SUCCESSFULLY_DELETE;
                            $itemsList = $banners;
                            $titleField = "caption";
                            break;

        case "event":       $level = new EventLevel(true);
                            $msgSucessUpdate = LANG_MSG_EVENT_SUCCESSFULLY_UPDATE;
                            $msgSuccessDelete = LANG_MSG_EVENT_SUCCESSFULLY_DELETE;
                            $itemsList = $events;
                            $moduleDefaultURL = $domainURL . ALIAS_EVENT_MODULE;
                            $summaryfield = "description";
                            $titleField = "title";
                            break;

        case "classified":  $level = new ClassifiedLevel(true);
                            $msgSucessUpdate = LANG_MSG_CLASSIFIED_SUCCESSFULLY_UPDATE;
                            $msgSuccessDelete = LANG_MSG_CLASSIFIED_SUCCESSFULLY_DELETE;
                            $itemsList = $classifieds;
                            $moduleDefaultURL = $domainURL . ALIAS_CLASSIFIED_MODULE;
                            $summaryfield = "summarydesc";
                            $titleField = "title";
                            break;

        case "article":     $level = new ArticleLevel(true);
                            $msgSucessUpdate = LANG_MSG_ARTICLE_SUCCESSFULLY_UPDATE;
                            $msgSuccessDelete = LANG_MSG_ARTICLE_SUCCESSFULLY_DELETE;
                            $itemsList = $articles;
                            $moduleDefaultURL = $domainURL . ALIAS_ARTICLE_MODULE;
                            $summaryfield = "abstract";
                            $titleField = "title";
                            break;

        case "promotion":   $msgSucessUpdate = LANG_MSG_PROMOTION_SUCCESSFULLY_UPDATE;
                            $msgSuccessDelete = LANG_MSG_PROMOTION_SUCCESSFULLY_DELETE;
                            $itemsList = $promotions;
                            $moduleDefaultURL =  $domainURL . ALIAS_PROMOTION_MODULE;
                            $summaryfield = "description";
                            $titleField = "name";
                            break;

        case "blog":        $msgSucessUpdate = LANG_MSG_POST_SUCCESSFULLY_UPDATED;
                            $msgSuccessDelete = LANG_SITEMGR_POST_WASSUCCESSDELETED;
                            $itemsList = $posts;
                            $moduleDefaultURL =  $domainURL . ALIAS_BLOG_MODULE;
                            $titleField = "title";
                            break;
        case "review":
                            $msgSucessUpdate = LANG_SITEMGR_REVIEW_SUCCESSAPROVED;
                            $msgSuccessDelete = LANG_SITEMGR_REVIEW_SUCCESSDELETED;
                            $itemsList = $reviewsArr;
                            $titleField = "review_title";
                            break;
    }

    /* ModStores Hooks */
    HookFire( "listmodule_after_load_modules", [
        "manageModule"     => &$manageModule,
        "msgSucessUpdate"  => &$msgSucessUpdate,
        "msgSuccessDelete" => &$msgSuccessDelete,
        "moduleDefaultURL" => &$moduleDefaultURL,
        "summaryfield"     => &$summaryfield,
        "titleField"       => &$titleField,
        "itemsList"        => &$itemsList,
    ]);

    if (is_object($level)) {
        $levelvalues = $level->getLevelValues();
    }
    $itemCount = count($itemsList);

    //Success and Error Messages
    if (is_numeric($message) && isset(${"msg_".$manageModule}[$message])) {
        echo "<p class=\"alert alert-success\">".${"msg_".$manageModule}[$message]."</p>";
    }
    if (is_numeric($error_message)) {
        echo "<p class=\"alert alert-warning\">".$msg_bulkupdate[$error_message]."</p>";
    } elseif ($error_msg) {
        echo "<p class=\"alert alert-warning\">".$error_msg."</p>";
    } elseif ($msg == "success") {
        echo "<p class=\"alert alert-success\">".$msgSucessUpdate."</p>";
    } elseif ($msg == "successdel") {
        echo "<p class=\"alert alert-success\">".$msgSuccessDelete."</p>";
    }
    unset($msg);

    /* ModStores Hooks */
    HookFire( "listmodule_before_render_section", [
        "manageModule" => &$manageModule
    ]);
?>

    <section>

        <form name="item_list" role="form">

            <ul class="list-content-item list">

                <?
                $cont = 0;
                $dbMain = db_getDBObject(DEFAULT_DB, true);
                $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

                if (is_object($level)) {
                    $levelValues = $level->getLevelValues();
                    if ($level->getDefaultLevel()) {
                        $levelDefault = $level->getLevel($level->getDefaultLevel());
                    }
                    $activeLevels = array();
                    if (is_array($levelValues)) foreach ($levelValues as $levelValue) {
                        if ($level->getActive($levelValue) == 'y') {
                            $activeLevels[] = $levelValue;
                        }
                    }
                }
                $status = new ItemStatus();
                $previewModule = array();

                if ($itemsList) foreach ($itemsList as $itemList) {

                    $cont++;

                    /* ModStores Hooks */
                    HookFire( "listmodule_before_load_itemdata", [
                        "manageModule"  => &$manageModule,
                        "previewModule" => &$previewModule,
                        "itemList"      => &$itemList,
                        "cont"          => &$cont,
                    ]);

                    if ($manageModule == "listing") {
                        $itemList = new $moduleObj($itemList->getNumber("id"));
                    }

                    $id = $itemList->getNumber("id");

                    $itemStatus = $itemList->getString("status");
                    if ($manageModule == "review"){
                        if($itemList->getNumber('approved') && (empty($itemList->getString('response')) || $itemList->getNumber('responseapproved'))) {
                            $itemStatus = 'A';
                        } else {
                            $itemStatus = 'P';
                        }
                    }

                    $moduleHasDetail = "y";
                    if (is_object($level) && !in_array($manageModule, ["article", "blog", "banner", "review" ])) {
                        $moduleHasDetail = $level->getDetail($itemList->getNumber("level"));
                    }
                    if ($manageModule == "review") {
                        $previewModule[$cont]["item_id"] = $itemList->getNumber("item_id");
                        $previewModule[$cont]["item_type"] = $itemList->getNumber("item_type");
                        $previewModule[$cont]["approved"] = $itemList->getNumber("approved");
                        $previewModule[$cont]["responseapproved"] = $itemList->getNumber("responseapproved");
                        $previewModule[$cont]["reviewer_name"] = $itemList->getString("reviewer_name", true, 0, "...", false);
                        $previewModule[$cont]["reviewer_email"] = $itemList->getString("reviewer_email", true, 0, "...", false);
                        $previewModule[$cont]["reviewer_location"] = $itemList->getString("reviewer_location", true, 0, "...", false);
                        $previewModule[$cont]["review_title"] = $itemList->getString("review_title", true, 0, "...", false);
                        $previewModule[$cont]["review"] = $itemList->getString("review", true, 0, "...", false);
                        $previewModule[$cont]["response"] = $itemList->getString("response", true, 0, "...", false);
                        //unset($itemObj);
                        switch ($itemList->getString("item_type")){
                            case "listing":
                                $itemObj = new Listing($itemList->getNumber("item_id"));
                                break;
                            case "article":
                                $itemObj = new Article($itemList->getNumber("item_id"));
                                break;
                        }
                    }

                    $allowPaymentInfo = true;

                    /* ModStores Hooks */
                    HookFire( "listmodule_before_load_paymentinfo", [
                        "manageModule"     => &$manageModule,
                        "allowPaymentInfo" => &$allowPaymentInfo,
                    ]);

                    if (!in_array($manageModule, ["promotion", "blog", "review"]) && $allowPaymentInfo) {
                        $sql = "SELECT payment_log_id FROM Payment_".ucfirst($manageModule)."_Log WHERE {$manageModule}_id = $id ORDER BY renewal_date DESC LIMIT 1";
                        $r = $db->query($sql);
                        $aux_transaction_data = mysqli_fetch_assoc($r);

                        if ($aux_transaction_data) {
                            $sql = "SELECT id, transaction_datetime, transaction_id FROM Payment_Log WHERE id = {$aux_transaction_data["payment_log_id"]} AND hidden = 'n'";
                            $r = $db->query($sql);
                            $transaction_data = mysqli_fetch_assoc($r);
                        } else {
                            unset($transaction_data);
                        }

                        // ---------------- //

                        $sql = "SELECT IL.invoice_id, IL.{$manageModule}_id, I.id, I.status, I.payment_date FROM Invoice I, Invoice_".ucfirst($manageModule)." IL WHERE IL.{$manageModule}_id = $id AND I.status = 'R' AND I.id = IL.invoice_id ORDER BY I.payment_date DESC LIMIT 1";
                        $r = $db->query($sql);
                        $invoice_data = mysqli_fetch_assoc($r);

                        // ---------------- //

                        list($t_month,$t_day,$t_year)     = explode("/",format_date($transaction_data["transaction_datetime"],DEFAULT_DATE_FORMAT,"datetime"));
                        list($i_month,$i_day,$i_year)     = explode("/",format_date($invoice_data["payment_date"],DEFAULT_DATE_FORMAT,"datetime"));
                        list($t_hour,$t_minute,$t_second) = explode(":",format_date($transaction_data["transaction_datetime"],"H:i:s","datetime"));
                        list($i_hour,$i_minute,$i_second) = explode(":",format_date($invoice_data["payment_date"],"H:i:s","datetime"));

                        $t_ts_date = mktime((int)$t_hour, (int)$t_minute, (int)$t_second, (int)$t_month, (int)$t_day, (int)$t_year);
                        $i_ts_date = mktime((int)$i_hour, (int)$i_minute, (int)$i_second, (int)$i_month, (int)$i_day, (int)$i_year);

                        if (PAYMENT_FEATURE == "on") {
                            if (((PAYMENT_MANUAL_STATUS == "on") || (CREDITCARDPAYMENT_FEATURE == "on")) && (PAYMENT_INVOICE_STATUS == "on")) {
                                if ($t_ts_date < $i_ts_date) {
                                    if ($invoice_data["id"]) $history_link = DEFAULT_URL."/".SITEMGR_ALIAS."/activity/invoices/index.php?search_id=".$invoice_data["id"];
                                    else unset($history_link);
                                } else {
                                    if ($transaction_data["id"]) $history_link = DEFAULT_URL."/".SITEMGR_ALIAS."/activity/transactions/index.php?search_id=".$transaction_data["transaction_id"];
                                    else unset($history_link);
                                }
                            } elseif ((PAYMENT_MANUAL_STATUS == "on") || (CREDITCARDPAYMENT_FEATURE == "on")) {
                                if ($transaction_data["id"]) $history_link = DEFAULT_URL."/".SITEMGR_ALIAS."/activity/transactions/index.php?search_id=".$transaction_data["transaction_id"];
                                else unset($history_link);
                            } elseif (PAYMENT_INVOICE_STATUS == "on") {
                                if ($invoice_data["id"]) $history_link = DEFAULT_URL."/".SITEMGR_ALIAS."/activity/invoices/index.php?search_id=".$invoice_data["id"];
                                else unset($history_link);
                            } else {
                                unset($history_link);
                            }
                        } else {
                            unset($history_link);
                        }

                    }

                    unset($account);

                    unset($array_fields);
                    if (in_array($manageModule, ["listing", "event", "classified"])) {
                        $array_fields = system_getFormFields(ucfirst($manageModule), $itemList->getNumber("level"));
                    }

                    $allowRenewalDate = true;

                    /* ModStores Hooks */
                    HookFire( "listmodule_before_load_renewaldate", [
                        "manageModule"     => &$manageModule,
                        "allowRenewalDate" => &$allowRenewalDate,
                    ]);

                    if (!in_array($manageModule, ["promotion", "blog", "review"]) && $allowRenewalDate) {
                        if ($itemList->hasRenewalDate()) {
                            $renewal_date = format_date($itemList->getString("renewal_date"));
                            if ($renewal_date) {
                                $renewaldate_field = $renewal_date;
                            } else {
                                $renewaldate_field = "";
                            }
                        } else {
                            $renewaldate_field = "";
                        }
                    } else {
                        $renewaldate_field = "";
                    }

                    //Prepare info to preview
                    $previewModule[$cont]["id"] = $itemList->getNumber("id");
                    $previewModule[$cont]["title"] = $itemList->getString($titleField);
                    $previewModule[$cont]["account_id"] = $itemList->getNumber("account_id");
                    if ($manageModule == "review"){
                        $previewModule[$cont]["account_id"] = $itemList->getNumber("member_id");
                    }

                    //Account info
                    if ($previewModule[$cont]["account_id"] && $manageModule != "blog") {
                        $account = db_getFromDB("account", "id", db_formatNumber($previewModule[$cont]["account_id"]));
                        $previewModule[$cont]["account"] = system_showAccountUserName($account->getString("username"));
                    } else {
                        $previewModule[$cont]["account"] = "";
                    }

                    //Summary description
                    if (is_array($array_fields) && in_array("summary_description", $array_fields)) {
                        $previewModule[$cont]["summary"] = $itemList->getNumber($summaryfield);
                    } elseif ($manageModule == "promotion" || $manageModule == "article") {
                        $previewModule[$cont]["summary"] = $itemList->getNumber($summaryfield);
                    }

                    //Phone
                    if (is_array($array_fields) && in_array("phone", $array_fields)) {
                        $previewModule[$cont]["phone"] = $itemList->getNumber("phone");
                    }

                    //Address
                    if ($manageModule == "listing" || $manageModule == "classified" || $manageModule == "event") {
                        $previewModule[$cont]["address"] = system_getItemAddressString(ucfirst($manageModule), $itemList->getNumber("id"));
                    }

                    //Preview
                    if ($manageModule != "banner") {
                        $previewModule[$cont]["preview_url"] = $itemList->getFriendlyURL($moduleDefaultURL, "friendly_url");
                    }

                    if ($manageModule === 'promotion') {
                        $previewModule[$cont]["listing_id"] = $itemList->getNumber("listing_id");
                    }

                    //Image
                    if ($itemList->getNumber("image_id")) {
                        $imageObj = new Image($itemList->getNumber("image_id"));
                        if ($imageObj->imageExists()) {
                            $previewModule[$cont]["image"] = $imageObj->getPath();
                        } else {
                            $previewModule[$cont]["image"] = "";
                        }
                    }

                    //Reviews
                    if ($review_enabled == "on" && $commenting_edir) {
                        if ($manageModule == "listing" && $levelsWithReview && in_array($itemList->getNumber("level"), $levelsWithReview)) {
                            $previewModule[$cont]["reviews"] = true;
                            $previewModule[$cont]["avg_review"] = $itemList->getNumber("avg_review");
                        }
                    }

                    //Leads
                    if (is_array($array_fields) && in_array("email", $array_fields)) {
                        $previewModule[$cont]["leads"] = true;
                    }

                    //Start/End date (Event)
                    if ($manageModule == "event") {
                        $previewModule[$cont]["date"] = $itemList->getDateString(true);
                    }

                    //Created date
                    $createdField = ($manageModule == "review" ? "added" : "entered");
                    $previewModule[$cont]["created"] = ($itemList->getString($createdField)) ? format_date($itemList->getString($createdField), DEFAULT_DATE_FORMAT, "datetime")." - ".format_getTimeString($itemList->getNumber($createdField)) : system_showText(LANG_NA);

                    //Last updated date
                    $previewModule[$cont]["updated"] = format_date($itemList->getNumber("updated"),DEFAULT_DATE_FORMAT, "datetime")." - ".format_getTimeString($itemList->getNumber("updated"));

                    $previewModule[$cont]["transation"] = $history_link;

                    /* ModStores Hooks */
                    HookFire( "listmodule_after_load_itemdata", [
                        "manageModule"  => &$manageModule,
                        "previewModule" => &$previewModule,
                        "itemList"      => &$itemList,
                        "cont"          => &$cont,
                    ]);

                    ?>

                <? // --------------- HTML code ------------------- //?>

                <li class="content-item" data-id="<?=$itemList->getNumber("id")?>">

                    <!-- STATUS -->
                    <? if ($manageModule != "promotion") { ?>
                        <div class="status text-hide"><?=$status->getStatusWithStyle($itemStatus);?></div>
                    <? } ?>

                    <div class="check-bulk">
                        <input type="checkbox" id="<?=$manageModule?>_id<?=$cont?>" name="item_check[]" value="<?=$id?>" onclick="bulkSelect('<?=$manageModule?>');"/>
                    </div>

                    <div class="item">
                        <? if ($manageModule == "blog") { ?>
                        <p>&nbsp;</p>
                        <? } ?>

                        <h3 class="item-title"><?=$itemList->getString($titleField);?></h3>

                        <? if ($manageModule != "blog") { ?>
                        <p>
                            <span class="item-author">
                                <? if ($manageModule == "review") { ?>
                                    <?= $itemObj->getString("title", true);?>
                                <? } else if (is_numeric($itemList->getNumber("account_id")) && isset($account)) { ?>
                                    <?=system_showText(LANG_LABEL_ACCOUNT);?>: <?=system_showAccountUserName($account->getString("username"));?>
                                <? } else { ?>
                                    <?=system_showText(LANG_SITEMGR_ACCOUNTSEARCH_NOOWNER);?>
                                <? } ?>
                            </span>


                            <span class="pull-right">
                                <? if ($manageModule == "review") { ?>
                                    <span class="<?=$status->getStyle($itemStatus)?>">
                                        <?= system_showText($itemStatus == "A"? LANG_LABEL_APPROVED : LANG_LABEL_PENDING) ?>
                                    </span>
                                <? } else if ($manageModule != "promotion") { ?>
                                    <?= $status->getStatusWithStyle($itemStatus);?>
                                <? } ?>
                            </span>
                        </p>
                        <? } ?>
                        <? if ($manageModule != "promotion") { ?>
                        <p>
                            <? if (!in_array($manageModule, ["article", "banner", "review"])) { ?>
                                <span class="pull-left">
                                    <?=(is_array($activeLevels) && in_array($itemList->getNumber("level"), $activeLevels) && is_object($level)) ? $level->showLevel($itemList->getNumber("level")) : string_ucwords($levelDefault)?>
                                </span>
                            <? } elseif ($manageModule == "banner") {
                                echo "<span class=\"pull-left\">";
                                echo $itemList->retrieveHumanReadableType($itemList->GetString("type"));
                                if ($levelStatus[$itemList->GetString("type")] == "n") {
                                    echo " (".LANG_BANNER_DISABLED.")";
                                }
                                echo "</span>";
                            } ?>
                            <? if ($manageModule == "review") { ?>
                                <span class="pull-left">
                                    <?=($itemList->getString("added")) ? format_date($itemList->getString("added"), DEFAULT_DATE_FORMAT, "datetime")." - ".format_getTimeString($itemList->getNumber("added")) : system_showText(LANG_NA);?>
                                </span>
                            <? } ?>

                            <? if ($renewaldate_field) { ?>
                            <span class="pull-right">
                                <?=system_showText(LANG_LABEL_RENEWAL);?>: <?=$renewaldate_field?>
                            </span>
                            <? } ?>
                        </p>
                        <? } ?>
                    </div>
                </li>
            <? } ?>

            </ul>

        </form>

    </section>
