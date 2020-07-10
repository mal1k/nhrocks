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
	# * FILE: /includes/code/blog.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------

	$seoTitleField = "seo_title";
	$seoDescField = "seo_abstract";

	include(INCLUDES_DIR."/code/coverimage.php");

	if ($_SERVER['REQUEST_METHOD'] == "POST") {
        NewImageUploader::treatPost($url_base, "Post");

		##################################################
		### KEYWORDS
		##################################################
        unset($arr_keywords);
        unset($each_keyword);
        unset($aux_kw);
        unset($new_arr_keywords);
        unset($aux_keywords);
        $arr_keywords = explode(",", $keywords);
        foreach ($arr_keywords as $each_keyword) {
            $aux_kw = trim($each_keyword);
            if (string_strlen($aux_kw) > 0) {
                $new_arr_keywords[] = $aux_kw;
            }
        }
        if ($new_arr_keywords) {
            $aux_keywords = implode(" || ", $new_arr_keywords);
        }
        $_POST["keywords"] = $aux_keywords;
        $_POST["array_keywords"] = $new_arr_keywords;
		##################################################

		if ($_POST["seo_abstract"]) {
			$_POST["seo_abstract"] = str_replace(array("\r\n", "\n"), " ", $_POST["seo_abstract"]);
			$_POST["seo_abstract"] = str_replace("\"", "", $_POST["seo_abstract"]);
		}
		if ( $_POST["seo_keywords"] ) {
			$_POST["seo_keywords"] = str_replace("\"", "", $_POST["seo_keywords"]);
			$_POST["seo_keywords"] = str_replace(array("\r\n", "\n"), ", ", $_POST["seo_keywords"]);
		}

		$_POST["title"] = trim($_POST["title"]);
		$_POST["title"] = preg_replace('/\s\s+/', ' ', $_POST["title"]);
		$_POST["friendly_url"] = str_replace(".htm", "", $_POST["friendly_url"]);
		$_POST["friendly_url"] = str_replace(".html", "", $_POST["friendly_url"]);
		$_POST["friendly_url"] = trim($_POST["friendly_url"]);
		$sqlFriendlyURL = "";
		$sqlFriendlyURL .= " SELECT friendly_url FROM Post WHERE friendly_url = ".db_formatString($_POST["friendly_url"])." ";
		if ($id) $sqlFriendlyURL .= " AND id != $id ";
		$sqlFriendlyURL .= " LIMIT 1 ";
		$dbMain = db_getDBObject(DEFAULT_DB, true);
		$dbObjFriendlyURL = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
		$resultFriendlyURL = $dbObjFriendlyURL->query($sqlFriendlyURL);
		if (mysqli_num_rows($resultFriendlyURL) > 0) {
			if ($id) $_POST["friendly_url"] = $_POST["friendly_url"].FRIENDLYURL_SEPARATOR.$id;
			else $_POST["friendly_url"] = $_POST["friendly_url"].FRIENDLYURL_SEPARATOR.uniqid('', false);
		}
        if (!$id && !$_POST["friendly_url"]) {
            $_POST["friendly_url"] = uniqid('', false);
        }

		if (validate_form("blog", $_POST, $message_blog)) {

            mixpanel_trackFirstItem('Post');

			// removing linebreaks from seo_description
			if ( !$id ) {
                ($_POST["seo_abstract"] = str_replace("\n", " ", $_POST["seo_abstract"]));
            }

			$post = new Post($id);

			if (!$post->getString("id") || $post->getString("id") == 0){

				system_addItemGallery($gallery_hash, "", $gallery, $image_id, true);
				$message = 0;
				$post->makeFromRow($_POST);

			} else {

				system_addItemGallery($gallery_hash, "", $gallery, $image_id, true);
				$message = 1;
				$post->makeFromRow($_POST);
			}

			if ($image_id) {
                $post->setNumber("image_id", $image_id);
			}

			if ($remove_image) {
				$post->setNumber("image_id", 0);
			}

			$post->Save();

            /* ModStores Hooks */
            HookFire("blogcode_after_save", [
                "post" => &$post
            ]);

			// setting categories
			$return_categories_array = explode(",", $return_categories);
			$post->setCategories($return_categories_array); // MUST BE ALWAYS AFTER $POSTOBJECT->SAVE();

			header("Location: $url_redirect/index.php?newest=".$newest."&message=".$message."&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : ""));
			exit;
		}

		// removing slashes added if required
		$_POST = format_magicQuotes($_POST);
		$_GET  = format_magicQuotes($_GET);

		extract($_POST, null);
		extract($_GET, null);

	}

	# ----------------------------------------------------------------------------------------------------
	# FORMS DEFINES
	# ----------------------------------------------------------------------------------------------------
	$id = $_GET["id"] ? $_GET["id"] : $_POST["id"];
	$gallery_hash = $_POST["gallery_hash"] ? $_POST["gallery_hash"] : "blog".($id ? "_$id" : "")."_".uniqid(rand(), true);

	if ($id) {

		$post = new Post($id);
		$post->extract();

		if (!$post->getNumber("id")) {
            header("Location: $url_redirect/");
            exit;
        }

		if (!$message_blog){
			$sess_id = $gallery_hash;
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			$sql = "DELETE FROM Gallery_Temp WHERE sess_id = '$sess_id'";
			$dbObj->query($sql);
		}

        /* ModStores Hooks */
        HookFire("blogcode_after_fill_formdata", [
            "post" => &$post,
        ]);

	} else {

		$post = new Post($id);
		$post->makeFromRow($_POST);

		if (!$message_blog){
			$sess_id = $gallery_hash;
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			$sql = "DELETE FROM Gallery_Temp WHERE sess_id = '$sess_id'";
			$dbObj->query($sql);
		}

	}

	extract($_POST, null);
	extract($_GET, null);

	$categories = [];
    $selectizeCategs = array();
    $selectizeCategsIndex = 0;
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		if ($return_categories) {
			$return_categories_array = explode(",", $return_categories);
			foreach ($return_categories_array as $each_category) {
				$categories[] = new BlogCategory($each_category);
			}
		}
	} else {
		if (!$categories) {
			if ($post) {
				$categories = $post->getCategories();
			}
		}
	}
	if ($categories) {
		for ($i=0, $iMax = count($categories); $i< $iMax; $i++) {
			$arr_category[$i]["name"] = $categories[$i]->getString("title");
			$arr_category[$i]["value"] = $categories[$i]->getNumber("id");
			$arr_return_categories[] = $categories[$i]->getNumber("id");
		}
		if ($arr_return_categories) $return_categories = implode(",", $arr_return_categories);
		array_multisort($arr_category);
		$feedDropDown = "<select name='feed' id='feed' multiple size='5' style=\"width:500px\">";
		if ($arr_category) foreach ($arr_category as $each_category) {
			$feedDropDown .= "<option value='".$each_category["value"]."'>".$each_category["name"]."</option>";
			$feedAjaxCategory[] = $each_category["value"];
            $selectizeCategs[$selectizeCategsIndex]["value"] = $each_category["value"];
            $selectizeCategs[$selectizeCategsIndex]["name"] = $each_category["name"];
            $selectizeCategsIndex++;
		}
		$feedDropDown .= "</select>";
	} else {
		if ($return_categories) {
			$return_categories_array = explode(",", $return_categories);
			if ($return_categories_array) {
				foreach ($return_categories_array as $each_category) {
					$categories[] = new BlogCategory($each_category);
				}
			}
		}
		$feedDropDown = "<select name='feed' id='feed' multiple size='5' style=\"width:500px\">";
		if ($categories) {
			foreach ($categories as $category) {
				$name = $category->getString("title");
				$feedDropDown .= "<option value='".$category->getNumber("id")."'>$name</option>";
				$feedAjaxCategory[] = $category->getNumber("id");
                $selectizeCategs[$selectizeCategsIndex]["value"] = $category->getNumber("id");
                $selectizeCategs[$selectizeCategsIndex]["name"] = $name;
                $selectizeCategsIndex++;
			}
		}
		$feedDropDown .= "</select>";
	}

	##################################################
	### KEYWORDS
	##################################################
    unset($arr_keywords);
    if ($_POST["keywords"]) {
        $arr_keywords = explode(" || ", $_POST["keywords"]);
        $keywords = implode(",", $arr_keywords);
    } elseif ($post->getString("keywords")) {
        $arr_keywords = explode(" || ", $post->getString("keywords"));
        $keywords = implode(",", $arr_keywords);
    }
	##################################################

	$hasImage = false;
	$sess_id = $gallery_hash;
	$dbMain = db_getDBObject(DEFAULT_DB, true);
	$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
	$sql = "SELECT image_id FROM Gallery_Temp WHERE sess_id = '$sess_id'";
	$result = $dbObj->query($sql);

	if ($row = mysqli_fetch_assoc($result)) {
		$hasImage = true;
	}

    // Status Drop Down
    $statusObj = new ItemStatus();
    unset($arrayValue);
    unset($arrayName);
    $arrayValue = $statusObj->getValues();
    $arrayName = $statusObj->getNames();
    unset($arrayValueDD);
    unset($arrayNameDD);
    for ($i = 0, $iMax = count($arrayValue); $i < $iMax; $i++) {
        if ($arrayValue[$i] != "E") {
            $arrayValueDD[] = $arrayValue[$i];
            $arrayNameDD[] = $arrayName[$i];
        }
    }
    $statusDropDown = html_selectBox("status", $arrayNameDD, $arrayValueDD, ($status ? $status : "A"), "", "class=\"form-control status-select\"", "");

    //Auxiliary array to prepare the tutorail
    $arrayTutorial = array();
    $counterTutorial = 0;

	$imageUploader = new NewImageUploader("blog", $gallery_hash, $gallery_id, $levelMaxImages, SELECTED_DOMAIN_ID, true, true);
	$imageUploader->registerJavaScript();

    /* ModStores Hooks */
    HookFire("blogcode_after_setup_form");