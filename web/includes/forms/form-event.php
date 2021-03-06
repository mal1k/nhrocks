<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/forms/form-event.php
# ----------------------------------------------------------------------------------------------------

$levelObjAux = new EventLevel();
?>

<div class="col-md-7">

    <!-- Item Name is separated from all informations -->
    <div class="form-group" id="tour-title">
        <?php system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_EVENT_TITLE), 'tour-title'); ?>
        <label for="name" class="label-lg"><?= system_showText(LANG_EVENT_TITLE); ?></label>
        <input type="text" class="form-control input-lg" name="title" id="name" value="<?= $title ?>" maxlength="100"
            <?= (!$id) ? " onblur=\"easyFriendlyUrl(this.value, 'friendly_url', '".FRIENDLYURL_VALIDCHARS."', '".FRIENDLYURL_SEPARATOR."'); populateField(this.value, 'seo_title', true);\" " : '' ?>
               placeholder="<?= system_showText(LANG_HOLDER_EVENTTITLE) ?>">
    </div>

    <!-- Panel Basic Informartion  -->
    <div class="panel panel-form">

        <?php if (!$members) { ?>
            <div class="form-group row">
                <?php
                system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_EVENT_LEVEL),
                    'tour-level');
                ?>
                <div class="col-xs-6 selectize" id="tour-level">
                    <label for="level"><?= system_showText(LANG_EVENT_LEVEL) ?></label>
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

                <div class="col-sm-8">
                    <input type="text" class="form-control" id="categories"
                           placeholder="<?= system_showText(LANG_SELECT_CATEGORIES); ?>">
                </div>

                <input type="hidden" name="return_categories" value="">

                <?= str_replace('<select', '<select class="hidden"', $feedDropDown); ?>

                <div class="col-sm-4">
                    <button type="button" class="btn btn-primary btn-block" data-toggle="modal"
                            data-target="#modal-categories"
                            id="action-categoryList"><?= system_showText(LANG_LABEL_SELECT); ?> <i
                                class="ionicons ion-ios7-photos-outline"></i></button>
                </div>

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

            <?php }

            /* ModStores Hooks */
            HookFire('formevent_after_render_renewaldate', ['id' => $id]);

            ?>

            <?php if (is_array($array_fields) && in_array('summary_description', $array_fields)) {
                system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_SUMMARY_DESCRIPTION),
                    'tour-summary');
                ?>
                <div class="form-group" id="tour-summary">
                    <label for="summary"><?= system_showText(LANG_LABEL_SUMMARY_DESCRIPTION) ?></label>
                    <textarea id="summary" name="description"
                              class="textarea-counter form-control <?= ($highlight == 'description' && !$description ? 'highlight' : '') ?>"
                        <?= (!$id) ? " onblur=\"populateField(this.value, 'seo_description', true);\" " : '' ?> rows="2"
                              data-chars="250" data-msg="<?= system_showText(LANG_MSG_CHARS_LEFT) ?>"
                              placeholder="<?= system_showText(LANG_HOLDER_EVENTSUMMARY); ?>"><?= $description; ?></textarea>
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
                    if (!HookFire('eventform_overwrite_longdescription', [
                        'content' => &$long_description
                    ])) { ?>
                        <textarea name="long_description" id="full-description"
                              class="form-control <?= ($highlight == 'description' && !$long_description ? 'highlight' : '') ?>"
                              rows="5"
                              placeholder="<?= system_showText(LANG_HOLDER_EVENTDESCRIPTION); ?>"><?= $long_description ?></textarea>
                    <? } ?>
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

            <?php
            /* ModStores Hooks */

            if (!HookFire('eventform_overwrite_facebookpage', [
                'event'        => &$event,
                'array_fields' => &$array_fields
            ])) { ?>
            <?php
                if (is_array($array_fields) && in_array('fbpage', $array_fields)) {
                    system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_FBPAGE),
                        'tour-facebook');
                    ?>
                    <div class="form-group" id="tour-facebook">
                        <label for="fbpage"><?= system_showText(LANG_LABEL_FBPAGE) ?></label>
                        <input type="text" name="facebook_page"
                               class="form-control <?= ($highlight == 'additional' && !$facebook_page ? 'highlight' : '') ?>"
                               id="fbpage" value="<?= $facebook_page ?>" placeholder="Ex: https://www.facebook.com/fanpage">
                    </div>
                <?php } ?>
            <?php } ?>

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
            <div class="row form-group <?= !$members ? '' : 'custom-content-row'; ?>">
                <?php if (is_array($array_fields) && in_array('contact_name', $array_fields)) { ?>
                    <div class="col-sm-6 <?= $members ? 'form-group' : ''; ?>">
                        <label for="contact_name"><?= system_showText(LANG_LABEL_CONTACT_NAME) ?></label>
                        <input type="text" name="contact_name" id="contact_name" value="<?= $contact_name ?>"
                               maxlength="50"
                               class="form-control <?= ($highlight == 'description' && !$contact_name ? 'highlight' : '') ?>">
                    </div>
                <?php } ?>

                <?php if (is_array($array_fields) && in_array('email', $array_fields)) { ?>
                    <div class="col-sm-6 <?= $members ? 'form-group' : ''; ?>">
                        <label for="email"><?= system_showText(LANG_LABEL_EMAIL) ?></label>
                        <input type="email" name="email" id="email" value="<?= $email ?>" maxlength="50"
                               class="form-control <?= ($highlight == 'description' && !$email ? 'highlight' : '') ?>"
                               placeholder="Ex: sample@email.com">
                    </div>
                <?php } ?>

            </div>

            <div class="row form-group <?= !$members ? '' : 'custom-content-row'; ?>">
                <?php if (is_array($array_fields) && in_array('phone', $array_fields)) { ?>
                    <div class="col-sm-6 <?= $members ? 'form-group' : ''; ?>">
                        <label for="phone"><?= system_showText(LANG_LABEL_PHONE) ?></label>
                        <input type="tel" name="phone" value="<?= $phone ?>"
                               class="form-control <?= ($highlight == 'description' && !$phone ? 'highlight' : '') ?>"
                               id="phone">
                    </div>
                <?php } ?>

                <?php if (is_array($array_fields) && in_array('url', $array_fields)) { ?>
                    <div class="col-sm-6 <?= $members ? 'form-group' : ''; ?>">
                        <label for="website"><?= system_showText(LANG_LABEL_URL) ?></label>
                        <input type="url" name="url" id="website" value="<?= $url ?>" maxlength="255"
                               class="form-control <?= ($highlight == 'additional' && !$url ? 'highlight' : '') ?>"
                               placeholder="Ex: www.website.com">
                    </div>
                <?php } ?>
            </div>

            <div class="row form-group <?= !$members ? '' : 'custom-content-row'; ?>">

                <div class="col-sm-6 <?= $members ? 'form-group' : ''; ?>">
                    <label for="location"><?= system_showText(system_showText(LANG_LABEL_LOCATION_NAME)); ?></label>
                    <input type="text" name="location" id="location" value="<?= $location ?>" maxlength="50"
                           class="form-control <?= ($highlight == 'description' && !$location ? 'highlight' : '') ?>">
                </div>

                <div class="col-sm-6 <?= $members ? 'form-group' : ''; ?>">
                    <label for="address"><?= system_showText(system_showText(LANG_LABEL_STREET_ADDRESS)); ?></label>
                    <input type="text" name="address" id="address" value="<?= $address ?>" maxlength="50"
                           class="form-control <?= ($highlight == 'description' && !$address ? 'highlight' : '') ?>"
                        <?= ($loadMap ? 'onblur="loadMap(document.event);"' : '') ?>
                           placeholder="<?= system_showText(LANG_ADDRESS_EXAMPLE) ?>">
                </div>

            </div>

            <div class="row form-group <?= !$members ? '' : 'custom-content-row'; ?>">
                <div class="col-sm-6 <?= $members ? 'form-group' : ''; ?>">
                    <label for="zip_code"><?= string_ucwords(ZIPCODE_LABEL) ?></label>
                    <input type="text" name="zip_code" id="zip_code" value="<?= $zip_code ?>" maxlength="10"
                           class="form-control <?= ($highlight == 'description' && !$zip_code ? 'highlight' : '') ?>" <?= ($loadMap ? 'onblur="loadMap(document.event);"' : '') ?>>
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

        </div>

    </div>

    <?php
    system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_EVENT_DATE), 'tour-date');
    ?>
    <div class="panel panel-form" id="tour-date">

        <div class="panel-heading">
            <?= system_showText(LANG_LABEL_EVENT_DATE) ?>
        </div>

        <div class="panel-body">
            <div id="range_date" class="row custom-content-row form-group">
                <div class="col-sm-6">
                    <label for="start_date"><?= system_showText(LANG_LABEL_START_DATE) ?></label>
                    <input type="text" autocomplete="off" class="form-control date-input" name="start_date" id="start_date"
                           value="<?= $start_date ?>" placeholder="<?= format_printDateStandard() ?>">
                </div>
                <div class="col-sm-6" id="labelEndDate">
                    <label for="end_date"><?= system_showText(LANG_LABEL_END_DATE) ?></label>
                    <input type="text" autocomplete="off" class="form-control date-input" name="end_date" id="end_date"
                           value="<?= $end_date ?>" placeholder="<?= format_printDateStandard() ?>">
                </div>
            </div>


            <?php if (is_array($array_fields) && in_array('start_time', $array_fields)) { ?>
                <?php if (string_strpos($_SERVER['REQUEST_URI'], SITEMGR_ALIAS) !== false) { ?>
                    <div class="form-group row custom-content-row">
                    <div class="col-xs-12">
                        <label><?= system_showText(LANG_LABEL_START_TIME) ?></label>
                    </div>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <div class="simple-select">
                                <?= $start_time_hour_DD ?>
                            </div>
                            <span class="input-group-addon-invisible">
                                :
                            </span>
                            <div class="simple-select">
                                <?= $start_time_min_DD ?>
                            </div>
                        </div>
                    </div>
                    <?php if (CLOCK_TYPE == '12') { ?>
                        <div class="col-sm-6 form-horizontal">
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" id="startTimeAM" name="start_time_am_pm"
                                           value="am" <?php if ($start_time_am_pm == 'am') {
                                        echo 'checked';
                                    } ?> >
                                    AM
                                </label>
                            </div>
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" id="startTimePM" name="start_time_am_pm"
                                           value="pm" <?php if ($start_time_am_pm == 'pm') {
                                        echo 'checked';
                                    } ?> >
                                    PM (hh:mm)
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php } else { ?>
                    <div class="form-group custom-content-row custom-hour-block">
                        <label><?= system_showText(LANG_LABEL_START_TIME) ?></label>
                        <div class="input-group">
                            <?= $start_time_hour_DD ?>
                            <span class="input-group-addon">:</span>
                            <?= $start_time_min_DD ?>
                        </div>
                        <?php if (CLOCK_TYPE == '12') { ?>
                            <div class="form-group">
                                <div class="radio-inline">
                                    <label>
                                        <input type="radio" id="startTimeAM" name="start_time_am_pm"
                                            value="am" <?php if ($start_time_am_pm == 'am') {
                                            echo 'checked';
                                        } ?> >
                                        AM
                                    </label>
                                </div>
                                <div class="radio-inline">
                                    <label>
                                        <input type="radio" id="startTimePM" name="start_time_am_pm"
                                            value="pm" <?php if ($start_time_am_pm == 'pm') {
                                            echo 'checked';
                                        } ?> >
                                        PM (hh:mm)
                                    </label>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>

            <?php if (is_array($array_fields) && in_array('end_time', $array_fields)) { ?>
                <?php if (string_strpos($_SERVER['REQUEST_URI'], SITEMGR_ALIAS) !== false) { ?>
                <div class="form-group row">
                    <div class="col-xs-12">
                        <label><?= system_showText(LANG_LABEL_END_TIME) ?></label>
                    </div>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <div class="simple-select">
                                <?= $end_time_hour_DD ?>
                            </div>
                            <span class="input-group-addon-invisible">
                                :
                            </span>
                            <div class="simple-select">
                                <?= $end_time_min_DD ?>
                            </div>
                        </div>
                    </div>
                    <?php if (CLOCK_TYPE == '12') { ?>
                        <div class="col-sm-6 form-horizontal">
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" id="endTimeAM" name="end_time_am_pm"
                                           value="am" <?php if ($end_time_am_pm == 'am') {
                                        echo 'checked';
                                    } ?> > AM
                                </label>
                            </div>
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" id="endTimePM" name="end_time_am_pm"
                                           value="pm" <?php if ($end_time_am_pm == 'pm') {
                                        echo 'checked';
                                    } ?>> PM (hh:mm)
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php } else { ?>
                    <div class="form-group custom-content-row custom-hour-block">
                        <label><?= system_showText(LANG_LABEL_END_TIME) ?></label>
                        <div class="input-group">
                            <?= $end_time_hour_DD ?>
                            <span class="input-group-addon">:</span>
                            <?= $end_time_min_DD ?>
                        </div>
                        <?php if (CLOCK_TYPE == '12') { ?>
                            <div class="form-group">
                                <div class="radio-inline">
                                    <label>
                                        <input type="radio" id="endTimeAM" name="end_time_am_pm"
                                            value="am" <?php if ($end_time_am_pm == 'am') {
                                            echo 'checked';
                                        } ?> > AM
                                    </label>
                                </div>
                                <div class="radio-inline">
                                    <label>
                                        <input type="radio" id="endTimePM" name="end_time_am_pm"
                                            value="pm" <?php if ($end_time_am_pm == 'pm') {
                                            echo 'checked';
                                        } ?>> PM (hh:mm)
                                    </label>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>

            <br>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="recurring" name="recurring" value="Y" <?=$recurring == 'Y' ? 'checked' : ''; ?> onclick="recurringcheck();">
                        <?= system_showText(LANG_EVENT_RECURRING) ?>
                    </label>
                </div>
            </div>


            <div id="reccuring_events" style="display:none;">
                <div class="form-group">
                    <div class="form-horizontal">
                        <div class="form-group row custom-content-row align-start">
                            <label class="col-sm-3 control-label"><?= system_showText(LANG_PERIOD) ?></label>
                            <div class="selectize custom-selectize col-sm-6">
                                <select id="period" name="period" onchange="chooseperiod(this.value)">
                                    <option value="daily" <?= ($period == 'daily') ? ' selected="selected"' : '' ?>><?= system_showText(LANG_DAILY) ?></option>
                                    <option value="weekly" <?= ($period == 'weekly' || !$period) ? ' selected="selected"' : '' ?>><?= system_showText(LANG_WEEKLY) ?></option>
                                    <option value="monthly"<?= ($period == 'monthly') ? ' selected="selected"' : '' ?>><?= system_showText(LANG_MONTHLY) ?></option>
                                    <option value="yearly" <?= ($period == 'yearly') ? ' selected="selected"' : '' ?>><?= system_showText(LANG_YEARLY) ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="select_day" style="display:none;">
                        <div class="row form-horizontal custom-content-row align-start">
                            <label class="col-sm-3 control-label">
                                <input type="radio" id="precision1" name="precision" value="day" <?=$precision == 'day' ? 'checked' : ''; ?> onclick="chooseprecision(this.value)">
                                <?= system_showText(LANG_EVERY2).' '.system_showText(LANG_DAY) ?>
                            </label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <?php
                                    $month_names = explode(',', LANG_DATE_MONTHS);
                                    $weekday_names = explode(',', LANG_DATE_WEEKDAYS);
                                    ?>
                                    <input type="number" id="day" name="day" class="form-control" value="<?= ($day == 0 ? '' : $day) ?>" maxlength="2">
                                    <span class="input-group-addon customized-addon">
                                        <?php if($members){ ?>
                                            <span id="of2"><?= system_showText(LANG_OF2) ?></span>
                                            <span id="of4"><?= system_showText(LANG_OF4) ?> <?= system_showText(LANG_THE_MONTH) ?></span>
                                        <?php } else { ?>
                                            <span id="of2">&nbsp;&nbsp;<?= system_showText(LANG_OF2) ?>
                                                &nbsp;&nbsp;</span>
                                            <span id="of4">&nbsp;&nbsp; <?= system_showText(LANG_OF4) ?>
                                                &nbsp;<?= system_showText(LANG_THE_MONTH) ?>&nbsp;&nbsp;</span>
                                        <?php } ?>
                                        <select id="month" name="month" class="input">
                                            <option value=""><?= system_showText(LANG_CHOOSE_MONTH) ?></option>
                                            <?php for ($i = 0; $i < 12; $i++) {
                                                echo '<option value="'.($i + 1).'" '.((($i + 1) == $month && $precision == 'day') ? ' selected="selected"' : '').'>'.ucfirst($month_names[$i])."</option>\n";
                                            } ?>

                                        </select>
                                        </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="select_week">
                        <div class="row form-horizontal custom-content-row align-start">
                            <label class="control-label col-sm-3" id="every">
                                <input type="radio" id="precision2" name="precision" value="weekday" <?=$precision == 'weekday' ? 'checked' : ''; ?> onclick="chooseprecision(this.value)">
                                <?= system_showText(LANG_EVERY) ?>
                            </label>
                            <div class="col-sm-9">
                                <div class="form-inline">
                                    <div id="dayofweek">
                                        <?php
                                        $array_dayofweek = explode(',', $dayofweek);

                                        foreach ($weekday_names as $k => $day_name) {
                                            echo "<div class=\"checkbox-inline\"><label><input type=\"checkbox\" id=\"dayofweek_$k\" name=\"dayofweek_$k\" ".(in_array($k + 1,
                                                    $array_dayofweek) ? 'checked' : '').'>'.ucfirst(string_substr($day_name,
                                                    0, 3)).'</label></div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="week_of" style="display:none;">
                            <div class="row form-horizontal custom-content-row align-start">
                                <div id="weeklabel" class="col-sm-3">
                                    <label class="control-label"><?= system_showText(LANG_WEEK) ?></label>
                                </div>
                                <label id="of"></label>
                                <div class="col-sm-9 form-inline">
                                    <div id="week">
                                        <?php
                                        $array_numberofweek = explode(',', $week);
                                        $numbers_week = [
                                            0 => LANG_FIRST, 1 => LANG_SECOND, 2 => LANG_THIRD, 3 => LANG_FOURTH,
                                            4 => LANG_LAST,
                                        ];
                                        foreach ($numbers_week as $k => $name) {
                                            echo "<div class=\"checkbox-inline\"><label><input type=\"checkbox\" id=\"numberofweek_$k\" name=\"numberofweek_$k\" ".(in_array($k + 1,
                                                    $array_numberofweek) ? 'checked' : '').' >'.ucfirst($name).'</label></div>';
                                        }
                                        ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="month_of" style="display:none;">
                            <div class="form-horizontal row custom-content-row align-start">
                                <label id="of3" class="col-sm-3 control-label"><?= system_showText(LANG_MONTH) ?></label>
                                <div class="col-sm-9">
                                    <select id="month2" name="month" class="input">
                                        <option value=""><?= system_showText(LANG_CHOOSE_MONTH) ?></option>
                                        <?php for ($i = 0; $i < 12; $i++) {
                                            echo '<option value="'.($i + 1).'" '.((($i + 1) == $month && $precision == 'weekday') ? ' selected="selected"' : '').'>'.ucfirst($month_names[$i])."</option>\n";
                                        } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- form-group -->
            </div><!-- RECURRING EVENT-->

            <div id="reccuring_ends" style="display:none;">
                <div class="form-group form-horizontal row custom-content-row align-start" style="align-items: flex-start;">
                    <label class="control-label col-sm-3">
                        <?= system_showText(LANG_ENDS_IN) ?>
                    </label>
                    <div class="col-sm-9">
                        <div id="dayofweek">
                            <?php if($members){ ?>
                                <div class="row custom-content-row align-start" style="align-items: center;">
                                    <div class="radio-inline col-sm-2">
                                        <label>
                                            <input type="radio" id="eventUntil" name="eventPeriod" value="until" <?php if ($until_date || $eventPeriod == 'until') echo 'checked="checked"' ?> onclick="enableUntil('2');">
                                            <?= system_showText(LANG_UNTIL) ?>
                                        </label>
                                    </div>
                                    <div class="col-sm-6">
                                        <input class="form-control date-input" type="text" <?php if (!$until_date && $eventPeriod == 'ever') echo 'disabled' ?> name="until_date" id="until_date" value="<?= $until_date ?>" placeholder="(<?= format_printDateStandard() ?>)">
                                    </div>
                                </div>
                                <br>
                                <div class="row custom-content-row">
                                    <div class="col-sm-12">
                                        <label>
                                            <input type="radio" id="eventEver" name="eventPeriod" value="ever" <?php if (!$until_date || $eventPeriod == 'ever') echo 'checked="checked"' ?> onclick="enableUntil('1');">
                                            <?= system_showText(LANG_NEVER) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php } else { ?>
                            <div class="form-horizontal">
                                <div class="radio-inline col-sm-2">
                                    <label>
                                            <input type="radio" id="eventUntil" name="eventPeriod" value="until" <?php if ($until_date || $eventPeriod == 'until') echo 'checked="checked"' ?> onclick="enableUntil('2');">
                                            <?= system_showText(LANG_UNTIL) ?>
                                    </label>
                                </div>
                                <div class="form-group col-sm-6">
                                        <input class="form-control date-input" type="text" <?php if (!$until_date && $eventPeriod == 'ever') echo 'disabled' ?> name="until_date" id="until_date" value="<?= $until_date ?>" placeholder="(<?= format_printDateStandard() ?>)">
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="radio col-xs-12">
                                    <label>
                                            <input type="radio" id="eventEver" name="eventPeriod" value="ever" <?php if (!$until_date || $eventPeriod == 'ever') echo 'checked="checked"' ?> onclick="enableUntil('1');">
                                        <?= system_showText(LANG_NEVER) ?>
                                    </label>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div><!-- RECURRING EVENT-->

        </div>

    </div>

    <?php include(INCLUDES_DIR.'/forms/form-module-seocenter.php'); ?>

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
                    <?php if (((!$event->getNumber('id')) || (($event) && ($event->needToCheckOut())) || (string_strpos($url_base,
                                '/'.SITEMGR_ALIAS.'')) || (($event) && ($event->getPrice('monthly') <= 0 && $event->getPrice('yearly') <= 0))) && ($process != 'signup')) { ?>
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

    <!-- Images-->
    <?php
    $renderImageFields = false;

    if ((is_array($array_fields) && in_array('main_image', $array_fields)) || $levelMaxImages > 0) {
        system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_IMAGE_PLURAL),
            'tour-images');
        $renderImageFields = true;
    }
    $imageUploader->buildform($renderImageFields);
    ?>

    <?php if ($levelObjAux->getHasCoverImage($level) === 'y' && $levelObjAux->getDetail($level) === 'y') { ?>
        <?php
        system_fieldsGuide($arrayTutorial, $counterTutorial, system_showText(LANG_LABEL_COVERIMAGE),
            'tour-cover-image');
        ?>
        <!-- Cover Image-->
        <div id="tour-cover-image" class="panel panel-form-media">
            <div class="panel-heading">
                <?= system_showText(LANG_LABEL_COVERIMAGE); ?>

                <?php if (string_strpos($_SERVER['REQUEST_URI'], SITEMGR_ALIAS) !== false) { ?>
                <span class="btn btn-sm btn-danger delete pull-right <?= (!(int)$cover_id ? 'hidden' : '') ?>"
                        id="buttonReset" style="margin-left: 8px;">
                    <i class="icon-ion-ios7-trash-outline"
                       onclick="sendCoverImage('event', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'deleteCover');"></i>
                </span>
                    <div class="pull-right" style="margin-left: 8px;">
                    <input type="file" name="cover-image" class="file-noinput"
                           onchange="sendCoverImage('event', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'uploadCover');">
                </div>
                    <?php if (!empty(UNSPLASH_ACCESS_KEY)) { ?>
                        <div class="pull-right">
                            <button type="button" class="btn btn-primary btn-sm btn-unsplash">Unsplash</button>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="panel-heading-action">
                        <input type="file" name="cover-image" class="file-noinput" onchange="sendCoverImage('event', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'uploadCover');">

                        <?php if (!empty(UNSPLASH_ACCESS_KEY)) { ?>
                            <button type="button" class="btn btn-primary btn-sm btn-unsplash">Unsplash</button>
                        <?php } ?>

                        <button type="button" class="button button-sm is-warning delete <?= (!(int)$cover_id ? 'hidden' : '') ?>" id="buttonReset" onclick="sendCoverImage('event', '<?= $_SERVER['PHP_SELF'] ?>', <?= (isset($account_id) && $account_id == null ? $account_id : 0) ?>, 'deleteCover');"><i class="fa fa-trash"></i></button>
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

                        ?>
                        <input type="hidden" name="cover_id" value="<?= $cover_id; ?>">
                        <?php
                    } ?>
                </div>

                <input type="hidden" name="curr_cover_id" value="<?= $cover_id; ?>">

                <p id="returnMessage" class="alert alert-warning" style="display:none;"></p>

            </div>
            <div class="panel-footer text-center">
                <p class="small text-muted"><?= system_showText(LANG_LABEL_RECOMMENDED_DIMENSIONS); ?>
                    : <?= COVER_IMAGE_WIDTH ?> x <?= COVER_IMAGE_HEIGHT ?> px (JPG, GIF <?= system_showText(LANG_OR); ?>
                    PNG)</p>
                <p class="small text-muted"><?= system_showText(LANG_MSG_MAX_FILE_SIZE) ?> <?= UPLOAD_MAX_SIZE; ?>
                    MB. <?= system_showText(LANG_MSG_ANIMATEDGIF_NOT_SUPPORTED); ?></p>
            </div>
        </div>

    <?php } ?>

    <!-- Video-->
    <?php if (is_array($array_fields) && in_array('video', $array_fields)) {
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
                       class="form-control <?= ($highlight == 'media' && !$video_url ? 'highlight' : '') ?>"
                       placeholder="<?= system_showText(LANG_HOLDER_VIDEO); ?>" onchange="autoEmbed('video');">
            </div>
        </div>
    <?php } ?>
</div>
