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

$target_dir = EDIRECTORY_ROOT . "/../image_uploads/";
$username = $accObj->username;
$imageFileTypes = ['jpg', 'png', 'pdf'];
$imageUploaded = false;
foreach ($imageFileTypes as $imageFileType) {
    $target_file = $target_dir . $username . '.' . $imageFileType;
    if (file_exists($target_file)) {
        $imageUploaded = true;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_FILES["fileToUpload"])) {
    if ($_FILES["fileToUpload"]['error'] !== 0) {
        $validate_contact = false;
        $message_account .= "&#149;&nbsp;" . "File upload too large</br>";
    } else {
        $imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));

        if ($imageFileType === 'jpeg') {
            $imageFileType = 'jpg';
        }

        if (in_array($imageFileType, ['jpg', 'png', 'pdf'])) {
            // Check if image file is a actual image or fake image
            $tmp_name = $_FILES["fileToUpload"]["tmp_name"];
            $check = getimagesize($tmp_name);
            $isPdf = mime_content_type($tmp_name) === 'application/pdf';
            if ($check !== false || $isPdf) {
                $uploadOk = 1;

                if (!file_exists($target_dir)) {
                    if (!mkdir($target_dir) && !is_dir($target_dir)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $target_dir));
                    }
                }

                $username = $accObj->username;
                $target_file = $target_dir . $username . '.' . $imageFileType;
                @unlink($target_dir . $username . '.jpg');
                @unlink($target_dir . $username . '.png');
                @unlink($target_dir . $username . '.pdf');
                move_uploaded_file($tmp_name, $target_file);
                $imageUploaded = true;
            } else {
                $message_account .= "&#149;&nbsp;" . "File must be a jpg, png or pdf</br>";
                $validate_contact = false;
            }
        } else {
            $message_account .= "&#149;&nbsp;" . "File must be a jpg, png or pdf</br>";
            $validate_contact = false;
        }
    }
}

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


    setting_get('stripe_pub_key', $stripe_pub_key);
    setting_get('locals_price_id', $locals_price_id);
    setting_get('locals_price_text', $locals_price_text);
    setting_get('locals_price_id_2', $locals_price_id_2);
    setting_get('locals_price_text_2', $locals_price_text_2);

    $localsCardHolderObj = new LocalsCardHolder($id);
    $isLocalCardHolder = $localsCardHolderObj->active === "1";
    $localCardDate = $localsCardHolderObj->getDate('entered');
    $localCurrent = false;

    if($localCardDate){
        $datetime1 = date_create($localCardDate);
        $datetime2 = date_create();

        $interval = date_diff($datetime1, $datetime2);

        $localCurrent = $interval->days < 366;
    }
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

                <!--Locals Card-->
                <!--Locals Card-->
                <!--Locals Card-->

                <div class="profile-content">

                    <div id="show-local-con"

                            style="
                    text-align: center;
                    border: 1px solid #e3ecf0;
                    border-radius: var(--border-radius,3px);
                    padding: 5px;
                    background-color: #e3ecf0;
                    "
                    >
                        <button type="button" class="button button-md is-primary" id="show_locals">Are you a resident?</button>

                    </div>

                    <div id="locals-con" class="members-panel edit-panel hide">
                        <div class="panel-header">Locals Card</div>
                        <div class="panel-body">
                            <?php if(!$isLocalCardHolder) { ?>
                                <h3 class="heading h-3 text-center">Redeem deals with locals card!</h3>
                                <div style="text-align: center;">
                                    <div style="text-align: center;display: inline-block;"><button href="#" class="button button-md is-primary" onclick="buyLocal(1)"><?=$locals_price_text ?? 'N/A'?></button></div>
                                    <div style="text-align: center;display: inline-block;"><button href="#" class="button button-md is-primary" onclick="buyLocal(2)"><?=$locals_price_text_2 ?? 'N/A'?></button></div>
                                    <div id="error-message"></div>
                                </div>

                                <?php if(!$imageUploaded && !$localCurrent) { ?>
                                    <div class="form-box" style="padding-top: 20px;">
                                        <p class="alert alert-warning hidden" id="validation">
                                        </p>
                                        <form method="post" autocomplete="off" enctype="multipart/form-data">
                                            <div style="border: 1px solid rgba(62,69,94,.25); padding: 5px; margin-top: 5px; border-radius: 3px;">
                                                <span>Required: Upload photo of Utility bill or NH drivers license</span>
                                                <input class="form-control custom-input-size" type="file" name="fileToUpload" id="fileToUpload">
                                            </div>
                                            <button style="margin-top: 10px;" type="submit" class="button button-md is-primary" value="Submit" id="standard_submit">Submit Photo</button>
                                        </form>
                                    </div>
                                <?php } ?>
                            <?php } else { ?>
                                <h3 class="heading h-3 text-center">Locals card is Active!</h3>
                            <?php } ?>
                        </div>
                    </div>

                    <!--FAVORITES-->
                    <!--FAVORITES-->
                    <!--FAVORITES-->

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

                    <!--RECENT_ACTIVITY-->
                    <!--RECENT_ACTIVITY-->
                    <!--RECENT_ACTIVITY-->

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

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // Replace with your own publishable key: https://dashboard.stripe.com/test/apikeys
        var PUBLISHABLE_KEY = '<?php echo $stripe_pub_key ?>';

        // Replace with the domain you want your users to be redirected back to after payment
        var DOMAIN = window.location.origin;

        var stripe = Stripe(PUBLISHABLE_KEY);

        var SUBSCRIPTION_BASIC_PRICE_ID = '<?php echo $locals_price_id ?>';
        var SUBSCRIPTION_ANNUAL_PRICE_ID = '<?php echo $locals_price_id_2 ?>';

        // Handle any errors from Checkout
        var handleResult = function (result) {
            if (result.error) {
                var displayError = document.getElementById("error-message");
                displayError.textContent = result.error.message;
            }
        };

        var redirectToCheckout = function (priceId) {
            // Make the call to Stripe.js to redirect to the checkout page
            // with the current quantity
            stripe
                .redirectToCheckout({
                    lineItems: [{ price: priceId, quantity: 1 }],
                    successUrl:
                        DOMAIN + "/profile/local_success.php?account_id=<?php echo $id?>&stripe_session_id={CHECKOUT_SESSION_ID}",
                    cancelUrl: DOMAIN + "/profile/",
                    mode: 'subscription',
                })
                .then(handleResult);
        };

        function buyLocal(type){
            if(type === 1){
                redirectToCheckout(SUBSCRIPTION_BASIC_PRICE_ID);
                return;
            }
            if(type === 2){
                redirectToCheckout(SUBSCRIPTION_ANNUAL_PRICE_ID);
                return;
            }
        }

    </script>

    <script>
        var errors = [];
        var validation = document.getElementById('validation');
        var photo_upload = document.getElementById('fileToUpload');
        var submit_button = document.getElementById('standard_submit');
        var show_locals_button = document.getElementById('show_locals');
        var locals_con = document.getElementById('locals-con');
        var show_local_con = document.getElementById('show-local-con');

        show_locals_button.addEventListener("click", function(event){
            locals_con.classList.remove('hide');
            show_local_con.classList.add('hide');
        });

        submit_button.addEventListener("click", function(event){
            errors = [];

            if( photo_upload.files.length === 0 ){
                errors.push("Drivers license: Required");
            }else{
                var file = photo_upload.files[0];
                if(file && file.size > (1024*1000*20)) { // 2 MB (this size is in bytes)
                    errors.push("Drivers license: Image too large");
                }
            }

            if(errors.length > 0){
                event.preventDefault();
                validation.classList.remove("hidden");

                var existing_validations = document.querySelectorAll('.validation_item');
                var existing_validations_br = document.querySelectorAll('#validation br');
                for (var x = 0; x < existing_validations.length; x++) {
                    existing_validations[x].parentNode.removeChild(existing_validations[x]);
                    existing_validations_br[x].parentNode.removeChild(existing_validations_br[x]);
                }

                for (var i = 0; i < errors.length; i++) {
                    var item = document.createElement('span');
                    item.classList.add('validation_item');
                    item.innerText = (errors[i]);
                    validation.appendChild(item);
                    validation.appendChild(document.createElement('br'));
                }
            }
        });
    </script>
<?
    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/frontend/footer.php");
