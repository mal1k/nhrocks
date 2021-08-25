<?php

use ArcaSolutions\WysiwygBundle\Entity\Widget;

setting_get('colorscheme_'.EDIR_THEME, $colors);
$colors = json_decode($colors, true);
$defaultColors = unserialize(ARRAY_DEFAULT_COLORS);

if(!empty($colors['brand'])) {
    $brandColor = $colors['brand'];
} else {
    $brandColor = $defaultColors['default']['brand'];
}

if(!empty($colors['neutral'])){
    $neutralColor = $colors['neutral'];
} else {
    $neutralColor = $defaultColors['default']['neutral'];
}

list($r, $g, $b) = sscanf('#'.$neutralColor, '#%02x%02x%02x');

?>

<?php if($isCard || (!empty($content['hasDesign']) && $content['hasDesign'] !== 'false')) { ?>
    <h3><?= LANG_SITEMGR_DESIGN_CUSTOM ?></h3>

    <?php if($isCard || isset($content['backgroundColor'])) { ?>
        <div class="widget-color-section">
            <h4><?= LANG_SITEMGR_COLOR_BACKGROUND ?></h4>
            <?php if(!$isCard) { ?>
                <div class="alert alert-success" style="max-width: 50%; display: none;"></div>
            <?php } ?>
            <div class="widget-color-list">
                <label class="color-item <?=$content['backgroundColor'] === 'brand' ? 'is-selected' : ''?>">
                    <input type="radio" name="backgroundColor" value="brand" <?=$content['backgroundColor'] === 'brand' ? 'checked=checked' : ''?>/>
                    <span><?=system_showText(LANG_SITEMGR_COLOR_BRAND)?></span>
                    <div class="color-preview" style="background-color: #<?=$brandColor?>;"></div>
                </label>

                <?php if($hasNeutralColor) { ?>
                    <label class="color-item <?=$content['backgroundColor'] === 'neutral' ? 'is-selected' : ''?>">
                        <input type="radio" name="backgroundColor" value="neutral" <?=$content['backgroundColor'] === 'neutral' ? 'checked=checked' : ''?>/>
                        <span><?=system_showText(LANG_SITEMGR_COLOR_NEUTRAL)?></span>
                        <div class="color-preview" style="background-color: rgba(<?=$r?>, <?=$g?>, <?=$b?>, 0.05);"></div>
                    </label>
                <?php } ?>

                <label class="color-item <?=$content['backgroundColor'] === 'base' || ($isCard && empty($content['backgroundColor'])) ? 'is-selected' : ''?>">
                    <input type="radio" name="backgroundColor" value="base" <?=$content['backgroundColor'] === 'base' || ($isCard && empty($content['backgroundColor'])) ? 'checked=checked' : ''?>/>
                    <span><?=system_showText(LANG_WHITE)?></span>
                    <div class="color-preview" style="background-color: #fff;"></div>
                </label>
            </div>
        </div>
    <?php } ?>

    <?php if($widgetType === Widget::HEADER_TYPE) { ?>
        <br>
        <div class="widget-options">
            <h4><?=LANG_SITEMGR_DISPLAY_OPTIONS?></h4>
            <div class="options-list">
                <div class="options-item">
                    <label class="option-title">
                        <input type="checkbox" name="isTransparent" value="true" <?=!empty($content['isTransparent']) && $content['isTransparent'] === 'true' ? 'checked=checked' : ''?>>
                        <?=LANG_SITEMGR_TRANSPARENT?>
                    </label>
                    <div class="option-description"><?=LANG_SITEMGR_TRANSPARENT_TIP;?></div>
                </div>
                <div class="options-item">
                    <label class="option-title">
                        <input type="checkbox" name="stickyMenu" value="true" <?=!empty($content['stickyMenu']) && $content['stickyMenu'] === 'true' ? 'checked=checked' : ''?>>
                        <?=LANG_SITEMGR_STICKY_MENU?>
                    </label>
                    <div class="option-description"><?=LANG_SITEMGR_STICKY_MENU_TIP;?></div>
                </div>
            </div>
        </div>
        <?php
        /* ModStores Hooks */
        HookFire('formdesignsettings_after_render_header_specific_block', [
            'content'        => &$content,
            'trans'          => &$trans,
            'originalWidget' => &$originalWidget,
            'pageWidget'     => &$pageWidget
        ]);
    } ?>

    <?php if($widgetType === Widget::FOOTER_TYPE) {
        /* ModStores Hooks */
        HookFire('formdesignsettings_after_render_footer_specific_block', [
            'content'        => &$content,
            'trans'          => &$trans,
            'originalWidget' => &$originalWidget,
            'pageWidget'     => &$pageWidget
        ]);
    } ?>

    <?php if(isset($content['dataAlignment'])) { ?>
        <h4><?=LANG_SITEMGR_CONTENT_ALIGNMENT?></h4>
        <select class="form-control" name="dataAlignment">
            <?php if($widgetType !== Widget::LEAD_TYPE) { ?>
                <option value="center" <?=($content['dataAlignment'] === 'center' ? 'selected' : '')?>>
                    <?=LANG_SITEMGR_COLOR_ALIGN_CENTER?>
                </option>
            <?php } ?>
            <option value="left" <?=($content['dataAlignment'] === 'left' ? 'selected' : '')?>>
                <?=LANG_SITEMGR_COLOR_ALIGN_LEFT?>
            </option>
            <?php if($widgetType !== Widget::NEWSLETTER_TYPE) { ?>
                <option value="right" <?=($content['dataAlignment'] === 'right' ? 'selected' : '')?>>
                    <?=LANG_SITEMGR_COLOR_ALIGN_RIGHT?>
                </option>
            <?php } ?>
        </select>
    <?php } ?>

    <?php if(isset($content['dataColumn'])) { ?>
        <h4><?=LANG_SITEMGR_NUMBER_OF_COLUMNS?></h4>
        <div class="row">
            <div class="col-md-2">
                <select class="form-control" name="dataColumn">
                    <option value="2" <?=($content['dataColumn'] === '2' ? 'selected' : '')?>>2</option>
                    <option value="3" <?=($content['dataColumn'] === '3' ? 'selected' : '')?>>3</option>
                    <option value="4" <?=($content['dataColumn'] === '4' ? 'selected' : '')?>>4</option>
                </select>
            </div>
        </div>
    <?php } ?>

    <?php if(!empty($content['hasCounter']) && $content['hasCounter'] === 'true') { ?>
        <br>
        <div class="checkbox-group">
            <label>
                <input type="checkbox" name="enableCounter" value="true" <?=!empty($content['enableCounter']) && $content['enableCounter'] === 'true' ? 'checked=checked' : ''?>>
                <?=LANG_SITEMGR_ENABLE_COUNTER?>
            </label>
        </div>
    <?php } ?>

    <?php if(isset($content['customBanners'])) { ?>
        <h4><?=LANG_SITEMGR_DISPLAY_BANNERS?></h4>
        <select class="form-control" name="customBanners">
            <option value='empty' <?=($content['customBanners'] === 'empty' ? 'selected' : '')?>>
                <?=LANG_SITEMGR_NOT_DISPLAY_BANNERS?>
            </option>
            <option value='square' <?=($content['customBanners'] === 'square' ? 'selected' : '')?>>
                <?=LANG_SITEMGR_DISPLAY_SQUARE_BANNERS?>
            </option>
            <option value='skyscraper' <?=($content['customBanners'] === 'skyscraper' ? 'selected' : '')?>>
                <?=LANG_SITEMGR_DISPLAY_SKYSCRAPPER_BANNERS?>
            </option>
        </select>
    <?php } ?>
<?php } ?>
