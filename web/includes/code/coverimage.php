<?php

    //Ajax requests
    if ($_GET['action'] === 'ajax') {

        //Upload cover image / author image
        switch ($_GET['type']) {
            case 'uploadCover':
                $fieldImage = 'cover';
                $typeAction = 'upload';
                break;
            case 'createCover':
                $fieldImage = 'cover';
                $typeAction = 'create';
                break;
            case 'deleteCover':
                $fieldImage = 'cover';
                $typeAction = 'delete';
                break;
            case 'uploadLogo':
                $fieldImage = 'logo';
                $typeAction = 'upload';
                break;
            case 'deleteLogo':
                $fieldImage = 'logo';
                $typeAction = 'delete';
                break;
            case 'uploadAuthor':
                $fieldImage = 'author_image';
                $typeAction = 'upload';
                break;
            case 'deleteAuthor':
                $fieldImage = 'author_image';
                $typeAction = 'delete';
                break;

        }

        $imgClass = 'img-responsive';
        if ($fieldImage === 'author_image') {
            $_GET['module'] = 'article';
            $imgClass = 'img-circle img-objectfit';
        }

        if ($typeAction === 'upload') {

            $return = '';
            $error = false;

            if (isset($_POST) && $_SERVER['REQUEST_METHOD'] === 'POST') {

                if ($_FILES[$fieldImage.'-image'] && strlen($_FILES[$fieldImage.'-image']['tmp_name']) > 0) {

                    $image_errors = array();

                    $maxImageSize = ((UPLOAD_MAX_SIZE * 10) + 1) . '00000';

                    if (!image_upload_check($_FILES[$fieldImage.'-image']['tmp_name'])) {
                        $image_errors[] = '&#149;&nbsp; ' . system_showText(LANG_UPLOAD_MSG_NOTALLOWED_WRONGFILETYPE);
                    }

                    if ($_FILES[$fieldImage.'-image']['size'] > $maxImageSize) {
                        $image_errors[] = '&#149;&nbsp; ' . system_showText(LANG_MSG_MAX_FILE_SIZE . ': ' . UPLOAD_MAX_SIZE . 'MB.');
                    }

                    if (count($image_errors) === 0) {
                        if ($_FILES[$fieldImage.'-image']['error'] === 0) {

                            if ($fieldImage === 'logo') {
                                $imageObj = image_upload($_FILES[$fieldImage.'-image']['tmp_name'], LOGO_IMAGE_WIDTH, LOGO_IMAGE_HEIGHT, ($_GET['account_id'] ? $_GET['account_id'] : 'sitemgr_'), false, true);
                            } else {
                                $imageObj = image_upload($_FILES[$fieldImage.'-image']['tmp_name'], '', '', ($_GET['account_id'] ? $_GET['account_id'] : 'sitemgr_'), false, false);
                            }
                            if ($imageObj) {
                                $return = "<input type='hidden' name='{$fieldImage}_id' value='" . $imageObj->getNumber("id") . "'>";
                                $return .= $imageObj->getTag(false, 0, 0, '', false, false, $imgClass);
                            }

                        } else {
                            $error = true;
                            $return = system_showText(LANG_MSGERROR_ERRORUPLOADINGIMAGE);
                        }
                    } else {
                        $error = true;
                        foreach ($image_errors as $imgError) {
                            $return .= $imgError . '<br />';
                        }
                    }

                } else {
                    $error = true;
                    $return = system_showText(LANG_IMAGE_EMPTY);
                }
            }

            echo ($error ? 'error' : 'ok') . '||' . $return;

        } elseif ($typeAction === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['unsplash']) and $_POST['unsplash']) {

                $info = getimagesize($_POST['unsplash']);

                $row_image["type"] = 'JPG';
                $row_image["width"] = $info[0];
                $row_image["height"] = $info[1];
                $row_image["unsplash"] = $_POST['unsplash'];

                $imageObj = new Image($row_image, false);
                $imageObj->save();

                if ($imageObj) {
                    $return = "<input type='hidden' name='{$fieldImage}_id' value='" . $imageObj->getNumber("id") . "'>";
                    $return .= $imageObj->getTag(false, 0, 0, '', false, false, $imgClass);
                } else {
                    $error = true;
                    $return = system_showText(LANG_IMAGE_EMPTY);
                }
            } else {
                $error = true;
                $return = system_showText(LANG_IMAGE_EMPTY);
            }

            echo ($error ? 'error' : 'ok') . '||' . $return;

            //Delete cover image / author image
        } elseif ($typeAction === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {

            if ($_POST['id']) {
                $moduleStr = ($_GET['module'] === 'blog' ? 'Post' : ucfirst($_GET['module']));
                $moduleObj = new $moduleStr($_POST['id']);
                // Sets NULL in SQL
                $moduleObj->setString($fieldImage.'_id', 'NULL');
                $moduleObj->save();
            }

            $imgObj = new Image($_POST['curr_'.$fieldImage.'_id']);
            if ($imgObj->getNumber('id')) {
                $imgObj->delete();
            }

            $newImageReturn = "<input type='hidden' name='".$fieldImage."_id' value=''>";

            if ($fieldImage === 'author_image') {
                if (string_strpos($_SERVER['PHP_SELF'], '/'.SITEMGR_ALIAS) !== false) {
                    $imgAuthor = DEFAULT_URL.'/'.SITEMGR_ALIAS.'/assets/img/profile-thumb.png';
                } else {
                    $imgAuthor =  DEFAULT_URL.'/assets/images/user-image.png';
                }
                $newImageReturn .= '<img class="img-circle img-objectfit" src="'.$imgAuthor.'" alt="'.system_showText(LANG_ARTICLE_AUTHOR_IMAGE).'">';
            }

            echo 'ok||'.$newImageReturn;

        }
        exit;
    }
