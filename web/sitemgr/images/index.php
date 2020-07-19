<?
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /sitemgr/imgages/visitor.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include("../../conf/loadconfig.inc.php");

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();
permission_hasSMPerm();

if (empty($_GET['username'])) {
	echo 'Error: must provide username';
	return;
}

$username = $_GET['username'];

$filename = EDIRECTORY_ROOT . '/../image_uploads/' . $username;
$ext = 'jpg';
if (!file_exists($filename . '.' . $ext)) {
	$ext = 'png';
	if (!file_exists($filename . '.' . $ext)) {
		echo 'Error: File not found';
		return;
	}
}

$outname = $username . '.' . $ext;

$contentType = 'image/jpeg';
if ($ext === 'png') {
	$contentType = 'image/png';
}

header("Content-type: " . $contentType);
header("Cache-Control: no-store, no-cache");
header('Content-Disposition: attachment; filename="' . $outname . '"');
readfile($filename . '.' . $ext);