<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/support/reset.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# THIS PAGE IS ONLY USED BY THE SUPPORT
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include '../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();

if (!permission_hasSMPermSection(SITEMGR_PERMISSION_SUPERADMIN)) {
    header('Location: '.DEFAULT_URL.'/'.SITEMGR_ALIAS.'/');
    exit;
}

$url_redirect = DEFAULT_URL.'/'.SITEMGR_ALIAS.'/support/reset.php';
extract($_GET);
extract($_POST);

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
if ($action) {

    switch ($action) {
        case 'sitemgr':
            if ($sitemgrpass) {
                $pwDBObj = db_getDBObject(DEFAULT_DB, true);
                $sql = 'UPDATE Setting SET value = '.db_formatString(md5($sitemgrpass))." WHERE name = 'sitemgr_password'";
                $pwDBObj->query($sql);
            }
            if ($sitemgrusername) {
                $pwDBObj = db_getDBObject(DEFAULT_DB, true);
                $sql = 'UPDATE Setting SET value = '.db_formatString($sitemgrusername)." WHERE name = 'sitemgr_username'";
                $pwDBObj->query($sql);
            }
            break;
        case 'langFiles':
            $langObj = new Lang();
            $langObj->writeLanguageFile();

            if (!setting_set('configChecker_lang', 'on')) {
                if (!setting_new('configChecker_lang', 'on')) {
                    $error = true;
                }
            }
            break;
        case 'signIn':
            if (!setting_set('foreignaccount_google', '')) {
                if (!setting_new('foreignaccount_google', '')) {
                    $error = true;
                }
            }

            if (!setting_set('foreignaccount_facebook', '')) {
                if (!setting_new('foreignaccount_facebook', '')) {
                    $error = true;
                }
            }

            if (!setting_set('foreignaccount_facebook_apisecret', '')) {
                if (!setting_new('foreignaccount_facebook_apisecret', '')) {
                    $error = true;
                }
            }

            if (!setting_set('foreignaccount_facebook_apiid', '')) {
                if (!setting_new('foreignaccount_facebook_apiid', '')) {
                    $error = true;
                }
            }

            if (!setting_set('configChecker_signIn', 'on')) {
                if (!setting_new('configChecker_signIn', 'on')) {
                    $error = true;
                }
            }
            break;
        case 'twitter':
            if (!setting_set('twitter_account', '')) {
                if (!setting_new('twitter_account', '')) {
                    $error = true;
                }
            }

            if (!setting_set('configChecker_twitter', 'on')) {
                if (!setting_new('configChecker_twitter', 'on')) {
                    $error = true;
                }
            }
            break;
        case 'fbComments':
            if (!setting_set('commenting_fb', '')) {
                if (!setting_new('commenting_fb', '')) {
                    $error = true;
                }
            }

            if (!setting_set('foreignaccount_facebook_apiid', '')) {
                if (!setting_new('foreignaccount_facebook_apiid', '')) {
                    $error = true;
                }
            }

            if (!setting_set('commenting_fb_user_id', '')) {
                if (!setting_new('commenting_fb_user_id', '')) {
                    $error = true;
                }
            }

            if (!setting_set('configChecker_fbComments', 'on')) {
                if (!setting_new('configChecker_fbComments', 'on')) {
                    $error = true;
                }
            }
            break;
        case 'gmaps':
            if (!setting_set('google_api_key', '')){
                setting_new('google_api_key', '');
            }
            if (!setting_set('google_map_status', 'off')){
                setting_new('google_map_status', 'off');
            }

            if (!setting_set('configChecker_gmaps', 'on')) {
                if (!setting_new('configChecker_gmaps', 'on')) {
                    $error = true;
                }
            }
            break;
        case 'gads':
            if (!setting_set('google_ads_status', 'off')){
                setting_new('google_ads_status', 'off');
            }
            if (!setting_set('google_ads_client', '')){
                setting_new('google_ads_client', '');
            }

            if (!setting_set('configChecker_gads', 'on')) {
                if (!setting_new('configChecker_gads', 'on')) {
                    $error = true;
                }
            }
            break;
        case 'ganalytics':
            if (!setting_set('google_ads_client', '')){
                setting_new('google_ads_client', '');
            }
            if (!setting_set('google_analytics_front', '')){
                setting_new('google_analytics_front', '');
            }
            if (!setting_set('google_analytics_members', '')){
                setting_new('google_analytics_members', '');
            }
            if (!setting_set('google_analytics_sitemgr', '')){
                setting_new('google_analytics_sitemgr', '');
            }

            if (!setting_set('configChecker_ganalytics', 'on')) {
                if (!setting_new('configChecker_ganalytics', 'on')) {
                    $error = true;
                }
            }
            break;
        case 'footer':
            if (!setting_set('setting_linkedin_link', '')) {
                if (!setting_new('setting_linkedin_link', '')) {
                    $error = true;
                }
            }
            if (!setting_set('setting_facebook_link', '')) {
                if (!setting_new('setting_facebook_link', '')) {
                    $error = true;
                }
            }

            if (!setting_set('configChecker_footer', 'on')) {
                if (!setting_new('configChecker_footer', 'on')) {
                    $error = true;
                }
            }
            break;
        case 'systemEmail':
            if (!setting_set('sitemgr_email', '')) {
                if (!setting_new('sitemgr_email', '')) {
                    $error = true;
                }
            }

            if (!setting_set('sitemgr_send_email', '')) {
                if (!setting_new('sitemgr_send_email', '')) {
                    $error = true;
                }
            }

            if (!setting_set('configChecker_systemEmail', 'on')) {
                if (!setting_new('configChecker_systemEmail', 'on')) {
                    $error = true;
                }
            }
            break;
        case 'todoItems':
            $dbObjMain = db_getDBObject(DEFAULT_DB, true);
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbObjMain);
            $sql = "UPDATE Setting SET value = 'yes' WHERE name LIKE '%todo_%'";
            $dbObj->query($sql);
            $sql = "UPDATE Setting SET value = '0' WHERE name = 'percentage_todo'";
            $dbObj->query($sql);
            break;
    }

    if ($error) {
        $errorMessage = 'System error!';
    } elseif ($errorFolder) {
        $errorMessage = 'Wrong permissions on custom folder!';
    } else {
        header('Location: '.$url_redirect.'?message=ok');
        exit;
    }
}

# ----------------------------------------------------------------------------------------------------
# FORMS DEFINES
# ----------------------------------------------------------------------------------------------------
setting_get('sitemgr_username', $sm_username);
setting_get('configChecker_lang', $configChecker_lang);
setting_get('configChecker_signIn', $configChecker_signIn);
setting_get('configChecker_twitter', $configChecker_twitter);
setting_get('configChecker_fbComments', $configChecker_fbComments);
setting_get('configChecker_gmaps', $configChecker_gmaps);
setting_get('configChecker_gads', $configChecker_gads);
setting_get('configChecker_ganalytics', $configChecker_ganalytics);
setting_get('configChecker_footer', $configChecker_footer);
setting_get('configChecker_systemEmail', $configChecker_systemEmail);
setting_get('configChecker_smtpEmail', $configChecker_smtpEmail);

//SignIn Options
setting_get('foreignaccount_facebook', $foreignaccount_facebook);
setting_get('foreignaccount_facebook_apisecret', $foreignaccount_facebook_apisecret);
setting_get('foreignaccount_facebook_apiid', $foreignaccount_facebook_apiid);
setting_get('foreignaccount_google', $foreignaccount_google);

//Twitter Options
setting_get('twitter_account', $twitter_account);

//Facebook comments options
setting_get('commenting_fb', $commenting_fb);
setting_get('foreignaccount_facebook_apiid', $foreignaccount_facebook_apiid);
setting_get('commenting_fb_user_id', $fb_user_id);

//Google Maps
setting_get('google_map_status', $google_maps);
setting_get('google_api_key', $google_maps_key);

//Google Ads
setting_get('google_ads_client', $google_ad_client);
setting_get('google_ads_status', $google_ad_status );

//Google Analytics
setting_get('google_analytics_status', $google_analytics_account);
setting_get('google_analytics_front', $google_analytics_front);
setting_get('google_analytics_members', $google_analytics_members);
setting_get('google_analytics_sitemgr', $google_analytics_sitemgr);

//Footer Links
setting_get('setting_linkedin_link', $setting_linkedin_link);
setting_get('setting_facebook_link', $setting_facebook_link);

//Sitemgr General E-mail
setting_get('sitemgr_email', $sitemgr_email);
setting_get('sitemgr_send_email', $send_email);

if (!$configChecker_lang) {
    $onclickLang = "onclick=\"resetOption('".$url_redirect."?action=langFiles');\"";
    $classLang = '';
} else {
    $onclickLang = 'onclick="javascript: void(0);"';
    $classLang = 'setup_done';
}

if (!$configChecker_signIn) {
    $onclicksignIn = "onclick=\"resetOption('".$url_redirect."?action=signIn');\"";
    $classsignIn = '';
} else {
    $onclicksignIn = 'onclick="javascript: void(0);"';
    $classsignIn = 'setup_done';
}

if (!$configChecker_twitter) {
    $onclicktwitter = "onclick=\"resetOption('".$url_redirect."?action=twitter');\"";
    $classtwitter = '';
} else {
    $onclicktwitter = 'onclick="javascript: void(0);"';
    $classtwitter = 'setup_done';
}

if (!$configChecker_fbComments) {
    $onclickfbComments = "onclick=\"resetOption('".$url_redirect."?action=fbComments');\"";
    $classfbComments = '';
} else {
    $onclickfbComments = 'onclick="javascript: void(0);"';
    $classfbComments = 'setup_done';
}

if (!$configChecker_gmaps) {
    $onclickgmaps = "onclick=\"resetOption('".$url_redirect."?action=gmaps');\"";
    $classgmaps = '';
} else {
    $onclickgmaps = 'onclick="javascript: void(0);"';
    $classgmaps = 'setup_done';
}

if (!$configChecker_gads) {
    $onclickgads = "onclick=\"resetOption('".$url_redirect."?action=gads');\"";
    $classgads = '';
} else {
    $onclickgads = 'onclick="javascript: void(0);"';
    $classgads = 'setup_done';
}

if (!$configChecker_ganalytics) {
    $onclickganalytics = "onclick=\"resetOption('".$url_redirect."?action=ganalytics');\"";
    $classganalytics = '';
} else {
    $onclickganalytics = 'onclick="javascript: void(0);"';
    $classganalytics = 'setup_done';
}

if (!$configChecker_footer) {
    $onclickfooter = "onclick=\"resetOption('".$url_redirect."?action=footer');\"";
    $classfooter = '';
} else {
    $onclickfooter = 'onclick="javascript: void(0);"';
    $classfooter = 'setup_done';
}

if (!$configChecker_systemEmail) {
    $onclicksystemEmail = "onclick=\"resetOption('".$url_redirect."?action=systemEmail');\"";
    $classsystemEmail = '';
} else {
    $onclicksystemEmail = 'onclick="javascript: void(0);"';
    $classsystemEmail = 'setup_done';
}

if (!$configChecker_smtpEmail) {
    $onclicksmtpEmail = "onclick=\"resetOption('".$url_redirect."?action=smtpEmail');\"";
    $classsmtpEmail = '';
} else {
    $onclicksmtpEmail = 'onclick="javascript: void(0);"';
    $classsmtpEmail = 'setup_done';
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
include SM_EDIRECTORY_ROOT.'/layout/sidebar-support.php';

?>

    <main class="wrapper-dashboard togglesidebar container-fluid">

        <? require EDIRECTORY_ROOT.'/'.SITEMGR_ALIAS.'/registration.php'; ?>
        <? require EDIRECTORY_ROOT.'/includes/code/checkregistration.php'; ?>

        <section class="heading">

            <h1>Reset Settings</h1>

            <? if ($errorMessage) { ?>
                <p class="alert alert-warning"><?= $errorMessage ?></p>
            <? } elseif ($_GET['message'] == 'ok') { ?>
                <p class="alert alert-success">Settings changed!</p>
            <? } ?>

        </section>

        <section class="row section-form">
            <form role="form" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
                <? include INCLUDES_DIR.'/forms/form-support-reset.php'; ?>
            </form>
        </section>

    </main>

<?php
# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
$customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/support.php';
include SM_EDIRECTORY_ROOT.'/layout/footer.php';