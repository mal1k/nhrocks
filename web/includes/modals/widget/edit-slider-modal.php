<!-- edit slider modal -->
<?php
$content = json_decode($content, true);
$sitemgrLanguage = substr($container->get('settings')->getSetting('sitemgr_language'), 0, 2);
?>
<div class="modal-dialog modal-lg widget-slider" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"
                id="myModalLabel"><?= system_showText(LANG_SITEMGR_EDIT_WIDGET); ?> - <span
                    class="widgetTitle"><?= $widgetTitle ?></span></h4>
        </div>
        <div class="modal-body">
            <div class="alert" id="messageAlertSlider" style="display: none">
                <div></div>
            </div>
            <input type="hidden" name="number_of_items" value="<?= TOTAL_SLIDER_ITEMS ?>">
            <input type="hidden" id="deletedSlides" name="deletedSlides" value="">
            <input type="hidden" id="slidetype" name="slidetype" value="content">

            <form id="form_slider" name="form_slider">
                <input type="hidden" id="pageWidgetId" name="pageWidgetId" value="<?= $pageWidgetId ?>">

                <?php if ($widgetType != 'common') { ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="labelStartYourSearch" class="control-label">
                                    <?= system_showText(LANG_SITEMGR_SEARCH_LABEL_1) ?>
                                </label>
                                <input type="text" class="form-control" id="labelStartYourSearch" maxlength="90"
                                       name="labelStartYourSearch" value="<?= $container->get('translator')->trans(/** @Ignore */ $content['labelStartYourSearch'], [], 'widgets', $sitemgrLanguage) ?>"
                                       placeholder="<?= system_showText(LANG_SITEMGR_START_SEARCH_HERE) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="placeholderSearchKeyword" class="control-label">
                                    <?= system_showText(LANG_SITEMGR_KEYWORD_SEARCH_PLACEHOLDER) ?>
                                </label>
                                <input type="text" class="form-control" id="placeholderSearchKeyword" maxlength="30"
                                       name="placeholderSearchKeyword" value="<?= $container->get('translator')->trans(/** @Ignore */ $content['placeholderSearchKeyword']['value'], [], 'widgets', $sitemgrLanguage) ?>"
                                       placeholder="<?= system_showText(LANG_SITEMGR_SEARCH_ANYTHING_PLACEHOLDER) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="labelWhatLookingFor" class="control-label">
                                    <?= system_showText(LANG_SITEMGR_SEARCH_LABEL_2) ?>
                                </label>
                                <input type="text" class="form-control" id="labelWhatLookingFor" maxlength="130"
                                       name="labelWhatLookingFor" value="<?=  $container->get('translator')->trans(/** @Ignore */ $content['labelWhatLookingFor'], [], 'widgets', $sitemgrLanguage) ?>"
                                       placeholder="<?= system_showText(LANG_SITEMGR_WHAT_LOOKING_FOR) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="placeholderSearchLocation" class="control-label">
                                    <?= system_showText(LANG_SITEMGR_LOCATION_SEARCH_PLACEHOLDER) ?>
                                </label>
                                <input type="text" class="form-control" id="placeholderSearchLocation" maxlength="30"
                                       name="placeholderSearchLocation" value="<?= $container->get('translator')->trans(/** @Ignore */ $content['placeholderSearchLocation']['value'], [], 'widgets', $sitemgrLanguage) ?>"
                                       placeholder="<?= system_showText(LANG_SITEMGR_LOCATION_PLACEHOLDER) ?>">
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </form>
            <hr />
            <form id="form_slider_info" name="form_slider_info">

                <div class="row">
                    <div class="col-md-12">
                        <ul id="sortableSlider" class="list-sortable ui-sortable row list-sortable-custom">
                            <?= $slider ?>
                            <li class="col5 col-sm-6 ui-sortable-add" id="addNavBarItem">
                                <h5>&nbsp;</h5>
                                <a href="#" class="createSliderItem" data-slidetype="content" data-maxslides="<?=TOTAL_SLIDER_ITEMS?>">
                                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>

            <div id="sliderInfoDiv">
                <?= $sliderInfo ?>
            </div>

            <hr>

            <form id="form_generic" name="form_generic">
                <div class="row">
                    <?php if($content['hasDesign']) { ?>
                        <input type="hidden" name="hasDesign" value="<?=$content['hasDesign']?>">
                    <?php } ?>

                    <div class="col-md-3">
                        <?php
                        $hasNeutralColor = true;

                        include INCLUDES_DIR . '/forms/form-design-settings.php';
                        ?>
                    </div>
                </div>

                <br>
                <div class="form-group checkbox">
                    <label>
                        <input type="checkbox" class="inputCheck" name="saveWidgetForAllPages">
                        <?= system_showText(LANG_SITEMGR_LABEL_SAVE_WIDGET_FOR_ALL_PAGES) ?>
                    </label>
                </div>
            </form>

        </div>
        <div class="modal-footer">
            <div class="row">
                <div class="col-xs-6 text-left">
                </div>
                <div class="col-xs-6 text-right">
                    <button type="button" class="btn btn-lg"
                            data-dismiss="modal"><?= system_showText(LANG_SITEMGR_CANCEL); ?></button>
                    <button type="button" class="btn btn-primary btn-lg action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"
                            id="<?= DEMO_LIVE_MODE ? 'livemodeMessage' : 'saveSliderWidget' ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                </div>
            </div>
        </div>
        <div class="alert alert-warning text-center">
            <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
            <?= system_showText(LANG_SITEMGR_CHANGES_WIDGET) ?>
        </div>
    </div>
</div>
