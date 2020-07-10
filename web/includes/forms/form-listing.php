<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/forms/form-listing.php
# ----------------------------------------------------------------------------------------------------

$levelObjAux = new ListingLevel();

?>

<div class="col-md-7">

    <!-- Item Name is separated from all informations -->
    <div class="form-group" id="tour-title">
        <?php if (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on') {
            system_fieldsGuide($arrayTutorial, $counterTutorial,
                (($template_title_field !== false) ? $template_title_field[0]['label'] : system_showText(LANG_LISTING_TITLE)),
                'tour-title');
            ?>
            <label for="name"
                   class="label-lg"><?= (($template_title_field !== false) ? $template_title_field[0]['label'] : system_showText(LANG_LISTING_TITLE)); ?></label>
        <?php } else {
            system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LISTING_TITLE), 'tour-title');
            ?>
            <label for="name" class="label-lg"><?= system_showText(LANG_LISTING_TITLE); ?></label>
        <?php } ?>
        <input type="text" class="form-control input-lg" name="title" id="name" value="<?= $title ?>" maxlength="100"
               <?= (!$id) ? " onblur=\"easyFriendlyUrl(this.value, 'friendly_url', '".FRIENDLYURL_VALIDCHARS."', '".FRIENDLYURL_SEPARATOR."'); populateField(this.value, 'seo_title', true); \" " : '' ?> placeholder="<?= (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && $template_title_field !== false && $template_title_field[0]['instructions'] ? $template_title_field[0]['instructions'] : system_showText(LANG_HOLDER_LISTINGTITLE)) ?>"
               required>
    </div>

    <!-- Panel Basic Information  -->
    <div class="panel panel-form">

        <?php if (!$members) { ?>

            <div class="form-group row">
                <?php if (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && system_showListingTypeDropdown($listingtemplate_id)) {
                    system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LISTING_TEMPLATE),
                        'tour-template');
                    ?>
                    <div class="col-sm-6 selectize" id="tour-template">
                        <label for="listingtemplate_id"><?= system_showText(LANG_LISTING_TEMPLATE); ?></label>
                        <select name="listingtemplate_id" id="listingtemplate_id" onchange="changeModuleLevel();">
                            <?php
                            $dbMain = db_getDBObject(DEFAULT_DB, true);
                            $dbObjLT = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
                            $sqlLT = "SELECT id FROM ListingTemplate WHERE status = 'enabled' ORDER BY editable, title";
                            $resultLT = $dbObjLT->query($sqlLT);
                            while ($rowLT = mysqli_fetch_assoc($resultLT)) {
                                $listingtemplate = new ListingTemplate($rowLT['id']);
                                echo '<option value="'.$listingtemplate->getNumber('id').'"';
                                if ($listingtemplate_id == $listingtemplate->getNumber('id')) {
                                    echo ' selected';
                                }
                                echo '>'.$listingtemplate->getString('title');
                                if ($listingtemplate->getString('price') > 0) {
                                    echo ' (+'.PAYMENT_CURRENCY_SYMBOL.$listingtemplate->getString('price').')';
                                }
                                echo '</option>';
                            }
                            ?>
                        </select>
                    </div>
                <?php } else { ?>
                    <input type="hidden" id="listingtemplate_id" name="listingtemplate_id" value="<?= $listingtemplate_id ?>">
                <?php } ?>

                <?php
                system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LISTING_LEVEL), 'tour-level');
                ?>
                <div class="col-sm-6 selectize" id="tour-level">
                    <label for="level"><?= system_showText(LANG_LISTING_LEVEL) ?></label>
                    <select name="level" id="level" onchange="changeModuleLevel();">
                        <?php
                        $levelvalues = $levelObjAux->getLevelValues();
                        foreach ($levelvalues as $levelvalue) { ?>
                            <option value="<?= $levelvalue ?>" <?= (($levelArray[$levelObjAux->getLevel($levelvalue)]) ? 'selected' : '') ?>>
                                <?= $levelObjAux->showLevel($levelvalue); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

            </div>

        <?php } ?>

        <div class="panel-heading"><?= system_showText(LANG_BASIC_INFO) ?></div>

        <div class="panel-body">

            <div class="form-group row" id="tour-categories">

                <?php
                system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_CATEGORY_PLURAL),
                    'tour-categories');
                ?>
                <div class="col-xs-12">
                    <label for="categories"><?= system_showText(LANG_LABEL_CATEGORY_PLURAL); ?></label>
                </div>

                <div class="col-sm-9">
                    <input type="text" class="form-control" id="categories"
                           placeholder="<?= system_showText(LANG_SELECT_CATEGORIES); ?>">
                </div>

                <input type="hidden" name="return_categories" value="">

                <?= str_replace('<select', '<select class="hidden"', $feedDropDown); ?>

                <?php if (Listing::enableCategorySelection($listing, $url_base)) { ?>

                    <div class="col-sm-3">
                        <button type="button" class="btn btn-primary btn-block" data-toggle="modal"
                                data-target="#modal-categories"
                                id="action-categoryList"><?= system_showText(LANG_LABEL_SELECT); ?> <i
                                    class="ionicons ion-ios7-photos-outline"></i></button>
                    </div>

                <?php } ?>

            </div>

            <?php if (!$members) { ?>
                <div class="form-group row">
                    <?php
                    system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_ACCOUNT),
                        'tour-owner');
                    ?>
                    <div class="col-sm-4" id="tour-owner">
                        <label for="account_id"><?= system_showText(LANG_LABEL_ACCOUNT); ?></label>
                        <input type="text" class="form-control mail-select" name="account_id" id="account_id"
                               placeholder="<?= system_showText(LANG_LABEL_ACCOUNT); ?>"
                               data-value="<?= is_numeric($account_id) ? $account_id : 0 ?>">
                        <?php if (system_getCountAccountsItems() <= MAXIMUM_NUMBER_OF_ITEMS_IN_SELECTIZE) {
                            system_generateAccountDropdown($auxAccountSelectize);
                        } else { ?>
                            <p id="helpBlockEmpty" class="help-block small">
                                <?= system_showText(LANG_TYPE_THE_ACCOUNTS_NAME_OR_EMAIL); ?>
                            </p>
                        <?php } ?>

                    </div>
                    <?php
                    system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_STATUS),
                        'tour-status');
                    ?>
                    <div class="col-sm-4" id="tour-status">
                        <label for="status"><?= system_showText(LANG_LABEL_STATUS); ?></label>
                        <?= ($statusDropDown) ?>
                    </div>
                    <?php
                    system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_RENEWAL_DATE),
                        'tour-expiration');
                    ?>
                    <div class="col-sm-4" id="tour-expiration">
                        <label for="expirationdate"><?= system_showText(LANG_LABEL_RENEWAL_DATE); ?></label>
                        <input type="text" class="form-control date-input" id="expirationdate" name="renewal_date"
                               value="<?= $renewal_date ?>"
                               placeholder="<?= system_showText(LANG_SITEMGR_CHANGEEXPIRATIONDATE) ?>">
                    </div>
                </div>

                <div class="form-group row">
                    <?php

                    /* ModStores Hooks */
                    HookFire('sitemgr_form_listing_enhancedlead', [
                        'leads_max' => &$leads_max
                    ]);

                    system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_SITEMGR_CLAIM_CLAIMS),
                        'tour-claim');
                    ?>
                    <div class="col-xs-12" id="tour-claim">
                        <div class="checkbox">
                            <label for="claim">
                                <input type="checkbox" name="claim_disable" id="claim"
                                       value="y" <?php if ($claim_disable == 'y') {
                                    echo 'checked';
                                } ?>>
                                <?= system_showText(LANG_SITEMGR_ACCOUNTSEARCH_DISABLECLAIM) ?>
                            </label>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if (is_array($array_fields) && in_array('summary_description', $array_fields)) {
                system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_SUMMARY_DESCRIPTION),
                    'tour-summary');
                ?>
                <div class="form-group" id="tour-summary">
                    <label for="summary"><?= system_showText(LANG_LABEL_SUMMARY_DESCRIPTION) ?></label>
                    <textarea id="summary" name="description"
                              class="textarea-counter form-control <?= ($highlight == 'description' && !$description ? 'highlight' : '') ?>"
                              <?= (!$id) ? " onblur=\"populateField(this.value, 'seo_description', true);\" " : '' ?> rows="3"
                              data-chars="250" data-msg="<?= system_showText(LANG_MSG_CHARS_LEFT) ?>"
                              placeholder="<?= system_showText(LANG_HOLDER_LISTINGSUMMARY); ?>"><?= $description; ?></textarea>
                </div>
            <?php } ?>

            <?php if (is_array($array_fields) && in_array('long_description', $array_fields)) {
                system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_DESCRIPTION),
                    'tour-description');
                ?>
                <div class="form-group" id="tour-description">
                    <label for="full-description"><?= system_showText(LANG_LABEL_DESCRIPTION) ?></label>
                    <?php

                    /* ModStores Hooks */

                    if(!HookFire('listingform_overwrite_longdescription', [
                        'content' => &$long_description
                    ])) { ?>
                        <textarea name="long_description" id="full-description"
                              class="form-control <?= ($highlight == 'description' && !$long_description ? 'highlight' : '') ?>"
                              rows="5"
                              placeholder="<?= system_showText(LANG_HOLDER_LISTINGDESCRIPTION); ?>"><?= $long_description ?></textarea>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php
            system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_KEYWORDS_FOR_SEARCH),
                'tour-keywords');
            ?>
            <div class="form-group" id="tour-keywords">
                <label for="keywords"><?= system_showText(LANG_LABEL_KEYWORDS_FOR_SEARCH) ?></label>
                <input type="text" name="keywords" id="keywords"
                       class="form-control tag-input <?= ($highlight == 'additional' && !$keywords ? 'highlight' : '') ?>"
                       placeholder="<?= system_showText(LANG_HOLDER_KEYWORDS); ?>" value="<?= $keywords ?>">
                <p class="help-block small"><?= ucfirst(system_showText(LANG_LABEL_MAX)); ?> <?= MAX_KEYWORDS ?> <?= system_showText(LANG_LABEL_KEYWORDS); ?></p>
            </div>

        </div>

    </div>

    <!-- Panel Contact Information  -->
    <?php
    system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_CONTACT_INFORMATION),
        'tour-contact');
    ?>
    <div class="panel panel-form" id="tour-contact">

        <div class="panel-heading">
            <?= system_showText(LANG_LABEL_CONTACT_INFORMATION) ?>
        </div>

        <div class="panel-body">
            <div class="form-group row custom-content-row">
                <?php if (is_array($array_fields) && in_array('email', $array_fields)) { ?>
                    <div class="col-sm-6">
                        <label for="email"><?= system_showText(LANG_LABEL_EMAIL) ?></label>
                        <input type="email" name="email" id="email" value="<?= $email ?>" maxlength="50"
                               class="form-control <?= ($highlight == 'description' && !$email ? 'highlight' : '') ?>"
                               placeholder="Ex: sample@email.com">
                    </div>
                <?php } ?>

                <?php if (is_array($array_fields) && in_array('url', $array_fields)) { ?>
                    <div class="col-sm-6">
                        <label for="website"><?= system_showText(LANG_LABEL_URL) ?></label>
                        <input type="url" name="url" id="website" value="<?= $url ?>" maxlength="255"
                               class="form-control <?= ($highlight == 'additional' && !$url ? 'highlight' : '') ?>"
                               placeholder="Ex: www.website.com">
                    </div>
                <?php } ?>
            </div>

            <div class="form-group row custom-content-row">
                <?php if (is_array($array_fields) && in_array('phone', $array_fields)) { ?>
                    <div class="col-sm-6">
                        <label for="phone"><?= system_showText(LANG_LABEL_PHONE) ?></label>
                        <input type="tel" name="phone" value="<?= $phone ?>"
                               class="form-control <?= ($highlight == 'description' && !$phone ? 'highlight' : '') ?>"
                               id="phone">
                    </div>
                <?php } ?>
                <?php if (is_array($array_fields) && in_array('additional_phone', $array_fields)) { ?>
                    <div class="col-sm-6">
                        <label for="label_additional_phone"><?= system_showText(LANG_LABEL_ADDITIONAL_PHONE) ?></label>
                        <div class="row custom-content-row">
                            <div class="col-sm-4">
                                <input type="text" name="label_additional_phone" value="<?= $label_additional_phone ?>" placeholder="<?= system_showText(LANG_LABEL_LABEL_ADDITIONAL_PHONE) ?>" class="form-control <?= ($highlight == 'description' && !$label_additional_phone ? 'highlight' : '') ?>" id="label_additional_phone">
                            </div>
                            <div class="col-sm-8">
                                <input type="tel" name="additional_phone" value="<?= $additional_phone ?>" placeholder="<?= system_showText(LANG_LABEL_ADDITIONAL_PHONE) ?>" class="form-control <?= ($highlight == 'description' && !$additional_phone ? 'highlight' : '') ?>" id="additional_phone">
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="form-group row custom-content-row">
                <div class="col-xs-12">
                    <?php if (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && $template_address_field !== false) { ?>
                        <label for="address"><?= $template_address_field[0]['label'] ?></label>
                    <?php } else { ?>
                        <label for="address"><?= system_showText(system_showText(LANG_LABEL_ADDRESS1)); ?></label>
                    <?php } ?>
                    <input type="text" name="address" id="address" value="<?= $address ?>" maxlength="100"
                           class="form-control <?= ($highlight == 'description' && !$address ? 'highlight' : '') ?>"
                           <?= ($loadMap ? 'onblur="loadMap(document.listing);"' : '') ?> placeholder="<?= (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && $template_address_field !== false && $template_address_field[0]['instructions'] ? $template_address_field[0]['instructions'] : system_showText(LANG_ADDRESS_EXAMPLE)) ?>">
                </div>

            </div>

            <div class="form-group row custom-content-row">

                <div class="col-sm-6">
                    <?php if (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && $template_address2_field !== false) { ?>
                        <label for="address2"><?= $template_address2_field[0]['label'] ?></label>
                    <?php } else { ?>
                        <label for="address2"><?= system_showText(system_showText(LANG_LABEL_ADDRESS2)); ?></label>
                    <?php } ?>
                    <input type="text" name="address2" id="address2" value="<?= $address2 ?>" maxlength="100"
                           class="form-control <?= ($highlight == 'description' && !$address2 ? 'highlight' : '') ?>"
                           placeholder="<?= (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && $template_address2_field !== false && $template_address2_field[0]['instructions'] ? $template_address2_field[0]['instructions'] : system_showText(LANG_ADDRESS2_EXAMPLE)) ?>">
                </div>

                <div class="col-sm-6">
                    <label for="zip_code"><?= string_ucwords(ZIPCODE_LABEL) ?></label>
                    <input type="text" name="zip_code" id="zip_code" value="<?= $zip_code ?>" maxlength="20"
                           class="form-control <?= ($highlight == 'description' && !$zip_code ? 'highlight' : '') ?>" <?= ($loadMap ? 'onblur="loadMap(document.listing);"' : '') ?>>
                </div>
            </div>

            <?php
            include(EDIRECTORY_ROOT.'/includes/code/load_location.php');

            if ($loadMap) { ?>

                <div class="form-group row custom-content-row">
                    <div class="col-xs-12" id="tableMapTuning" <?= ($hasValidCoord ? '' : 'style="display: none"') ?>>
                        <div id="map" style="height: 200px"></div>
                        <input type="hidden" name="latitude_longitude" id="myLatitudeLongitude"
                               value="<?= $latitude_longitude ?>">
                        <input type="hidden" name="map_zoom" id="map_zoom" value="<?= $map_zoom ?>">
                        <input type="hidden" name="latitude" id="latitude" value="<?= $latitude ?>">
                        <input type="hidden" name="longitude" id="longitude" value="<?= $longitude ?>">
                    </div>
                </div>

            <?php } ?>

            <?php if (is_array($array_fields) && in_array('locations', $array_fields)) {
                system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_REFERENCE),
                    'tour-reference');
                ?>
                <div class="form-group row custom-content-row">
                    <div class="col-xs-12" id="tour-reference">
                        <label for="reference"><?= system_showText(LANG_LABEL_REFERENCE) ?></label>
                        <textarea id="reference" name="locations"
                                  class="form-control <?= ($highlight == 'description' && !$locations ? 'highlight' : '') ?>"
                                  rows="5"
                                  placeholder="<?= system_showText(LANG_LABEL_LOCATIONS_TIP); ?>"><?= $locations; ?></textarea>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php

    /* Modstores Hooks*/
    HookFire('listingform_after_contact_information_panel', [
        'listing'      => &$listing,
        'highlight'    => &$highlight,
        'array_fields' => &$array_fields
    ]);

    /* ModStores Hooks */
    HookFire('sitemgr_ml_form', [
        'level'                   => &$level,
        'template_address_field'  => &$template_address_field,
        'template_address2_field' => &$template_address2_field,
        'loadMap'                 => &$loadMap,
        'highlight'               => &$highlight,
        '_default_locations_info' => &$_default_locations_info,
        '_non_default_locations'  => &$_non_default_locations,
        '_location_father_level'  => &$_location_father_level,
        '_location_child_level'   => &$_location_child_level,
        '_location_level'         => &$_location_level,
        'sitemgrSearch'           => &$sitemgrSearch,
        'formLoadMap'             => &$formLoadMap,
        'members'                 => $members,
    ]); ?>

    <!-- Panel Additional Information  -->
    <?php if ((is_array($array_fields) && in_array('social_network', $array_fields)) || HookExist('listingform_overwrite_socialnetworking')) { ?>
        <div class="panel panel-form">

            <div class="panel-heading">
                <?= system_showText(LANG_LABEL_SOCIALNETWORK); ?>
            </div>

            <div class="panel-body">

                <?php

                /* ModStores Hooks */
                if (!HookFire('listingform_overwrite_socialnetworking', [
                    'listing'      => &$listing,
                    'array_fields' => &$array_fields
                ])) { ?>
                    <?php if (is_array($array_fields) && in_array('social_network', $array_fields)) {
                        system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_SOCIALNETWORK),
                            'tour-socialnetwork'); ?>
                        <div id="tour-socialnetwork">
                            <?php foreach ($socialNetworkFields as $socialNetwork => $value) {
                                $fieldName = sprintf('social_network['.$socialNetworkFieldsDefaultName.']', $socialNetwork);
                                ?>
                                <div class="form-group">
                                    <label for="<?= $fieldName ?>"><?= $value['label'] ?></label>
                                    <input type="text" name="<?= $fieldName ?>"
                                           class="form-control <?= ($highlight == 'additional' && !$fieldName ? 'highlight' : '') ?>"
                                           id="<?= $fieldName ?>"
                                           value="<?= isset($social_network[$socialNetwork]) ? $social_network[$socialNetwork] : '' ?>"
                                           placeholder="<?= $value['placeholder'] ?>">
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>

    <?php } ?>

    <?php if (is_array($array_fields) && in_array('features', $array_fields)) {
        system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_FEATURES),
            'tour-features');
        ?>

        <div class="panel panel-form">

            <div class="panel-heading">
                <?= system_showText(LANG_LABEL_FEATURES); ?>
            </div>

            <div class="panel-body">
                <div class="form-group" id="tour-features">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="list-features">
                                <?php if (!empty($features) and is_array($features) and count($features)) { ?>
                                    <?php foreach ($features as $key => $feature) {
                                        if(!empty($feature['title'])) {
                                            ?>
                                            <div class="group-feature" data-feature-id="<?php echo $key ?>">
                                                <input type="hidden" name="features[<?php echo $key; ?>][icon]" class="input-feature-icon" value="<?php echo $feature['icon']; ?>">
                                                <input type="hidden" name="features[<?php echo $key; ?>][title]" class="input-feature-title" value="<?php echo htmlspecialchars($feature['title'], ENT_QUOTES); ?>">
                                                <div class="group-feature-icon">
                                                    <i class="fa <?php echo $feature['icon']; ?>" aria-hidden="true"></i>
                                                </div>
                                                <a href="javascript:;" class="group-feature-link"><?php echo $feature['title']; ?></a>
                                            </div>
                                    <?php }
                                        } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($fontAwesomeIcons)) { ?>
                        <div class="row custom-content-row" style="justify-content: flex-start;">
                            <div class="col-md-4">
                                <label for="feature_icon">
                                    <?= system_showText(LANG_LABEL_ICON); ?>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label for="feature_title">
                                    <?= system_showText(LANG_LABEL_NAME); ?>
                                </label>
                            </div>
                        </div>
                        <div class="row feature-content custom-content-row" data-feature-edit-id="">
                            <div class="col-md-4 selectize">
                                <select name="feature_icon" id="feature_icon" class="form-control feature-icon <?= ($highlight == 'features' && !$features ? 'highlight' : '') ?>">
                                    <?php foreach ($fontAwesomeIcons as $key => $item) { ?>
                                        <option value="<?php echo $key; ?>"><?php echo $item; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="feature_title" id="feature_title" class="form-control <?= ($highlight == 'features' && !$features ? 'highlight' : '') ?>" placeholder="Ex: Wifi">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="button button-md is-primary btn btn-primary btn-block btn-save-feature" <?=$members ? 'style="height: 50px;"' : '';?>><?= system_showText(LANG_LABEL_ADD) ?></button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="button button-bg is-warning btn btn-danger btn-block btn-delete-feature" style="display: none;">
                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="alert alert-danger" id="alert-features" style="display: none;"></div>
            </div>
        </div>

        <?php } ?>

        <?php if (is_array($array_fields) && in_array('hours_of_work', $array_fields)) {
            system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_HOURS_OF_WORK),
                'tour-hours');
            ?>

        <div class="panel panel-form panel-hours-of-work">

            <div class="panel-heading">
                <?= system_showText(LANG_LABEL_HOURS_OF_WORK); ?>
            </div>

            <div class="panel-body">
                <div id="tour-hours">
                    <div class="form-group">
                        <div class="row custom-content-row">
                            <div class="col-sm-6 selectize selectize-customized">
                                <select class="form-control weekday <?= ($highlight == 'additional' && !$hours_work ? 'highlight' : '') ?>">
                                    <option value="" disabled selected><?=LANG_SELECT_DAY_WEEK?></option>
                                    <?php foreach ($weekDays as $key => $weekDay) { ?>
                                        <option value="<?php echo $key; ?>"><?php echo ucfirst($weekDay); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row custom-content-row">
                            <div class="col-md-2">
                                <label for="hours-start">
                                    <?=LANG_LABEL_START_TIME?>
                                </label>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control hours-start time-input <?= ($highlight == 'additional' && !$hours_work ? 'highlight' : '') ?>" id="hours-start">
                            </div>
                            <div class="col-md-2">
                                <label for="hours-end">
                                    <?=LANG_LABEL_END_TIME?>
                                </label>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control hours-end time-input <?= ($highlight == 'additional' && !$hours_work ? 'highlight' : '') ?>" id="hours-end" value="">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="button button-bg is-primary btn btn-primary btn-block btn-add-hours" full-width="true"><?= system_showText(LANG_LABEL_ADD) ?></button>
                            </div>
                        </div>
                        <input type="hidden" id="end-nextday">
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <div class="list-hours-work">
                                <?php if (!empty($hours_work) and is_array($hours_work) and count($hours_work)) { ?>
                                    <?php foreach ($hours_work as $key => $hour_work) { ?>
                                        <div class="form-group row custom-content-row group-hours-work" data-hours-work-id="<?php echo $key ?>">
                                            <input type="hidden" name="hours_work[<?php echo $key ?>][weekday]" value="<?php echo $hour_work['weekday'] ?>">
                                            <input type="hidden" name="hours_work[<?php echo $key ?>][hours_start]" value="<?php echo $hour_work['hours_start'] ?>">
                                            <input type="hidden" name="hours_work[<?php echo $key ?>][hours_end]" value="<?php echo $hour_work['hours_end'] ?>">

                                            <div class="col-md-3">
                                                <input type="text" value="<?php echo system_getWeekDay($hour_work['weekday']); ?>" class="form-control" disabled>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" value="<?php echo system_getHour($hour_work['hours_start']); ?>" class="form-control" disabled>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" value="<?php echo system_getHour($hour_work['hours_end']); ?>" class="form-control" disabled>
                                            </div>
                                            <div class="col-md-3">
                                                <a href="javascript:;" class="btn btn-block btn-link remove-hours-work"><?= system_showText(LANG_LABEL_REMOVE) ?></a>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger alert-hours-of-work" style="display: none;"></div>
                </div>
            </div>
                    </div>

                <?php }

                unset($_SESSION['custom_type_field']);

                if (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on') {
                    include(INCLUDES_DIR.'/forms/form-listing-extra-fields.php');
    }

    include(INCLUDES_DIR.'/forms/form-module-seocenter.php'); ?>

    <!-- Panel Promotional Code  -->
    <?php if (PAYMENT_FEATURE == 'on' && (CREDITCARDPAYMENT_FEATURE == 'on' || PAYMENT_INVOICE_STATUS == 'on')) {
        system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_DISCOUNT_CODE),
            'tour-discount');
        ?>
        <div class="panel panel-form" id="tour-discount">

            <div class="panel-heading">
                <?= system_showText(LANG_LABEL_DISCOUNT_CODE); ?>
            </div>

            <div class="panel-body">

                <div class="form-group">
                    <?php if (Listing::enableCategorySelection($listing, $url_base, true)) { ?>
                        <label for="discount_id"><?= system_showText(LANG_HOLDER_DISCOUNTCODE); ?></label>
                        <input type="text" name="discount_id" id="discount_id" class="form-control"
                               value="<?= $discount_id ?>" maxlength="10" placeholder="">
                    <?php } else { ?>
                        <p><?= (($discount_id) ? $discount_id : system_showText(LANG_NA)) ?></p>
                        <input type="hidden" name="discount_id" value="<?= $discount_id ?>" maxlength="10">
                    <?php } ?>
                </div>

            </div>

        </div>
    <?php } ?>

</div>

<div class="col-md-5">

    <?php if ($underClaim) { ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <p><?= system_showText(LANG_CLAIM_ALERTMEDIA) ?></p>
            </div>
        </div>
    <?php } ?>

    <!-- Images-->
    <?php
    $renderImageFields = false;

    if (((is_array($array_fields) && in_array('main_image', $array_fields)) || $levelMaxImages > 0) && !$underClaim) {
        system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_IMAGE_PLURAL),
            'tour-images');
        $renderImageFields = true;
    }
    $imageUploader->buildform($renderImageFields);
    ?>

    <?php if (!$underClaim && $levelObjAux->getHasCoverImage($level) === 'y' && $levelObjAux->getDetail($level) === 'y') { ?>
        <?php system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_COVERIMAGE),
            'tour-cover-image'); ?>
        <!-- Cover Image-->
        <div class="panel panel-form-media" id="tour-cover-image">
            <div class="panel-heading">
                <?= system_showText(LANG_LABEL_COVERIMAGE); ?>
                <?php if (!$members) { ?>
                    <span class="btn btn-sm btn-danger delete pull-right <?= (!(int)$cover_id ? 'hidden' : '') ?>" id="buttonReset" style="margin-left: 8px;">
                    <i class="icon-ion-ios7-trash-outline"
                       onclick="sendCoverImage('listing', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'deleteCover');"></i>
                </span>
                    <div class="pull-right" style="margin-left: 8px;">
                    <input type="file" name="cover-image" class="file-noinput"
                           onchange="sendCoverImage('listing', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'uploadCover');">
                </div>
                    <?php if (!empty(UNSPLASH_ACCESS_KEY)) { ?>
                        <div class="pull-right">
                            <button type="button" class="btn btn-primary btn-sm btn-unsplash">Unsplash</button>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="panel-heading-action">
                        <input type="file" name="cover-image" class="file-noinput" onchange="sendCoverImage('listing', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'uploadCover');">

                        <?php if (!empty(UNSPLASH_ACCESS_KEY)) { ?>
                            <button type="button" class="btn btn-primary btn-sm btn-unsplash">Unsplash</button>
                        <?php } ?>

                        <button type="button" class="button button-sm is-warning delete <?= (!(int)$cover_id ? 'hidden' : '') ?>" id="buttonReset" onclick="sendCoverImage('listing', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'deleteCover');"><i class="fa fa-trash"></i></button>
                    </div>
                <?php } ?>
            </div>
            <div class="panel-body">
                <div id="coverimage" class="files">
                    <?php if ((int)$cover_id) {
                        $imgObj = new Image($cover_id);
                        if ($imgObj->imageExists()) {
                            echo $imgObj->getTag(false, 0, 0, '', false, false, 'img-responsive');
                        }
                    } ?>
                        <input type="hidden" name="cover_id" value="<?= $cover_id; ?>">
                </div>

                <input type="hidden" name="curr_cover_id" value="<?= $cover_id; ?>">

                <p id="returnMessage" class="alert alert-warning" style="display:none;"></p>

            </div>
            <div class="panel-footer text-center">
                <p class="small text-muted"><?= system_showText(LANG_LABEL_RECOMMENDED_DIMENSIONS); ?>
                    : <?= COVER_IMAGE_WIDTH ?> x <?= COVER_IMAGE_HEIGHT ?> px (JPG,
                    GIF <?= system_showText(LANG_OR); ?> PNG)</p>
                <p class="small text-muted"><?= system_showText(LANG_MSG_MAX_FILE_SIZE) ?> <?= UPLOAD_MAX_SIZE; ?>
                    MB. <?= system_showText(LANG_MSG_ANIMATEDGIF_NOT_SUPPORTED); ?></p>
            </div>
        </div>
    <?php } ?>

    <?php if (!$underClaim && $levelObjAux->getHasLogoImage($level) === 'y' && $levelObjAux->getDetail($level) === 'y') { ?>
        <?php system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_LOGOIMAGE),
            'tour-logo-image'); ?>
        <!-- Logo Image-->
        <div class="panel panel-form-media" id="tour-logo-image">
            <div class="panel-heading">
                <?= system_showText(LANG_LABEL_LOGOIMAGE); ?>

                <?php if (!$members) { ?>
                    <span class="btn btn-sm btn-danger delete pull-right <?= (!(int)$logo_id ? 'hidden' : '') ?>"
                        id="buttonResetLogo">
                        <i class="icon-ion-ios7-trash-outline"
                        onclick="sendLogoImage('listing', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'deleteLogo');"></i>
                    </span>
                    <div class="pull-right">
                        <input type="file" name="logo-image" class="file-noinput"
                            onchange="sendLogoImage('listing', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'uploadLogo');">
                    </div>
                <?php } else { ?>
                    <div class="panel-heading-action">
                        <input type="file" name="logo-image" class="file-noinput" onchange="sendLogoImage('listing', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'uploadLogo');">

                        <button type="button" class="button button-sm is-warning delete <?= (!(int)$logo_id ? 'hidden' : '') ?>" id="buttonResetLogo" onclick="sendLogoImage('listing', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'deleteLogo');"><i class="fa fa-trash"></i></button>
                    </div>
                <?php } ?>
            </div>
            <div class="panel-body">
                <div id="logoimage" class="files">
                    <?php if ((int)$logo_id) {
                        $imgObj = new Image($logo_id);
                        if ($imgObj->imageExists()) {
                            echo $imgObj->getTag(true, LOGO_IMAGE_WIDTH, LOGO_IMAGE_HEIGHT, '', false, false, 'img-responsive');
                        }

                        ?>
                        <input type="hidden" name="logo_id" value="<?= $logo_id; ?>">
                        <?php
                    } ?>
                </div>

                <input type="hidden" name="curr_logo_id" value="<?= $logo_id; ?>">

                <p id="logoReturnMessage" class="alert alert-warning" style="display:none;"></p>

            </div>
            <div class="panel-footer text-center">
                <p class="small text-muted"><?= system_showText(LANG_LABEL_RECOMMENDED_DIMENSIONS); ?>
                    : <?= LOGO_IMAGE_WIDTH ?> x <?= LOGO_IMAGE_HEIGHT ?> px (JPG,
                    GIF <?= system_showText(LANG_OR); ?> PNG)</p>
                <p class="small text-muted"><?= system_showText(LANG_MSG_MAX_FILE_SIZE) ?> <?= UPLOAD_MAX_SIZE; ?>
                    MB. <?= system_showText(LANG_MSG_ANIMATEDGIF_NOT_SUPPORTED); ?></p>
            </div>
        </div>
    <?php } ?>

    <? /* ModStores Hooks */ ?>
    <? if(!HookFire('listingform_overwrite_video',[
        'listing' => $listing
    ])) { ?>
    <!-- Video-->
        <?php if (is_array($array_fields) && in_array('video', $array_fields) && !$underClaim) {
            system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_VIDEO), 'tour-video');
            ?>
            <div class="panel panel-form-media" id="tour-video">
                <div class="panel-heading">
                    <?= system_showText(LANG_LABEL_VIDEO); ?>
                </div>
                <div class="panel-body form-group">
                    <div class="center-block text-center">
                        <i id="icon" class="icon-movie"></i>
                    </div>
                    <div id="videoMsg" class="alert alert-warning fade in hidden" role="alert">
                        <small><?= system_showText(LANG_VIDEO_NOTFOUND) ?></small>
                    </div>
                    <div id="video_frame" style="display:none"></div>
                    <label for="video">
                        <?=system_showText(LANG_LABEL_VIDEO_URL);?>
                    </label>
                    <input type="url" name="video_url" id="video" value="<?= $video_url ?>"
                           class="form-control form-group <?= ($highlight == 'media' && !$video_url ? 'highlight' : '') ?>"
                           placeholder="<?= system_showText(LANG_HOLDER_VIDEO); ?>" onchange="autoEmbed('video');">
                    <label for="video_description">
                        <?=system_showText(LANG_LABEL_VIDEO_DESCRIPTION);?>
                    </label>
                    <input type="text" name="video_description" id="video_description" value="<?= $video_description ?>"
                           class="form-control" maxlength="250"
                           placeholder="<?= system_showText(LANG_HOLDER_VIDEOCAPTION); ?>">
                </div>
            </div>
        <?php } ?>
    <?php } ?>

    <!-- Attached File-->
    <?php if (is_array($array_fields) && in_array('attachment_file', $array_fields) && !$underClaim) {
        system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_ATTACH_ADDITIONAL_FILE),
            'tour-file');
        ?>
        <div class="panel panel-form-media" id="tour-file">
            <div class="panel-heading">
                <?= system_showText(LANG_LABEL_ATTACH_ADDITIONAL_FILE) ?>
            </div>
            <div class="panel-body">

                <?php if ($listing->getString('attachment_file') && file_exists(EXTRAFILE_DIR.'/'.$listing->getString('attachment_file'))) { ?>
                    <div class="files uploaded-files">
                        <div class="row item" id="div_attachment">
                            <div class="col-sm-2 col-xs-4 custom-attachment-icon">
                                <span class="icon-ion-ios7-paper-outline icon-3x"></span>
                            </div>
                            <div class="col-sm-3 col-xs-8 <?=(!$members) ? 'pull-right' : ''; ?> custom-attachment-actions">

                                <?php if(!$members) { ?>

                                    <p><span onclick="removeAttachment();" class="btn btn-sm btn-primary  pull-right action-delete-image"><i class="icon-ion-ios7-trash-outline"></i></span></p>

                                <?php } else { ?>

                                    <button type="button" class="button button-sm is-warning action-delete-image" onclick="removeAttachment();"><i class="fa fa-trash"></i></button>

                                <?php } ?>

                                <input type="hidden" name="remove_attachment" id="remove_attachment" value="">

                            </div>
                            <div class="col-sm-7 col-xs-12 custom-attachment-title">
                                <strong><?= ($listing->getString('attachment_caption') ? $listing->getString('attachment_caption') : system_showText(LANG_MSG_ATTACHMENT_HAS_NO_CAPTION)) ?></strong>
                                <p>
                                    <a href="<?= EXTRAFILE_URL ?>/<?= $listing->getString('attachment_file') ?>"
                                       target="_blank">
                                        <?= $listing->getString('attachment_file') ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <div class="form-group">
                    <input type="file" name="attachment_file" class="file-withinput form-control" maxlength="250"
                       class="filestyle upload-files <?= ($highlight == 'additional' && !$attachment_file ? 'highlight' : '') ?>"><br>
                </div>

                <div class="form-group">
                    <input type="text" name="attachment_caption" value="<?= $attachment_caption ?>" class="form-control"
                           maxlength="250" placeholder="<?= system_showText(LANG_HOLDER_ATTACHCAPTION); ?>">
                </div>

            </div>
            <div class="panel-footer text-center">
                <p class="small text-muted"><?= system_showText(LANG_MSG_MAX_FILE_SIZE) ?> <?= UPLOAD_MAX_SIZE; ?>
                    MB</p>
                <p class="small text-muted"><?= system_showText(LANG_MSG_ALLOWED_FILE_TYPES) ?>: pdf, doc, docx, txt,
                    jpg, gif, png</p>
            </div>
        </div>
    <?php } ?>

    <!-- Badges-->
    <?php if (is_array($array_fields) && in_array('badges', $array_fields) && $editorChoices && !$underClaim) {
        system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LISTING_DESIGNATION_PLURAL),
            'tour-badges');
        ?>
        <div class="panel panel-form-media" id="tour-badges">
            <div class="panel-heading">
                <?= system_showText(LANG_LISTING_DESIGNATION_PLURAL); ?>
                <?php if (!$members) { ?>
                    <div class="pull-right">
                        <small><a class="text-info" href="<?= DEFAULT_URL.'/'.SITEMGR_ALIAS.'/promote/awards/' ?>" target="_blank"><?= system_showText(LANG_HOLDER_BADGES); ?></a></small>
                    </div>
                <?php } ?>
            </div>
            <div class="panel-body">
                <div class="text-center form-group form-horizontal custom-content-badge">
                    <?php
                    foreach ($editorChoices as $editor) {
                        $listingChoiceObj = new ListingChoice($editor->getNumber('id'), $id);
                        $imageObj = new Image($editor->getNumber('image_id'));
                        $checkedStr = '';
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            if ($_POST['choice']) {
                                if (in_array($editor->getNumber('id'), $_POST['choice'])) {
                                    $checkedStr = 'checked';
                                }
                            }
                        } elseif ($listingChoiceObj->getNumber('listing_id')) {
                            $checkedStr = 'checked';
                        }
                        if ($imageObj->imageExists()) { ?>
                        <div class="checkbox-inline edir-badge">
                            <div class="badge badge-icon">
                                <?= $imageObj->getTag(IS_UPGRADE == 'on' ? true : false, IMAGE_DESIGNATION_WIDTH,
                                    IMAGE_DESIGNATION_HEIGHT, $editor->getString('name', false)) ?>
                            </div>
                            <label>
                                <input type="checkbox" name="choice[]" <?= $checkedStr ?> value="<?= $editor->getNumber('id') ?>"> <?= $editor->getString('name') ?>
                            </label>
                        </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
