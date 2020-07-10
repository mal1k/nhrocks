<?
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/forms/form-siteinfo.php
# ----------------------------------------------------------------------------------------------------
?>

<div class="panel panel-default">

    <div class="panel-heading"><?=system_showText(LANG_SITEMGR_BASIC_INFO);?></div>

    <div class="panel-body">

        <div class="form-group row">
            <div class="col-xs-6">
                <label for="header_title"><?=system_showText(LANG_SITEMGR_BASIC_INFO_WEBSITETITLE);?></label>
                <input type="text" name="header_title" id="header_title" value="<?=$header_title?>" maxlength="255" class="form-control">
            </div>
            <div class="col-xs-6">
                <label for="header_author"><?=system_showText(LANG_SITEMGR_LABEL_AUTHOR)?></label>
                <input type="text" name="header_author" id="header_author" value="<?=$header_author?>" maxlength="255" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="address"><?=system_showText(LANG_LABEL_ADDRESS)?></label>
            <input type="text" class="form-control" name="contact_address" id="address" value="<?=$contact_address?>" <?=($loadMap ? 'onblur="loadMap();"' : '')?>>
        </div>

        <div class="form-group row">
            <div class="col-xs-6">
                <label for="company"><?=system_showText(LANG_SITEMGR_LABEL_COMPANYNAME)?></label>
                <input type="text" class="form-control" id="company" name="contact_company" value="<?=$contact_company?>">
            </div>
            <div class="col-xs-6">
                <label for="email"><?=system_showText(LANG_LABEL_EMAIL)?></label>
                <input type="email" class="form-control" id="email" name="contact_email" value="<?=$contact_email?>">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-xs-6">
                <label for="phone"><?=system_showText(LANG_LABEL_PHONE)?></label>
                <input type="tel" class="form-control" id="phone" name="contact_phone" value="<?=$contact_phone?>">
            </div>
            <div class="col-xs-6">
                <label for="setting_facebook_link"><?=system_showText(LANG_SITEMGR_WIDGET_FACEBOOK_LINK)?></label>
                <input type="text" class="form-control" id="facebook" name="setting_facebook_link" value="<?=$setting_facebook_link?>">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-xs-6">
                <label for="twitter_account"><?=system_showText(LANG_SITEMGR_WIDGET_TWITTER_LINK)?></label>
                <input type="text" class="form-control" id="twitter" name="twitter_account" value="<?=$twitter_account?>">
            </div>
            <div class="col-xs-6">
                <label for="setting_linkedin_link"><?=system_showText(LANG_SITEMGR_WIDGET_LINKEDIN_LINK)?></label>
                <input type="text" class="form-control" id="linkedin" name="setting_linkedin_link" value="<?=$setting_linkedin_link?>">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-xs-6">
                <label for="setting_instagram_link"><?=system_showText(LANG_SITEMGR_WIDGET_INSTAGRAM_LINK)?></label>
                <input type="text" class="form-control" id="instagram" name="setting_instagram_link" value="<?=$setting_instagram_link?>">
            </div>
            <div class="col-xs-6">
                <label for="setting_pinterest_link"><?=system_showText(LANG_SITEMGR_WIDGET_PINTEREST_LINK)?></label>
                <input type="text" class="form-control" id="pinterest" name="setting_pinterest_link" value="<?=$setting_pinterest_link?>">
            </div>
        </div>

        <?php
        /* ModStores Hooks */
        HookFire('sitemgr_form_siteinfo_before_address_fields_rows', [
            'http_post_array' => &$_POST,
            'http_get_array'  => &$_GET
        ]);
        ?>

        <div class="form-group row">
            <div class="col-xs-6">
                <label for="city"><?=system_showText(LANG_LABEL_CITY)?></label>
                <input type="text" class="form-control" name="contact_city" id="city" value="<?=$contact_city?>" <?=($loadMap ? 'onblur="loadMap();"' : '')?>>
            </div>
            <div class="col-xs-6">
                <label for="state"><?=system_showText(LANG_LABEL_STATE)?></label>
                <input type="text" class="form-control" name="contact_state" id="state" value="<?=$contact_state?>" <?=($loadMap ? 'onblur="loadMap();"' : '')?>>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-xs-6">
                <label for="country"><?=system_showText(LANG_LABEL_COUNTRY)?></label>
                <input type="text" class="form-control" name="contact_country" id="country" value="<?=$contact_country?>">
            </div>
            <div class="col-xs-6">
                <label for="zip_code"><?=string_ucwords(ZIPCODE_LABEL)?></label>
                <input type="text" class="form-control" name="contact_zipcode" id="zip_code" value="<?=$contact_zipcode?>" <?=($loadMap ? 'onblur="loadMap();"' : '')?>>
            </div>
        </div>
        <? if ($loadMap) { ?>
            <div class="form-group row">
                <div class="col-xs-12" id="tableMapTuning" <?=($hasValidCoord ? '' : 'style="display: none"')?>>
                    <div id="map" style="height: 200px"></div>
                    <input type="hidden" name="contact_latitude_longitude" id="myLatitudeLongitude" value="<?=$contact_latitude_longitude?>" />
                    <input type="hidden" name="contact_mapzoom" id="map_zoom" value="<?=$contact_mapzoom?>" />
                    <input type="hidden" name="contact_latitude" id="latitude" value="<?=$contact_latitude?>" />
                    <input type="hidden" name="contact_longitude" id="longitude" value="<?=$contact_longitude?>" />
                </div>
            </div>
        <? } ?>

    </div>

    <div class="panel-footer">
        <button type="button" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>" onclick="<?=DEMO_LIVE_MODE ? 'livemodeMessage(true, false);' : 'document.header.submit();' ?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
    </div>

</div>
