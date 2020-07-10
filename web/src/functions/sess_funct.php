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
# * FILE: /functions/sess_funct.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# * MEMBERS
# ----------------------------------------------------------------------------------------------------

function sess_authenticateAccount($username, $password, &$authmessage)
{

    $dbObj = db_getDBObject(DEFAULT_DB, true);

    ####################################################################################################
    ### BEGIN - MEMBER
    ####################################################################################################
    $sql = "SELECT faillogin_count, faillogin_datetime FROM Account WHERE foreignaccount = 'n' AND username = ".db_formatString($username).'';
    $row = mysqli_fetch_assoc($dbObj->query($sql));
    $faillogin_count = $row['faillogin_count'];
    $faillogin_datetime = $row['faillogin_datetime'];
    if (($faillogincount = (int)((int)$faillogin_count / (FAILLOGIN_MAXFAIL + 1))) > 0) {
        if (($faillogin_count % (FAILLOGIN_MAXFAIL + 1)) == 0) {
            $faillogindatetime = preg_split('/[-, ,:]+/', $faillogin_datetime);
            $failloginnow = preg_split('/[-, ,:]+/', date('Y-m-d H:i:s'));
            if (($failloginsec = (mktime($failloginnow[3], $failloginnow[4], $failloginnow[5], $failloginnow[1],
                        $failloginnow[2], $failloginnow[0]) - mktime($faillogindatetime[3], $faillogindatetime[4],
                        $faillogindatetime[5], $faillogindatetime[1], $faillogindatetime[2],
                        $faillogindatetime[0]))) < ($faillogincount * FAILLOGIN_TIMEBLOCK * 60)) {
                $authmessage = system_showText(LANG_MSG_ACCOUNTLOCKED1).' '.(($faillogincount * FAILLOGIN_TIMEBLOCK) - (int)($failloginsec / 60)).' '.system_showText(LANG_MSG_ACCOUNTLOCKED2);

                return false;
            }
        }
    }
    ####################################################################################################
    ### END - MEMBER
    ####################################################################################################

    $sql = "SELECT * FROM Account WHERE (foreignaccount = 'n' AND username = ".db_formatString($username).' AND password = '.db_formatString(((string_strtolower(PASSWORD_ENCRYPTION) === 'on') ? md5($password) : $password)).") OR (foreignaccount = 'y' AND facebook_username LIKE 'facebook%' AND username = ".db_formatString($username).' AND password = '.db_formatString(((string_strtolower(PASSWORD_ENCRYPTION) === 'on') ? md5($password) : $password)).')';

    $row = mysqli_fetch_array($dbObj->query($sql));
    if (mysqli_affected_rows($dbObj->link_id)) {
        $sql = "UPDATE Account SET faillogin_count = 0, faillogin_datetime = '0000-00-00 00:00:00' WHERE foreignaccount = 'n' AND username = ".db_formatString($username).'';
        $dbObj->query($sql);

        return true;
    }

    $sql = "UPDATE Account SET faillogin_count = faillogin_count + 1, faillogin_datetime = NOW() WHERE foreignaccount = 'n' AND username = ".db_formatString($username).'';
    $dbObj->query($sql);
    $authmessage = system_showText(LANG_MSG_USERNAME_OR_PASSWORD_INCORRECT);

    return false;

}

function sess_registerAccountInSession($username, $facebook = false)
{

    if ($_SESSION === null) {
        session_start(sess_generateSessIdString());
    }

    if ($_SESSION['SM_LOGGEDIN']) {
        $dbLogoutObj = db_getDBObject();
        if ($_SESSION['SESS_SM_ID']) {
            $smacctObj = db_getFromDB('smaccount', 'id', db_formatNumber($_SESSION['SESS_SM_ID']));
            $logoutSQL = 'INSERT INTO Report_Login (datetime, ip, type, page, username) VALUES (NOW(), '.db_formatString(getenv('REMOTE_ADDR')).", 'logout', ".db_formatString($_SERVER['PHP_SELF']).', '.db_formatString($smacctObj->getString('username')).')';
        } else {
            setting_get('sitemgr_username', $smusername);
            $logoutSQL = 'INSERT INTO Report_Login (datetime, ip, type, page, username) VALUES (NOW(), '.db_formatString(getenv('REMOTE_ADDR')).", 'logout', ".db_formatString($_SERVER['PHP_SELF']).', '.db_formatString($smusername).')';
        }
        $dbLogoutObj->query($logoutSQL);
    }

    unset($_SESSION['SM_LOGGEDIN']);
    unset($_SESSION['SESS_SM_ID']);
    unset($_SESSION['SESS_SM_PERM']);

    $_x_user_perm = $_SESSION['USER_PERM'];
    $_x_request_uri = $_SESSION['REQUEST_URI'];
    $_x_item_action = $_SESSION['ITEM_ACTION'];
    $_x_http_refer = $_SESSION['HTTP_REFER'];
    $_x_item_type = $_SESSION['ITEM_TYPE'];
    $_x_item_id = $_SESSION['ITEM_ID'];
    $_x_account_redirect = $_SESSION['ACCOUNT_REDIRECT'];
    $_x_renewal_period = $_SESSION['order_renewal_period'];

    unset($arrFBAux);
    foreach ($_SESSION as $key => $value) {
        if (string_strpos($key, 'fb_') !== false) {
            $arrFBAux[$key] = $value;
        }
    }

    unset($arrGOAux);
    foreach ($_SESSION as $key => $value) {
        if (string_strpos($key, 'go_') !== false) {
            $arrGOAux[$key] = $value;
        }
    }

    session_unset();

    if (is_array($arrFBAux) && $arrFBAux !== null) {
        foreach ($arrFBAux as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    if (is_array($arrGOAux) && $arrGOAux !== null) {
        foreach ($arrGOAux as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }
    $_SESSION['USER_PERM'] = $_x_user_perm;
    $_SESSION['REQUEST_URI'] = $_x_request_uri;
    $_SESSION['ITEM_ACTION'] = $_x_item_action;
    $_SESSION['HTTP_REFER'] = $_x_http_refer;
    $_SESSION['ITEM_TYPE'] = $_x_item_type;
    $_SESSION['ITEM_ID'] = $_x_item_id;
    $_SESSION['ACCOUNT_REDIRECT'] = $_x_account_redirect;
    $_SESSION['order_renewal_period'] = $_x_renewal_period;

    if ($facebook) {
        $acctObj = db_getFromDB('account', 'facebook_username', db_formatString($username));
    } else {
        $acctObj = db_getFromDB('account', 'username', db_formatString($username));
    }

    if ($acctObj) {
        $acctObj->updateLastLogin();
    }
    $_SESSION['SESS_ACCOUNT_ID'] = $acctObj->getNumber('id');

    /*
     * This make edirectory's session work in symfony
     * If you wanna pass other data by session to symfony using session
     * you must do this key `_sf2_attributes`
     */
    $_SESSION['_sf2_attributes']['SESS_ACCOUNT_ID'] = $_SESSION[SESS_ACCOUNT_ID];

    $dbLoginObj = db_getDBObject();
    $loginSQL = 'INSERT INTO Report_Login (datetime, ip, type, page, username) VALUES (NOW(), '.db_formatString(getenv('REMOTE_ADDR')).", 'login', ".db_formatString($_SERVER['PHP_SELF']).', '.db_formatString($username).')';
    $dbLoginObj->query($loginSQL);

}

function sess_logoutAccount()
{

    if ($_SESSION === null) {
        session_start();
    }

    if ($_SESSION[SESS_ACCOUNT_ID]) {
        $dbLogoutObj = db_getDBObject();
        $acctObj = db_getFromDB('account', 'id', db_formatNumber($_SESSION[SESS_ACCOUNT_ID]));
        $logoutSQL = 'INSERT INTO Report_Login (datetime, ip, type, page, username) VALUES (NOW(), '.db_formatString(getenv('REMOTE_ADDR')).", 'logout', ".db_formatString($_SERVER['PHP_SELF']).', '.db_formatString($acctObj->getString('username')).')';
        $dbLogoutObj->query($logoutSQL);
    }

    session_unset();
    setcookie(session_name(), '', (time() - 2592000), '/', '', 0);
    setcookie('automatic_login_members', 'false', time() + 60 * 60 * 24 * 30, ''.EDIRECTORY_FOLDER.'/');
    setcookie('complementary_info_members', '', 0, ''.EDIRECTORY_FOLDER.'/');

    $host = string_strtoupper(str_replace('www.', '', $_SERVER['HTTP_HOST']));

    setcookie($host.'_DOMAIN_ID_MEMBERS', '', time() + 60 * 60 * 24 * 30, ''.EDIRECTORY_FOLDER.'/');
    setcookie($host.'_DOMAIN_ID', '', time() + 60 * 60 * 24 * 30, ''.EDIRECTORY_FOLDER.'/');

    session_destroy();
    header('Location: '.MEMBERS_LOGIN_PAGE);
    exit;

}

function sess_validateSession()
{

    global $_COOKIE;

    if ($_SESSION === null) {
        session_start();
    }

    if ($_COOKIE['automatic_login_members'] === 'true') {
        if ($_COOKIE['complementary_info_members']) {
            $db = db_getDBObject(DEFAULT_DB, true);
            $sql = 'SELECT * FROM Account WHERE username = '.db_formatString($_COOKIE['username_members']).' AND complementary_info = '.db_formatString($_COOKIE['complementary_info_members']).'';
            $result = $db->query($sql);
            if (mysqli_num_rows($result) == 0) {
                sess_logoutAccount();
            }
        } else {
            sess_logoutAccount();
        }
    }

    if (!empty($_SESSION[SESS_ACCOUNT_ID]) || ($_COOKIE['automatic_login_members'] === 'true')) {
        if (!empty($_SESSION[SESS_ACCOUNT_ID])) {
            $acctObj = db_getFromDB('account', 'id', db_formatNumber($_SESSION[SESS_ACCOUNT_ID]));
            if (!$acctObj || !$acctObj->getNumber('id') || ($acctObj->getNumber('id') <= 0)) {
                sess_logoutAccount();
            }
        }
        if (($_COOKIE['automatic_login_members'] === 'true') && empty($_SESSION[SESS_ACCOUNT_ID])) {
            sess_registerAccountInSession($_COOKIE['username_members']);
        }
    } else {
        sess_logoutAccount();
    }

    $accountObj = db_getFromDB('account', 'id', db_formatNumber($_SESSION[SESS_ACCOUNT_ID]));
    if ($accountObj->getNumber('id') > 0) {
        $contactObj = new Contact($accountObj->getNumber('id'));
        if (!$contactObj->getString('email')) {
            if ((string_strpos($_SERVER['PHP_SELF'],
                        '/account/index.php') === false) && (string_strpos($_SERVER['PHP_SELF'],
                        '/logout.php') === false)) {
                header('Location: '.DEFAULT_URL.'/'.MEMBERS_ALIAS.'/account/index.php?id='.$accountObj->getNumber('id'));
                exit;
            }
        }
    }

    if ($accountObj->getString('is_sponsor') !== 'y') {
        if (string_strpos($_SERVER['PHP_SELF'], MEMBERS_ALIAS) == true) {
            if (string_strpos($_SERVER['PHP_SELF'],
                    MEMBERS_ALIAS.'/login.php') === false && string_strpos($_SERVER['PHP_SELF'],
                    MEMBERS_ALIAS.'/logout.php') === false && string_strpos($_SERVER['PHP_SELF'],
                    MEMBERS_ALIAS.'/resetpassword.php') === false) {
                header('Location: '.MEMBERS_LOGIN_PAGE.'?np=1');
                exit;
            }
        }
    }

    /*
     * Workaround to avoid errors in case the Account information is not saved on table AccountProfileContact
     * I'm not proud of it
     */
    $profileObj = new Profile($accountObj->getNumber('id'));
    $accDomain = new Account_Domain($accountObj->getNumber('id'), SELECTED_DOMAIN_ID);
    $accDomain->Save();
    $accDomain->saveOnDomain($accountObj->getNumber('id'), $accountObj, false, $profileObj);
}

# ----------------------------------------------------------------------------------------------------
# * SITEMGR
# ----------------------------------------------------------------------------------------------------

function sess_authenticateSM($username, $password, &$authmessage)
{

    $dbMain = db_getDBObject(DEFAULT_DB, true);

    $username = db_formatString($username);
    $sql = "SELECT * FROM Setting WHERE name = 'sitemgr_username' AND value = $username";
    $row = mysqli_fetch_array($dbMain->query($sql));
    if (mysqli_affected_rows($dbMain->link_id)) {

        ####################################################################################################
        ### BEGIN - SUPER SITE MANAGER
        ####################################################################################################
        setting_get('sitemgr_faillogin_count', $faillogin_count);
        setting_get('sitemgr_faillogin_datetime', $faillogin_datetime);
        if (($faillogincount = (int)((int)$faillogin_count / (FAILLOGIN_MAXFAIL + 1))) > 0) {
            if (($faillogin_count % (FAILLOGIN_MAXFAIL + 1)) == 0) {
                $faillogindatetime = preg_split('/[-, ,:]+/', $faillogin_datetime);
                $failloginnow = preg_split('/[-, ,:]+/', date('Y-m-d H:i:s'));
                if (($failloginsec = (mktime($failloginnow[3], $failloginnow[4], $failloginnow[5], $failloginnow[1],
                            $failloginnow[2], $failloginnow[0]) - mktime($faillogindatetime[3], $faillogindatetime[4],
                            $faillogindatetime[5], $faillogindatetime[1], $faillogindatetime[2],
                            $faillogindatetime[0]))) < ($faillogincount * FAILLOGIN_TIMEBLOCK * 60)) {
                    $authmessage = system_showText(LANG_MSG_ACCOUNTLOCKED1).' '.(($faillogincount * FAILLOGIN_TIMEBLOCK) - (int)($failloginsec / 60)).' '.system_showText(LANG_MSG_ACCOUNTLOCKED2);

                    return false;
                }
            }
        }
        ####################################################################################################
        ### END - SUPER SITE MANAGER
        ####################################################################################################

        $sql = "SELECT * FROM Setting WHERE name = 'sitemgr_password' AND value = ".db_formatString(md5($password)).'';
        $row = mysqli_fetch_array($dbMain->query($sql));
        if (mysqli_affected_rows($dbMain->link_id)) {
            setting_set('sitemgr_faillogin_count', '0');
            setting_set('sitemgr_faillogin_datetime', '0000-00-00 00:00:00');

            return true;
        }

        setting_get('sitemgr_faillogin_count', $sitemgr_faillogin_count);
        if (!DEMO_LIVE_MODE) {
            setting_set('sitemgr_faillogin_count', (int)$sitemgr_faillogin_count + 1);
        }
        setting_set('sitemgr_faillogin_datetime', date('Y-m-d H:i:s'));

    } else {

        ####################################################################################################
        ### BEGIN - SITE MANAGER
        ####################################################################################################
        $sql = "SELECT faillogin_count, faillogin_datetime FROM SMAccount WHERE username = $username";
        $row = mysqli_fetch_assoc($dbMain->query($sql));
        $faillogin_count = $row['faillogin_count'];
        $faillogin_datetime = $row['faillogin_datetime'];
        if (($faillogincount = (int)((int)$faillogin_count / (FAILLOGIN_MAXFAIL + 1))) > 0) {
            if (($faillogin_count % (FAILLOGIN_MAXFAIL + 1)) == 0) {
                $faillogindatetime = preg_split('/[-, ,:]+/', $faillogin_datetime);
                $failloginnow = preg_split('/[-, ,:]+/', date('Y-m-d H:i:s'));
                if (($failloginsec = (mktime($failloginnow[3], $failloginnow[4], $failloginnow[5], $failloginnow[1],
                            $failloginnow[2], $failloginnow[0]) - mktime($faillogindatetime[3], $faillogindatetime[4],
                            $faillogindatetime[5], $faillogindatetime[1], $faillogindatetime[2],
                            $faillogindatetime[0]))) < ($faillogincount * FAILLOGIN_TIMEBLOCK * 60)) {
                    $authmessage = system_showText(LANG_MSG_ACCOUNTLOCKED1).' '.(($faillogincount * FAILLOGIN_TIMEBLOCK) - (int)($failloginsec / 60)).' '.system_showText(LANG_MSG_ACCOUNTLOCKED2);

                    return false;
                }
            }
        }
        ####################################################################################################
        ### END - SITE MANAGER
        ####################################################################################################

        $sql = "SELECT * FROM SMAccount WHERE username = $username AND password = ".db_formatString(md5($password)).'';
        $row = mysqli_fetch_array($dbMain->query($sql));
        if (mysqli_affected_rows($dbMain->link_id)) {

            $hasAcessActive = false;
            if ($row['active'] === 'y') {
                $hasAcessActive = true;
            }

            $hasAccess = false;
            $remote_ipaddress = explode('.', $_SERVER['REMOTE_ADDR']);
            $iprestrictions = explode("\n", $row['iprestriction']);
            foreach ($iprestrictions as $iprestriction) {
                $iprestriction = str_replace("\r", '', $iprestriction);
                if ($iprestriction) {
                    $iprestriction = explode('.', $iprestriction);
                    if ($iprestriction[0] === '*') {
                        $hasAccess = true;
                    } elseif (($remote_ipaddress[0] == $iprestriction[0]) && ($iprestriction[1] === '*')) {
                        $hasAccess = true;
                    } elseif (($remote_ipaddress[0] == $iprestriction[0]) && ($remote_ipaddress[1] == $iprestriction[1]) && ($iprestriction[2] === '*')) {
                        $hasAccess = true;
                    } elseif (($remote_ipaddress[0] == $iprestriction[0]) && ($remote_ipaddress[1] == $iprestriction[1]) && ($remote_ipaddress[2] == $iprestriction[2]) && ($iprestriction[3] === '*')) {
                        $hasAccess = true;
                    } elseif (($remote_ipaddress[0] == $iprestriction[0]) && ($remote_ipaddress[1] == $iprestriction[1]) && ($remote_ipaddress[2] == $iprestriction[2]) && ($remote_ipaddress[3] == $iprestriction[3])) {
                        $hasAccess = true;
                    }
                } else {
                    $hasAccess = true;
                }
            }
            if ($hasAccess && $hasAcessActive) {
                $sql = "UPDATE SMAccount SET faillogin_count = 0, faillogin_datetime = '0000-00-00 00:00:00' WHERE username = $username";
                $dbMain->query($sql);

                return true;
            }

            if (!$hasAcessActive) {
                $authmessage = system_showText(LANG_MSG_ACCOUNT_DEACTIVE);
            } else {
                $authmessage = system_showText(LANG_MSG_YOUDONTHAVEACCESSFROMTHISIPADDRESS);
            }

            return false;

        }

        $sql = "UPDATE SMAccount SET faillogin_count = faillogin_count + 1, faillogin_datetime = NOW() WHERE username = $username";
        $dbMain->query($sql);

    }

    $authmessage = system_showText(LANG_MSG_USERNAME_OR_PASSWORD_INCORRECT);

    return false;

}

function sess_registerSMInSession($username)
{

    if ($_SESSION === null) {
        session_start(sess_generateSessIdString());
    }

    if ($_SESSION['SESS_ACCOUNT_ID']) {
        $dbLogoutObj = db_getDBObject();
        $acctObj = db_getFromDB('account', 'id', db_formatNumber($_SESSION['SESS_ACCOUNT_ID']));
        $logoutSQL = 'INSERT INTO Report_Login (datetime, ip, type, page, username) VALUES (NOW(), '.db_formatString(getenv('REMOTE_ADDR')).", 'logout', ".db_formatString($_SERVER['PHP_SELF']).', '.db_formatString($acctObj->getString('username')).')';
        $dbLogoutObj->query($logoutSQL);
    }

    unset($_SESSION['SESS_ACCOUNT_ID']);

    session_unset();

    $_SESSION['SM_LOGGEDIN'] = 'true';
    $smacctObj = db_getFromDB('smaccount', 'username', db_formatString($username));
    if ($smacctObj->getNumber('id') > 0) {
        $_SESSION['SESS_SM_ID'] = $smacctObj->getNumber('id');
        $_SESSION['SESS_SM_PERM'] = $smacctObj->getString('permission');
    }

    /*
     * This make edirectory's session work in symfony
     * If you wanna pass other data by session to symfony using session
     * you must do this key `_sf2_attributes`
     */
    $_SESSION['_sf2_attributes']['SM_LOGGEDIN'] = $_SESSION['SM_LOGGEDIN'];

    $dbLoginObj = db_getDBObject();
    $loginSQL = 'INSERT INTO Report_Login (datetime, ip, type, page, username) VALUES (NOW(), '.db_formatString(getenv('REMOTE_ADDR')).", 'login', ".db_formatString($_SERVER['PHP_SELF']).', '.db_formatString($username).')';
    $dbLoginObj->query($loginSQL);

}

function sess_logoutSM()
{

    if ($_SESSION === null) {
        session_start();
    }

    if ($_SESSION['SM_LOGGEDIN']) {
        $dbLogoutObj = db_getDBObject();
        if ($_SESSION['SESS_SM_ID']) {
            $smacctObj = db_getFromDB('smaccount', 'id', db_formatNumber($_SESSION['SESS_SM_ID']));
            $logoutSQL = 'INSERT INTO Report_Login (datetime, ip, type, page, username) VALUES (NOW(), '.db_formatString(getenv('REMOTE_ADDR')).", 'logout', ".db_formatString($_SERVER['PHP_SELF']).', '.db_formatString($smacctObj->getString('username')).')';
        } else {
            setting_get('sitemgr_username', $username);
            $logoutSQL = 'INSERT INTO Report_Login (datetime, ip, type, page, username) VALUES (NOW(), '.db_formatString(getenv('REMOTE_ADDR')).", 'logout', ".db_formatString($_SERVER['PHP_SELF']).', '.db_formatString($username).')';
        }
        $dbLogoutObj->query($logoutSQL);
    }

    session_unset();
    setcookie(session_name(), '', (time() - 2592000), '/', '', 0);
    setcookie('automatic_login_sitemgr', 'false', time() + 60 * 60 * 24 * 30, ''.EDIRECTORY_FOLDER.'/');
    setcookie('complementary_info_sitemgr', '', 0, ''.EDIRECTORY_FOLDER.'/');

    $host = string_strtoupper(str_replace('www.', '', $_SERVER['HTTP_HOST']));

    setcookie($host.'_DOMAIN_ID_SITEMGR', '', time() + 60 * 60 * 24 * 30, ''.EDIRECTORY_FOLDER.'/');

		session_destroy();
		mixpanel_track('Logged out');
		header('Location: '.SM_LOGIN_PAGE);
		exit;

}

function sess_validateSMSession()
{

    global $_COOKIE;

    if ($_SESSION === null) {
        session_start();
    }

    $cookie = $_COOKIE['complementary_info_sitemgr'];

    if ($_COOKIE['automatic_login_sitemgr'] === 'true') {
        if ($cookie) {
            setting_get('sitemgr_username', $sitemgr_username);
            if (!$_SESSION['SESS_SM_ID'] && $sitemgr_username == $_COOKIE['username_sitemgr']) {
                setting_get('complementary_info', $complementary_info);
                if ($cookie != $complementary_info) {
                    sess_logoutSM();
                }
            } else {
                $db = db_getDBObject(DEFAULT_DB, true);
                $sql = 'SELECT * FROM SMAccount WHERE username = '.db_formatString($_COOKIE['username_sitemgr']).' AND complementary_info = '.db_formatString($cookie);
                $result = $db->query($sql);
                if (mysqli_num_rows($result) == 0) {
                    sess_logoutSM();
                } else {
                    $row = mysqli_fetch_assoc($result);
                }
            }
        } else {
            header('Location: '.SM_LOGIN_PAGE.'?destiny='.$_SERVER['PHP_SELF'].'&query='.$_SERVER['QUERY_STRING']);
            exit;
        }
    }

    if (!empty($_SESSION['SM_LOGGEDIN']) || ($_COOKIE['automatic_login_sitemgr'] === 'true')) {
        if (!empty($_SESSION['SM_LOGGEDIN'])) {
            if ($_SESSION['SESS_SM_ID']) {
                $smacctObj = db_getFromDB('smaccount', 'id', db_formatNumber($_SESSION['SESS_SM_ID']));
                if (!$smacctObj || !$smacctObj->getNumber('id') || ($smacctObj->getNumber('id') <= 0)) {
                    sess_logoutSM();
                }
            }
        }
        if (($_COOKIE['automatic_login_sitemgr'] === 'true') && empty($_SESSION['SM_LOGGEDIN'])) {
            mixpanel_track('Session restored with automatic login');
            sess_registerSMInSession($_COOKIE['username_sitemgr']);
        }

    } else {
        header('Location: '.SM_LOGIN_PAGE.'?destiny='.$_SERVER['PHP_SELF'].'&query='.$_SERVER['QUERY_STRING']);
        exit;
    }

    if (!DEMO_DEV_MODE && !DEMO_LIVE_MODE) {
        setting_get('sitemgr_first_login', $sitemgr_first_login);
        if ($sitemgr_first_login === 'yes') {
            if ((string_strpos($_SERVER['PHP_SELF'], '/setlogin.php') === false) && (string_strpos($_SERVER['PHP_SELF'],
                        '/logout.php') === false)) {
                if (!permission_hasSMPermSection(SITEMGR_PERMISSION_SUPERADMIN)) {
                    header('Location: '.DEFAULT_URL.'/'.SITEMGR_ALIAS.'/setlogin.php?destiny='.$_SERVER['PHP_SELF'].'&query='.$_SERVER['QUERY_STRING']);
                    exit;
                }
            }
        }
    }
}

# ----------------------------------------------------------------------------------------------------
# * COMON
# ----------------------------------------------------------------------------------------------------

function sess_generateSessIdString()
{
    $sid = time() * rand(1, 10000);
    $sid = md5($sid);

    return $sid;
}

function sess_getAccountIdFromSession()
{
    return $_SESSION['SESS_ACCOUNT_ID'];
}

function sess_getSMIdFromSession()
{
    return $_SESSION['SESS_SM_ID'];
}

function sess_isAccountLogged($check_folder = false)
{
    if ($check_folder) {
        return $_SESSION['SESS_ACCOUNT_ID'] && (string_strpos($_SERVER['PHP_SELF'], '/'.MEMBERS_ALIAS.'') !== false);
    }

    if ($_SESSION['SESS_ACCOUNT_ID']) {
        return true;
    }

    return false;
}

function sess_isSitemgrLogged($check_folder = false)
{
    if ($check_folder) {
        return $_SESSION['SM_LOGGEDIN'] && (string_strpos($_SERVER['PHP_SELF'], '/'.SITEMGR_ALIAS.'') !== false);
    }

    if ($_SESSION['SM_LOGGEDIN']) {
        return true;
    }

    return false;
}

//-----------------------------------------
function sess_validateSessionFront()
{

    global $_COOKIE;

    if ($_SESSION === null) {
        session_start();
    }

    if ($_COOKIE['automatic_login_members'] === 'true') {
        if ($_COOKIE['complementary_info_members']) {
            $db = db_getDBObject(DEFAULT_DB, true);
            $sql = 'SELECT * FROM Account WHERE username = '.db_formatString($_COOKIE['username_members']).' AND complementary_info = '.db_formatString($_COOKIE['complementary_info_members']).'';
            $result = $db->query($sql);
            if (mysqli_num_rows($result) == 0) {
                setcookie('automatic_login_members', 'false');
                setcookie('username_members', '');

                header('Location: '.DEFAULT_URL);
                exit;
            }
        } else {
            setcookie('automatic_login_members', 'false');
            setcookie('username_members', '');

            header('Location: '.DEFAULT_URL);
            exit;
        }
    }

    if (!empty($_SESSION[SESS_ACCOUNT_ID]) || ($_COOKIE['automatic_login_members'] === 'true')) {
        if (!empty($_SESSION[SESS_ACCOUNT_ID])) {
            $acctObj = db_getFromDB('account', 'id', db_formatNumber($_SESSION[SESS_ACCOUNT_ID]));
            if (!$acctObj || !$acctObj->getNumber('id') || ($acctObj->getNumber('id') <= 0)) {
                sess_logoutAccount();
            }
        }
        if (($_COOKIE['automatic_login_members'] === 'true') && empty($_SESSION[SESS_ACCOUNT_ID])) {
            sess_registerAccountInSession($_COOKIE['username_members']);
        }
    }
}

function sess_logoutAccountFront($url = '')
{

    if ($_SESSION === null) {
        session_start();
    }
    session_unset();
    setcookie(session_name(), '', (time() - 2592000), '/', '', 0);
    setcookie('automatic_login_members', 'false', time() + 60 * 60 * 24 * 30, ''.EDIRECTORY_FOLDER.'/');
    setcookie('complementary_info_members', '', 0, ''.EDIRECTORY_FOLDER.'/');

    $host = string_strtoupper(str_replace('www.', '', $_SERVER['HTTP_HOST']));

    setcookie($host.'_DOMAIN_ID_MEMBERS', '', time() + 60 * 60 * 24 * 30, ''.EDIRECTORY_FOLDER.'/');
    setcookie($host.'_DOMAIN_ID', '', time() + 60 * 60 * 24 * 30, ''.EDIRECTORY_FOLDER.'/');

    session_destroy();

    if ($url) {
        header('Location: '.$url);
        exit;
    }

    header('Location: '.DEFAULT_URL.'/');
    exit;
}

function sess_validateItemPreview()
{
    return sess_isSitemgrLogged() && string_strpos($_SERVER['HTTP_REFERER'],
            DEFAULT_URL.'/'.SITEMGR_ALIAS.'/content/') !== false;
}
