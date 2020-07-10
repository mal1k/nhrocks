<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/sidebar-content.php
	# ----------------------------------------------------------------------------------------------------

?>

    <div class="sidebar sidebar-ext togglepush" id="sidebar" role="navigation">

        <div class="short-sidebar">
            <?php
            /* General Sidebar*/
            include(SM_EDIRECTORY_ROOT."/layout/sidebar-general.php");
            ?>
        </div>
        <div class="second-sidebar">
            <h1><?=system_showText(LANG_SITEMGR_CONTENT_MANAGER);?></h1>
            <p><?=system_showText(LANG_SITEMGR_CONTENT_MANAGER_TIP);?></p>
            <div class="navigation nano">
                <div class="list-group nano-content">
                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/"?>" class="list-group-item <?=(string_strpos($_SERVER["PHP_SELF"], "/content/".LISTING_FEATURE_FOLDER."/") !== false ? "active" : "")?>"><?=system_showText(LANG_SITEMGR_NAVBAR_LISTING);?></a>

                    <?php
                    /* ModStores Hooks */
                    HookFire( "sidebarcontent_after_render_listingitem");

                    if (PROMOTION_FEATURE == "on" && CUSTOM_HAS_PROMOTION == "on" && CUSTOM_PROMOTION_FEATURE == "on") { ?>
                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".PROMOTION_FEATURE_FOLDER."/"?>" class="list-group-item <?=(string_strpos($_SERVER["PHP_SELF"], "/content/".PROMOTION_FEATURE_FOLDER) !== false ? "active" : "")?>"><?=system_showText(LANG_SITEMGR_NAVBAR_PROMOTION);?></a>
                    <? }

                    /* ModStores Hooks */
                    HookFire( "sidebarcontent_after_render_dealitem");

                    if (BANNER_FEATURE == "on" && CUSTOM_BANNER_FEATURE == "on") { ?>
                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".BANNER_FEATURE_FOLDER."/"?>" class="list-group-item <?=(string_strpos($_SERVER["PHP_SELF"], "/content/".BANNER_FEATURE_FOLDER) !== false ? "active" : "")?>"><?=system_showText(LANG_SITEMGR_NAVBAR_BANNER);?></a>
                    <? }

                    /* ModStores Hooks */
                    HookFire( "sidebarcontent_after_render_banneritem");

                    if (EVENT_FEATURE == "on" && CUSTOM_EVENT_FEATURE == "on") { ?>
                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".EVENT_FEATURE_FOLDER."/"?>" class="list-group-item <?=(string_strpos($_SERVER["PHP_SELF"], "/content/".EVENT_FEATURE_FOLDER) !== false ? "active" : "")?>"><?=system_showText(LANG_SITEMGR_NAVBAR_EVENT);?></a>
                    <? }

                    /* ModStores Hooks */
                    HookFire( "sidebarcontent_after_render_eventitem");

                    if (CLASSIFIED_FEATURE == "on" && CUSTOM_CLASSIFIED_FEATURE == "on") { ?>
                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".CLASSIFIED_FEATURE_FOLDER."/"?>" class="list-group-item <?=(string_strpos($_SERVER["PHP_SELF"], "/content/".CLASSIFIED_FEATURE_FOLDER) !== false ? "active" : "")?>"><?=system_showText(LANG_SITEMGR_NAVBAR_CLASSIFIED);?></a>
                    <? }

                    /* ModStores Hooks */
                    HookFire( "sidebarcontent_after_render_classifieditem");

                    if (ARTICLE_FEATURE == "on" && CUSTOM_ARTICLE_FEATURE == "on") { ?>
                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".ARTICLE_FEATURE_FOLDER."/"?>" class="list-group-item <?=(string_strpos($_SERVER["PHP_SELF"], "/content/".ARTICLE_FEATURE_FOLDER) !== false ? "active" : "")?>"><?=system_showText(LANG_SITEMGR_NAVBAR_ARTICLE);?></a>
                    <? }

                    /* ModStores Hooks */
                    HookFire( "sidebarcontent_after_render_articleitem");

                    if (BLOG_FEATURE == "on" && CUSTOM_BLOG_FEATURE == "on") { ?>
                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".BLOG_FEATURE_FOLDER."/"?>" class="list-group-item <?=(string_strpos($_SERVER["PHP_SELF"], "/content/".BLOG_FEATURE_FOLDER) !== false ? "active" : "")?>"><?=system_showText(LANG_SITEMGR_BLOG_SING);?></a>
                    <? }

                    /* ModStores Hooks */
                    HookFire( "sidebarcontent_after_render_blogitem");

                    if (LISTINGTEMPLATE_FEATURE == "on" && CUSTOM_LISTINGTEMPLATE_FEATURE == "on") { ?>
                        <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/listing-types/"?>" class="list-group-item <?=(string_strpos($_SERVER["PHP_SELF"], "/content/listing-types") !== false ? "active" : "")?>"><?=ucwords(system_showText(LANG_SITEMGR_LISTINGTEMPLATE_PLURAL));?></a>
                    <? }

                    /* ModStores Hooks */
                    HookFire( "sidebarcontent_after_render_listingtype"); ?>

                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/import/"?>" class="list-group-item <?=(string_strpos($_SERVER["PHP_SELF"], "/content/import/") !== false ? "active" : "")?>"><?=system_showText(LANG_SITEMGR_CONTENT_IMPORT);?></a>

                    <?php
                    /* ModStores Hooks */
                    HookFire( "sidebarcontent_after_render_importitem");
                    ?>

                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/export/"?>" class="list-group-item <?=(string_strpos($_SERVER["PHP_SELF"], "/content/export/") !== false ? "active" : "")?>"><?=system_showText(LANG_SITEMGR_CONTENT_EXPORT);?></a>

                    <?php
                    /* ModStores Hooks */
                    HookFire( "sidebarcontent_after_render_exportitem");
                    ?>

                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/faq/"?>" class="list-group-item <?=(string_strpos($_SERVER["PHP_SELF"], "/content/faq") !== false ? "active" : "")?>"><?=string_ucwords(system_showText(LANG_SITEMGR_MENU_FAQ))?></a>

                    <?php
                    /* ModStores Hooks */
                    HookFire( "sidebarcontent_after_render_faqitem");
                    ?>
                </div>
            </div>
        </div>

    </div>
