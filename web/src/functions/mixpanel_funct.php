<?php

/**
 * @return \ArcaSolutions\WebBundle\Mixpanel\MixpanelHelper|object
 */
function mixpanel_getMixpanelHelper() {
    return SymfonyCore::getContainer()->get('mixpanel.helper');
}

/**
 * Track an event
 *
 * @param string $event
 * @param array $options
 */
function mixpanel_track($event, $options = [])
{
    //Do not track for Arcalogin user
    if (permission_hasSMPermSection(SITEMGR_PERMISSION_SUPERADMIN)) {
        return;
    }
    mixpanel_getMixpanelHelper()->trackEvent($event, $options);
}

/**
 * Identify sitemgr.
 */
function mixpanel_identify()
{
    $distinctId = null;

    if(!sess_isSitemgrLogged()){
        return null;
    }

    if(empty(sess_getSMIdFromSession())) {
        setting_get('mixpanel_distinct_id', $distinctId);
    } else {
        $distinctId = (new SMAccount(sess_getSMIdFromSession()))->getString('mixpanelDistinctId');
    }

    mixpanel_getMixpanelHelper()->identify($distinctId);
}

/**
 * Create profile and identify user
 *
 * @param string $distinctId
 * @param string $username
 * @param boolean $isMainSitemgr
 */
function mixpanel_createProfile($distinctId, $username) {
    setting_get('mixpanel_fresh_install', $isFreshInstall);
    setting_get("freetrial_end_date", $freetrial_end_date);
    setting_get("install_name", $install_name);

    $date1 = new DateTime($freetrial_end_date);
    $date2 = new DateTime(date('Y-m-d'));

    $options = [
        "is_mainsitemgr" => (empty(sess_getSMIdFromSession())),
        "fresh_install" => ($isFreshInstall == 'off' ? 'No' : 'Yes'),
        "free_trial" => ($freetrial_end_date ? 'Yes' : 'No'),
        "free_trial_ended" => ($date1 < $date2 ? 'Yes' : 'No'),
        "install_name" => ($install_name ? $install_name : ''),
    ];

    mixpanel_getMixpanelHelper()->createProfile($distinctId, $username, $options);
}

/**
 * Get mixpanel distinct id
 *
 * @return string
 */
function mixpanel_getDistinctId()
{
    if ($_SESSION['SESS_SM_ID']) {
        $smAccount = new SMAccount($_SESSION['SESS_SM_ID']);
        return mixpanel_getMixpanelHelper()->getSitemgrDistinctId($smAccount->getString("username"));
    }
    return mixpanel_getMixpanelHelper()->getMainSitemgrDistinctId();
}

/**
 * Track event when a module is created for the first time
 *
 * @param string $item
 */
function mixpanel_trackFirstItem($item)
{
    $dbObjMain = db_getDBObject(DEFAULT_DB, true);
    $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbObjMain);
    $sql = "SELECT COUNT(*) as item_count FROM $item";
    $result = $dbObj->query($sql);
    $row = mysqli_fetch_assoc($result);
    if ($row['item_count'] == 0) {
        mixpanel_track("First {$item} created");
    }
}
