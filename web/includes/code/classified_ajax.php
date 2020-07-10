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
    # * FILE: /includes/code/classified_ajax.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    if (isset($_GET["domain_id"])) define("SELECTED_DOMAIN_ID", $_GET["domain_id"]);
    include("../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    if(!empty($_SESSION['SM_LOGGEDIN'])){
        sess_validateSMSession();
    } else {
        sess_validateSession();
        $acctId = sess_getAccountIdFromSession();
    }

    header("Content-Type: application/json; charset=".EDIR_CHARSET, TRUE);
    header("Accept-Encoding: gzip, deflate");
    header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check", FALSE);
    header("Pragma: no-cache");

    # ----------------------------------------------------------------------------------------------------
    # GET
    # ----------------------------------------------------------------------------------------------------\
    if ($_SERVER['REQUEST_METHOD'] == "GET" and isset($_SERVER['HTTP_X_REQUESTED_WITH']) and ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
        $auxSelectize = [];

        $accountId = (int)$_GET['accountId'];
        $classifiedId = (int)$_GET['classifiedId'];
        $listingId = (int)$_GET['listingId'];

        if ($members and $acctId != $accountId) {
            echo json_encode([]);
            exit;
        }

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        /**
         * Get level with classified
         */
        $levelObj = new ListingLevel();
        $levels   = $levelObj->getValues();
        $classifiedLevels = [];
        foreach ( $levels as $level )
        {
            if ( $levelObj->getClassifiedQuantityAssociation( $level ) > 0)
            {
                $classifiedLevels[] = $level;
            }
        }

        if(count($classifiedLevels) == 0){
            echo json_encode([]);
            exit;
        }

        $classifiedLevels = implode(',', $classifiedLevels);

        $where = sprintf(' level IN (%s) ', $classifiedLevels);

        if ((int)$accountId > 0) {
            // with account
            $where .= ' AND account_id = '.$accountId;
        } else {
            $where .= ' AND (account_id = 0 OR account_id IS NULL) ';
        }

        if(!empty($_GET['query'])){
            $where .= " AND Listing.`title` LIKE '%".$_GET['query']."%' ";
        }

        /* the limit in SQL is linked with the limit of the plugin used, improve it */
        $sql = "SELECT id, title
                FROM Listing
                WHERE {$where}
                ORDER BY title
                LIMIT 1000";

        $result = $dbObj->query($sql);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $auxSelectize[] = [
                    'title' => $row['title'],
                    'id'    => $row['id'],
                ];
            }
        }

        echo json_encode($auxSelectize, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        exit;
    }
