<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/design/colors-fonts/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
    include '../../../conf/loadconfig.inc.php';

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

	mixpanel_track('Accessed section Colors & Fonts');

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
    $availableColors = unserialize(ARRAY_DEFAULT_COLORS);

    include INCLUDES_DIR.'/code/layout_editor.php';

    setting_get('colorscheme_'.EDIR_THEME, $colors);
    $colors = json_decode($colors, true);

    //Get colors
    foreach ($availableColors[EDIR_THEME] as $k => $availableColor) {
        if (!$colors[$k]) {
            ${$k} = $availableColors[EDIR_THEME][$k];
        } else {
            ${$k} = $colors[$k];
        }
    }

    if(empty($image_border)) {
        $image_border = 3;
    }

    if(empty($input_border)) {
        $input_border = 3;
    }

    if(empty($font)) {
        $font = 16;
    }

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include SM_EDIRECTORY_ROOT.'/layout/header.php';

    # ----------------------------------------------------------------------------------------------------
	# NAVBAR
	# ----------------------------------------------------------------------------------------------------
	include SM_EDIRECTORY_ROOT.'/layout/navbar.php';

    # ----------------------------------------------------------------------------------------------------
	# SIDEBAR
	# ----------------------------------------------------------------------------------------------------
	include SM_EDIRECTORY_ROOT.'/layout/sidebar-design.php';

    if(!empty($heading_font)) {
        ?>
        <style>
            @import url('https://fonts.googleapis.com/css?family=<?=$heading_font?>:regular');
        </style>
        <?php
    }

	if(!empty($paragraph_font)) {
	    ?>
            <style>
                @import url('https://fonts.googleapis.com/css?family=<?=$paragraph_font?>:regular');
            </style>
        <?php
    }

?>

    <main class="wrapper togglesidebar container-fluid wysiwyg">

        <?php
        require SM_EDIRECTORY_ROOT.'/registration.php';
        require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
        ?>

        <section class="heading">
            <div class="pull-right">
                <button type="button" name="reset_button" value="Submit" class="btn btn-default btn-lg action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>" onclick="<?=(DEMO_LIVE_MODE ? 'livemodeMessage(true, false);' : "JS_submitColors('reset');")?>"><?=system_showText(LANG_SITEMGR_RESET)?></button>
                <button type="button" name="submit_button" value="Submit" class="btn btn-primary btn-lg action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>" onclick="<?=(DEMO_LIVE_MODE ? 'livemodeMessage(true, false);' : "JS_submitColors('submit');")?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
            </div>
            <h1><?=system_showText(LANG_SITEMGR_COLORS_FONTS)?></h1>
            <p><?=LANG_SITEMGR_COLOR_FONTS_TIP?></p>
        </section>

        <section class="color-font-section">

            <form name="color_palette" id="color_palette" role="form" action="<?=system_getFormAction($_SERVER['PHP_SELF'])?>" method="post">

                <input type="hidden" name="submitAction" value="changecolors">
                <input type="hidden" name="action" id="action" value="submit">

                <h3><?=LANG_SITEMGR_COLOR_OPTIONS?></h3>
                <p><?=str_replace('%link%', '<a href="https://support.edirectory.com/customer/en/portal/articles/2975682-colors?b_id=7909" target=”_blank” rel=”noopener”>'.LANG_SITEMGR_CLICK_HERE.'</a>', LANG_SITEMGR_COLOR_TIP)?></p>

                <div class="colors-sections">
                    <?php
                        $count = 0;
                        foreach ($availableColors[EDIR_THEME] as $k => $availableColor) {
                            if ($count < 8) {
                    ?>
                    <div class="colors-item">
                        <span><?=system_showText(constant('LANG_SITEMGR_COLOR_'.string_strtoupper($k)));?></span>
                        <div class="colorSelector-<?= $k ?> color-box color-select" data-id="color<?= $k ?>" style="background-color:#<?= ${$k} ?>"></div>
                        <input type="hidden" id="color<?= $k ?>" name="<?= $k ?>"  value="<?= ${$k} ?>">
                    </div>
                    <?php
                            }
                            $count++;
                        }
                    ?>
                </div>

                <br>
                <h3><?=LANG_SITEMGR_FONT_OPTIONS?></h3>
                <p><?=str_replace('%link%', '<a href="http://support.edirectory.com/customer/portal/articles/2973913-how-to-change-font-family?b_id=7909" target=”_blank” rel=”noopener”>'.LANG_SITEMGR_CLICK_HERE.'</a>', LANG_SITEMGR_FONT_TIP)?></p>

                <div class="fonts-sections">
                    <div class="row form-group">
                        <div class="col-md-2">
                            <label for="heading_font"><?=LANG_SITEMGR_COLOR_HEADING_FONT;?></label>
                            <input type="text" class="form-control font-select font-family" name="heading_font" id="heading_font" data-value="<?= !empty($heading_font) ? $heading_font : 'Rubik' ?>" data-type="heading">
                        </div>
                        <div class="col-md-10">
                            <div class="font-family-preview" id="heading_sample" style="<?=!empty($heading_font) ? 'font-family: ' . $heading_font : ''?>">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
                        </div>
                    </div>

                    <div class="row form-group">
                        <div class="col-md-2">
                            <label for="paragraph_font"><?=LANG_SITEMGR_COLOR_PARAGRAPH_FONT;?></label>
                            <input type="text" class="form-control font-select font-family" name="paragraph_font" id="paragraph_font" data-value="<?= !empty($paragraph_font) ? $paragraph_font : 'Roboto' ?>" data-type="paragraph">
                        </div>
                        <div class="col-md-10">
                            <div class="font-family-preview" id="paragraph_sample" style="<?=!empty($paragraph_font) ? 'font-family: ' . $paragraph_font : ''?>">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
                        </div>
                    </div>
                    
                    <div class="row form-group">
                        <div class="col-md-2">
                            <label for="font_range"><?=system_showText(LANG_SITEMGR_COLOR_FONTSIZE);?> <small id="font_size">(<?=$font?>px)</small></label>
                            <input type="range" id="font_range" min="8" max="40" step="1" value="<?=$font?>" style="margin-top: 8px;">
                            <input type="hidden" id="font" name="font" value="<?=$font?>">
                        </div>
                        <div class="col-md-10">
                            <div class="font-size-preview" id="font_sample" style="font-size: <?=$font?>px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
                        </div>
                    </div>
                </div>

                <br>
                <h3><?=LANG_SITEMGR_BORDER_OPTIONS?></h3>
                <p><?=LANG_SITEMGR_BORDER_TIP?></p>

                <div class="border-sections">
                    <div class="row form-group">
                        <div class="col-md-3">
                            <label for="image_border_range"><?=LANG_SITEMGR_IMAGES_AND_CARDS?> <small id="image_border_size">(<?=$image_border?>px)</small></label>
                            <input type="range" id="image_border_range" min="0" max="33" step="3" value="<?=$image_border?>">
                            <input type="hidden" id="image_border" name="image_border" value="<?=$image_border?>">
                        </div>
                        <div class="col-md-9">
                            <img src="/assets/images/placeholders/180x90.jpg" id="image_border_sample" alt="" style="border-radius: <?=$image_border?>px;">
                        </div>
                    </div>
                </div>

                <div class="border-sections">
                    <div class="row form-group">
                        <div class="col-md-3">
                            <label for="input_border_range"><?=LANG_SITEMGR_INPUTS_BUTTONS_TAGS?> <small id="input_border_size">(<?=$input_border?>px)</small></label>
                            <input type="range" id="input_border_range" min="0" max="33" step="3" value="<?=$input_border?>">
                            <input type="hidden" id="input_border" name="input_border" value="<?=$input_border?>">
                        </div>
                        <div class="col-md-9">
                            <button type="button" id="input_border_sample" class="btn btn-primary btn-lg" style="border-radius: <?=$input_border?>px;"><?=LANG_SITEMGR_EXAMPLE?></button>
                        </div>
                    </div>
                </div>

            </form>

        </section>

    </main>

<?php
    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/design.php';
    include SM_EDIRECTORY_ROOT.'/layout/footer.php';
