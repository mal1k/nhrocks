<!-- edit video modal -->
<div class="modal-dialog modal-lg widget-slider" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"
                id="myModalLabel"><?= system_showText(LANG_SITEMGR_EDIT_WIDGET); ?> - <span class="widgetTitle"><?= $widgetTitle ?></span></h4>
        </div>
        <div class="modal-body">
            <div class="alert" id="messageAlertSlider" style="display: none">
                <div></div>
            </div>
            <input type="hidden" id="deletedSlides" name="deletedSlides" value="">
            <input type="hidden" id="slidetype" name="slidetype" value="video">

            <form id="form_slider" name="form_slider">
                <input type="hidden" id="pageWidgetId" name="pageWidgetId" value="<?= $pageWidgetId ?>">
                <input type="hidden" name="saveWidgetForAllPages" value="0">

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php
                            $content = json_decode($content, true);
                            $trans = json_decode($trans, true);
                            echo $widgetService->getGenericLabelInputs($content, $trans);
                            ?>
                        </div>
                    </div>
                </div>
            </form>

            <form id="form_slider_info" name="form_slider_info">
                <hr />

                <div class="row">
                    <div class="col-md-12">
                        <ul id="sortableSlider" class="list-sortable ui-sortable row">
                            <? if (is_array($content['videos']) && count ($content['videos']) > 0) {
                                foreach ($content['videos'] as $k => $video) {
                                    include INCLUDES_DIR.'/forms/form-video-structure.php';
                                }
                                echo $sliderHtml;
                            } ?>
                            <li class="col5 col-sm-6 ui-sortable-add" id="addNavBarItem">
                                <a href="#" class="createSliderItem" data-slidetype="video" data-maxslides="100">
                                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>

            <div id="sliderInfoDiv">

                <? if (is_array($content['videos']) && count ($content['videos']) > 0) {
                    foreach ($content['videos'] as $k => $video) {
                        include INCLUDES_DIR.'/forms/form-video-info-structure.php';
                        echo $sliderInfoHtml;
                    } ?>
                <? } ?>
            </div>

            <hr>

            <form id="form_generic" name="form_generic">
                <div class="row">
                    <?php if($content['hasDesign']) { ?>
                        <input type="hidden" name="hasDesign" value="<?=$content['hasDesign']?>">
                    <?php } ?>

                    <div class="col-md-12">
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
                            id="<?= DEMO_LIVE_MODE ? "livemodeMessage" : "saveSliderWidget" ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
