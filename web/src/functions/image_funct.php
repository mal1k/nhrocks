<?

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
# * FILE: /functions/image_funct.php
# ----------------------------------------------------------------------------------------------------

function image_getNewDimension($maxW, $maxH, $oldW, $oldH, &$newW, &$newH)
{
    if (($oldW <= $maxW) && ($oldH <= $maxH)) { // without resize
        $newW = $oldW;
        $newH = $oldH;
    } else { // with resize
        if (($maxW / $oldW) <= ($maxH / $oldH)) { // resize from width
            $newW = $oldW * ($maxW / $oldW);
            $newH = $oldH * ($maxW / $oldW);
        } elseif (($maxW / $oldW) > ($maxH / $oldH)) { // resize from height
            $newW = $oldW * ($maxH / $oldH);
            $newH = $oldH * ($maxH / $oldH);
        }
    }
}

function image_upload_check($tmp_name)
{
    $types = ["1" => "GIF", "2" => "JPG", "3" => "PNG"];
    $image_temp = $tmp_name;
    $info = @getimagesize($image_temp);
    $row_image = [];
    $row_image["type"] = $types[$info[2]];

    return in_array($types[$info[2]], ["JPG", "GIF", "PNG"]);
}

function image_upload($tmp_name, $maxWidth, $maxHeight, $prefix, $force_main = false, $resize = true)
{

    if ($force_main) {
        $_image_dir = PROFILE_IMAGE_DIR;
    } else {
        $_image_dir = IMAGE_DIR;
    }

    $types = ["1" => "GIF", "2" => "JPG", "3" => "PNG"];
    $image_temp = $tmp_name;
    $info = @getimagesize($image_temp);
    $row_image["type"] = $types[$info[2]];
    $row_image["width"] = $info[0];
    $row_image["height"] = $info[1];
    $row_image["prefix"] = $prefix;

    if (($types[$info[2]] == "JPG") || ($types[$info[2]] == "GIF") || ($types[$info[2]] == "PNG")) {

        $unique_id = md5(uniqid(rand(), true));

        if ($row_image["type"] == "GIF") {
            $new_name = $unique_id.".gif";
        }
        if ($row_image["type"] == "JPG") {
            $new_name = $unique_id.".jpg";
        }
        if ($row_image["type"] == "PNG") {
            $new_name = $unique_id.".png";
        }

        @rename($image_temp, TMP_FOLDER."/$new_name");

        if ($resize) {
            image_getNewDimension($maxWidth, $maxHeight, $row_image["width"], $row_image["height"], $newWidth,
                $newHeight);
        } else {
            $newWidth = $row_image["width"];
            $newHeight = $row_image["height"];
        }

        $thumb = new ThumbGenerator();
        $thumb->set("thumbWidth", $newWidth);
        $thumb->set("thumbHeight", $newHeight);
        $thumb->set("destination_path", $image_temp);
        $thumb->makeThumb(TMP_FOLDER."/$new_name");

        //if ($row_image["type"] == "GIF") $extension = "gif";
        //if ($row_image["type"] == "JPG") $extension = "jpg";
        //if ($row_image["type"] == "PNG") $extension = "png";

        $extension = string_strtolower($types[$info[2]]);

        if ((FORCE_SAVE_JPG_AS_PNG == "on") && ($extension == "jpg")) {
            $extension = "png";
            $types[$info[2]] = $extension;
        }

        $info = getimagesize($image_temp);

        $row_image["type"] = $types[$info[2]];
        $row_image["width"] = $info[0];
        $row_image["height"] = $info[1];
        $row_image["prefix"] = $prefix;

        $imageObj = new Image($row_image, $force_main);
        $imageObj->save();

        copy($image_temp,
            $_image_dir."/".$imageObj->getString("prefix")."photo_".$imageObj->getNumber("id").".$extension");
        @unlink($image_temp);

        if ($new_name) {
            @unlink(TMP_FOLDER."/".$new_name);
        }

        return $imageObj;

    } else {
        @unlink($image_temp);

        return;
    }

}

function image_uploadForItem(
    $tmp_name,
    $prefix,
    $fullwidth,
    $fullheight,
    $force_main = false,
    $field_name = 'image_id'
) {

    $types = ["1" => "GIF", "2" => "JPG", "3" => "PNG"];
    $info = getimagesize($tmp_name);
    $row_image["type"] = $types[$info[2]];

    if (($types[$info[2]] == "JPG") || ($types[$info[2]] == "GIF") || ($types[$info[2]] == "PNG")) {

        copy($tmp_name, TMP_FOLDER."/thumb_".string_substr(strrchr($tmp_name, "/"), 1));

        $imageObj = image_upload($tmp_name, $fullwidth, $fullheight, $prefix, $force_main);
        $imageObj->save();

        $array["success"] = true;
        $array[$field_name] = $imageObj->id;

    } else {
        unlink($tmp_name);
        $array["success"] = false;
        $array[$field_name] = 0;
    }

    return $array;

}

function image_uploadBadges($tmp_name, $prefix, $fullwidth, $fullheight, $force_main = false)
{

    $types = ["1" => "GIF", "2" => "JPG", "3" => "PNG"];
    $info = getimagesize($tmp_name);
    $row_image["type"] = $types[$info[2]];

    if (($types[$info[2]] == "JPG") || ($types[$info[2]] == "GIF") || ($types[$info[2]] == "PNG")) {

        copy($tmp_name, TMP_FOLDER."/thumb_".string_substr(strrchr($tmp_name, "/"), 1));

        $imageObj = image_upload($tmp_name, $fullwidth, $fullheight, $prefix, $force_main);
        $imageObj->save();

        $array["success"] = true;
        $array["image_id"] = $imageObj->id;

    } else {
        unlink($tmp_name);
        $array["success"] = false;
        $array["image_id"] = 0;
    }

    return $array;

}

/**
 * Validates file extension and moves $tmp_name to $filename.
 *
 * @param string $filename
 * @param string $tmp_name
 * @param boolean $replaceExtension Replaces extension of the $filename with the correct extention for the mime type.
 * @return string Uploaded file path
 */
function image_uploadImage($filename, $tmp_name, $replaceExtension = false)
{
    $supports = [
        IMAGETYPE_GIF  => '.gif',
        IMAGETYPE_JPEG => '.jpg',
        IMAGETYPE_PNG  => '.png',
    ];

    $info = getimagesize($tmp_name);

    $imageType = $info[2];

    if ($replaceExtension) {
        $filename = preg_replace('/\.[A-Za-z0-9]{3,6}$/', $supports[$imageType], $filename);
    }

    if (is_array($info) && in_array($imageType, array_keys($supports))) {
        @copy($tmp_name, EDIRECTORY_ROOT.$filename);
        @unlink($tmp_name);

        return $filename;
    }

    if ($tmp_name) {
        unlink($tmp_name);
    }

    return false;
}

/**
 * Upload no image.
 * @param $filename
 * @param $tmp_name
 * @return string Uploaded file path
 */
function image_uploadForNoImage($filename, $tmp_name)
{
    $filename = image_uploadImage($filename, $tmp_name, true);

    if (!$filename) {
        return false;
    }

    //Fix IE7 / IE8
    $cssFile = EDIRECTORY_ROOT.NOIMAGE_PATH."/".NOIMAGE_NAME.".".NOIMAGE_CSSEXT;
    $content = ".no-image{\n\t".system_getNoImageStyle()." !important;\n}";
    $content .= "\n.ie .no-image {
        filter: progid:DXImageTransform.Microsoft.AlphaImageLoader( 
                src='".system_getNoImageStyle(false, true)."', sizingMethod='scale');
        background-image : none !important;
    }";

    if (!file_put_contents($cssFile, $content)) {
        return false;
    }

    return $filename;
}

function image_getImageSizeByURL($url)
{
    if (!$url) {
        return false;
    }

    $agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)";
    $ref = DEFAULT_URL.$_SERVER["PHP_SELF"];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_REFERER, $ref);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $data = curl_exec($ch);

    curl_close($ch);
    $filename = EDIRECTORY_ROOT."/custom/domain_".SELECTED_DOMAIN_ID."/tmp/temp.".time();

    if (!is_dir($concurrentDirectory = EDIRECTORY_ROOT."/custom/domain_".SELECTED_DOMAIN_ID."/tmp/") && !mkdir($concurrentDirectory, 0755,
            true) && !is_dir($concurrentDirectory)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }

    $fp = fopen($filename, "w+");
    fwrite($fp, $data);
    fclose($fp);

    $info = getimagesize($filename);

    @unlink($filename);

    return $info;
}

/**
 * Function to Convert JPG to PNG
 * @param $file
 * @param $fileSize
 * @param $maxfileSize
 * @return bool|string
 */
function image_ConvertJPGtoPNG($file, $fileSize, $maxfileSize)
{
    if (!$file) {
        return false;
    }

    if ($fileSize > $maxfileSize) {
        return false;
    }

    $info = getimagesize($file);

    if ($info[2] != IMAGETYPE_JPEG) {
        return false;
    }

    $unique_id = md5(uniqid(rand(), true));
    $new_name = TMP_FOLDER."/".$unique_id.".png";

    $image_p = imagecreatetruecolor($info[0], $info[1]);
    $image = imagecreatefromjpeg($file);

    imagecopyresampled($image_p, $image, 0, 0, 0, 0,
        $info[0], $info[1], $info[0], $info[1]);

    imagepng($image_p, $new_name);

    return $new_name;
}

function image_resizeImage($file, $newWidth, $newHeight)
{

    $info = @getimagesize($file);
    $imageType = $info[2];
    $dir = EDIRECTORY_ROOT."/custom/domain_".SELECTED_DOMAIN_ID."/image_files/";

    switch ($imageType) {
        case 1:
            $img_type = 'gif';
            $img_r = imagecreatefromgif($file);
            break;
        case 2:
            $img_type = 'jpeg';
            $img_r = imagecreatefromjpeg($file);
            break;
        case 3:
            $img_type = 'png';
            $img_r = imagecreatefrompng($file);
            break;
    }

    $dst_r = ImageCreateTrueColor($newWidth, $newHeight);

    if ($img_r) {
        $lowQuality = false;
        if ($img_type == "png" || $img_type == "gif") {
            imagealphablending($dst_r, false);
            imagesavealpha($dst_r, true);
            $transparent = imagecolorallocatealpha($dst_r, 255, 255, 255, 127);
            imagefill($dst_r, 0, 0, $transparent);
            imagecolortransparent($dst_r, $transparent);
            $transindex = imagecolortransparent($img_r);
            if ($transindex >= 0) {
                $lowQuality = true; //only use imagecopyresized (low quality) if the image is a transparent gif
            }
        }

        if ($img_type == "gif" && $lowQuality) { //use imagecopyresized for gif to keep the transparency. The functions imagecopyresized and imagecopyresampled works in the same way with the exception that the resized image generated through imagecopyresampled is smoothed so that it is still visible.
            //low quality
            imagecopyresized($dst_r, $img_r, 0, 0, 0, 0, $newWidth, $newHeight, $info[0], $info[1]);
        } else {
            //better quality
            imagecopyresampled($dst_r, $img_r, 0, 0, 0, 0, $newWidth, $newHeight, $info[0], $info[1]);
        }
    }

    if ((FORCE_SAVE_JPG_AS_PNG == "on") && ($img_type == "jpeg")) {
        $crop_image = $dir."crop_image.png";
    } else {
        $crop_image = $dir."crop_image.$img_type";
    }

    if ($img_type == 'gif') {
        imagegif($dst_r, $crop_image);
    } elseif ($img_type == 'jpeg') {
        if (FORCE_SAVE_JPG_AS_PNG == "on") {
            imagepng($dst_r, $crop_image);
        } else {
            imagejpeg($dst_r, $crop_image);
        }
    } elseif ($img_type == 'png') {
        imagepng($dst_r, $crop_image);
    }

    return $crop_image;

}

function image_LogoUploaded()
{
    $headerImage = SymfonyCore::getContainer()->get('multi_domain.parameter')->get('domain.header.image');

    return $headerImage && file_exists(EDIRECTORY_ROOT.$headerImage);
}

function image_DefaultImageUploaded()
{
    $defaultImage = SymfonyCore::getContainer()->get('multi_domain.parameter')->get('domain.noimage');

    return $defaultImage && file_exists(EDIRECTORY_ROOT.$defaultImage);
}

/**
 * Get logo image
 * @author Diego de Biagi <diego.biagi@arcasolutions.com>
 * @since v11.4.00
 * @param $placeholder boolean
 * @return string
 */
function image_getLogoImage($placeholder = false, $defaultUrl = false)
{
    $headerImage = SymfonyCore::getContainer()->get('multi_domain.parameter')->get('domain.header.image');

    if ($headerImage && file_exists(EDIRECTORY_ROOT.$headerImage)) {
        return ($defaultUrl ? $defaultUrl : DEFAULT_URL).$headerImage.'?'.time();
    }

    if ($placeholder) {
        return $placeholder;
    }

    return DEFAULT_URL.'/assets/images/img_logo.png';
}

function image_getLogoImagePath()
{
    $logoImg = SymfonyCore::getContainer()->getParameter('domain.header.image');

    return SymfonyCore::getContainer()->get("request_stack")->getCurrentRequest()->getSchemeAndHttpHost().$logoImg;
}

/**
 * Get no image
 *
 * @author Diego de Biagi <diego.biagi@arcasolutions.com>
 * @since v11.4.00
 * @return string
 */
function image_getNoImage($placeholder = false)
{
    $defaultImage = SymfonyCore::getContainer()->get('multi_domain.parameter')->get('domain.noimage');

    if ($defaultImage && file_exists(EDIRECTORY_ROOT.$defaultImage)) {
        return DEFAULT_URL.$defaultImage.'?'.time();
    }

    if ($placeholder) {
        return $placeholder;
    }

    return false;
}

/**
 * Get list of Images using Unsplash API
 *
 * @author João P. Scnias <joao.schias@arcasolutions.com>
 *
 * @param $page int
 * @param $query string
 *
 * @return array
 */
function image_getUnsplash($page = 1, $query = '')
{
    $endpoint = 'https://api.unsplash.com/';

    $data = [
        'page' => $page,
        'per_page' => '12',
        'orientation' => 'landscape',
        'client_id' => UNSPLASH_ACCESS_KEY,
    ];

    $return = [];

    if (isset($query) and $query) {
        $data['query'] = $query;
        $endpoint .= 'search/photos?' . http_build_query($data);
    } else {
        $data['order_by'] = 'latest';
        $endpoint .= 'photos?' . http_build_query($data);
    }

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Version: v1'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = json_decode(curl_exec($ch));
    $error = curl_error($ch);
    curl_close($ch);

    $data = (isset($data->results) ? $data->results : $data);

    if (isset($data) and $data and count($data)) {
        foreach ($data as $key => $value) {
            $return[$key]['id'] = $value->id;
            $return[$key]['description'] = $value->description;
            $return[$key]['thumb'] = $value->urls->thumb;
            $return[$key]['regular'] = $value->urls->regular;
            $return[$key]['download_location'] = $value->links->download_location . '?client_id=' . UNSPLASH_ACCESS_KEY;
            $return[$key]['photographer'] = $value->user->name;
            $return[$key]['photographer_link'] = $value->user->links->html.'?utm_source=eDirectory&utm_medium=referral&utm_campaign=api-credit';
        }
    }

    return $return;
}

