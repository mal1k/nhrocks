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

$EXT_MAP = [
	'jpg' => 'image/jpeg',
	'png' => 'image/png',
	'pdf' => 'application/pdf',
];

$username = $_GET['username'];

$filename = EDIRECTORY_ROOT . '/../image_uploads/' . $username;

$path = null;
$contentType = null;
$outName = null;
foreach (array_keys($EXT_MAP) as $ext){
	if (file_exists($filename . '.' . $ext)) {
		$path = $filename . '.' . $ext;
		$contentType = $EXT_MAP[$ext];
		$outName = $username . '.' . $ext;
	}
}

if(!$path){
	echo 'Error: File not found';
	return;
}

header("Content-type: " . $contentType);
header("Cache-Control: no-store, no-cache");
header('Content-Disposition: attachment; filename="' . $outName . '"');
readfile($path);