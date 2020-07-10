<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/mobile/appbuilder/finalstep.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include '../../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();
permission_hasSMPerm();

mixpanel_track("Accessed Final Step on App Builder section");

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
extract($_POST, null);
extract($_GET, null);

unset($domainObj);
$domainObj = new Domain(SELECTED_DOMAIN_ID);

$registeredDomain = $domainObj->getString("url");
$registeredDomainID = SELECTED_DOMAIN_ID;

$errorRegistration = true;
$isregisteredBin = isRegistered($registeredDomain, $registeredDomainID);
if ($isregisteredBin) {
    $errorRegistration = false;
}
$isregisteredBin2 = isRegistered($registeredDomain, $registeredDomainID);
if (!$isregisteredBin2) {
    $errorRegistration = true;
}

setting_get("appbuilder_app_name", $appname);
setting_get("appbuilder_icon_id", $appbuilder_icon_id);
setting_get("appbuilder_icon_extension", $appbuilder_icon_extension);
setting_get("appbuilder_splash_id", $appbuilder_splash_id);
setting_get("appbuilder_splash_extension", $appbuilder_splash_extension);
$hasIconImage = false;
$hasSplashImage = false;
if (file_exists(EDIRECTORY_ROOT."/".IMAGE_APPBUILDER_PATH."/appbuilder_icon_{$appbuilder_icon_id}.{$appbuilder_icon_extension}")) {
    $hasIconImage = true;
}
if (file_exists(EDIRECTORY_ROOT."/".IMAGE_APPBUILDER_PATH."/appbuilder_splash_{$appbuilder_splash_id}.{$appbuilder_splash_extension}")) {
    $hasSplashImage = true;
}

$pendingSteps = array();

if (!$appname) {
    $pendingSteps[] = "<a href=\"".DEFAULT_URL."/".SITEMGR_ALIAS."/mobile/appbuilder/index.php\">".system_showText(LANG_SITEMGR_APPNAME)."</a>";
}
if (!$hasIconImage) {
    $pendingSteps[] = "<a href=\"".DEFAULT_URL."/".SITEMGR_ALIAS."/mobile/appbuilder/index.php\">".system_showText(LANG_SITEMGR_APPICON_REQUIRED)."</a>";
}
if (!$hasSplashImage) {
    $pendingSteps[] = "<a href=\"".DEFAULT_URL."/".SITEMGR_ALIAS."/mobile/appbuilder/step2.php\">".system_showText(LANG_SITEMGR_APPSPLASH_REQUIRED)."</a>";
}

$skipProcess = false;
if (count($pendingSteps) > 0) {
    $skipProcess = true;
}

//Enable API automatically
if (!setting_set("edirectory_api_enabled", "on")) {
    setting_new("edirectory_api_enabled", "on");
}

setting_get("edirectory_api_key", $edirectory_api_key);

//Create new key if needed
if (!$edirectory_api_key) {
    $domainObj = new Domain(SELECTED_DOMAIN_ID);
    $domain = $domainObj->getString("url");
    $edir_key = getKey($domain);
    $edirectory_api_key_new = md5($domain.VERSION.$edir_key);

    unset($new_key);
    $j = 0;
    for($i = 0, $iMax = strlen($edirectory_api_key_new); $i < $iMax; $i++) {
        if ($j < 4) {
            $new_key .= substr($edirectory_api_key_new, $i, 1);
        } else {
            $new_key .= "-".substr($edirectory_api_key_new, $i, 1);
            $j = 0;
        }
        $j++;
    }

    if (!setting_set("edirectory_api_key", $new_key)) {
        setting_new("edirectory_api_key", $new_key);
    }
}

# ----------------------------------------------------------------------------------------------------
# HEADER
# ----------------------------------------------------------------------------------------------------
include SM_EDIRECTORY_ROOT.'/layout/header.php';

# ----------------------------------------------------------------------------------------------------
# NAVBAR
# ----------------------------------------------------------------------------------------------------
include SM_EDIRECTORY_ROOT.'/layout/navbar.php';

# ----------------------------------------------------------------------------------------------------
# SIDEBAR
# ----------------------------------------------------------------------------------------------------
include SM_EDIRECTORY_ROOT.'/layout/sidebar-mobile.php';

?>

    <main class="wrapper togglesidebar container-fluid">

        <section class="row heading">
            <div class="container">
                <h1><?=system_showText(LANG_SITEMGR_APPBUILDER_CONGRATS);?></h1>
                <p><?=system_showText(LANG_SITEMGR_APPBUILDER_CONGRATS_TIP);?></p>
            </div>
        </section>

        <section class="row appbuilder">
            <div class="appbuilder-container">

                <?php
                require EDIRECTORY_ROOT.'/'.SITEMGR_ALIAS.'/registration.php';
                require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';;

                /*  Navbar  */
                include 'navbar.php';
                ?>

                <section class="container">

                    <?php if ($errorRegistration) { ?>
                        <p class="errorMessage"><?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_ACTIVATIONISREQUIRED)?></p>
                    <?php } else {

                        if (MOBILE_FEATURE == "on") { ?>

                            <h4><?=system_showText(LANG_SITEMGR_WHERE_DO_I_GO_NEXT)?></h4><br>
                            <p><?=system_showText(LANG_SITEMGR_CONGRATULATIONS_MESSAGE)?></p>
                            <p><?=system_showText(LANG_SITEMGR_CONGRATULATIONS_MESSAGE2)?></p>

                            <div id="reviewSteps" class="alert alert-warning" style="display:none;">
                                <?=system_showText(LANG_SITEMGR_COMPLETE_STEPS);?><br>
                                <?=implode("<br />", $pendingSteps);?>
                            </div>

                            <div class="grid-group">
                                <div class="span100 text-center">
                                    <div class="hidden-sm hidden-xs">
                                        <a data-mixpanel-event='Clicked on button "Build & Submit" from App Builder section' class="btn btn-default" href="javascript:void(0);" onclick="<?=DEMO_LIVE_MODE ? "livemodeMessage(true, false);" : ($errorRegistration ? "livemodeMessage(true, 2);" : ($skipProcess ? "boxReviewSteps();" : "window.open('".DEFAULT_URL."/".SITEMGR_ALIAS."/mobile/appbuilder/connect.php', '_blank')"))?>"><?=system_showText(LANG_SITEMGR_BUILD_AND_SUBMIT2);?></a>
                                    </div>
                                    <div class="visible-sm visible-xs">
                                        <div class="alert alert-danger"><?=system_showText(LANG_SITEMGR_NOT_RESPONSIVE)?></div>
                                    </div>
                                </div>
                            </div>

                        <?php } else { ?>
                            <div class="active-your-plan">
                                <h3><?=system_showText(LANG_SITEMGR_UPGRADE_PLAN_BUILD)?></h3>
                                <p><?=system_showText(LANG_SITEMGR_UPGRADE_MESSAGE)?></p>
                                <a data-mixpanel-event='Clicked on button "Upgrade my plan" from App Builder section' href="http://edirectory.com/orders/" target="_blank" class="btn btn-primary"><?=system_showText(LANG_SITEMGR_UPGRADE_MY_PLAN)?></a>
                            </div>
                        <?php } ?>

                    <?php } ?>

                </section>

            </div>

        </section>

    </main>

<?php
# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
$customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/appbuilder.php';
include SM_EDIRECTORY_ROOT.'/layout/footer.php';
