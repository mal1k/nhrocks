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
    # * FILE: /profile/index.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include("../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # MAINTENANCE MODE
    # ----------------------------------------------------------------------------------------------------
    verify_maintenanceMode();

    //Remove item from favorites
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "remove") {
        extract($_POST, null);
        if (is_numeric($account_id) && $account_id != 0 && is_numeric($item_id) && $item_id != 0 && $item_type) {
            $quicklistObj = new Quicklist("", $account_id, $item_id, $item_type);
            $quicklistObj->Delete();
            exit;
        }
    }

    # ----------------------------------------------------------------------------------------------------
    # VALIDATION
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/includes/code/validate_querystring.php");

    if (SOCIALNETWORK_FEATURE == "off") { exit; }

    if (isset($_GET["oauth_token"])) {
        header("Location: ".DEFAULT_URL."/".SOCIALNETWORK_FEATURE_NAME."/edit.php?oauth_token=".$_GET["oauth_token"]);
        exit;
    }
    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSessionFront();

    # ----------------------------------------------------------------------------------------------------
    # MODE REWRITE
    # ----------------------------------------------------------------------------------------------------
    setting_get("review_listing_enabled", $review_enabled);
    include(EDIRECTORY_ROOT."/".SOCIALNETWORK_FEATURE_NAME."/mod_rewrite.php");

    # ----------------------------------------------------------------------------------------------------
    # BODY
    # ----------------------------------------------------------------------------------------------------
    $info = socialnetwork_retrieveInfoProfile($id);

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    $headertag_title = ($id ? $info["nickname"] : '');
    $headertag_description = ($id ? str_replace("\n", "", $info["personal_message"]) : '');
    $headertag_keywords = '';
    include(EDIRECTORY_ROOT."/frontend/header.php");

    //Prepare User information
    extract($_GET, null);

    $accObj = new Account($id);
    $publish = $accObj->getString("publish_contact");
    $profileObj = new Profile(sess_getAccountIdFromSession());
    $profileObj->extract();

    //Facebook integration
    $redirectURI_params = [
        "destiny" => "attach_account",
        "edir_account" => sess_getAccountIdFromSession()
    ];

    if (isset($_GET["signoffFacebook"])){
        $facebookMessage = system_showText(LANG_LABEL_FB_ACT_DISC).".";

        $accountObj = new Account(sess_getAccountIdFromSession());
        $accountObj->setString("facebook_username", "");
        $accountObj->setString("foreignaccount", "n");
        $accountObj->Save();

        $profileObj = new Profile(sess_getAccountIdFromSession());
        $profileObj->setString("facebook_uid", "");
        $profileObj->Save();
    }

    //Recent activity
    $dbMain = db_getDBObject(DEFAULT_DB, true);
    $dbDomain = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
    $userActivity = array();

    //Get Deals Redeemed
    $sql = "SELECT Promotion_Redeem.id, datetime, redeem_code, promotion_id, amount FROM Promotion_Redeem, Promotion WHERE Promotion.id = Promotion_Redeem.promotion_id AND Promotion_Redeem.account_id  = ".db_formatNumber($id);
    $result = $dbDomain->query($sql);
    while ($row = mysqli_fetch_assoc($result)) {

        $promotionObj = new Promotion($row["promotion_id"]);

        if ($promotionObj->getNumber("id")) {

            $userActivity["deal_".$row["id"]]["id"] = $row["id"];
            $userActivity["deal_".$row["id"]]["added"] = $row["datetime"];
            $userActivity["deal_".$row["id"]]["redeem_code"] = $row["redeem_code"];
            $userActivity["deal_".$row["id"]]["used"] = $row["used"];
            $userActivity["deal_".$row["id"]]["promotion_id"] = $row["promotion_id"];
            $userActivity["deal_".$row["id"]]["title"] = $promotionObj->getString("name");
            $userActivity["deal_".$row["id"]]["amount"] = $row["amount"];

            if ($promotionObj->getNumber("listing_id") && $promotionObj->getString("listing_status") == "A" && (validate_date_deal($promotionObj->getDate("start_date"), $promotionObj->getDate("end_date"))) && (validate_period_deal($promotionObj->getNumber("visibility_start"), $promotionObj->getNumber("visibility_end")))) {
                $userActivity["deal_".$row["id"]]["title_url"] = "<a href=\"".$promotionObj->getFriendlyURL(PROMOTION_DEFAULT_URL)."\" class='heading h-4'>".$promotionObj->getString("name")."</a>";
            } else {
                $userActivity["deal_".$row["id"]]["title_url"] = "<h4 class='heading h-4'>" . $promotionObj->getString("name") . "</h4>";
            }

        }

    }

    //Get Reviews
    $sql = "SELECT id, item_type, item_id, review, review_title, rating, response, responseapproved, added FROM Review WHERE member_id = ".db_formatNumber($id)." AND approved = 1";
    $result = $dbDomain->query($sql);
    $levelObj = new ListingLevel(true);
    while ($row = mysqli_fetch_assoc($result)) {

        switch ($row["item_type"]) {
            case "listing":
                $itemObj = new Listing($row["item_id"]);
                $friendlyURL = LISTING_DEFAULT_URL;
                if ($itemObj->getString("status") == "A") {
                    $itemAvailable = true;
                    if ($levelObj->getDetail($itemObj->getNumber("level")) == "y") {
                        $hasDetail = true;
                    } else {
                        $hasDetail = false;
                    }
                } else {
                    $itemAvailable = false;
                }
                break;

            case "article":
                $itemObj = new Article($row["item_id"]);
                $friendlyURL = ARTICLE_DEFAULT_URL;
                if ($itemObj->getString("status") == "A") {
                    $itemAvailable = true;
                } else {
                    $itemAvailable = false;
                }
                $hasDetail = true;
                break;

            case "promotion":
                $itemObj = new Promotion($row["item_id"]);
                $friendlyURL = Promotion_DEFAULT_URL;
                if ($itemObj->getNumber("listing_id") && $itemObj->getString("listing_status") == "A" && (validate_date_deal($itemObj->getDate("start_date"), $itemObj->getDate("end_date"))) && (validate_period_deal($itemObj->getNumber("visibility_start"), $itemObj->getNumber("visibility_end")))) {
                    $itemAvailable = true;
                } else {
                    $itemAvailable = false;
                }
                $hasDetail = true;
                break;
        }

        if ($itemObj->getNumber("id") && $itemAvailable) {
            $userActivity["review_".$row["id"]]["id"] = $row["id"];
            $userActivity["review_".$row["id"]]["item_type"] = $row["item_type"];
            $userActivity["review_".$row["id"]]["item_id"] = $row["item_id"];
            $userActivity["review_".$row["id"]]["review"] = $row["review"];
            $userActivity["review_".$row["id"]]["review_title"] = $row["review_title"];
            $userActivity["review_".$row["id"]]["rating"] = $row["rating"];
            $userActivity["review_".$row["id"]]["response"] = $row["response"];
            $userActivity["review_".$row["id"]]["responseapproved"] = $row["responseapproved"];
            $userActivity["review_".$row["id"]]["added"] = $row["added"];
            $userActivity["review_".$row["id"]]["title"] = $itemObj->getString(($row["item_type"] == "promotion" ? "name" : "title"));
            $userActivity["review_".$row["id"]]["title_url"] = "<a href=\"".$itemObj->getFriendlyURL($friendlyURL)."\" class='heading h-4'>".$itemObj->getString(($row["item_type"] == "promotion" ? "name" : "title"))."</a>";
        }
    }

    //Get Blog Comments
    $sql = "SELECT id, post_id, description, added FROM Comments WHERE member_id = $id AND approved = 1";
    $result = $dbDomain->query($sql);
    while ($row = mysqli_fetch_assoc($result)) {

        $postObj = new Post($row["post_id"]);

        if ($postObj->getNumber("id") && $postObj->getString("status") == "A") {
            $userActivity["comment_".$row["id"]]["id"] = $row["id"];
            $userActivity["comment_".$row["id"]]["description"] = $row["description"];
            $userActivity["comment_".$row["id"]]["added"] = $row["added"];
            $userActivity["comment_".$row["id"]]["title"] = $postObj->getString("title");
            $userActivity["comment_".$row["id"]]["title_url"] = "<a href=\"".$postObj->getFriendlyURL(BLOG_DEFAULT_URL)."\" class='heading h-4'>".$postObj->getString("title")."</a>";
        }

    }

    //Order by date
    $ord = array();
    foreach ($userActivity as $key => $value){
        $ord[] = strtotime($value["added"]);
    }

    $container = SymfonyCore::getContainer();
    $hasDeal = false;

    array_multisort($ord, SORT_DESC, $userActivity);
    $cover_title = system_showText(LANG_LABEL_PROFILE);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>

    <div class="members-page profile-page">
        <div class="container">
            <div class="members-wrapper">
                <div class="profile-sidebar">
                    <div class="members-panel edit-panel">
                        <div class="panel-header header-spaced">
                            <?=system_showText(LANG_LABEL_ABOUT_ME);?>
                            <? if ($id === sess_getAccountIdFromSession()) { ?>
                                <a href="<?=SOCIALNETWORK_URL;?>/edit.php" class="button button-sm is-secondary"><?=LANG_LABEL_EDITPROFILE;?></a>
                            <? } ?>
                        </div>
                        <div class="panel-body">
                            <div class="profile-user-info">
                                <?php
                                if (!$info["facebook_image"]) {
                                    $imgObj = new Image($info["image_id"], true);
                                    if ($imgObj->imageExists()) {
                                        echo $imgObj->getTag(true, PROFILE_MEMBERS_IMAGE_WIDTH, PROFILE_MEMBERS_IMAGE_HEIGHT, "", false, htmlspecialchars($info["nickname"]), "user-picture");
                                    } else { ?>
                                        <img class="user-picture" width="100" height="100" src="<?=DEFAULT_URL?>/assets/images/user-image.png" alt="<?=htmlspecialchars($info["nickname"]);?>">
                                    <?php }

                                } else {

                                    if (HTTPS_MODE == "on") {
                                        $info["facebook_image"] = str_replace("http://", "https://", $info["facebook_image"]);
                                    } ?>

                                    <img class="user-picture" width="100" height="100" src="<?=$info["facebook_image"]?>" alt="<?=htmlspecialchars($info["nickname"]);?>">
                                <?php } ?>

                                <div class="heading h-4 text-center user-name"><?=htmlspecialchars($info["nickname"]);?></div>

                                <? if ($info["entered"]) { ?>
                                    <div class="paragraph p-3 text-center user-since"><strong><?=system_showText(LANG_LABEL_MEMBER_SINCE);?></strong> <?=format_date($info["entered"])?></div>
                                <? } ?>

                                <? if ($info["country"] || $info["state"] || $info["city"]) {
                                    $arrayLocUser = [];
                                    if ($info["country"]) { $arrayLocUser[] = $info["city"];}
                                    if ($info["state"]) {  $arrayLocUser[] = $info["state"]; }
                                    if ($info["city"]) {  $arrayLocUser[] = $info["country"]; }
                                ?>
                                    <div class="paragraph p-3 text-center user-from"><strong><?=ucfirst(system_showtext(LANG_FROM))?></strong> <?=(implode(", ", $arrayLocUser))?></div>
                                <? } ?>
                            </div>
                            <div class="profile-user-description">
                                <?php if ($publish == "y") { ?>

                                    <?php if ($info["company"]) { ?>
                                        <div class="paragraph p-3 text-center user-company"><strong><?=ucfirst(system_showText(LANG_LABEL_COMPANY))?>:</strong> <?=nl2br(htmlspecialchars($info["company"]))?></div>
                                    <?php } ?>

                                    <?php if ($info["address"]) { ?>
                                        <div class="paragraph p-3 text-center user-address"><?=nl2br(htmlspecialchars($info["address"]))?></div>
                                    <?php } ?>

                                    <?php if ($info["address2"]) { ?>
                                        <div class="paragraph p-3 text-center user-address"><?=nl2br(htmlspecialchars($info["address2"]))?></div>
                                    <?php } ?>

                                    <?php if ($info["phone"]) { ?>
                                        <div class="paragraph p-3 text-center user-phone"><strong><?=system_showText(LANG_LABEL_PHONE)?>:</strong> <?=$info["phone"];?></div>
                                    <?php } ?>

                                <?php } ?>

                                <?php if ($info["url"]) { ?>
                                    <a href="<?=nl2br(htmlspecialchars($info["url"]))?>" class="link text-center" title="<?=system_showText(LANG_LABEL_URL)." ".system_showText(LANG_PAGING_PAGEOF)." ".$info["nickname"]?>" target="_blank"><?=nl2br(htmlspecialchars($info["url"]));?></a>
                                <? } ?>
                                
                                <?php if ($info["personal_message"]) { ?>
                                    <div class="paragraph p-3 text-center user-about"><?=nl2br(htmlspecialchars($info["personal_message"]))?></div>
                                <? } ?>
                                
                                <div class="facebook-link text-center">
                                <?php
                                    if ($id == sess_getAccountIdFromSession() && ((FACEBOOK_APP_ENABLED == "on" && $accObj->getString("username") != $accObj->getString("facebook_username")))) {

                                        if ($_GET["error"] == "disableAttach") {
                                            echo '<div class="form-edit-alert">'.system_showText(LANG_FB_ALREADY_LINKED).'</div>';
                                        }

                                        if (isset($_GET["facebookerror"])) {
                                            echo '<div class="form-edit-alert">'.system_showText(LANG_MSG_ERROR_NUMBER)." 10001. ".system_showText(LANG_MSG_TRY_AGAIN).'</div>';
                                        }

                                        if ($accObj->getString("username") != $accObj->getString("facebook_username") && FACEBOOK_APP_ENABLED == "on") {

                                            //Account already associated
                                            if ($profileObj && $profileObj->facebook_uid != "") {

                                            //Unlink account
                                            if (isset($_GET["facebookattached"])) {
                                                echo '<div class="form-edit-alert">'.system_showText(LANG_LABEL_FB_SIGNFB_CONN).'</div>';
                                            } ?>

                                            <a class="button button-sm is-primary" href="<?=DEFAULT_URL?>/<?=SOCIALNETWORK_FEATURE_NAME?>/index.php?signoffFacebook"><?=system_showText(LANG_LABEL_UNLINK_FB);?></a>
                                        
                                        <?php } else {
                                            //Account not associated
                                            $linkAttachFB = true;

                                            //Link Account
                                            if ($facebookMessage) {
                                                echo '<div class="form-edit-alert-success">'.$facebookMessage.'</div>';
                                            }

                                            include(INCLUDES_DIR."/forms/form_facebooklogin.php");

                                        }
                                    }

                                }
                                ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="profile-content">
                    <div class="members-panel edit-panel">
                        <div class="panel-header">
                            <?=system_showText(LANG_LABEL_PROFILE_RECENT_ACTIVITY)?>
                        </div>
                        <div class="panel-body">
                            <?php if ($id == sess_getAccountIdFromSession()) { ?>
                                <h3 class="heading h-3 text-center"><?=system_showText(LANG_LABEL_WELCOME);?>, <?=htmlspecialchars($info["nickname"]);?>!</h3>
                                <div class="paragraph p-2 text-center"><?=system_showText(LANG_LABEL_PROFILE_TIP1);?></div>
                            <? } ?>

                            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                            <?php
                                if (count($userActivity)) {
                                    foreach ($userActivity as $key => $activity) { ?>
                                <br>
                                <div class="members-panel">
                                    <div class="panel-header" role="tab" id="<?=$key?>">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse-<?=$key?>" aria-expanded="true" aria-controls="collapse-<?=$key?>">
                                        <? if (string_strpos($key, "deal") !== false) { ?>
                                            <?=system_showText(LANG_LABEL_REDEEMED);?> <b><?=$activity["title"]?></b>
                                        <? } elseif (string_strpos($key, "review") !== false) { ?>
                                            <?=system_showText(LANG_LABEL_RATED);?> <b><?=$activity["title"]?></b> <?=system_showText(LANG_WITH);?> <span class="stars-rating"><span class="rate-<?=$activity["rating"]?>"></span></span>
                                        <? } elseif (string_strpos($key, "comment") !== false) { ?>
                                            <?=system_showText(LANG_LABEL_COMMENTED);?> <b><?=$activity["title"]?></b>
                                        <? } ?>
                                        </a>
                                    </div>
                                    <div id="collapse-<?=$key?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?=$key?>" aria-expanded="false">
                                        <div class="panel-body redem-body">
                                            <?php if (string_strpos($key, "deal") !== false) { ?>
                                                <div class="redem-title">
                                                    <?=$activity["title_url"]?>
                                                    <?php if ($id == sess_getAccountIdFromSession() && !$activity["used"]) { ?>
                                                        <a class="button button-sm is-secondary pull-right" href="#" data-code="<?=$activity['redeem_code'];?>" data-name="<?=htmlspecialchars($info['nickname']);?>" data-id="<?=$activity['promotion_id']?>" data-modal="deal" data-ajax="true"><?=system_showText(LANG_LABEL_PRINT);?></a>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>

                                            <div class="redem-date">
                                                <?=system_showText(LANG_BLOG_ON)?>
                                                <strong><?=format_date($activity["added"], DEFAULT_DATE_FORMAT, "datestring");?></strong>
                                            </div>

                                            <?php if (string_strpos($key, "deal") !== false) { ?>
                                                <?php if ($id == sess_getAccountIdFromSession()) { ?>
                                                    <div class="redem-code"><?=system_showText(LANG_LABEL_DEAL_CODE);?> <strong><?=$activity["redeem_code"];?></strong></div>
                                                <?php } ?>

                                                <?php $hasDeal = true; ?>

                                            <?php } elseif (string_strpos($key, "review") !== false) { ?>
                                                <div class="redem-title" style="margin-top: 8px">
                                                    <?=$activity["title_url"]?>
                                                    <div class="paragraph p-1"><b><?=$activity["review_title"]?></b></div>
                                                </div>
                                                
                                                <div class="redem-review"><?=$activity["review"]?></div>

                                                <?php if ($activity["responseapproved"]) { ?>
                                                    <div class="redem-response"><?=$activity["response"]?></div>
                                                <?php } ?>


                                            <?php } elseif (string_strpos($key, "comment") !== false) { ?>
                                                <div class="redem-date">
                                                    <?=system_showText(LANG_BLOG_ON)?>
                                                    <strong><?=format_date($activity["added"], DEFAULT_DATE_FORMAT, "datestring");?></strong>
                                                </div>

                                                <div class="redem-title">
                                                    <?=$activity["title_url"]?>
                                                </div>

                                                <div class="redem-review"><?=$activity["description"]?></div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php
                        if (!$_GET["id"]) {
                            $id = sess_getAccountIdFromSession();
                        } else {
                            $id = $_GET["id"];
                        }
                        $favoritesItems = system_getUserActivities("favorites", $id);

                        if (is_array($favoritesItems) && count($favoritesItems)) {
                            setting_get("review_listing_enabled", $review_enabled);
                            $levelsWithReview = system_retrieveLevelsWithInfoEnabled("has_review");
                    ?>
                    <br>
                    <div class="members-panel">
                        <div class="panel-header">
                            <?=system_showText(LANG_LABEL_FAVORITES);?>
                        </div>
                        <div class="panel-body">
                            <?php foreach ($favoritesItems as $module => $favorites) {
                                if (is_array($favorites)) {
                                    foreach ($favorites as $favorite) {
                                        include(INCLUDES_DIR."/views/view_favorite.php");
                                    }
                                }
                            } ?>
                        </div>
                    </div>
                    <?php } ?>
                    <br>
                    <?php
                        /* ModStores Hooks */
                        HookFire( 'profilehomepage_after_render', [
                            'id' => $id
                        ]);
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="details-default">
        <?php if ($hasDeal) {
            echo $container->get('twig')->render('@Deal/modal-redeem.html.twig');
        } ?>
    </div>
<?
    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/frontend/footer.php");
