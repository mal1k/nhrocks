<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/configuration/google/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# VALIDATING FEATURES
	# ----------------------------------------------------------------------------------------------------
	if (GOOGLE_MAPS_ENABLED != "on" && GOOGLE_ADS_ENABLED != "on" && GOOGLE_ANALYTICS_ENABLED != "on" && GOOGLE_TAGMANAGER_ENABLED != "on") { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track("Accessed section Google Settings");

	extract($_POST);
	extract($_GET);

	# ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $message = null;

        switch( $gtype )
        {
            case "maps" :
                $google_maps_key = trim($google_maps_key);
                if( $google_maps_status and empty( $google_maps_key ) )
                {
                    MessageHandler::registerError( system_showText(LANG_SITEMGR_GOOGLEMAPS_ERROR_KEY) );
                }

                if ( !MessageHandler::haveErrors() )
                {
                    if (!setting_set('google_api_key', $google_maps_key)){
                        setting_new('google_api_key', $google_maps_key);
                    }

                    $google_maps_status = $google_maps_status ? "on" : "off";
                    if (!setting_set('google_map_status', $google_maps_status)){
                        setting_new('google_map_status', $google_maps_status);
                    }

                    mixpanel_track(($google_maps_status == "on" ? "Enabled" : "Disabled")." Google Maps");

                    $message = system_showText(LANG_SITEMGR_GOOGLEMAPS_SETTINGSSUCCESSCHANGED);
                }

                break;
            case "ads" :
                $advertTypeFlags = 0;
                $google_ad_type_text  and $advertTypeFlags += 1;
                $google_ad_type_image and $advertTypeFlags += 2;

                if (!setting_set('google_ads_client', $google_ad_client)){
                    setting_new('google_ads_client', $google_ad_client);
                }

                if (!setting_set('google_ads_type', $advertTypeFlags)){
                    setting_new('google_ads_type', $advertTypeFlags);
                }

                $google_ad_status = $google_ad_status ? "on" : "off";

                mixpanel_track(($google_ad_status == "on" ? "Enabled" : "Disabled")." Google Ads");

                if (!setting_set('google_ads_status', $google_ad_status)){
                    setting_new('google_ads_status', $google_ad_status);
                }

                $message = system_showText(LANG_SITEMGR_GOOGLEADS_SETTINGSSUCCESSCHANGED);
                break;
            case "analytics" :
                if (!setting_set('google_analytics_status', $google_analytics_account)){
                    setting_new('google_analytics_status', $google_analytics_account);
                }

                mixpanel_track(($google_analytics_front == "on" ? "Enabled" : "Disabled")." Google Analytics on front pages");
                mixpanel_track(($google_analytics_members == "on" ? "Enabled" : "Disabled")." Google Analytics on sponsors pages");
                mixpanel_track(($google_analytics_sitemgr == "on" ? "Enabled" : "Disabled")." Google Analytics on sitemgr pages");

                $google_analytics_front = $google_analytics_front ? "on" : "off";
                if (!setting_set('google_analytics_front', $google_analytics_front)){
                    setting_new('google_analytics_front', $google_analytics_front);
                }

                $google_analytics_members = $google_analytics_members ? "on" : "off";
                if (!setting_set('google_analytics_members', $google_analytics_members)){
                    setting_new('google_analytics_members', $google_analytics_members);
                }

                $google_analytics_sitemgr = $google_analytics_sitemgr ? "on" : "off";
                if (!setting_set('google_analytics_sitemgr', $google_analytics_sitemgr)){
                    setting_new('google_analytics_sitemgr', $google_analytics_sitemgr);
                }

                $message = system_showText( LANG_SITEMGR_GOOGLEANALYTICS_SETTINGSSUCCESSCHANGED );
                break;
            case "tag" :
                if (!setting_set('google_tagmanager_clientID', $google_tag_client)){
                    setting_new('google_tagmanager_clientID', $google_tag_client);
                }

                $google_tag_status = $google_tag_status ? "on" : "off";

                mixpanel_track(($google_tag_status == "on" ? "Enabled" : "Disabled")." Google Tag manager");

                if (!setting_set('google_tagmanager_status', $google_tag_status)){
                    setting_new('google_tagmanager_status', $google_tag_status);
                }

                $message = system_showText(LANG_SITEMGR_GOOGLETAG_SETTINGSSUCCESSCHANGED);

                break;
            case "verification" :
                include(INCLUDES_DIR."/code/google_verification.php");
                break;
            case "recaptcha" :

                if( !empty( $google_recaptcha_status ) )
                {
                    if( empty( $google_recaptcha_sitekey ) )
                    {
                        MessageHandler::registerError( system_showText(LANG_SITEMGR_GOOGLERECAPTCHA_ERROR_SITEKEY) );
                    }

                    if( empty( $google_recaptcha_secretkey ) )
                    {
                        MessageHandler::registerError( system_showText(LANG_SITEMGR_GOOGLERECAPTCHA_ERROR_SECRETKEY) );
                    }
                }

                if( !MessageHandler::haveErrors() )
                {
                    if (!setting_set('google_recaptcha_secretkey', $google_recaptcha_secretkey)){
                        setting_new('google_recaptcha_secretkey', $google_recaptcha_secretkey);
                    }

                    if (!setting_set('google_recaptcha_sitekey', $google_recaptcha_sitekey)){
                        setting_new('google_recaptcha_sitekey', $google_recaptcha_sitekey);
                    }

                    $google_recaptcha_status = $google_recaptcha_status ? "on" : "off";

                    mixpanel_track(($google_recaptcha_status == "on" ? "Enabled" : "Disabled")." Google Recaptcha");

                    if (!setting_set('google_recaptcha_status', $google_recaptcha_status)){
                        setting_new('google_recaptcha_status', $google_recaptcha_status);
                    }

                    $message = system_showText(LANG_SITEMGR_GOOGLERECAPTCHA_SETTINGSSUCCESSCHANGED);
                }
                break;
        }

        if( !MessageHandler::haveErrors() )
        {
            MessageHandler::registerSuccess( $message );
        }

	}

	# ----------------------------------------------------------------------------------------------------
	# DEFINES
	# ----------------------------------------------------------------------------------------------------
	//Ads
    setting_get('google_ads_client', $google_ad_client);
    setting_get('google_ads_status', $google_ad_status);
    setting_get('google_ads_type', $google_ad_type);
    if (!$google_ad_type) {
        $google_ad_type = 0;
    }

    //Analytics
    setting_get('google_analytics_status', $google_analytics_account);
    setting_get('google_analytics_front', $google_analytics_front);
    setting_get('google_analytics_members', $google_analytics_members);
    setting_get('google_analytics_sitemgr', $google_analytics_sitemgr);

    //Maps
    setting_get('google_map_status', $google_maps_status);
    setting_get('google_api_key', $google_maps_key);

    //Tag
    setting_get('google_tagmanager_clientID', $google_tag_client);
    setting_get('google_tagmanager_status', $google_tag_status);

    //reCAPTCHA
    setting_get('google_recaptcha_status', $google_recaptcha_status);
    setting_get('google_recaptcha_sitekey', $google_recaptcha_sitekey);
    setting_get('google_recaptcha_secretkey', $google_recaptcha_secretkey);

    //Verification
    setting_get('google_webmaster_validation', $googleTag);
    $googleTag = html_entity_decode($googleTag);

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/header.php");

    # ----------------------------------------------------------------------------------------------------
	# NAVBAR
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/navbar.php");

    # ----------------------------------------------------------------------------------------------------
	# SIDEBAR
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/sidebar-configuration.php");

?>

    <main class="wrapper togglesidebar container-fluid">

        <?php
        require(SM_EDIRECTORY_ROOT."/registration.php");
        require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
        ?>

        <section class="heading">
            <h1><?=string_ucwords(system_showText(LANG_SITEMGR_NAVBAR_GOOGLESETTINGS))?></h1>
            <p><?=system_showText(LANG_SITEMGR_GOOGLEPREFS_TIP_1);?></p>
        </section>

            <?php MessageHandler::render(); ?>

        <div class="tab-options">

            <ul class="row nav nav-tabs" role="tablist">
                <li class="<?=($gtype == "maps" || !$_POST ? "active" : "")?>"><a href="#maps" role="tab" data-toggle="tab">Google Maps</a></li>
                <li class="<?=($gtype == "ads"             ? "active" : "")?>"><a href="#ads" role="tab" data-toggle="tab">Google Ads</a></li>
                <li class="<?=($gtype == "analytics"       ? "active" : "")?>"><a href="#analytics" role="tab" data-toggle="tab">Google Analytics</a></li>
                <li class="<?=($gtype == "tag"             ? "active" : "")?>"><a href="#tags" role="tab" data-toggle="tab">Google Tag Manager</a></li>
                <li class="<?=($gtype == "verification"    ? "active" : "")?>"><a href="#verification" role="tab" data-toggle="tab">Google Search Console</a></li>
                <li class="<?=($gtype == "recaptcha"       ? "active" : "")?>"><a href="#recaptcha" role="tab" data-toggle="tab">Google reCAPTCHA</a></li>
            </ul>

            <div class="row tab-content">
                <section id="maps" class="tab-pane <?=($gtype == "maps" || !$_POST ? "active" : "")?>">
                    <form name="googlemaps" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                        <input type="hidden" name="gtype" value="maps" />
                        <? include(INCLUDES_DIR."/forms/form-google-maps.php"); ?>
                    </form>
                </section>

                <section id="ads" class="tab-pane <?=($gtype == "ads" ? "active" : "")?>">
                    <form name="googleads" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                        <input type="hidden" name="gtype" value="ads" />
                        <? include(INCLUDES_DIR."/forms/form-google-ads.php"); ?>
                    </form>
                </section>

                <section id="analytics" class="tab-pane <?=($gtype == "analytics" ? "active" : "")?>">
                    <form name="googleanalytics" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                        <input type="hidden" name="gtype" value="analytics" />
                        <? include(INCLUDES_DIR."/forms/form-google-analytics.php"); ?>
                    </form>
                </section>

                <section id="tags" class="tab-pane <?=($gtype == "tag" ? "active" : "")?>">
                    <form name="googletag" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                        <input type="hidden" name="gtype" value="tag" />
                        <? include(INCLUDES_DIR."/forms/form-google-tags.php"); ?>
                    </form>
                </section>

                <section id="verification" class="tab-pane <?=($gtype == "verification" ? "active" : "")?>">
                    <form name="googleverification" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                        <input type="hidden" name="gtype" value="verification" />
                        <? include(INCLUDES_DIR."/forms/form-google-verification.php"); ?>
                    </form>
                </section>

                <section id="recaptcha" class="tab-pane <?=($gtype == "recaptcha" ? "active" : "")?>">
                    <form name="googlerecaptcha" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                        <input type="hidden" name="gtype" value="recaptcha" />
                        <? include(INCLUDES_DIR."/forms/form-google-recaptcha.php"); ?>
                    </form>
                </section>
            </div>

        </div>

    </main>

<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
