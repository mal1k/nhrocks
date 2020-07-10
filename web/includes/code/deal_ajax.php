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
    # * FILE: /includes/code/deal_ajax.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    if (isset($_GET["domain_id"])) define("SELECTED_DOMAIN_ID", $_GET["domain_id"]);
    include("../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSMSession();

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
        $listingId = (int)$_GET['listingId'];

        $dealId = (int)$_GET['dealId'];
        if ($dealId) {
            $promotion = new Promotion($dealId);
            $listingByDealId = $promotion->getNumber("listing_id");
        } else {
            $listingByDealId = null;
        }

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        /**
         * Get level with promotion
         */
        $levelObj = new ListingLevel();
        $levels = $levelObj->getValues();
        $dealLevels = [];
        foreach ($levels as $level) {
            if ($levelObj->getDeals($level) > 0) {
                $dealLevels[] = $level;
            }
        }

        if ($dealLevels) {
            $accountSegment = "AND (ISNULL(account_id) OR account_id = 0)";
            if ($accountId > 0) {
                $accountSegment = " AND account_id = " . $accountId;
            }

            $dealLevels = implode(",", $dealLevels);

            $where = " (level IN ({$dealLevels}) {$accountSegment}) ";

            if($_GET['query'] != ''){
                $where .= "AND Listing.`title` LIKE '%".$_GET['query']."%' ";
            }

            if($listingId != ''){
                $where .= " AND Listing.`id` = {$listingId} ";
            }

            $sql = "SELECT id, title, status,account_id, `level`
                    FROM Listing
                    WHERE {$where}
                    ORDER BY Listing.`title` LIMIT 1000";

            $result = $dbObj->query($sql);

            $listLevelObj = new ListingLevel();
            $listObj = new Listing();
            $countListing = 1;

            while ($rowListings = mysqli_fetch_assoc($result)) {
                $countDeal = $listObj->countDeals($rowListings['id']);
                $limitDeal = $listLevelObj->getDeals($rowListings['level']);

                if ($rowListings['id'] == $listingByDealId || $limitDeal >= $countDeal) {
                    $auxSelectize[] = [
                        'title' => $rowListings['title'],
                        'id'    => $rowListings['id'],
                    ];
                }
            }
        }
        echo json_encode($auxSelectize, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        exit;
    }
