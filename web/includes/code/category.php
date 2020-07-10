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
# * FILE: /includes/code/category.php
# ----------------------------------------------------------------------------------------------------

####################################################################################################
### PAY ATTENTION - SAME CODE FOR LISTING, EVENT, CLASSIFIED, ARTICLE AND BLOG
####################################################################################################

# ----------------------------------------------------------------------------------------------------
# AUX
# ----------------------------------------------------------------------------------------------------
if ($_POST['title']) {
    $_POST['title'] = trim($_POST['title']);
    $_POST['title'] = preg_replace('/\s\s+/', ' ', $_POST['title']);
}

# ----------------------------------------------------------------------------------------------------
# SUBMIT
# ----------------------------------------------------------------------------------------------------

/* Handles all AJAX requests */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'ajax') {
        $response = [
            'status' => false
        ];

        /* Category image removal */
        if ($_POST['type'] === 'removeImage') {

            $category = new $_POST['module']($id);
            $imageId = $category->getNumber('image_id');

            $category->setString('image_id', 'NULL');

            $category->Save();

            if ($imageId) {
                $image = new Image($imageId);
                $image->getNumber('id') and $image->delete();
            }

            $response['status'] = true;
        }

        /* Category icon removal */
        if ($_POST['type'] === 'removeIcon') {

            $category = new $_POST['module']($id);
            $iconId = $category->getNumber('icon_id');

            $category->setString('icon_id', 'NULL');

            $category->Save();

            if ($iconId) {
                $image = new Image($iconId);
                $image->getNumber('id') and $image->delete();
            }

            $response['status'] = true;
        }

        /* ModStores Hooks */
        HookFire('categorycode_after_remove_image', [
            'id'       => &$id,
            'response' => &$response
        ]);

        echo json_encode($response);
        exit();
    }


    if ($_POST['seo_description']) {
        $_POST['seo_description'] = str_replace('"', '', $_POST['seo_description']);
    }
    if ($_POST['seo_keywords']) {
        $_POST['seo_keywords'] = str_replace('"', '', $_POST['seo_keywords']);
    }

    if (validate_form('category', $_POST, $message_category)) {

        /* @var ListingCategory $category */

        $obj = $_POST['table_category'];

        /* ModStores Hooks */
        HookFire( 'categorycode_before_initialize_objectonformvalidate', [
            'obj' => &$obj
        ]);

        $category = new $obj($id);

        if ($_FILES['image']['name'] && $_FILES['image']['tmp_name'] && !$_FILES['image']['error']) {
            $imageArray = image_uploadForItem($_FILES['image']['tmp_name'], 'sitemgr_', IMAGE_CATEGORY_FULL_WIDTH,
                IMAGE_CATEGORY_FULL_HEIGHT);
            if ($imageArray['success']) {
                $upload_image = 'success';
            } else {
                $upload_image = 'failed';
            }
        }

        if ($_FILES['icon']['name'] && $_FILES['icon']['tmp_name'] && !$_FILES['icon']['error']) {
            $iconArray = image_uploadForItem($_FILES['icon']['tmp_name'], 'sitemgr_', ICON_CATEGORY_WIDTH, ICON_CATEGORY_HEIGHT, false, 'icon_id');
            if ($iconArray['success']) {
                $upload_icon = 'success';
            } else {
                $upload_icon = 'failed';
            }
        }

        //Saving category
        if ($upload_image !== 'failed' && $upload_icon !== 'failed') {

            $_POST['featured'] = ($_POST['featured'] === 'on' ? 'y' : 'n');
            $_POST['enabled'] = ($_POST['clickToDisable'] === 'on' ? 'n' : 'y');

            $category->makeFromRow($_POST);

            if ($upload_image === 'success') {
                $category->updateImage($imageArray);
            }
            if ($upload_icon === 'success') {
                $category->updateIcon($iconArray);
            }

            if (string_strlen($keywords) === '') {
                $category->setString('keywords', '');
            }

            if ($category_id && $_POST['featured'] && count($category->getFullPath()) == 2) {
                $dbMain = db_getDBObject(DEFAULT_DB, true);
                $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
                $sql = 'SELECT featured FROM '. $_POST['table_category'] . " WHERE id = $category_id";
                $result = $dbObj->query($sql);
                $row = mysqli_fetch_assoc($result);
                $father_featured = $row['featured'];
                if ($father_featured === 'n') {
                    $featuredMessage = 8;
                }
            }

            mixpanel_trackFirstItem($_POST['table_category']);

            $category->Save();

            $moduleScalabilityConstantName = string_strtoupper(str_replace('Category',
                    '', $_POST['table_category'])) .'_SCALABILITY_OPTIMIZATION';
            $moduleScalability = (defined($moduleScalabilityConstantName) ? constant($moduleScalabilityConstantName) : 'off');

            /* ModStores Hooks */
            HookFire('categorycode_after_save', [
                'category' => &$category,
            ]);

            //Updating items fulltext fields
            if ($moduleScalability !== 'on') {
                $category->updateFullTextItems();
            }

            if ($_POST['category_id']) {
                if ($_POST['id']) {
                    $message = 2;
                    if ($_POST['clickToDisable']) {
                        $langMessage = 6;
                    }
                } else {
                    $message = 3;
                    if ($_POST['clickToDisable']) {
                        $langMessage = 6;
                    }
                }
            } else {
                if ($_POST['id']) {
                    $message = 4;
                    if ($_POST['clickToDisable']) {
                        $langMessage = 7;
                    }
                } else {
                    $message = 5;
                    if ($_POST['clickToDisable']) {
                        $langMessage = 7;
                    }
                }
            }

            header("Location: $url_redirect/index.php?message=" . $message .'&langmessage='. $langMessage .'&featmessage='. $featuredMessage .'&category_id='. $category_id . "&screen=$screen&letter=$letter" . (($url_search_params) ? "&$url_search_params" : ''));
            exit;
        } else {
            if ($upload_image === 'failed' || $upload_icon === 'failed') {
                $message_category .= system_showText(LANG_MSG_INVALID_IMAGE_TYPE);
            }
        }

    }

    // removing slashes added if required
    $_POST = format_magicQuotes($_POST);
    $_GET = format_magicQuotes($_GET);
    extract($_POST);
    extract($_GET);

}

# ----------------------------------------------------------------------------------------------------
# FORMS DEFINES
# ----------------------------------------------------------------------------------------------------
if ($id) {
    if (!is_numeric($id)) {
        header("Location: $url_redirect/");
        exit;
    }
    $category = db_getFromDB(string_strtolower($table_category), 'id', db_formatNumber($id), 1, '', 'object',
        SELECTED_DOMAIN_ID);
    $category->extract();
    $featured = ($featured === 'y' ? 'on' : '');
    $enabled = ($enabled === 'y' ? 'on' : '');

    /* ModStores Hooks */
    HookFire('categorycode_after_fill_formdata', [
        'category' => &$category,
    ]);

} else {
    $enabled = ($_POST['clickToDisable'] === 'on' ? '' : 'on');
    $featured = 'new';
}

extract($_POST);
extract($_GET);

$fatherCategoryArray = db_getFromDB(string_strtolower($table_category), 'id', $category_id, 1, '', 'array',
    SELECTED_DOMAIN_ID, false, '`id`, `title`');

$featuredcategory = '';
setting_get(string_strtolower(str_replace('Category', '', $table_category)) .'_featuredcategory', $featuredcategory);
if ($featuredcategory) {

    $dbMain = db_getDBObject(DEFAULT_DB, true);
    $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

    $cat_level = 0;
    $category_id_aux = $fatherCategoryArray['id'];
    while ($category_id_aux != 0) {
        $sql = "SELECT category_id FROM $table_category WHERE id = $category_id_aux";
        $result = $dbObj->query($sql);
        $row = mysqli_fetch_assoc($result);
        $category_id_aux = $row['category_id'];
        $cat_level++;
    }

    if ($cat_level >= FEATUREDCATEGORY_LEVEL_AMOUNT) {
        $featuredcategory = '';
    }
}

$fullWidth = $category_id ? true : false;

/* ModStores Hooks */
HookFire('categorycode_after_setup_form', [
    'message_category' => &$message_category,
    'fullWidth'        => &$fullWidth
]);