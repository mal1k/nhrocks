<?php

/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2019 Arca Solutions, Inc. All Rights Reserved.           #
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
# * FILE: /includes/code/unsplash.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
if (isset($_GET["domain_id"])) define("SELECTED_DOMAIN_ID", $_GET["domain_id"]);
include("../../conf/loadconfig.inc.php");

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
if(strpos( $_SERVER['HTTP_REFERER'], MEMBERS_ALIAS) !== false){
    sess_validateSession();
} else {
    sess_validateSMSession();
}

header("Content-Type: application/json; charset=" . EDIR_CHARSET, TRUE);
header("Accept-Encoding: gzip, deflate");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check", FALSE);
header("Pragma: no-cache");

# ----------------------------------------------------------------------------------------------------
# GET
# ----------------------------------------------------------------------------------------------------\

if ($_SERVER['REQUEST_METHOD'] == "GET" and isset($_SERVER['HTTP_X_REQUESTED_WITH']) and ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {

    $page = (isset($_GET['page']) ? $_GET['page'] : 1);
    $query = $_GET['query'];

    $return = image_getUnsplash($page, $query);

    echo json_encode($return, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    exit;
}
