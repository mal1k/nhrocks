<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /sitemgr/account_ajax.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	if (isset($_GET["domain_id"])) define("SELECTED_DOMAIN_ID", $_GET["domain_id"]);
	if (isset($_POST["domain_id"])) define("SELECTED_DOMAIN_ID", $_POST["domain_id"]);
    include("../conf/loadconfig.inc.php");

    header("Content-Type: application/json; charset=".EDIR_CHARSET, TRUE);
    header("Accept-Encoding: gzip, deflate");
    header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check", FALSE);
    header("Pragma: no-cache");

    # ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
    sess_validateSMSession();

    extract($_GET);
    extract($_POST);

    # ----------------------------------------------------------------------------------------------------
	# GET
	# ----------------------------------------------------------------------------------------------------\
    if ($_SERVER['REQUEST_METHOD'] == "GET" and isset($_SERVER['HTTP_X_REQUESTED_WITH']) and ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
        $auxSelectize = [];

        // Retrieving Accounts by username, first_name and last_name
        $dbObj = db_getDBObject(DEFAULT_DB, true);

        $where = "Account.`is_sponsor` = 'y' ";

        if($_GET['query'] != ''){
            $where .= "AND (Contact.`first_name` LIKE '%".$_GET['query']."%' OR Contact.`last_name` LIKE '%".$_GET['query']."%' OR Account.`username` LIKE '%".$_GET['query']."%') ";
        }

        if($_GET['account_id'] != ''){
            $where .= "AND Account.`id` = ".$_GET['account_id']." ";
        }

        $sql = "SELECT
                    Account.`id`,
                    Contact.`first_name`,
                    Contact.`last_name`,
                    Contact.`email`
                FROM `Account` AS Account
                    LEFT OUTER JOIN `Contact` AS Contact ON (Account.`id` = Contact.`account_id`)
                WHERE {$where} 
                ORDER BY Contact.`first_name`";

        $result = $dbObj->query($sql);

        $auxSelectize[0]['id'] = 0;
        $auxSelectize[0]['name'] = system_showText(LANG_SITEMGR_NOOWNER);
        $auxSelectize[0]['email'] = '';
        $countAcc = 1;

        while ($row = mysqli_fetch_assoc($result)) {
            $auxSelectize[$countAcc]['id'] = $row['id'];
            $auxSelectize[$countAcc]['name'] = $row['first_name'].' '.$row['last_name'];
            $auxSelectize[$countAcc]['email'] = $row['email'];
            $countAcc++;
        }


        echo json_encode($auxSelectize, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        exit;
    }

?>

