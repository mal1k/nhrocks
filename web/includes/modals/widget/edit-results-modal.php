<?php
$content = json_decode($content, true);
?>

<!-- edit results modal -->
<div class="modal-dialog" role="document" id="">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"
                id="myModalLabel"><?= system_showText(LANG_SITEMGR_EDIT_WIDGET); ?> - <span class="widgetTitle"><?= $widgetTitle ?></span></h4>
        </div>
        <div class="modal-body">
            <form class="form" name="form_generic" id="form_generic">
                <input type="hidden" name="pageWidgetId" value="<?= $pageWidgetId ?>" />

                <div class="row">
                    <div class="col-md-12">
                        <h4><?=LANG_SITEMGR_VIEW_MODE?></h4>
                        <div class="widget-color-list">
                            <label class="color-item">
                                <input type="radio" name="resultView" value="list" <?=$content['resultView'] === 'list' ? 'checked=checked' : ''?>/>
                                <span><?=LANG_SITEMGR_RESULT_LIST?></span>
                            </label>

                            <label class="color-item">
                                <input type="radio" name="resultView" value="grid" <?=$content['resultView'] === 'grid' ? 'checked=checked' : ''?>/>
                                <span><?=LANG_SITEMGR_RESULT_GRID?></span>
                            </label>

                            <label class="color-item">
                                <input type="radio" name="resultView" value="list-grid" <?=$content['resultView'] === 'list-grid' ? 'checked=checked' : ''?>/>
                                <span><?=LANG_SITEMGR_RESULT_LIST_AND_GRID?></span>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4><?=LANG_SITEMGR_FILTER_POSITION?></h4>
                        <select class="form-control" name="filterSide">
                            <option value='left' <?=($content['filterSide'] === 'left' ? 'selected' : '')?>>
                                <?=LANG_SITEMGR_RESULT_LEFT?>
                            </option>
                            <option value='right' <?=($content['filterSide'] === 'right' ? 'selected' : '')?>>
                                <?=LANG_SITEMGR_RESULT_RIGHT?>
                            </option>
                        </select>
                    </div>
                </div>

                <hr>

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
                <div class="col-xs-6 text-right widget-modal-buttons">
                    <button type="button" class="btn btn-lg"
                            data-dismiss="modal"><?= system_showText(LANG_SITEMGR_CANCEL); ?></button>
                    <button type="button" class="btn btn-primary btn-lg action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"
                            onclick="<?= DEMO_LIVE_MODE ? 'livemodeMessage(true, false);' : "saveWidget('generic');" ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
