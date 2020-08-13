<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/configuration/general-settings/index.php
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

mixpanel_track('Accessed section General Settings');

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------

extract($_POST);
extract($_GET);

$dbMain = db_getDBObject(DEFAULT_DB, true);
$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = false;
    $success = false;

    // Promotion Default Condition
    if ($promotionDefaults) {

        mixpanel_track('Changed Deal Default Conditions text');

        ((setting_set('deal_default_conditions', $deal_default_conditions)
                or setting_new('deal_default_conditions', $deal_default_conditions)) and
            $success = true)
        or $error = true;
    }

    //eDirectory API
    if ($api) {

        $domain = new Domain(SELECTED_DOMAIN_ID);
        $symfony = new Symfony('domain.yml');

        mixpanel_track(($edirectory_api_enabled ? 'Enabled' : 'Disabled').' eDirectory API');

        if (!setting_set('edirectory_api_enabled', $edirectory_api_enabled)) {
            setting_new('edirectory_api_enabled', $edirectory_api_enabled);
        }

        setting_get('edirectory_api_key', $aux_edirectory_api_key);

        /*
         * Workaround to avoid api key being replaced after the apps were already published.
         */

        if (!$aux_edirectory_api_key) {
            if (!setting_set('edirectory_api_key', $edirectory_api_key)) {
                setting_new('edirectory_api_key', $edirectory_api_key);
            }
        }

        $edirectory_api_enabled == 'on' and $symfony->save('api_tokens', [
            $domain->getString('url') => $edirectory_api_key,
        ]);

        $success = true;

    }

    //MaintenanceMode
    if ($maintenance && !DEMO_LIVE_MODE) {

        if ($maintenance_mode) {
            $maintenance_mode = 'on';
        } else {
            $maintenance_mode = 'off';
        }

        mixpanel_track(($maintenance_mode == 'on' ? 'Enabled' : 'Disabled').' Maintenance Mode');

        if (!setting_set('maintenance_mode', $maintenance_mode)) {
            setting_new('maintenance_mode', $maintenance_mode);
        }

        $success = true;
    }

    //Approval Requirement
    if ($approvalrequirement) {

        mixpanel_track('Changed Approval Requirements options', [
                'Need to approve Listing is paid'                => $listing_approve_paid,
                'Need to approve Free listing is added'          => $listing_approve_free,
                'Need to approve Existing listing is updated'    => $listing_approve_updated,
                'Send email New listing is added'                => $new_listing_email,
                'Send email Existing listing is updated'         => $update_listing_email,
                'Need to approve Event is paid'                  => $event_approve_paid,
                'Need to approve Free event is added'            => $event_approve_free,
                'Need to approve Existing event is updated'      => $event_approve_updated,
                'Send email New event is added'                  => $new_event_email,
                'Send email Existing event is updated'           => $update_event_email,
                'Need to approve Classified is paid'             => $classified_approve_paid,
                'Need to approve Free classified is added'       => $classified_approve_free,
                'Need to approve Existing classified is updated' => $classified_approve_updated,
                'Send email New classified is added'             => $new_classified_email,
                'Send email Existing classified is updated'      => $update_classified_email,
                'Need to approve Article is paid'                => $article_approve_paid,
                'Need to approve Free article is added'          => $article_approve_free,
                'Need to approve Existing article is updated'    => $article_approve_updated,
                'Send email New article is added'                => $new_article_email,
                'Send email Existing article is updated'         => $update_article_email,
                'Need to approve Banner is paid'                 => $banner_approve_paid,
                'Need to approve Free banner is added'           => $banner_approve_free,
                'Need to approve Existing banner is updated'     => $banner_approve_updated,
                'Send email New banner is added'                 => $new_banner_email,
                'Send email Existing banner is updated'          => $update_banner_email,
            ]
        );

        if (!setting_set('listing_approve_paid', $listing_approve_paid)) {
            if (!setting_new('listing_approve_paid', $listing_approve_paid)) {
                $error = true;
            }
        }
        if (!setting_set('listing_approve_free', $listing_approve_free)) {
            if (!setting_new('listing_approve_free', $listing_approve_free)) {
                $error = true;
            }
        }
        if (!setting_set('listing_approve_updated', $listing_approve_updated)) {
            if (!setting_new('listing_approve_updated', $listing_approve_updated)) {
                $error = true;
            }
        }
        if (!setting_set('new_listing_email', $new_listing_email)) {
            if (!setting_new('new_listing_email', $new_listing_email)) {
                $error = true;
            }
        }

        if (!setting_set('update_listing_email', $update_listing_email)) {
            if (!setting_new('update_listing_email', $update_listing_email)) {
                $error = true;
            }
        }

        if (!setting_set('event_approve_paid', $event_approve_paid)) {
            if (!setting_new('event_approve_paid', $event_approve_paid)) {
                $error = true;
            }
        }
        if (!setting_set('event_approve_free', $event_approve_free)) {
            if (!setting_new('event_approve_free', $event_approve_free)) {
                $error = true;
            }
        }
        if (!setting_set('event_approve_updated', $event_approve_updated)) {
            if (!setting_new('event_approve_updated', $event_approve_updated)) {
                $error = true;
            }
        }
        if (!setting_set('new_event_email', $new_event_email)) {
            if (!setting_new('new_event_email', $new_event_email)) {
                $error = true;
            }
        }

        if (!setting_set('update_event_email', $update_event_email)) {
            if (!setting_new('update_event_email', $update_event_email)) {
                $error = true;
            }
        }
        if (!setting_set('classified_approve_paid', $classified_approve_paid)) {
            if (!setting_new('classified_approve_paid', $classified_approve_paid)) {
                $error = true;
            }
        }
        if (!setting_set('classified_approve_free', $classified_approve_free)) {
            if (!setting_new('classified_approve_free', $classified_approve_free)) {
                $error = true;
            }
        }
        if (!setting_set('classified_approve_updated', $classified_approve_updated)) {
            if (!setting_new('classified_approve_updated', $classified_approve_updated)) {
                $error = true;
            }
        }
        if (!setting_set('new_classified_email', $new_classified_email)) {
            if (!setting_new('new_classified_email', $new_classified_email)) {
                $error = true;
            }
        }

        if (!setting_set('update_classified_email', $update_classified_email)) {
            if (!setting_new('update_classified_email', $update_classified_email)) {
                $error = true;
            }
        }
        if (!setting_set('article_approve_paid', $article_approve_paid)) {
            if (!setting_new('article_approve_paid', $article_approve_paid)) {
                $error = true;
            }
        }
        if (!setting_set('article_approve_free', $article_approve_free)) {
            if (!setting_new('article_approve_free', $article_approve_free)) {
                $error = true;
            }
        }
        if (!setting_set('article_approve_updated', $article_approve_updated)) {
            if (!setting_new('article_approve_updated', $article_approve_updated)) {
                $error = true;
            }
        }
        if (!setting_set('new_article_email', $new_article_email)) {
            if (!setting_new('new_article_email', $new_article_email)) {
                $error = true;
            }
        }

        if (!setting_set('update_article_email', $update_article_email)) {
            if (!setting_new('update_article_email', $update_article_email)) {
                $error = true;
            }
        }
        if (!setting_set('banner_approve_paid', $banner_approve_paid)) {
            if (!setting_new('banner_approve_paid', $banner_approve_paid)) {
                $error = true;
            }
        }
        if (!setting_set('banner_approve_free', $banner_approve_free)) {
            if (!setting_new('banner_approve_free', $banner_approve_free)) {
                $error = true;
            }
        }
        if (!setting_set('banner_approve_updated', $banner_approve_updated)) {
            if (!setting_new('banner_approve_updated', $banner_approve_updated)) {
                $error = true;
            }
        }
        if (!setting_set('new_banner_email', $new_banner_email)) {
            if (!setting_new('new_banner_email', $new_banner_email)) {
                $error = true;
            }
        }

        if (!setting_set('update_banner_email', $update_banner_email)) {
            if (!setting_new('update_banner_email', $update_banner_email)) {
                $error = true;
            }
        }

        $success = true;

    }

    //Claim
    if ($claim) {

        mixpanel_track('Changed Claim options', [
                'Need Approval'       => $claim_approve,
                'Return to front'     => $claim_deny,
                'Send Approval Email' => $claim_approveemail,
                'Send Denying Email'  => $claim_denyemail
            ]
        );


        if (!setting_set('claim_approve', $claim_approve)) {
            if (!setting_new('claim_approve', $claim_approve)) {
                $error = true;
            }
        }
        if (!setting_set('claim_deny', $claim_deny)) {
            if (!setting_new('claim_deny', $claim_deny)) {
                $error = true;
            }
        }
        if (!setting_set('claim_approveemail', $claim_approveemail)) {
            if (!setting_new('claim_approveemail', $claim_approveemail)) {
                $error = true;
            }
        }
        if (!setting_set('claim_denyemail', $claim_denyemail)) {
            if (!setting_new('claim_denyemail', $claim_denyemail)) {
                $error = true;
            }
        }

        if (trim($claim_textlink) == '') {
            $claim_textlink = 'Is this your '.LISTING_FEATURE_NAME.'?';
        }

        if (!setting_set('claim_textlink', $claim_textlink)) {
            if (!setting_new('claim_textlink', $claim_textlink)) {
                $error = true;
            }
        }

        $success = true;
    }

    //Available Modules
    if ($modules_options && !DEMO_LIVE_MODE) {

        mixpanel_track('Changed Modules options', [
                'Event'      => $check_event_feature,
                'Classified' => $check_classified_feature,
                'Article'    => $check_article_feature,
                'Banner'     => $check_banner_feature,
                'Deal'       => $check_promotion_feature,
                'Blog'       => $check_blog_feature]
        );

        if (ARTICLE_FEATURE == 'on') {
            if (!setting_set('custom_article_feature', $check_article_feature)) {
                if (!setting_new('custom_article_feature', $check_article_feature)) {
                    $error = true;
                }
            }
        }

        if (BANNER_FEATURE == 'on') {
            if (!setting_set('custom_banner_feature', $check_banner_feature)) {
                if (!setting_new('custom_banner_feature', $check_banner_feature)) {
                    $error = true;
                }
            }
        }

        if (BLOG_FEATURE == 'on') {
            if (!setting_set('custom_blog_feature', $check_blog_feature)) {
                if (!setting_new('custom_blog_feature', $check_blog_feature)) {
                    $error = true;
                }
            }
        }

        if (CLASSIFIED_FEATURE == 'on') {
            if (!setting_set('custom_classified_feature', $check_classified_feature)) {
                if (!setting_new('custom_classified_feature', $check_classified_feature)) {
                    $error = true;
                }
            }
        }

        if (EVENT_FEATURE == 'on') {
            if (!setting_set('custom_event_feature', $check_event_feature)) {
                if (!setting_new('custom_event_feature', $check_event_feature)) {
                    $error = true;
                }
            }
        }

        if (PROMOTION_FEATURE == 'on') {
            if (!setting_set('custom_promotion_feature', $check_promotion_feature)) {
                if (!setting_new('custom_promotion_feature', $check_promotion_feature)) {
                    $error = true;
                }
            }
        }

        // Saves yaml
        // @todo navigation
        $domain = new Domain(SELECTED_DOMAIN_ID);
        $symfony = new Symfony('domains/'.$domain->getString('url').'.configs.yml');

        $modules = [];
        $modules_array = ['article', 'banner', 'blog', 'classified', 'event', 'promotion'];
        foreach ($modules_array as $module) {
            if (!is_null(${'check_'.$module.'_feature'})) {
                $modules['modules.available'][] = str_replace('promotion', 'deal', $module);
            }
        }
        $modules['modules.available'] = count($modules['modules.available']) ? implode(',',
            $modules['modules.available']) : '';

        $symfony->save('Configs', ['parameters' => $modules]);

        $success = true;

    }

    //Visitor profile settings
    if ($visitor) {
        $visitorprofile = filter_input(INPUT_POST, 'socialnetwork_feature') ?: 'off';

        mixpanel_track('Changed Visitor Profile options', [
                'Profile' => $visitorprofile
            ]
        );

        setting_set('socialnetwork_feature', $visitorprofile);

        if (!is_dir(EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/socialnetwork')) {
            mkdir(EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/socialnetwork');
        }

        $file = EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/socialnetwork/socialnetwork.inc.php';
        $file = fopen($file, 'w+');

        $buffer = '<?'.PHP_EOL;
        $buffer .= '	define("SOCIALNETWORK_FEATURE", "'.$visitorprofile.'");'.PHP_EOL;
        $buffer .= '?>'.PHP_EOL;

        fwrite($file, $buffer, strlen($buffer));
        fclose($file);

        $success = true;
    }

    //Comments and Reviews options
    if ($reviews) {

        $listing_login_review = filter_input(INPUT_POST, 'listing_login_review');

        mixpanel_track('Changed Reviews options', [
                'Listing'                => $review_listing_enabled,
                'Needs Approval'         => $review_approve,
                'Login to Rate Listings' => $listing_login_review,
            ]
        );

        if (!setting_set('review_listing_enabled', $review_listing_enabled)) {
            if (!setting_new('review_listing_enabled', $review_listing_enabled)) {
                $error = true;
            }
        }

        if (!setting_set('review_approve', $review_approve)) {
            if (!setting_new('review_approve', $review_approve)) {
                $error = true;
            }
        }

        if (!setting_set('listing_login_review', $listing_login_review)) {
            if (!setting_new('listing_login_review', $listing_login_review)) {
                $error = true;
            }
        }

        $success = true;
    }

    //productLinks
    if ($productLinks) {

        if (!setting_set('product_link_one', $product_link_one)) {
            if (!setting_new('product_link_one', $product_link_one)) {
                $error = true;
            }
        }

        if (!setting_set('product_link_two', $product_link_two)) {
            if (!setting_new('product_link_two', $product_link_two)) {
                $error = true;
            }
        }

        if (!setting_set('product_link_three', $product_link_three)) {
            if (!setting_new('product_link_three', $product_link_three)) {
                $error = true;
            }
        }

        if (!setting_set('product_promo_code', $product_promo_code)) {
            if (!setting_new('product_promo_code', $product_promo_code)) {
                $error = true;
            }
        }

        if (!setting_set('product_promo_text', $product_promo_text)) {
            if (!setting_new('product_promo_text', $product_promo_text)) {
                $error = true;
            }
        }

    }

    if ($stripLocalsSettings) {

        if (!setting_set('stripe_pub_key', trim($stripe_pub_key))) {
            if (!setting_new('stripe_pub_key', trim($stripe_pub_key))) {
                $error = true;
            }
        }

        if (!setting_set('locals_price_id', trim($locals_price_id))) {
            if (!setting_new('locals_price_id', trim($locals_price_id))) {
                $error = true;
            }
        }

        if (!setting_set('locals_price_text', trim($locals_price_text))) {
            if (!setting_new('locals_price_text', trim($locals_price_text))) {
                $error = true;
            }
        }

        if (!setting_set('locals_price_id_2', trim($locals_price_id_2))) {
            if (!setting_new('locals_price_id_2', trim($locals_price_id_2))) {
                $error = true;
            }
        }

        if (!setting_set('locals_price_text_2', trim($locals_price_text_2))) {
            if (!setting_new('locals_price_text_2', trim($locals_price_text_2))) {
                $error = true;
            }
        }
    }

    /* ModStores Hooks */
    HookFire('generalsettings_after_save', [
        'success'         => &$success,
        'error'           => &$error,
        'http_post_array' => &$_POST,
        'http_get_array'  => &$_GET
    ]);
}

# ----------------------------------------------------------------------------------------------------
# FORMS DEFINES
# ----------------------------------------------------------------------------------------------------
// Promotion Default Condition
setting_get('deal_default_conditions', $deal_default_conditions);

//eDirectory API
setting_get('edirectory_api_key', $edirectory_api_key);
setting_get('edirectory_api_enabled', $edirectory_api_enabled);
if ($edirectory_api_enabled) {
    $edirectory_api_enabled_checked = 'checked';
}

//Generate new eDirectory API key
$domainObj = new Domain(SELECTED_DOMAIN_ID);
$domain = $domainObj->getString('url');
$edir_key = getKey($domain);
if (!$edirectory_api_key) {
    $edirectory_api_key_new = md5($domain.VERSION.$edir_key);

    unset($new_key);
    $j = 0;
    for ($i = 0; $i < strlen($edirectory_api_key_new); $i++) {
        if ($j < 4) {
            $new_key .= substr($edirectory_api_key_new, $i, 1);
        } else {
            $new_key .= '-'.substr($edirectory_api_key_new, $i, 1);
            $j = 0;
        }
        $j++;
    }
    $edirectory_api_key_new = $new_key;
} else {
    $edirectory_api_key_new = $edirectory_api_key;
}

//Maintenance mode
setting_get('maintenance_mode', $maintenance_mode);

//Approval Requirements
setting_get('listing_approve_paid', $listing_approve_paid);
if ($listing_approve_paid) {
    $listing_approve_paid_checked = 'checked';
}

setting_get('listing_approve_free', $listing_approve_free);
if ($listing_approve_free) {
    $listing_approve_free_checked = 'checked';
}

setting_get('listing_approve_updated', $listing_approve_updated);
if ($listing_approve_updated) {
    $listing_approve_updated_checked = 'checked';
}

setting_get('new_listing_email', $new_listing_email);
if ($new_listing_email) {
    $new_listing_email_checked = 'checked';
}

setting_get('update_listing_email', $update_listing_email);
if ($update_listing_email) {
    $update_listing_email_checked = 'checked';
}

setting_get('article_approve_paid', $article_approve_paid);
if ($article_approve_paid) {
    $article_approve_paid_checked = 'checked';
}

setting_get('article_approve_free', $article_approve_free);
if ($article_approve_free) {
    $article_approve_free_checked = 'checked';
}

setting_get('article_approve_updated', $article_approve_updated);
if ($article_approve_updated) {
    $article_approve_updated_checked = 'checked';
}

setting_get('new_article_email', $new_article_email);
if ($new_article_email) {
    $new_article_email_checked = 'checked';
}

setting_get('update_article_email', $update_article_email);
if ($update_article_email) {
    $update_article_email_checked = 'checked';
}

setting_get('classified_approve_paid', $classified_approve_paid);
if ($classified_approve_paid) {
    $classified_approve_paid_checked = 'checked';
}

setting_get('classified_approve_free', $classified_approve_free);
if ($classified_approve_free) {
    $classified_approve_free_checked = 'checked';
}

setting_get('classified_approve_updated', $classified_approve_updated);
if ($classified_approve_updated) {
    $classified_approve_updated_checked = 'checked';
}

setting_get('new_classified_email', $new_classified_email);
if ($new_classified_email) {
    $new_classified_email_checked = 'checked';
}

setting_get('update_classified_email', $update_classified_email);
if ($update_classified_email) {
    $update_classified_email_checked = 'checked';
}

setting_get('event_approve_paid', $event_approve_paid);
if ($event_approve_paid) {
    $event_approve_paid_checked = 'checked';
}

setting_get('event_approve_free', $event_approve_free);
if ($event_approve_free) {
    $event_approve_free_checked = 'checked';
}

setting_get('event_approve_updated', $event_approve_updated);
if ($event_approve_updated) {
    $event_approve_updated_checked = 'checked';
}

setting_get('new_event_email', $new_event_email);
if ($new_event_email) {
    $new_event_email_checked = 'checked';
}

setting_get('update_event_email', $update_event_email);
if ($update_event_email) {
    $update_event_email_checked = 'checked';
}

setting_get('banner_approve_paid', $banner_approve_paid);
if ($banner_approve_paid) {
    $banner_approve_paid_checked = 'checked';
}

setting_get('banner_approve_free', $banner_approve_free);
if ($banner_approve_free) {
    $banner_approve_free_checked = 'checked';
}

setting_get('banner_approve_updated', $banner_approve_updated);
if ($banner_approve_updated) {
    $banner_approve_updated_checked = 'checked';
}

setting_get('new_banner_email', $new_banner_email);
if ($new_banner_email) {
    $new_banner_email_checked = 'checked';
}

setting_get('update_banner_email', $update_banner_email);
if ($update_banner_email) {
    $update_banner_email_checked = 'checked';
}

$approvalModules = [];
$approvalModules[] = 'listing';
if (EVENT_FEATURE == 'on' && CUSTOM_EVENT_FEATURE == 'on') {
    $approvalModules[] = 'event';
}
if (CLASSIFIED_FEATURE == 'on' && CUSTOM_CLASSIFIED_FEATURE == 'on') {
    $approvalModules[] = 'classified';
}
if (ARTICLE_FEATURE == 'on' && CUSTOM_ARTICLE_FEATURE == 'on') {
    $approvalModules[] = 'article';
}
if (BANNER_FEATURE == 'on' && CUSTOM_BANNER_FEATURE == 'on') {
    $approvalModules[] = 'banner';
}

//Claim
setting_get('claim_approve', $claim_approve);
if ($claim_approve) {
    $claim_approve_checked = 'checked';
}
setting_get('claim_deny', $claim_deny);
if ($claim_deny) {
    $claim_deny_checked = 'checked';
}
setting_get('claim_approveemail', $claim_approveemail);
if ($claim_approveemail) {
    $claim_approveemail_checked = 'checked';
}
setting_get('claim_denyemail', $claim_denyemail);
if ($claim_denyemail) {
    $claim_denyemail_checked = 'checked';
}
setting_get('claim_textlink', $claim_textlink);

//Modules
setting_get('custom_article_feature', $check_article_feature);
if ($check_article_feature) {
    $custom_article_feature_checked = 'checked';
}
setting_get('custom_banner_feature', $check_banner_feature);
if ($check_banner_feature) {
    $custom_banner_feature_checked = 'checked';
}
setting_get('custom_blog_feature', $check_blog_feature);
if ($check_blog_feature) {
    $custom_blog_feature_checked = 'checked';
}
setting_get('custom_classified_feature', $check_classified_feature);
if ($check_classified_feature) {
    $custom_classified_feature_checked = 'checked';
}
setting_get('custom_event_feature', $check_event_feature);
if ($check_event_feature) {
    $custom_event_feature_checked = 'checked';
}
setting_get('custom_promotion_feature', $check_promotion_feature);
if ($check_promotion_feature) {
    $custom_promotion_feature_checked = 'checked';
}

$activeModules = [];
if (EVENT_FEATURE == 'on' && FORCE_DISABLE_EVENT_FEATURE != 'on') {
    $activeModules[] = 'event';
}
if (CLASSIFIED_FEATURE == 'on' && FORCE_DISABLE_CLASSIFIED_FEATURE != 'on') {
    $activeModules[] = 'classified';
}
if (ARTICLE_FEATURE == 'on' && FORCE_DISABLE_ARTICLE_FEATURE != 'on') {
    $activeModules[] = 'article';
}
if (BANNER_FEATURE == 'on') {
    $activeModules[] = 'banner';
}
if (PROMOTION_FEATURE == 'on' && FORCE_DISABLE_PROMOTION_FEATURE != 'on') {
    $activeModules[] = 'promotion';
}
if (BLOG_FEATURE == 'on') {
    $activeModules[] = 'blog';
}

//Visitor Profile options
setting_get('socialnetwork_feature', $socialnetwork_feature);

//Reviews
setting_get('review_listing_enabled', $review_listing_enabled);
if ($review_listing_enabled) {
    $review_listing_enabled_checked = 'checked';
}

setting_get('review_approve', $review_approve);
if ($review_approve) {
    $review_approve_checked = 'checked';
}

setting_get('listing_login_review', $listing_login_review);

// Sponsor Products
setting_get('product_link_one', $product_link_one);
setting_get('product_link_two', $product_link_two);
setting_get('product_link_three', $product_link_three);
setting_get('product_promo_code', $product_promo_code);
setting_get('product_promo_text', $product_promo_text);

// Locals Card Stripe
setting_get('stripe_pub_key', $stripe_pub_key);
setting_get('locals_price_id', $locals_price_id);
setting_get('locals_price_text', $locals_price_text);
setting_get('locals_price_id_2', $locals_price_id_2);
setting_get('locals_price_text_2', $locals_price_text_2);

//Get maintenance page id
$sql = "SELECT id FROM Page WHERE pagetype_id = (SELECT id FROM PageType WHERE title = '".\ArcaSolutions\WysiwygBundle\Entity\PageType::MAINTENANCE_PAGE."')";
$result = $dbObj->query($sql);
$idMaintance = mysqli_fetch_assoc($result);

/* ModStores Hooks */
HookFire('generalsettings_after_fill_form');

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
include SM_EDIRECTORY_ROOT.'/layout/sidebar-configuration.php';

?>

<main class="wrapper togglesidebar container-fluid">

    <?php
    require SM_EDIRECTORY_ROOT.'/registration.php';
    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
    ?>

    <section class="heading">
        <h1><?= system_showText(LANG_SITEMGR_GENERAL_SETTINGS); ?></h1>
        <p><?= system_showText(LANG_SITEMGR_SETTINGS_TIP_1); ?></p>
    </section>
    <div class="row tab-options">
        <?php if(HookExist('generalsettings_after_render_form')) { ?>
            <ul class="nav nav-tabs" role="tablist">
                <li id="general-tab" class="active">
                    <a href="#general" role="tab" data-toggle="tab" tabindex="1"><?= system_showText(LANG_SITEMGR_GENERAL) ?></a>
                </li>

                <li id="plugins-tab" >
                    <a href="#plugins" role="tab" data-toggle="tab">Plugins</a>
                </li>

            </ul>
        <?php } ?>
        <div class="tab-content">
            <div class="tab-pane active" id="general">
                <section class="row section-form">
                    <?php include INCLUDES_DIR.'/forms/form-settings.php'; ?>
                </section>
            </div>
            <?php if(HookExist('generalsettings_after_render_form')) { ?>
                <div class="tab-pane" id="plugins">
                    <section class="row">
                        <form name="plugins-settings" action="<?= system_getFormAction($_SERVER['PHP_SELF']).'#plugins' ?>" method="post">
                            <?php if ($success) { ?>
                                <div class="col-xs-10">
                                    <div class="alert alert-success fade in" role="alert">
                                        <p><?= system_showText(LANG_SITEMGR_SETTINGS_YOURSETTINGSWERECHANGED); ?></p>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="col-md-10">
                                <?php
                                /* ModStores Hooks */
                                HookFire('generalsettings_after_render_form', [
                                    'http_post_array' => &$_POST,
                                    'http_get_array'  => &$_GET
                                ]);
                                ?>
                            </div>
                        </form>
                    </section>
                </div>
            <?php } ?>
        </div>
    </div>
</main>

<?php include INCLUDES_DIR.'/modals/modal-api.php'; ?>

<?php
# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
$customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/settings.php';
include SM_EDIRECTORY_ROOT.'/layout/footer.php';
?>
