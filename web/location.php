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
# * FILE: /location.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include('./conf/loadconfig.inc.php');

$format = isset($_GET['format']) ? $_GET['format'] : 'html';

# ----------------------------------------------------------------------------------------------------
# FILE HEADER
# ----------------------------------------------------------------------------------------------------
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

if ($format === 'json') {
    header('Content-Type: application/json; charset='.EDIR_CHARSET, true);
} else {
    header('Content-Type: text/html; charset='.EDIR_CHARSET, true);
}

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------

function locationFormatHtml($locations, $level)
{
    if (!$locations) {
        return 'empty';
    }

    $return = '<option id="l_location_'.$level.'" value=""></option>';
    foreach ($locations as $each_location) {
        $location_id = $each_location['id'];
        $location_name = $each_location['name'];
        $return .= '<option id="option_L'.$level.'_ID'.$location_id.'" value="'.$location_id.'">'.trim($location_name).'</option>';
    }

    return $return;
}

function locationFormatJson($locations)
{
    $return = [];

    if (!$locations) {
        return json_encode([]);
    }

    foreach ($locations as $location) {
        $return[] = [
            'id'   => $location['id'],
            'name' => $location['name'],
        ];
    }

    return json_encode($return);
}

$return = 'empty';

if (!is_numeric($_GET['level']) || !is_numeric($_GET['childLevel']) || !is_numeric($_GET['id'])) {
    exit;
}

$id = $_GET['id'];
$level = $_GET['level'];
$childLevel = $_GET['childLevel'];
$type = $_GET['type'];

if ($type === 'byId' && $childLevel) {
    $objLocationLabel = 'Location'.$childLevel;
    $locationObject = new $objLocationLabel;
    $locationObject->SetString('location_'.$level, $id);
    $retrieved_locations = $locationObject->retrieveLocationByLocation($level);

    if ($format === 'html') {
        $return = locationFormatHtml($retrieved_locations, $childLevel);
    } elseif ($format === 'json') {
        $return = locationFormatJson($retrieved_locations);
    }
} elseif ($type === 'All') {
    $objLocationLabel = 'Location'.$level;
    $locationObject = new $objLocationLabel;
    $retrieved_locations = $locationObject->retrieveAllLocation();

    if ($format === 'html') {
        $return = locationFormatHtml($retrieved_locations, $level);
    } elseif ($format === 'json') {
        $return = locationFormatJson($retrieved_locations);
    }
}

echo $return;
