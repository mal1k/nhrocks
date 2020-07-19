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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	http_response_code(500);
	return;
}

if (empty($_POST['username'])) {
	echo 'Error: must provide username';
	return;
}

$username = $_POST['username'];

$filename = EDIRECTORY_ROOT . '/../image_uploads/' . $username;
$ext = 'jpg';
$deleted = false;
if (file_exists($filename . '.' . $ext)) {
	unlink($filename . '.' . $ext);
	$deleted = true;
}
$ext = 'png';
if (file_exists($filename . '.' . $ext)) {
	unlink($filename . '.' . $ext);
	$deleted = true;
}

if ($deleted) {
	echo 'File deleted';
} else {
	http_response_code(404);
	echo 'File not found';
}