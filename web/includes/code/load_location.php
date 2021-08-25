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
# * FILE: /includes/code/load_location.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
if (string_strpos($_SERVER['REQUEST_URI'], SITEMGR_ALIAS) !== false) { ?>
    <div id="formsLocation" class="form-location form-group row">
        <?php if ($_default_locations_info) {
            foreach ($_default_locations_info as $_default_location_info) {
                if ($_default_location_info['show'] == 'y') { ?>
                    <div class="col-sm-12 col-md-6">
                        <label for="location_<?= $_default_location_info['type'] ?>">
                            <?= system_showText(constant('LANG_LABEL_'.constant('LOCATION'.$_default_location_info['type'].'_SYSTEM'))) ?>
                        </label>
                        <input type="text" class="form-control" disabled value="<?= $_default_location_info['name'] ?>">
                    </div>

                <?php } ?>
                <input type="hidden" name="location_<?= $_default_location_info['type'] ?>"
                       value="<?= $_default_location_info['id'] ?>">
                <?php
            }
        }

        if ($_non_default_locations) {
            foreach ($_non_default_locations as $_location_level) {

                system_retrieveLocationRelationship($_non_default_locations, $_location_level, $_location_father_level,
                    $_location_child_level);
                $location_name = system_showText(constant('LANG_LABEL_'.constant('LOCATION'.$_location_level.'_SYSTEM')));
                ?>

                <div class="col-xs-6"
                     id="div_location_<?= $_location_level ?>" <?= ((${'locations'.$_location_level} && $_POST['new_location'.$_location_level.'_field'] == '') || (!${'locations'.$_location_level} && ${'location_'.$_location_father_level} && !$_POST['new_location'.$_location_level.'_field'])) ? '' : 'style="display:none;"' ?>>

                    <label for="location_<?= $_location_level ?>"><?= $location_name ?></label>

                    <div class="field" id="div_img_loading_<?= $_location_level ?>" style="display:none;">
                        <img src="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/assets/img/preloader-32.gif"
                             alt="<?= system_showText(LANG_WAITLOADING) ?>"/>
                    </div>
                    <div id="div_select_<?= $_location_level ?>" class="field locationSelect">
                        <select
                            <?= (${'locations'.$_location_level} ? '' : 'style="display:none"') ?>

                                class="select <?= ($highlight == 'description' && !${'location_'.$_location_level} ? 'highlight' : '') ?> <?=$members?'form-control':''?>"

                                name="<?= ($sitemgrSearch ? 'search_' : '') ?>location_<?= $_location_level ?>"

                                id="location_<?= $_location_level ?>"

                            <?php
                            if ($_location_child_level) { ?>

                                onchange="loadLocationSitemgrMembers('<?= DEFAULT_URL ?>', '<?= EDIR_LOCATIONS ?>', <?= $_location_level ?>, <?= $_location_child_level ?>, this.value); <?php if ($loadMap) { ?> loadMap(<?=$formLoadMap?>); <?php } ?> "

                                <?php
                            } elseif ($loadMap) { ?>

                                onchange="loadMap(<?= $formLoadMap ?>);"

                            <?php } ?>
                        >

                            <option id="l_location_<?= $_location_level ?>"
                                    value=""><?= system_showText(constant('LANG_MSG_SELECT_A_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></option>
                            <?php
                            if (is_array(${'locations'.$_location_level})) {
                                foreach (${'locations'.$_location_level} as $each_location) {
                                    $selected = (${'location_'.$_location_level} == $each_location['id']) ? 'selected' : '';
                                    ?>
                                    <option <?= $selected ?>
                                    value="<?= $each_location['id'] ?>"><?= $each_location['name'] ?></option><?php
                                    unset($selected);
                                }
                            }
                            ?>
                        </select>
                        <div class="field"
                             id="box_no_location_found_<?= $_location_level ?>" <?= (!${'locations'.$_location_level} && ${'location_'.$_location_father_level} && !$_POST['new_location'.$_location_level.'_field'] ? '' : 'style="display:none;"') ?>><?= system_showText(constant('LANG_LABEL_NO_LOCATIONS_FOUND')) ?>
                        </div>
                    </div>
                    <div class="field">
                        <div id="div_new_location<?= $_location_level ?>_link" <?= ($_POST['new_location'.$_location_level.'_field'] == '' ? '' : 'style="display:none;"') ?> >
                            <?php if ($_location_level != 1 && !string_strpos($_SERVER['PHP_SELF'], 'index.php')) { ?>
                                <a class="small" href="javascript:void(0);"
                                   onclick="showNewLocationField('<?= $_location_level ?>', '<?= EDIR_LOCATIONS ?>', true);"
                                   style=" cursor: pointer">+ <?= system_showText(constant('LANG_LABEL_ADD_A_NEW_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></a>
                            <?php } else {
                                echo '&nbsp;';
                            } ?>
                        </div>
                    </div>
                </div>

                <?php if ($_location_level != 1 && !string_strpos($_SERVER['PHP_SELF'], 'index.php')) { ?>

                    <div class="col-xs-6"
                         id="div_new_location<?= $_location_level ?>_field" <?= ($_POST['new_location'.$_location_level.'_field'] != '' ? '' : ($_POST['new_location'.$_location_father_level.'_field'] != '' ? '' : 'style="display:none;"')) ?>>
                        <div>
                            <label for="newlocation"><?= system_showText(constant('LANG_LABEL_ADD_A_NEW_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></label>
                        </div>
                        <div class="field">
                            <input
                                    type="text"
                                    class="form-control"
                                    name="new_location<?= $_location_level ?>_field"
                                    id="new_location<?= $_location_level ?>_field"
                                    value="<?= $_POST['new_location'.$_location_level.'_field'] ?>"

                                <?php if ($_location_child_level) { ?>

                                    onfocus="showNewLocationField('<?= $_location_child_level ?>', '<?= EDIR_LOCATIONS ?>', false);"
                                <?php } ?>

                                    onblur="easyFriendlyUrl(this.value, 'new_location<?= $_location_level ?>_friendly', '<?= FRIENDLYURL_VALIDCHARS ?>', '<?= FRIENDLYURL_SEPARATOR ?>'); <?php if ($loadMap) { ?> loadMap(<?=$formLoadMap?>); <?php } ?> ">

                            <input type="hidden" name="new_location<?= $_location_level ?>_friendly"
                                   id="new_location<?= $_location_level ?>_friendly"
                                   value="<?= $_POST['new_location'.$_location_level.'_friendly'] ?>"/>
                        </div>
                        <div class="field" colspan="2">
                            <div id="div_new_location<?= $_location_level ?>_back" <?= ($_POST['new_location'.$_location_father_level.'_field'] == '' ? '' : 'style="display:none;"') ?>>
                                <a class="small" href="javascript:void(0);"
                                   onclick="hideNewLocationField('<?= $_location_level ?>', '<?= EDIR_LOCATIONS ?>');"
                                   style=" cursor: pointer">- <?= system_showText(constant('LANG_LABEL_CHOOSE_AN_EXISTING_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            unset ($_location_father_level);
            unset ($_location_child_level);
            unset ($_location_level);
        }
        ?>
    </div> <?php
} else { ?>

    <div id="formsLocation" class="form-location row custom-content-row">
        <?php

        if ($_default_locations_info) {
            foreach ($_default_locations_info as $_default_location_info) {
                if ($_default_location_info['show'] == 'y') {
                    ?>
                    <div class="form-group col-sm-6">
                        <label for="location_<?= $_default_location_info['type'] ?>">
                            <?= system_showText(constant('LANG_LABEL_'.constant('LOCATION'.$_default_location_info['type'].'_SYSTEM'))) ?>
                        </label>
                        <input type="text" class="form-control" disabled value="<?= $_default_location_info['name'] ?>">
                    </div>

                    <?php
                } ?>
                <input type="hidden" name="location_<?= $_default_location_info['type'] ?>"
                       value="<?= $_default_location_info['id'] ?>"><?php
            }
        }

        if ($_non_default_locations) {
            foreach ($_non_default_locations as $_location_level) {

                system_retrieveLocationRelationship($_non_default_locations, $_location_level, $_location_father_level,
                    $_location_child_level);
                $location_name = system_showText(constant('LANG_LABEL_'.constant('LOCATION'.$_location_level.'_SYSTEM')));
                ?>

                <div class="form-group col-sm-6"
                     id="div_location_<?= $_location_level ?>" <?= ((${'locations'.$_location_level} & $_POST['new_location'.$_location_level.'_field'] == '') || (!${'locations'.$_location_level} && ${'location_'.$_location_father_level} && !$_POST['new_location'.$_location_level.'_field'])) ? '' : 'style="display:none;"' ?>>

                    <label for="location_<?= $_location_level ?>"><?= $location_name ?></label>

                    <div class="field" id="div_img_loading_<?= $_location_level ?>" style="display:none;">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                    <div id="div_select_<?= $_location_level ?>" class="field locationSelect">
                        <select
                            <?= ((${'locations'.$_location_level}) ? '' : 'style="display:none"') ?>

                                class="select <?= ($highlight == 'description' && !${'location_'.$_location_level} ? 'highlight' : '') ?> form-control cutom-select-appearence"

                                name="<?= ($sitemgrSearch ? 'search_' : '') ?>location_<?= $_location_level ?>"

                                id="location_<?= $_location_level ?>"

                            <?php if ($_location_child_level) { ?>

                                onchange="loadLocationSitemgrMembers('<?= DEFAULT_URL ?>', '<?= EDIR_LOCATIONS ?>', <?= $_location_level ?>, <?= $_location_child_level ?>, this.value); <?php if ($loadMap) { ?> loadMap(<?=$formLoadMap?>); <?php } ?> "

                            <?php } elseif ($loadMap) { ?>

                                onchange="loadMap(<?= $formLoadMap ?>);"

                            <?php } ?>
                        >
                            <option id="l_location_<?= $_location_level ?>"
                                    value=""><?= system_showText(constant('LANG_MSG_SELECT_A_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></option>
                            <?php
                            if (is_array(${'locations'.$_location_level})) {
                                foreach (${'locations'.$_location_level} as $each_location) {
                                    $selected = (${'location_'.$_location_level} == $each_location['id']) ? 'selected' : '';
                                    ?>
                                    <option <?= $selected ?>
                                    value="<?= $each_location['id'] ?>"><?= $each_location['name'] ?></option><?php
                                    unset($selected);
                                }
                            }
                            ?>
                        </select>
                        <div class="field"
                             id="box_no_location_found_<?= $_location_level ?>" <?= (!${'locations'.$_location_level} && ${'location_'.$_location_father_level} && !$_POST['new_location'.$_location_level.'_field'] ? '' : 'style="display:none;"') ?>><?= system_showText(constant('LANG_LABEL_NO_LOCATIONS_FOUND')) ?>
                            .
                        </div>
                    </div>
                    <div class="field">
                        <div class=""
                             id="div_new_location<?= $_location_level ?>_link" <?= ($_POST['new_location'.$_location_level.'_field'] == '' ? '' : 'style="display:none;"') ?> >
                            <?php if ($_location_level != 1 && !string_strpos($_SERVER['PHP_SELF'], 'search.php')) { ?>
                                <a href="javascript:void(0);"
                                   onclick="showNewLocationField('<?= $_location_level ?>', '<?= EDIR_LOCATIONS ?>', true);"
                                   style=" cursor: pointer">+ <?= system_showText(constant('LANG_LABEL_ADD_A_NEW_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></a>
                            <?php } else {
                                echo '&nbsp;';
                            } ?>
                        </div>
                    </div>
                </div>

                <?php if ($_location_level != 1 && !string_strpos($_SERVER['PHP_SELF'], 'search.php')) { ?>

                    <div class="form-group col-sm-6"
                         id="div_new_location<?= $_location_level ?>_field" <?= ($_POST['new_location'.$_location_level.'_field'] != '' ? '' : ($_POST['new_location'.$_location_father_level.'_field'] != '' ? '' : 'style="display:none;"')) ?>>

                        <label for="newlocation">
                            <?= system_showText(constant('LANG_LABEL_ADD_A_NEW_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?>
                        </label>

                        <div class="field">
                            <input
                                    type="text"
                                    class="form-control"
                                    name="new_location<?= $_location_level ?>_field"
                                    id="new_location<?= $_location_level ?>_field"
                                    value="<?= $_POST['new_location'.$_location_level.'_field'] ?>"

                                <?php if ($_location_child_level) { ?>
                                    onfocus="showNewLocationField('<?= $_location_child_level ?>', '<?= EDIR_LOCATIONS ?>', false);"
                                <?php } ?>

                                    onblur="easyFriendlyUrl(this.value, 'new_location<?= $_location_level ?>_friendly', '<?= FRIENDLYURL_VALIDCHARS ?>', '<?= FRIENDLYURL_SEPARATOR ?>');

                                    <?php if ($loadMap) { ?>
                                            loadMap(<?=$formLoadMap?>);
                                    <?php } ?> "
                            >

                            <input type="hidden" name="new_location<?= $_location_level ?>_friendly"
                                   id="new_location<?= $_location_level ?>_friendly"
                                   value="<?= $_POST['new_location'.$_location_level.'_friendly'] ?>"/>
                        </div>
                        <div class="field">
                            <div id="div_new_location<?= $_location_level ?>_back" <?= ($_POST['new_location'.$_location_father_level.'_field'] == '' ? '' : 'style="display:none;"') ?>>
                                <a href="javascript:void(0);"
                                   onclick="hideNewLocationField('<?= $_location_level ?>', '<?= EDIR_LOCATIONS ?>');"
                                   style=" cursor: pointer">- <?= system_showText(constant('LANG_LABEL_CHOOSE_AN_EXISTING_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            unset ($_location_father_level);
            unset ($_location_child_level);
            unset ($_location_level);
        }
        ?>
    </div>

<?php } ?>
