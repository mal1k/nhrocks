<?

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
# * FILE: /includes/forms/form_advertise.php
# ----------------------------------------------------------------------------------------------------

include INCLUDES_DIR.'/code/newsletter.php';

$defaultusername = $username;
$defaultpassword = '';
if (DEMO_DEV_MODE) {
    $defaultusername = 'demo@demodirectory.com';
    $defaultpassword = 'abc123';
}

$defaultActionForm = $formloginaction.($advertiseItem == 'banner' ? '&amp;query=type='.($_POST['type'] ? $_POST['type'] : $_GET['type']) : '&amp;query=level='.($_POST['level'] ? $_POST['level'] : $_GET['level']));

if ($advertiseItem == 'listing') {
    $defaultActionForm .= '&amp;listingtemplate_id='.($_POST['listingtemplate_id'] ? $_POST['listingtemplate_id'] : $_GET['listingtemplate_id']);
}

$redirectURI_params = [
    'destiny'        => 'advertise',
    'advertise_item' => $advertiseItem,
    'item_id'        => "$unique_id",
];

$hasListingType = false;

if ($advertiseItem == 'listing' && LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && system_showListingTypeDropdown($listingtemplate_id)) {
    $hasListingType = true;
}

?>

<div style="display:none">

    <form id="formDirectory" name="formDirectory" method="post" action="<?=$defaultActionForm;?>">

        <input type="hidden" name="advertise" value="yes">
        <input type="hidden" name="destiny" value="<?=$destiny?>">
        <input type="hidden" name="query" value="<?=urlencode($query)?>">

        <input type="hidden" name="username" id="form_username" value="">
        <input type="hidden" name="password" id="form_password" value="">

    </form>

</div>

<form name="order_item" action="<?=system_getFormAction($_SERVER['REQUEST_URI'])?>" method="post" class="form" onsubmit="JS_submit();" autocomplete="off">

    <input type="hidden" name="advertise" value="yes">
    <input type="hidden" name="signup" value="true">

    <? if ($advertiseItem == 'banner') { ?>
        <input type="hidden" name="type" id="type" value="<?=$type?>">
    <? } else { ?>
        <input type="hidden" name="level" id="level" value="<?=$level?>">
    <? } ?>

    <? if ($advertiseItem == 'listing') { ?>
        <input type="hidden" name="listingtemplate_id" id="listingtemplate_id" value="<?=$listingtemplate_id?>">
    <? } ?>

    <div class="members-page" id="screen1" <?=($message_account || $message_contact ? 'style="display: none;"' : 'style="display: block;"')?>>
        <div class="container">
            <div class="form-edit-alert hidden" id="errorMessage">&nbsp;</div>
            <div class="members-panel edit-panel" id="listing-info">
                <div class="panel-header">
                    <?=system_showText(constant('LANG_'.strtoupper($advertiseItem).'INFO'));?>
                </div>
                <div class="panel-body">
                    <div class="panel-description">
                        <p><?=system_showText(constant('LANG_'.strtoupper($advertiseItem).'INFO_TIP'));?></p>
                        <?php if ($hasListingType) { ?>
                        <p><?=system_showText(LANG_LISTINGINFO_TIP2);?></p>
                        <?php } ?>
                    </div>

                    <div class="custom-edit-content custom-biling">
                        <div class="row default-row-biling">
                            <div class="form-group <?=($hasListingType) ? 'col-md-6' : 'col-md-12';?>">
                                <label id="title_label" for="<?=$advertiseItem?>-title"><?=($template_title_field !== false && $advertiseItem == 'listing') ? $template_title_field[0]['label'] : ($advertiseItem == 'banner' ? system_showText(LANG_LABEL_CAPTION) : system_showText(LANG_LABEL_TITLE))?></label>
                                <? if ($advertiseItem == 'banner') { ?>
                                    <input class="form-control" type="text" name="caption" id="<?=$advertiseItem?>-title" value="<?=$caption?>" onblur="updateFormAction(); $('#adv_title').html(this.value);">
                                <? } else { ?>
                                    <input class="form-control" type="text" name="title" id="<?=$advertiseItem?>-title" value="<?=$title?>" onblur="easyFriendlyUrl(this.value, 'friendly_url', '<?=FRIENDLYURL_VALIDCHARS?>', '<?=FRIENDLYURL_SEPARATOR?>'); updateFormAction(); $('#adv_title').html(this.value);">
                                    <input type="hidden" name="friendly_url" id="friendly_url" value="<?=$friendly_url?>">
                                <? } ?>
                            </div>
                            <?php if ($hasListingType) {?>
                            <div class="form-group col-md-6 custom-selectize">
                                <label for="listing-template"><?=system_showText(LANG_LISTING_LABELTEMPLATE)?></label>
                                <select class="form-control input cutom-select-appearence" id="listing-template" name="select_listingtemplate_id" onchange="templateSwitch(this.value);">
                                    <?=$listingTypeOptions;?>
                                </select>
                            </div>
                            <?php } ?>
                        </div>
                        <? if ($advertiseItem == 'event') { ?>
                            <div class="row default-row-biling">
                                <div class="form-group col-md-6">
                                    <label for="start_date"><?=system_showText(LANG_LABEL_STARTDATE)?> <span class="sr-only"><?=system_showText(LANG_LABEL_REQUIRED_FIELD);?></span></label>
                                    <input class="form-control date-input" type="text" name="start_date" id="start_date" value="<?=$start_date?>">
                                    <small class="help-block">(<?=format_printDateStandard()?>)</small>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="end_date"><?=system_showText(LANG_LABEL_ENDDATE)?> <span class="sr-only"><?=system_showText(LANG_LABEL_REQUIRED_FIELD);?></span></label>
                                    <input class="form-control date-input" type="text" name="end_date" id="end_date" value="<?=$end_date?>">
                                    <small class="help-block">(<?=format_printDateStandard()?>)</small>
                                </div>
                            </div>
                        <? } ?>
                    </div>
                </div>
            </div>
            <br>

            <? if ($advertiseItem == 'listing') { ?>
            <div class="members-panel edit-panel" id="categories">
                <div class="panel-header">
                    <?=system_showText(LANG_CATEGORIES_TITLE)?>
                </div>
                <div class="panel-body">
                    <div class="panel-description">
                        <? if (LISTING_MAX_CATEGORY_ALLOWED != $listingLevelObj->getFreeCategory($level) && PAYMENTSYSTEM_FEATURE === 'on') { ?>
                            <p id="extracategory_note">
                                <?=string_ucwords(system_showText(($listingLevelObj->getFreeCategory($level) > 1) ? LANG_CATEGORY_PLURAL : LANG_CATEGORY))?>
                                <strong><?=system_showText(LANG_INCLUDED)?>:</strong>
                                <?=$listingLevelObj->getFreeCategory($level)?>.
                                <?=system_showText(LANG_CATEGORIES_PRICEDESC1)?>
                                <strong><?=system_showText(LANG_CATEGORIES_PRICEDESC2)?> <?=PAYMENT_CURRENCY_SYMBOL?> <?=$listingLevelObj->getCategoryPrice($level)?></strong>
                                <?=system_showText(LANG_CATEGORIES_PRICEDESC3)?>
                            </p>
                        <? } ?>
                        <p><?=system_showText(LANG_CATEGORIES_CATEGORIESMAXTIP1).' <strong>'.system_showText(LISTING_MAX_CATEGORY_ALLOWED).'</strong> '.system_showText(LANG_CATEGORIES_CATEGORIESMAXTIP2)?></p>
                        <p><?=system_showText(LANG_CATEGORIES_MSG1)?><br><?=system_showText(LANG_CATEGORIES_MSG2)?></p>
                    </div>
                    <div class="row default-row-biling">
                        <div class="col-md-6">
                            <input type="hidden" name="return_categories" value="">
                            <div class="custom-tree-view">
                                <ul id="listing_categorytree_id_0">
                                    <li>&nbsp;</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">

                            <div class="form-group">
                                <label><?=system_showText(LANG_LISTING_CATEGORIES);?> <span class="sr-only"><?=system_showText(LANG_LABEL_REQUIRED_FIELD);?></span></label>
                                <div class="multiple-select">
                                    <?=$feedDropDown?>
                                </div>
                                <div class="text-center" id="removeCategoriesButton" style="display:none;">
                                    <a href="javascript:void(0);" onclick="JS_removeCategory(document.order_item.feed, true);"><?=(system_showText(LANG_CATEGORY_REMOVESELECTED))?></a>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
            <br>
            <?php } ?>
            
            <div class="<?=$checkoutpayment_class?>" id="payment-method">
                <div class="members-panel edit-panel">
                    <div class="panel-header">
                        <?=system_showText(LANG_LABEL_PAYMENT_METHOD);?>
                    </div>
                    <div class="panel-body">
                        <div class="panel-description">
                            <?=system_showText(LANG_LABEL_PAYMENT_METHOD_TIP);?>
                        </div>
                        <div class="custom-edit-content custom-biling">
                            <div class="row default-row-biling">
                                <div class="form-group col-md-6 custom-inline-radio">
                                    <? include INCLUDES_DIR.'/forms/form_paymentmethod.php'; ?>
                                </div>
                                <? if (PAYMENT_FEATURE == 'on' && ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on'))) { ?>
                                    <div class="form-group col-md-6">
                                        <label for="promocode"><?=string_ucwords(system_showText(LANG_LABEL_DISCOUNTCODE))?></label>
                                        <input class="form-control" type="text" id="promocode" name="discount_id" value="<?=$discount_id?>" maxlength="10" onblur="orderCalculate(); updateFormAction();">
                                    </div>
                                <? } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="price-resume">
                <div id="loadingOrderCalculate" class="loadingOrderCalculate"><?=system_showText(LANG_WAITLOADING)?></div>

                <input type="hidden" name="free_item" id="free_item" value="">

                <? if (PAYMENT_FEATURE == 'on' && ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on'))) { ?>
                    <div id="check_out_payment" class="<?=$checkoutpayment_class?>">
                        <div class="text-center">
                            <div id="checkoutpayment_total" class="orderTotalAmount"></div>
                            <br>
                        </div>
                        <div class="price-action">
                            <button class="button button-md is-primary" id="button1" type="button" onclick="<?=("nextStep('$advertiseItem', ".($advertiseItem == 'listing' ? 'document.order_item.feed' : 'false').", '$advertiseItem-title', ".($hasPackage ? 'true' : 'false').');')?>"><?=system_showText(LANG_BUTTON_CONTINUE)?></button>
                            <a href="<?=DEFAULT_URL?>/<?=ALIAS_ADVERTISE_URL_DIVISOR?>/" class="button button-md is-outline"><?=system_showText(LANG_ADVERTISE_BACK);?></a>
                        </div>
                    </div>
                <? } ?>

                <div id="check_out_free" class="<?=$checkoutfree_class?>">

                    <div class="text-center">
                        <div id="checkoutfree_total" class="orderTotalAmount"></div>
                        <br>
                    </div>
                    <div class="price-action">
                        <button class="button button-md is-primary" type="button" id="button2" onclick="<?=("nextStep('$advertiseItem', ".($advertiseItem == 'listing' ? 'document.order_item.feed' : 'false').", '$advertiseItem-title', ".($hasPackage ? 'true' : 'false').');')?>"><?=system_showText(LANG_BUTTON_CONTINUE)?></button>
                        <a href="<?=DEFAULT_URL?>/<?=ALIAS_ADVERTISE_URL_DIVISOR?>/"><?=system_showText(LANG_ADVERTISE_BACK);?></a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php if ($hasPackage) { ?>
        <div class="members-page" id="screenPackage" style="display: none;">
            <div class="container">
                <div class="members-panel edit-panel">
                    <div class="panel-header">
                        <?=$labelName;?>
                    </div>
                    <div class="panel-body">
                        <?php include EDIRECTORY_ROOT.'/includes/forms/form_advertise_package.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <div id="screen2" <?=($message_account || $message_contact ? 'style="display: block;"' : 'style="display: none;"')?>>
        <div class="claim-signup-breadcrumb" id="checkout_steps">
            <div class="breadcrumb-item" is-active="true">
                <strong>1:</strong> <?=system_showText(LANG_ADVERTISE_IDENTIFICATION);?>
            </div>
            <div class="breadcrumb-item">
                <strong>2:</strong> <?=system_showText(LANG_CHECKOUT);?>
            </div>
            <?php if (PAYMENT_FEATURE === 'on') { ?>
                <div class="breadcrumb-item">
                    <strong>3:</strong> <?=system_showText(LANG_ADVERTISE_CONFIRMATION);?>
                </div>
            <?php } ?>
        </div>

        <div class="modal-default modal-sign keep-style login-page-advertise" is-page="true" id="advertise_signup">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="content-tab content-sign-up" id="sign-up">
                        <?php if ($foreignaccount_google == 'on' || FACEBOOK_APP_ENABLED == 'on') { ?>
                        <div class="modal-social">
                            <?php
                                if ($facebookEnabled) {
                                    $fbLabel = 'Facebook';
                                    include INCLUDES_DIR.'/forms/form_facebooklogin.php';
                                    unset($fbLabel);
                                }

                                if ($googleEnabled) {
                                    $goLabel = 'Google';
                                    include INCLUDES_DIR.'/forms/form_googlelogin.php';
                                    unset($goLabel);
                                }
                            ?>
                        </div>
                        <span class="heading or-label"><?= system_showText(LANG_OR_SIGNUPEMAIL); ?></span>
                        <?php } ?>
                        <div class="modal-form">
                            <?php
                                $advertise_section = true;
                                include INCLUDES_DIR.'/forms/form_addaccount.php';
                            ?>
                        </div>
                        <div class="not-member"><?=system_showText(LANG_ALREADYHAVEACCOUNT);?> <a href="javascript:void(0);" onclick="$('#advertise_signup').css('display', 'none'); $('#advertise_login').fadeIn(500);" class="link"><?=system_showText(LANG_LABEL_LOGIN);?></a></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</form>

<div id="advertise_login" style="display:none">
    <div class="modal-default modal-sign keep-style login-page-advertise" is-page="true">
        <div class="modal-content">
            <div class="modal-body">
                <div class="content-tab content-sign-in active" id="sign-in">
                    <?php if ($foreignaccount_google == 'on' || FACEBOOK_APP_ENABLED == 'on') { ?>
                    <div class="modal-social">
                        <?php
                            $redirectURI_params = [
                                "destiny" => "claim",
                                "claimlistingid" => $claimlistingid
                            ];
                            if (FACEBOOK_APP_ENABLED == "on") {
                                $fbLabel = 'Facebook';
                                include(INCLUDES_DIR."/forms/form_facebooklogin.php");
                                unset($fbLabel);
                            }

                            if ($foreignaccount_google == "on") {
                                $goLabel = 'Google';
                                include(INCLUDES_DIR."/forms/form_googlelogin.php");
                                unset($goLabel);
                            }
                        ?>
                    </div>
                    <span class="heading or-label"><?= system_showText(LANG_OR_SIGNUPEMAIL); ?></span>
                    <?php } ?>
                    <div class="modal-form">
                        <?php
                            $advertise_section = true;
                            include INCLUDES_DIR.'/forms/form_login.php';
                        ?>
                    </div>
                    <div class="not-member"><?=system_showText(LANG_ACCOUNTNEWUSER);?> <a href="javascript:void(0);" onclick="$('#advertise_login').css('display', 'none'); $('#advertise_signup').fadeIn(500);" class="link"><?=system_showText(LANG_ADVERTISE_CREATE_ACC);?></a></div>
                </div>
            </div>
        </div>
    </div>
</div>
