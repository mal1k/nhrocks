<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/login.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# DOMAIN COOKIE VALIDATION
	# ----------------------------------------------------------------------------------------------------
	if (!isset($_COOKIE["automatic_login_sitemgr"]) || $_COOKIE["automatic_login_sitemgr"] == "false") {
		$resetDomainSession = true;
	}

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------

	$sitemgr_section = true;

	$_GET = format_magicQuotes($_GET);
	$_POST = format_magicQuotes($_POST);
	$destiny = $_GET["destiny"] ? $_GET["destiny"] : $_POST["destiny"];
	$destiny = urldecode($destiny);
	if ($destiny) {
		$destiny = system_denyInjections($destiny);
		if (string_strpos($destiny, "://") !== false) {
			if (string_strpos($destiny, $_SERVER["HTTP_HOST"]) === false) {
				$destiny = "";
			}
		}
	}

	if ($_SERVER["QUERY_STRING"]) {
		if (string_strpos($_SERVER["QUERY_STRING"], "query=") !== false) {
			$query = string_substr($_SERVER["QUERY_STRING"], string_strpos($_SERVER["QUERY_STRING"], "query=")+6);
		} else {
			$query = $_GET["query"] ? $_GET["query"] : $_POST["query"];
			$query = urldecode($query);
		}
	} else {
		$query = $_GET["query"] ? $_GET["query"] : $_POST["query"];
		$query = urldecode($query);
	}
	if ($query) {
		$query = system_denyInjections($query);
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if(empty($_POST["username"])||empty($_POST["password"])) {
            $authmessage = system_showText(LANG_MSG_USERNAME_OR_PASSWORD_INCORRECT);
        }else{
            if (sess_authenticateSM($_POST["username"], $_POST["password"], $authmessage)) {

                sess_registerSMInSession($_POST["username"]);
                setcookie("username_sitemgr", $_POST["username"], time() + 60 * 60 * 24 * 30, "" . EDIRECTORY_FOLDER . "/");

                if ($_POST["automatic_login"]) {
                    setcookie("automatic_login_sitemgr", "true", time() + 60 * 60 * 24 * 30, "" . EDIRECTORY_FOLDER . "/");
                    $_POST["password"] = string_strtolower(PASSWORD_ENCRYPTION) == "on" ? md5($_POST["password"]) : $_POST["password"];
                    $aux = md5(trim($_POST["username"]) . $_POST["password"]);

                    setcookie("complementary_info_sitemgr", $aux, time() + 60 * 60 * 24 * 30, "" . EDIRECTORY_FOLDER . "/");

                    if (!$_SESSION["SESS_SM_ID"] && setting_get("sitemgr_username", $sitemgr_username) == $_POST["username"]) {
                        if (setting_get("complementary_info", $complementary_info)) {
                            setting_set("complementary_info", $aux);
                        } else {
                            setting_new("complementary_info", $aux);
                        }
                    } else {
                        $SMAccountObj = db_getFromDB("smaccount", "username", db_formatString($_POST["username"]));
                        $SMAccountObj->Save();
                    }

                } else setcookie("automatic_login_sitemgr", "false", time() + 60 * 60 * 24 * 30, "" . EDIRECTORY_FOLDER . "/");

                if ($destiny) {
                    $destiny = str_replace(EDIRECTORY_FOLDER, "", $destiny);
                    $url = DEFAULT_URL . $destiny;
                    if ($query) $url .= "?" . $query;
                } else {
                    $url = DEFAULT_URL . "/" . SITEMGR_ALIAS . "/";
                }

                // Mixpanel
                if (!permission_hasSMPermSection(SITEMGR_PERMISSION_SUPERADMIN)) {
                    mixpanel_createProfile($_POST['distinct_id'], $_POST['username']);
                    mixpanel_track(\ArcaSolutions\WebBundle\Mixpanel\TrackEvents::SITEMGR_LOGGED_IN);
                }

                header("Location: " . $url);
                exit;

            }
        }

		$username = $_POST["username"];
		$message_login = $authmessage;

	} elseif ($_GET["key"]) {

        $connection = new PDO("mysql:host="._DIRECTORYDB_HOST.";dbname="._DIRECTORYDB_NAME.";charset=utf8", _DIRECTORYDB_USER, _DIRECTORYDB_PASS);
        $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $connection->prepare('SELECT * FROM Forgot_Password WHERE unique_key = :key');

        $statement->execute([":key" => $_GET["key"]]);

        if ($result = $statement->fetchObject()) {
            $forgotPasswordObj = new forgotPassword($result->unique_key);
        } else {
            header("Location: ".DEFAULT_URL);
            exit;
        }

		if ($forgotPasswordObj->getString("unique_key") && ($forgotPasswordObj->getString("section") == "sitemgr")) {

			if (!$forgotPasswordObj->getString("account_id")) {
				setting_get("sitemgr_username", $sitemgr_username);
			} else {
				$smaccountObj = new SMAccount($forgotPasswordObj->getString("account_id"));
				$sitemgr_username = $smaccountObj->getString("username");
			}

			if ($sitemgr_username) {

				sess_registerSMInSession($sitemgr_username);
				setcookie("username_sitemgr", $sitemgr_username, time()+60*60*24*30, "".EDIRECTORY_FOLDER."/");

				header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/resetpassword.php?key=".$_GET["key"]);
				exit;

			} else {
				$message_login = system_showText(LANG_SITEMGR_FORGOTPASS_SORRYWRONGACCOUNT);
			}

		} else {
			$message_login = system_showText(LANG_SITEMGR_FORGOTPASS_SORRYWRONGKEY);
		}

	} else {

		$username = $_COOKIE["username_sitemgr"];
		if ($_COOKIE["automatic_login_sitemgr"] == "true") $checked = "checked";
		else $checked = "";

	}

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/header.php");

	# ----------------------------------------------------------------------------------------------------
	# Navbar
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/navbar.php");

?>
    <div class="container">
        <div class="container-fluid row">
            <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
                <br><br>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?=system_showText(LANG_SITEMGR_LOGIN_ACCOUNT);?>
                    </div>
                    <div class="panel-body">
                        <form id="form-signin" name="form-signin" role="form" method="post" action="<?=SM_LOGIN_PAGE;?>">
                            <input type="hidden" name="distinct_id" value="" />
                            <? include(INCLUDES_DIR."/forms/form-login.php"); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
