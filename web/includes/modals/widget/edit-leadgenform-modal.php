<!-- edit slider modal -->
<?php
$content = json_decode($content, true);
$trans = json_decode($trans, true);

$tempWidgetId = $pageWidgetId ?: system_generatePassword();
?>
<div class="modal-dialog modal-lg widget-slider" data-widget-type="leadgen" role="document">
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

            <hr />
            <br />

            <form id="form_slider" name="form_slider">
                <input type="hidden" id="pageWidgetId" name="pageWidgetId" value="<?= $pageWidgetId ?>">
                <input type="hidden" id="tempWidgetId" name="tempWidgetId" value="<?= $tempWidgetId ?>">

                <div class="row">
                    <div class="col-md-6">
                        <div id="labelInputs">
                            <?php echo $widgetService->getGenericLabelInputs($content, $trans); ?>
                        </div>

                        <?php
                        include INCLUDES_DIR . '/forms/form-design-settings.php';
                        ?>
                    </div>
                    <div class="col-md-6">
                        <section class="section-form">
                            <p><?=system_showText(LANG_SITEMGR_LEADS_TIP2)?></p>
                            <p id="successMessage" class="alert alert-success" style="display:none;"><?=system_showText(LANG_SITEMGR_LEADS_SUCCESS)?></p>

                            <input type="hidden" name="domain_url" id="domain_url" value="<?=$domainURL?>" />
                            <input type="hidden" name="livemode" id="livemode" value="<?=(DEMO_LIVE_MODE ? 1 : 0)?>" />
                            <input type="hidden" name="livemode_msg" id="livemode_msg" value="<?=system_showText(LANG_SITEMGR_THEME_DEMO_MESSAGE2);?>" />
                            <div id="form-builder" class="form-builder form-builder-leadgen"></div>
                        </section>
                    </div>
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
                    <button type="button" class="frmb-submit btn btn-primary btn-lg action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"
                            id="frmb-0-save-button"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                </div>
            </div>
        </div>
        <div class="alert alert-warning text-center">
            <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
            <?= system_showText(LANG_SITEMGR_CHANGES_WIDGET) ?>
        </div>
    </div>
</div>
