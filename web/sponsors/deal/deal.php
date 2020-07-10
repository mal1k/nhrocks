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
    # * FILE: /sponsors/deal/deal.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include("../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # VALIDATION
    # ----------------------------------------------------------------------------------------------------
    if ( PROMOTION_FEATURE != "on" || CUSTOM_PROMOTION_FEATURE != "on" || CUSTOM_HAS_PROMOTION != "on"){
        exit;
    }

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSession();
    $acctId = sess_getAccountIdFromSession();

    # ----------------------------------------------------------------------------------------------------
    # AUX
    # ----------------------------------------------------------------------------------------------------
    extract($_GET);
    extract($_POST);

    # ----------------------------------------------------------------------------------------------------
    # CODE
    # ----------------------------------------------------------------------------------------------------
    $url_base = "".DEFAULT_URL."/".MEMBERS_ALIAS."";
    $members = 1;

    if ($_POST["action"] == "useDeal" && $_POST["promotion_id"]){
        $dealObj = new Promotion();
        $dealObj->setPromoCode($_POST["promotion_id"], 1);
        die("OK");
    }
    if ($_POST["action"]== "freeUpDeal" && $_POST["promotion_id"]){
        $dealObj = new Promotion();
        $dealObj->setPromoCode($_POST["promotion_id"], 0);
        die("OK");
    }

    if (system_blockListingCreation($id)) {
        header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
        exit;
    }

    include(EDIRECTORY_ROOT."/includes/code/promotion.php");

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");

    # ----------------------------------------------------------------------------------------------------
    # NAVBAR
    # ----------------------------------------------------------------------------------------------------
    include(MEMBERS_EDIRECTORY_ROOT."/layout/navbar.php");
    $cover_title = system_showText($id ? LANG_LABEL_EDIT : LANG_ADD) ." ". system_showText(LANG_PROMOTION_FEATURE_NAME);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>

    <div class="members-page">
        <div class="container">
            <div class="members-wrapper">
                <div class="members-panel edit-panel">
                    <div class="panel-header">
                        <?=system_showText(LANG_PROMOTION_INFORMATION)?>
                    </div>
                    <div class="panel-body">
                        <form name="promotion" id="promotion" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="id" value="<?=$id?>">
                            <input type="hidden" name="listing_id" value="<?=$listing_id?>">
                            <input type="hidden" name="account_id" id="account_id" value="<?=$acctId?>">

                            <? if ($message_promotion) { ?>
                                <div class="form-edit-alert">
                                    <?=$message_promotion;?>
                                </div>
                            <? } ?>

                            <div class="custom-edit-content" has-sidebar="true">
                                <? include(INCLUDES_DIR."/forms/form-promotion.php"); ?>
                            </div>
                        </form>

                        <form action="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/" method="get" class="form-action-sponsors">
                            <button class="button button-md is-outline" type="submit"><?=system_showText(LANG_BUTTON_CANCEL)?></button>
                            <button class="button button-md is-primary" type="button" onclick="document.promotion.submit();"><?=system_showText(LANG_MSG_SAVE_CHANGES)?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include(INCLUDES_DIR."/modals/modal-crop.php");
    if (!empty(UNSPLASH_ACCESS_KEY)) {
        include(INCLUDES_DIR . "/modals/modal-unsplash.php");
        JavaScriptHandler::registerFile(DEFAULT_URL . '/assets/js/lib/unsplash.js');
    }
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/modules.php";

    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------

    include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
