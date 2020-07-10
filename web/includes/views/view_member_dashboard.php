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
# * FILE: /includes/views/member_dashboard.php
# ----------------------------------------------------------------------------------------------------

?>
<div class="content-dashboard">
    <div class="dashboard-header">
        <h2 class="heading h-2 dashboard-title">
            <div class="dashboard-icon">
                <i class="fa <?=$icon;?>"></i>
            </div>
            <?= $item_title; ?>
            <? if ($visibilityButton) { ?>
                <a href="<?= $item_levellink; ?>" class="button button-sm is-primary"><?= system_showText(LANG_LABEL_INCREASEVISIBILITY); ?></a>
            <? } ?>
            <? if ($item_new) { ?>
                <span class="item-new"><?=system_showText(LANG_LABEL_NEW); ?></span>
            <? } ?>
        </h2>
        <div class="dashboard-info-title">
            <? if ($item_renewal) { ?>
                <div class="renew-item">
                    <?= system_showText(LANG_LABEL_EXPIRESON); ?>
                    <strong><?= $item_renewal; ?> <?= $item_renewal_period ?></strong>
                    <a class="link" href="<?= DEFAULT_URL ?>/<?= MEMBERS_ALIAS ?>/billing/index.php"><?= system_showText(LANG_LABEL_RENEW); ?></a>
                </div>
            <? } elseif ($hastocheckout) { ?>
                <a class="link" href="<?= DEFAULT_URL ?>/<?= MEMBERS_ALIAS ?>/billing/index.php">
                    <?= system_showText(@constant("LANG_MSG_CONTINUE_TO_PAY_" . string_strtoupper($item_type))); ?>
                </a>
            <? } ?>
        </div>
    </div>
    <div class="row">
        <? if ($arrayCompletion["total"] < 100) { ?>
        <div class="col-md-6 listing-completion">
            <div class="members-panel">
                <div class="panel-header header-spaced">
                    <?=system_showText(constant("LANG_LABEL_" . string_strtoupper($item_type) . "_COMPLETION"));?>
                    <button class="button button-sm is-secondary panel-toggler"><i class="fa fa-minus"></i></button>
                </div>
                <div class="panel-body">
                    <div class="completion-content">
                        <div class="completion-chart">
                            <input type="text" value="<?= $arrayCompletion["total"] ?>" class="dial">
                        </div>
                        <div class="completion-steps">
                            <div class="steps-title">
                                <?= system_showText(LANG_LABEL_GAMEFY_TIP); ?>
                            </div>
                            <div class="steps-list">
                                <? if (is_numeric($arrayCompletion["desc"]) && $arrayCompletion["desc"] < 100) { ?>
                                    <a href="<?= $item_link ?>&highlight=description" class="link steps-item"><?= system_showText(LANG_LABEL_GAMEFY_DESC); ?></a>
                                <? } ?>

                                <? if (is_numeric($arrayCompletion["media"]) && $arrayCompletion["media"] < 100) { ?>
                                    <a href="<?= $item_link ?>&highlight=media" class="link steps-item"><?= system_showText(LANG_LABEL_GAMEFY_MEDIA); ?></a>
                                <? } ?>

                                <? if (is_numeric($arrayCompletion["additional"]) && $arrayCompletion["additional"] < 100) { ?>
                                    <a href="<?= $item_link ?>&highlight=additional" class="link steps-item"><?= system_showText(LANG_LABEL_GAMEFY_ADDITIONAL); ?></a>
                                <? } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if ($item_hasActivity) { ?>
        <div class="col-md-6">
            <div class="members-panel">
                <div class="panel-header header-spaced">
                    <?= system_showText(LANG_LABEL_ACTIVITYREPORT); ?>
                    <button class="button button-sm is-secondary panel-toggler"><i class="fa fa-minus"></i></button>
                </div>
                <div class="panel-body">
                    <div class="activity-content">
                        <div class="total-views">
                            <? if ($item_hasDetail || $item_type == "Banner") { ?>
                                <div class="views-item">
                                    <div class="heading h-1 views-count"><?= ($item_type == "Banner" ? $banner_views : $item_numberviews); ?></div>
                                    <div class="views-label"><?= system_showText(LANG_LABEL_TOTALVIEWERS); ?></div>
                                </div>
                            <?php } ?>

                            <?php if ($item_type == "Banner" && $showBannerClicks) { ?>
                                <div class="views-item">
                                    <div class="heading h-1 views-count"><?= $banner_clicks; ?></div>
                                    <div class="views-label"><?= system_showText(LANG_LABEL_WEBSITEVIEWS); ?></div>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="activity-list">
                            <? if (($item_hasphone || $item_haswebsite) && strtolower($item_type) == "listing") { ?>
                                <? if ($item_hasphone) { ?>
                                    <div class="activicty-item">
                                        <strong><?= $item_phoneviews; ?></strong> <?= system_showText(constant("LANG_LABEL_PHONEVIEW" . ($item_phoneviews == 1 ? "" : "S"))); ?>
                                    </div>
                                <?php } ?>

                                <? if ($item_haswebsite) { ?>
                                    <div class="activicty-item">
                                        <strong><?= $item_websiteviews; ?></strong> <?= system_showText(constant("LANG_LABEL_WEBSITEVIEW" . ($item_websiteviews == 1 ? "" : "S"))); ?>
                                    </div>
                                <?php } ?>
                            <?php } ?>

                            <?php
                            /* ModStores Hooks */
                            if(!HookFire("view_member_dashboard_leads", [
                                "item_type"          => &$item_type,
                                "leadsArr"           => &$leadsArr,
                                "count_total_leads"  => &$count_total_leads,
                                "newLeads"           => &$newLeads,
                                "count_unread_leads" => &$count_unread_leads,
                                "newLeadsTip"        => &$newLeadsTip,
                            ])) {
                                if ($item_hasemail) { ?>
                                    <div class="activicty-item">
                                        <strong><?= count($leadsArr) ?></strong>
                                        <?= system_showText(constant("LANG_LABEL_LEAD" . (count($leadsArr) == 1 ? "" : "S"))); ?>
                                        <br>
                                        <? if (count($leadsArr)) { ?>
                                            <a href="javascript:void(0);" onclick="scrollPage('#leads-list');" class="link"><?= system_showText(LANG_LABEL_SEE_LEADS); ?></a>
                                            <? if ($newLeads) { ?>
                                                <span class="count"><?= ($newLeads > 10 ? "9+" : $newLeads) ?></span>
                                            <? } ?>
                                        <? } ?>
                                    </div>
                                <?php }
                            }

                            if ($item_hasreview) { ?>
                            <div class="activicty-item">
                                <div class="stars-rating">
                                    <div class="rate-<?= $item_avgreview; ?>"></div>
                                </div>
                                <?= system_showText(LANG_LABEL_BASED_ON); ?> <strong><?= count($reviewsArr) ?></strong> <?= (count($reviewsArr) == 1 ? LANG_REVIEW : LANG_REVIEW_PLURAL) ?>
                                <br>
                                <? if (count($reviewsArr)) { ?>
                                    <a href="javascript:void(0);" onclick="scrollPage('#reviews-list');" class="link"><?= system_showText(LANG_LABEL_SEE_REVIEWS); ?></a>
                                    <? if ($newReviews) { ?>
                                        <span class="count"><?= ($newReviews > 10 ? "9+" : $newReviews) ?></span>
                                    <? } ?>
                                <? } ?>
                            </div>
                            <? } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <br>

    <?php
        /* Will print chart HTML if $showChart is true :
        * If its not IE or if there was any data to show */
        $showChart and $superReports and $superReports->renderGraphs($reportData);
    ?>

    <?php if ($item_hasreview) { ?>
    <br>
    <div class="members-panel" id="reviews-list">
        <div class="panel-header header-spaced">
            <span>
                <?= (count($reviewsArr) == 1 ? LANG_REVIEW : LANG_REVIEW_PLURAL) ?> (<?= count($reviewsArr) ?>)
            </span>

            <? if ($item_status == "A") { ?>
            <div class="panel-share">
                <a <?= $shareFacebook ?>><i class="fa fa-facebook-square"></i></a>
                <a <?= $shareTwitter ?>><i class="fa fa-twitter-square"></i></a>
                <?= system_showText(LANG_LABEL_DASHBOARD_SHARE); ?>
            </div>
            <? } ?>

            <button class="button button-sm is-secondary panel-toggler"><i class="fa fa-minus"></i></button>
        </div>
        <div class="panel-body">
            <div class="content-list">
            <?
            $countReview = 1;

            if ($reviewsArr) {
                foreach ($reviewsArr as $each_rate) {
                    $profile = new Profile($each_rate->getNumber("member_id"));

                    //Review Title
                    if ($each_rate->getString("review_title")) {
                        $review_title = $each_rate->getString("review_title");
                    } else {
                        $review_title = system_showText(LANG_NA);
                    }

                    //Reviewer Name
                    if ($each_rate->getString("reviewer_name")) {
                        $reviewer_name = $each_rate->getString("reviewer_name");
                    } else {
                        $reviewer_name = system_showText(LANG_NA);
                    }

                    //Reviewer Image
                    $imgTag = "";
                    $reviewer_link = "";
                    if ($each_rate->getNumber("member_id")) {
                        $imgTag = socialnetwork_writeLink($each_rate->getNumber("member_id"), "", "",
                            $profile->getNumber("image_id"), false, false, "class='content-picture'", true, "user-profile", true);
                        $reviewer_link = SOCIALNETWORK_URL."/".$profile->getString("friendly_url")."/";
                    } elseif (SOCIALNETWORK_FEATURE == "on") {
                        $imgTag = "<img src=\"" . DEFAULT_URL . "/assets/images/user-image.png\" alt=\"$reviewer_name\">";
                    }

                    //Review Status
                    $pending = true;
                    if ($each_rate->getNumber("approved") == 0) {

                        if (string_strlen(trim($each_rate->getString("response"))) > 0) {

                            //Pending Review and Pending Reply
                            if ($each_rate->getNumber("responseapproved") == 0) {
                                $reviewStatus = system_showText(LANG_MSG_WAITINGSITEMGRAPPROVE_REVIEW_REPLY);
                            } else {
                                //Pending Review
                                $reviewStatus = system_showText(LANG_MSG_WAITINGSITEMGRAPPROVE_REVIEW);
                            }

                        } else {
                            //Pending Review
                            $reviewStatus = system_showText(LANG_MSG_WAITINGSITEMGRAPPROVE_REVIEW);
                        }

                    } elseif ($each_rate->getNumber("approved") == 1) {

                        if (string_strlen(trim($each_rate->getString("response"))) == 0) {

                            //Review approved
                            $pending = false;
                            $reviewStatus = system_showText(LANG_MSG_REVIEW_ALREADY_APPROVED);

                        } elseif (string_strlen($each_rate->getString("response")) > 0) {

                            //Reply pending
                            if ($each_rate->getNumber("responseapproved") == 0) {
                                $reviewStatus = system_showText(LANG_MSG_WAITINGSITEMGRAPPROVE_REPLY);
                            } else {
                                //Review and reply approved
                                $pending = false;
                                $reviewStatus = system_showText(LANG_MSG_REVIEWANDREPLY_ALREADY_APPROVED);
                            }

                        }

                    }
                ?>
                <div class="content-item" data-id="<?= $each_rate->getNumber("id") ?>" is-new="<?= ($each_rate->getString("new") == "y" ? "true" : "false"); ?>" id="item-review_<?= $countReview ?>" <?= $countReview > $maxItems ? "style=\"display:none;\"" : "" ?>>
                    <div class="content-header">
                        <?=$imgTag;?>
                        <div class="content-info">
                            <h4 class="heading h-4 content-title"><?=$review_title;?> - <time><?= ($each_rate->getString("added")) ? format_date($each_rate->getString("added"),
                                    DEFAULT_DATE_FORMAT, "datestring") : system_showText(LANG_NA); ?></time></h4>
                            <div class="paragraph p-3 content-author"><?= system_showText(LANG_LABEL_REVIEWBY); ?>
                                <?php if($reviewer_link){ ?>
                                <a href="<?=$reviewer_link?>" class="link">
                                    <?= $reviewer_name ?>
                                </a>
                                <?php } else { ?>
                                <span><?= $reviewer_name ?></span>
                                <?php } ?>
                            </div>
                            <div class="stars-rating">
                                <div class="rate-<?= $each_rate->getString("rating") ?>"></div>
                            </div>
                            <? if ($pending) { ?>
                            <div class="content-status"><?= $reviewStatus; ?></div>
                            <?php } ?>
                        </div>
                        <button class="button button-sm is-primary button-edit-reply" data-text='["<?= system_showText(($each_rate->getString("response") ? LANG_LABEL_EDIT_REPLY : LANG_LABEL_REPLY)); ?>", "<?=system_showText(LANG_BUTTON_CANCEL);?>"]' data-ref="<?= $each_rate->getNumber("id") ?>">
                            <?= system_showText(($each_rate->getString("response") ? LANG_LABEL_EDIT_REPLY : LANG_LABEL_REPLY)); ?>
                        </button>
                    </div>
                    <div class="content-body">
                        <q class="content-text"><?= $each_rate->getString("review", true); ?></q>
                        <? if (string_strlen(trim($each_rate->getString("response"))) > 0) { ?>
                        <div class="reply-block">
                            <blockquote class="content-reply">
                                <strong><?= system_showtext(LANG_LABEL_REPLY); ?>:</strong>
                                <div class="reply-text">
                                    <?=nl2br($each_rate->getString("response"));?>
                                </div>
                            </blockquote>
                        </div>
                        <? } ?>
                        <form action="javascript:void(0);" method="post" class="reply-form" data-action="review" id="reply-form-<?= $each_rate->getNumber("id") ?>">
                            <div class="reply-message" data-type="success"><?= system_showText(LANG_REPLY_SUCCESSFULLY); ?></div>
                            <div class="reply-message" data-type="error"><?= system_showText(LANG_REPLY_EMPTY); ?></div>

                            <input type="hidden" name="item_id" value="<?= $each_rate->getNumber("item_id"); ?>">
                            <input type="hidden" name="item_type" value="<?= $each_rate->getNumber("item_type"); ?>">
                            <input type="hidden" name="idReview" value="<?= $each_rate->getNumber("id"); ?>">
                            <input type="hidden" name="ajax_type" value="review_reply">

                            <label for=""><?= system_showText(LANG_LABEL_WRITE_REPLY); ?></label>
                            <textarea name="reply" rows="5" class="input"><?= $each_rate->getString("response"); ?></textarea>

                            <div class="text-center">
                                <button class="button button-md is-primary">
                                    <?= system_showText(LANG_BUTTON_SUBMIT) ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
                $countReview++;
                }
            }
            ?>
            <?php if ($countReview > ($maxItems + 1)) { ?>
                <div class="content-viewmore">
                    <a href="javascript:void(0);" class="button button-md is-secondary text-center" full-width="true" id="linkMoreReview" onclick="showmore('item-review_', 'linkMoreReview', <?= $countReview ?>, <?= $maxItems ?>);"><?= system_showText(LANG_VIEWMORE); ?></a>
                    <input type="hidden" id="item-review_" value="<?= $maxItems ?>">
                </div>
            <? } ?>
            </div>
        </div>
    </div>
    <?php }

    /* ModStores Hooks */
    if(!HookFire("view_member_dashboard_leads_listing_hasemail", [
        "item_type"          => &$item_type,
        "item_hasemail"      => &$item_hasemail,
        "count_total_leads"  => &$count_total_leads,
        "newLeads"           => &$newLeads,
        "count_unread_leads" => &$count_unread_leads,
        "newLeadsTip"        => &$newLeadsTip,
        "show_leadsTables"   => &$show_leadsTables,
        "item_status"        => &$item_status,
        "shareFacebook"      => &$shareFacebook,
        "shareTwitter"       => &$shareTwitter,
        "leadsArr"           => &$leadsArr,
        "limit"              => &$limit,
        "item"               => &$item,
        "show_upgrade_link"  => &$show_upgrade_link,
        "message"            => &$message,
        "maxItems"           => &$maxItems,
        "item_id"            => &$item_id,
        "to"                 => &$to,
        "action"             => &$action,
        "idLead"             => &$idLead,
        "filter_year"        => &$filter_year,
        "filter_month"       => &$filter_month,
        "select_month"       => &$select_month,
        "select_year"        => &$select_year
    ])) {

        if ($item_hasemail) { ?>
        <br>

        <div class="members-panel" id="leads-list">
            <div class="panel-header header-spaced">
                <span>
                    <?= (count($leadsArr) == 1 ? LANG_LABEL_LEAD : LANG_LABEL_LEADS) ?> (<?= count($leadsArr) ?>)
                </span>

                <? if ($item_status == "A") { ?>
                <div class="panel-share">
                    <a <?= $shareFacebook ?>><i class="fa fa-facebook-square"></i></a>
                    <a <?= $shareTwitter ?>><i class="fa fa-twitter-square"></i></a>
                    <?= system_showText(LANG_LABEL_DASHBOARD_SHARE2); ?>
                </div>
                <? } ?>

                <button class="button button-sm is-secondary panel-toggler"><i class="fa fa-minus"></i></button>
            </div>
            <div class="panel-body">
                <div class="content-list">
                    <?php
                        $countLead = 1;
                        if ($leadsArr) {
                            foreach ($leadsArr as $each_lead) {
                                $auxMessage = @unserialize($each_lead["message"]);
                                if (is_array($auxMessage)) {
                                    $each_lead["message"] = "";
                                    foreach ($auxMessage as $key => $value) {
                                        $each_lead["message"] .= (defined($key) ? constant($key) : $key) . ($value ? ": " . $value : "") . "\n";
                                    }
                                }

                                $replied = false;
                                if ($each_lead["reply_date"] && $each_lead["reply_date"] != "0000-00-00 00:00:00") {
                                    $replied = true;
                                    $titleIco = system_showText(LANG_LEAD_REPLIED_ICO) . " (" . format_date($each_lead["reply_date"],
                                            DEFAULT_DATE_FORMAT, "datestring") . ")";
                                }
                                $titleIcoToday = system_showText(LANG_LEAD_REPLIED_ICO) . " (" . format_date(date("Y") . "-" . date("m") . "-" . date("d"),
                                        DEFAULT_DATE_FORMAT, "datestring") . ")";

                                $lead_name = $each_lead["first_name"] . ($each_lead["last_name"] ? " " . $each_lead["last_name"] : "");
                    ?>
                    <div class="content-item" data-id="<?= $each_lead["id"] ?>" is-new="<?= ($each_lead["new"] == "y" ? "true" : "false"); ?>" id="item-lead_<?= $countLead ?>" <?= $countLead > $maxItems ? "style=\"display:none;\"" : "" ?>>
                        <div class="content-header no-flex">
                            <? if ($replied) { ?>
                                <div class="content-replied-date"><?= system_showText($titleIco) ?></div>
                            <?php } ?>
                            <h4 class="heading h-4 content-title"><?= $each_lead["subject"]; ?> - <time><?= ($each_lead["entered"]) ? format_date($each_lead["entered"], DEFAULT_DATE_FORMAT,
                                            "datestring") : system_showText(LANG_NA); ?></time></h4>
                            <div class="content-from">
                                <?= system_showText(LANG_LABEL_FROM); ?>
                                <strong><?= $lead_name ?></strong>
                            </div>
                            <button class="button button-sm is-primary button-edit-reply" data-text='["<?=system_showText(LANG_LABEL_REPLY);?>", "<?=system_showText(LANG_BUTTON_CANCEL);?>"]' data-ref="<?= $each_lead["id"] ?>"><?= system_showText(LANG_LABEL_REPLY); ?></button>
                        </div>
                        <div class="content-body">
                            <div class="reply-block">
                                <blockquote class="content-reply">
                                    <div class="reply-text">
                                        <?= nl2br($each_lead["message"]); ?>
                                    </div>
                                </blockquote>
                            </div>
                            <form action="javascript:void(0);" method="post" class="reply-form" data-action="lead" id="reply-form-<?= $each_lead["id"] ?>">
                                <div class="reply-message" data-type="success"><?= system_showText(LANG_LEAD_REPLIED); ?></div>
                                <div class="reply-message" data-type="error"></div>

                                <input type="hidden" name="item_id" value="<?= $item_id; ?>">
                                <input type="hidden" name="item_type" value="<?= $item_type; ?>">
                                <input type="hidden" name="type" value="<?= $item_type; ?>">
                                <input type="hidden" name="idLead" value="<?= $each_lead["id"]; ?>">
                                <input type="hidden" name="action" value="reply">
                                <input type="hidden" name="ajax_type" value="lead_reply">
                                <input type="hidden" name="reply_to" value="<?= !empty($itemObj->getString('email')) ? $itemObj->getString('email') : $accountObj->getString('username');?>">

                                <div class="form-group">
                                    <label for="lead-mail"><?= system_showText(LANG_LABEL_TO); ?>: </label>
                                    <input id="lead-mail" class="input" type="email" name="to" value="<?= ($to && $action == "reply" && $idLead == $each_lead["id"] ? $to : $each_lead["email"]); ?>">
                                </div>
                                <br>
                                <div class="form-group">
                                    <label for="lead-message"><?= system_showText(LANG_LABEL_MESSAGE); ?>:</label>
                                    <textarea id="lead-message" class="input" name="message" rows="5"><?= ($message && $action == "reply" && $idLead == $each_lead["id"] ? $message : ""); ?></textarea>
                                </div>

                                <div class="text-center">
                                    <button class="button button-md is-primary"><?= system_showText(LANG_BUTTON_SUBMIT) ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                        $countLead++;
                            }
                        }
                    ?>
                    <?php if ($countLead > ($maxItems + 1)) { ?>
                        <br>
                        <div class="content-viewmore">
                            <a href="javascript:void(0);" class="button button-md is-secondary text-center" full-width="true" id="linkMoreleads" onclick="showmore('item-lead_', 'linkMoreleads', <?= $countLead ?>, <?= $maxItems ?>);"><?= system_showText(LANG_VIEWMORE); ?></a>
                            <input type="hidden" id="item-lead_" value="<?= $maxItems ?>">
                        </div>
                    <? } ?>
                </div>
            </div>
        </div>
        <?php } ?>
    <?php } ?>

    <?php if (strtolower($item_type) == "promotion") { ?>
    <br>
    <div class="members-panel" id="deals-list">
        <div class="panel-header header-spaced">
            <span>
                <?= (count($dealsRedeemed) == 1 ? system_showText(LANG_PROMOTION_FEATURE_NAME) : system_showText(LANG_PROMOTION_FEATURE_NAME_PLURAL)) ?> (<?= count($dealsRedeemed) ?>)
            </span>

            <? if ($item_status != "A") { ?>
            <div class="panel-share">
                <a <?= $shareFacebook ?>><i class="fa fa-facebook-square"></i></a>
                <a <?= $shareTwitter ?>><i class="fa fa-twitter-square"></i></a>
                <?= system_showText(LANG_LABEL_DASHBOARD_SHARE3); ?>
            </div>
            <? } ?>

            <button class="button button-sm is-secondary panel-toggler"><i class="fa fa-minus"></i></button>
        </div>
        <div class="panel-body">
            <div class="content-list">
            <?php
                $countDeal = 1;
                if ($dealsRedeemed) {
                    foreach ($dealsRedeemed as $eachDeal) {
                        $profileObj = new Profile($eachDeal["account_id"]);
                        $imgTag = "";
                        if ($profileObj->getString("nickname")) {
                            $eachDeal["profile_name"] = $profileObj->getString("nickname");
                            $imgTag = socialnetwork_writeLink($eachDeal["account_id"], "", "",
                                $profileObj->getNumber("image_id"), false, false, "", true, "user-profile", true);
                        } elseif (SOCIALNETWORK_FEATURE == "on") {
                            $imgTag = "<img src=\"" . DEFAULT_URL . "/assets/images/user-image.png\" alt=\"$reviewer_name\">";
                        }
            ?>
                <div class="content-item" data-id="<?= $eachDeal["id"] ?>" id="item-deal_<?= $countDeal ?>" <?= $countDeal > $maxItems ? "style=\"display:none;\"" : "" ?>>
                    <div class="content-header">
                        <?=$imgTag;?>
                        <div class="content-info">
                            <h5 class="heading h-5 content-title"><?= system_showText(LANG_LABEL_REDEEMED_BY); ?> <strong><?= $eachDeal["profile_name"] ?></strong></h5>
                            <div class="paragraph p-3 content-author"><?= ($eachDeal["datetime"]) ? format_date($eachDeal["datetime"], DEFAULT_DATE_FORMAT,
                                    "datestring") : system_showText(LANG_NA); ?></div>
                            <div class="content-status"><?= system_showText(LANG_LABEL_CODE) ?>: <strong><?= $eachDeal["redeem_code"] ?></strong></div>
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="form-status">
                            <label>
                                <input type="radio" name="status<?= $eachDeal["id"] ?>" <?= $eachDeal["used"] ? "checked" : "" ?> onclick="changeDealStatus('useDeal', <?= $eachDeal["id"] ?>, '<?= $eachDeal["redeem_code"] ?>');"><?= ucwords(system_showText(LANG_DEAL_CHECKOUT)); ?>
                            </label>
                            <label>
                                <input type="radio" name="status<?= $eachDeal["id"] ?>" <?= $eachDeal["used"] ? "" : "checked" ?> onclick="changeDealStatus('freeUpDeal', <?= $eachDeal["id"] ?>, '<?= $eachDeal["redeem_code"] ?>');"><?= ucwords(system_showText(LANG_DEAL_OPENED)); ?>
                            </label>
                        </div>
                    </div>
                </div>
            <?php
                    }
                    $countDeal++;
                }
            ?>
        <?php if ($countDeal > ($maxItems + 1)) { ?>
            <div class="content-viewmore">
                <a href="javascript:void(0);" class="button button-md is-secondary text-center" full-width="true" id="linkMoreDeal" onclick="showmore('item-deal_', 'linkMoreDeal', <?= $countDeal ?>, <?= $maxItems ?>);"><?= system_showText(LANG_VIEWMORE); ?></a>
                <input type="hidden" id="item-deal_" value="<?= $maxItems ?>">
            </div>
        <? } ?>
    <?php } ?>
</div>
