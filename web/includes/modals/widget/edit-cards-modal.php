<?php
$content = json_decode($content, true);
?>

<div class="modal-dialog modal-lg modal-card-custom" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">
                <?= system_showText(LANG_SITEMGR_EDIT_WIDGET); ?> - <span class="widgetTitle"><?= $widgetTitle ?></span>
            </h4>
        </div>
        <div class="modal-body widget-card widget-edit-card">
            <?php require EDIRECTORY_ROOT.'/includes/forms/form-card.php' ?>
        </div>
        <div class="modal-footer">
            <div class="row">
                <div class="col-xs-6 text-left"></div>
                <div class="col-xs-6 text-right widget-modal-buttons">
                    <button type="button" class="btn btn-lg" data-dismiss="modal"><?= system_showText(LANG_SITEMGR_CANCEL); ?></button>
                    <button id="bt_save_card" type="button" class="btn btn-primary btn-lg action-save" data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"
                            onclick="<?= DEMO_LIVE_MODE ? 'livemodeMessage(true, false);' : '' ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
