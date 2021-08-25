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
	# * FILE: /loadcategorytree.php
	# ----------------------------------------------------------------------------------------------------

	define("SELECTED_DOMAIN_ID", $_GET["domain_id"]);

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("./conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# VALIDATION
	# ----------------------------------------------------------------------------------------------------
	include(EDIRECTORY_ROOT."/includes/code/validate_querystring.php");
	include(EDIRECTORY_ROOT."/includes/code/validate_frontrequest.php");

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", FALSE);
	header("Pragma: no-cache");
    header("Content-Type: text/html; charset=".EDIR_CHARSET, TRUE);
    header('Access-Control-Allow-Origin: *');

    // APENAS PARA DEV
    //header('Access-Control-Allow-Origin: *');

	$_GET["prefix"] = system_denyInjections($_GET["prefix"]);
	$_GET["category"] = system_denyInjections($_GET["category"]);
	$_GET["domain_id"] = system_denyInjections($_GET["domain_id"]);

	$return = "";

    if (string_strpos(string_strtolower($_GET["category"]), "category") !== false) {

        if ($_GET["category"] === "ListingCategory" && $_GET["action"] === "template") {
            $listingtemplate = new ListingTemplate($_GET["template_id"]);
            if ($listingtemplate) {
                $templatecategories = $listingtemplate->getCategories(false);
            }
        }

        $isNullSegment = "";
        if (!($_GET["category_id"] > 0)) {
            $isNullSegment = "ISNULL(category_id) OR ";
        }
        $sql_categories = 'SELECT id, title FROM '.$_GET['category'].' WHERE ('. $isNullSegment .' category_id = '.db_formatNumber($_GET['category_id']).')'.(is_array($templatecategories) ? ' AND id IN ('.implode($templatecategories, ',').')' : '')." AND title <> '' AND enabled = 'y' ORDER BY title";
        $categories = db_getFromDBBySQL($_GET["category"], $sql_categories,'',true, $_GET["domain_id"]);
    }

    if ($categories) {

        $arrayCategoriesIds = explode(",",$_GET["ajax_categories"]);

        $dbObj_main = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID($_GET["domain_id"], $dbObj_main);
        foreach ($categories as $category) {

            if (in_array($category->getNumber("id"), $arrayCategoriesIds)) {
                $style = "style=\"display:none;\"";
                $styleSpan = "class=\"selected category-child\"";
            } else {
                $style = "";
                $styleSpan = "class=\"category-child\"";
            }

            if ($_GET["new_tree"] == "true") {
                $path_count = count($category->getFullPath());
                $sql = "SELECT id FROM ".$_GET["category"]." WHERE category_id =".$category->getNumber("id")." AND title <> '' AND enabled = 'y'";
                $result = $dbObj->query($sql);
                if ($_GET["action"] != "main" && (($path_count < CATEGORY_LEVEL_AMOUNT) && (mysqli_num_rows($result) > 0))) {
                    //Load subcategories
                    $return .= "<li><span class=\"btn btn-opencategory\" id=\"openTree_".$category->getNumber("id")."\" onclick=\"loadCategoryTree('all', '".$_GET["prefix"]."', '".$_GET["category"]."', ".$category->getNumber("id").", 0, '".DEFAULT_URL."'".($_GET["domain_id"] ? ",".$_GET["domain_id"] : "").", true);\" ><i class=\"ionicons ion-ios7-plus-outline\"></i><i class=\"ionicons ion-ios7-minus-outline hidden\"></i> ".$category->getString("title")."</span>\n<ul id=\"".$_GET["prefix"]."categorytree_id_".$category->getNumber("id")."\"></ul>\n</li>\n";
                } else {
                    //Add category
                    $return .= "<li class=\"no-child\"><span $styleSpan id=\"span_".$category->getNumber("id")."\" data-catID=\"".$category->getNumber("id")."\">".$category->getString("title")."</span></li>";
                    $return .= "<li id=\"liContent".$category->getNumber("id")."\" style=\"display:none\">".$category->getString("title")."</li>";
                }
            } else {

                if ($_GET["action"] == "main") {
                    $return .= "<li class=\"categoryBullet\">".$category->getString("title")." <a id='categoryAdd".$category->getNumber("id")."' $style href=\"javascript:void(0);\" onclick=\"JS_addCategory(".$category->getNumber("id").");\" class=\"categoryAdd\">".system_showText(LANG_ADD)."</a></li>";
                    $return .= "<li id=\"liContent".$category->getNumber("id")."\" style=\"display:none\">".$category->getString("title")."</li>";
                } else {
                    $path_count = count($category->getFullPath());
                    $sql = "SELECT id FROM ".$_GET["category"]." WHERE category_id =".$category->getNumber("id")." AND title <> '' AND enabled = 'y'";
                    $result = $dbObj->query($sql);
                    if (($path_count < CATEGORY_LEVEL_AMOUNT) && (mysqli_num_rows($result) > 0)) {
                        $return .= "<li><a href=\"javascript:void(0);\" onclick=\"loadCategoryTree('all', '".$_GET["prefix"]."', '".$_GET["category"]."', ".$category->getNumber("id").", 0, '".DEFAULT_URL."',".$_GET["domain_id"].");\" class=\"switchOpen\" id=\"".$_GET["prefix"]."opencategorytree_id_".$category->getNumber("id")."\">+</a><a href=\"javascript:void(0);\" onclick=\"loadCategoryTree('all', '".$_GET["prefix"]."', '".$_GET["category"]."', ".$category->getNumber("id").", 0, '".DEFAULT_URL."',".$_GET["domain_id"].");\" class=\"categoryTitle\" id=\"".$_GET["prefix"]."opencategorytree_title_id_".$category->getNumber("id")."\">".$category->getString("title")."</a><a href=\"javascript:void(0);\" onclick=\"closeCategoryTree('".$_GET["prefix"]."', '".$_GET["category"]."', ".$category->getNumber("id").", '".DEFAULT_URL."');\" class=\"switchClose\" id=\"".$_GET["prefix"]."closecategorytree_id_".$category->getNumber("id")."\" style=\"display: none;\">-</a><a href=\"javascript:void(0);\" onclick=\"closeCategoryTree('".$_GET["prefix"]."', '".$_GET["category"]."', ".$category->getNumber("id").", '".DEFAULT_URL."');\" class=\"categoryTitle\" id=\"".$_GET["prefix"]."closecategorytree_title_id_".$category->getNumber("id")."\" style=\"display: none;\">".$category->getString("title")."</a>\n<ul id=\"".$_GET["prefix"]."categorytree_id_".$category->getNumber("id")."\" style=\"display: none;\"></ul>\n</li>\n";
                    } else {
                        $return .= "<li class=\"categoryBullet\">".$category->getString("title")." <a id='categoryAdd".$category->getNumber("id")."' href=\"javascript:void(0);\" $style onclick=\"JS_addCategory(".$category->getNumber("id").");\" class=\"categoryAdd\">".system_showText(LANG_ADD)."</a></li>";
                        $return .= "<li id=\"liContent".$category->getNumber("id")."\" style=\"display:none\">".$category->getString("title")."</li>";
                    }
                }

            }
        }
    } else {
        $return = "<li class=\"informationMessage\">".system_showText(LANG_CATEGORY_NOTFOUND)."</li>";
    }

	echo $return;
