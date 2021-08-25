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
	# * FILE: /inclues/forms/form_profile.php
	# ----------------------------------------------------------------------------------------------------

    $has_picture = false;

    $noImgTag = '<img class="user-picture" width="100" height="100" src="'.DEFAULT_URL.'/assets/images/user-image.png" alt="'.htmlspecialchars($info["nickname"]).'">';

    if (!$facebook_image) {
        if ($image_id) {

            $imageObj = new Image($image_id, true);
            if ($imageObj->imageExists()) {
                $imgTag = $imageObj->getTag(true, PROFILE_MEMBERS_IMAGE_WIDTH, PROFILE_MEMBERS_IMAGE_HEIGHT);
                $has_picture = true;
            }
        }
    } else {
        if ($facebook_image) {
            if (HTTPS_MODE == "on") {
                $facebook_image = str_replace("http://", "https://", $facebook_image);
            }
            $imgTag = "<img src=\"".$facebook_image."\" width=\"".PROFILE_MEMBERS_IMAGE_WIDTH."\" height=\"".PROFILE_MEMBERS_IMAGE_HEIGHT."\" alt=\"Facebook Image\">";
            $has_picture = true;
        }
    }

    $domain = new Domain(SELECTED_DOMAIN_ID);
	$domain_url = (SSL_ENABLED == "on" && FORCE_PROFILE_SSL == "on" ? "https://" : "http://").$domain->getString("url").EDIRECTORY_FOLDER."/".SOCIALNETWORK_FEATURE_NAME;

    ?>

    <div id="hiddenFields" style="display: none;">
        <input type="hidden" id="facebook_image" name="facebook_image" value="<?=$facebook_image?>">
    </div>

    <div class="members-panel edit-panel">
        <div class="panel-header">
            <?=system_showText(LANG_LABEL_PERSONAL_PAGE)?>
        </div>
        <div class="panel-body">
            <div class="custom-edit-content">
                <div class="row custom-content-row responsive-rows">
                    <div class="col-md-2">
                        <div class="profile-edit-picture">
                            <div class="preview-picture">
                                <div class="profile-picture <?=$has_picture and 'has-picture';?>" id="image_fb"><?=$imgTag ? : $noImgTag;?></div>
                                <button class="button button-md is-secondary" full-width="true" type="button" id="buttonfile"><?=system_showText(LANG_LABEL_PROFILE_CHANGEPHOTO);?></button>
                                <input type="file" name="image" id="image" size="1" class="file-noinput" onchange="uploadProfilePicture();">
                            </div>

                            <?php if ($accountObj->getString("facebook_username")) { ?>
                                <span class="or"><?= system_showText(LANG_OR); ?></span>
                                <button type="button" class="button button-md is-primary" full-width="true" onclick="getFacebookImage();"><?=system_showText(LANG_LABEL_IMAGE_FROM_FACEBOOK);?></button>
                            <?php } ?>

                            <div class="picture-actions <?=($image_id || $facebook_image) ?: 'hidden'?>" id="linkRemovePhoto">
                                <button type="button" class="button button-md is-warning" full-width="true" onclick="removePhoto();"><?=system_showText(LANG_LABEL_PROFILE_REMOVEPHOTO);?></button>
                            </div>
                        </div>
                        <?php if ($is_sponsor == "y") { ?>
                            <div class="heading personal-page-label"><?=system_showText(LANG_LABEL_CREATE_PERSONAL_PAGE);?></div>
                            <div class="personal-page">
                                <input type="radio" class="switch-input" name="has_profile" value="has_profile_true" id="has_profile_true" data-value="on" onclick="profileStatus(this.id);" <?=($has_profile == "y") ? "checked": "" ?>>
                                <label for="has_profile_true" class="switch-label switch-label-off"><?=system_showText(LANG_YES);?></label>
                                <input type="radio" class="switch-input" name="has_profile" value="has_profile_false" id="has_profile_false" data-value="off" onclick="profileStatus(this.id);" <?=($has_profile == "n") ? "checked": "" ?>>
                                <label for="has_profile_false" class="switch-label switch-label-on"><?=system_showText(LANG_NO);?></label>
                                <span class="switch-selection"></span>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                            <label for="nickname"><?=system_showText(LANG_LABEL_PROFILE_DISPLAYNAME);?></label>
                            <input class="form-control" id="nickname" type="text" name="nickname" value="<?=$nickname?>">
                        </div>

                        <div class="form-group">
                            <label for="personal_message"><?=system_showText(LANG_LABEL_ABOUT_ME);?></label>
                            <textarea class="form-control" id="personal_message" name="personal_message" rows="7" cols="1"><?=$personal_message?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="friendly_url"><?=system_showText(LANG_LABEL_YOUR_URL);?> </label>

                            <div class="input-group custom-input-group">
                                <input class="form-control" type="text" name="friendly_url" id="friendly_url" value="<?=$friendly_url?>" onblur="easyFriendlyUrl(this.value, 'friendly_url', '<?=FRIENDLYURL_VALIDCHARS?>', '<?=FRIENDLYURL_SEPARATOR?>'); validateFriendlyURL(this.value, <?=(sess_getAccountIdFromSession() ? sess_getAccountIdFromSession() : 0)?>);">
                                <span class="input-group-addon">
                                    <span id="URL_ok" ><i class="fa fa-check text-success"></i> <small><?=system_showText(LANG_LABEL_URLOK);?></small></span>
                                    <span id="URL_notok" style="display: none;"><i class="fa fa-times text-warning"></i> <small><?=system_showText(LANG_LABEL_URLNOTOK);?></small></span>
                                </span>
                            </div>
                        </div>

                        <div class="url-example"><?=$domain_url;?>/<b id="urlSample"><?=($friendly_url ? $friendly_url : system_showText(LANG_LABEL_YOUR_URLTIP))?></b>/</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
