<!-- edit footer modal -->
<?php
$content = json_decode($content, true);
$trans = json_decode($trans, true);
?>
<div class="modal-dialog modal-lg widget-footer" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"
                id="myModalLabel"><?= system_showText(LANG_SITEMGR_EDIT_WIDGET); ?> - <span class="widgetTitle"><?= $widgetTitle ?></span></h4>
        </div>
        <div class="modal-body">

            <div class="alert" id="messageAlertHeader" style="display: none">
                <div></div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <form role="form" id="form_navigation_footer" name="form_navigation_footer">
                        <h4 class="subtitle"><?= system_showText(LANG_SITEMGR_LABEL_FOOTER_NAVIGATION); ?>
                            <a class="pull-right" onclick="resetNavigation('footer')">
                                <small><?= system_showText(LANG_SITEMGR_RESET_NAVIGATION) ?></small>
                            </a>
                        </h4>
                        <ul id="sortableNav" class="list-sortable list-lg">
                            <?= $navbarFooter ?>
                            <li class="ui-sortable-handle ui-sortable-add" id="addNavBarItem">
                                <a class="sortable-add createItem" data-modalaux="footer" href="javascript:void(0)">
                                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                </a>
                            </li>
                        </ul>
                    </form>
                </div>
                <div class="col-md-6">
                    <h4 class="subtitle"><?= system_showText(LANG_SITEMGR_MOBILE_APPS); ?></h4>
                    <form class="form form-<?=$pageWidgetClass?>" id="form_mobile" name="form_mobile">
                        <div class="alert" style="display: none;"></div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="playStoreLabel" class="control-label">
                                        <?= system_showText(LANG_SITEMGR_ANDROID_LABEL) ?>
                                    </label>
                                    <input type="text" class="form-control" id="playStoreLabel"
                                           name="playStoreLabel" value="<?= $content['playStoreLabel'] ?>"
                                           placeholder="<?= system_showText(LANG_SITEMGR_ANDROID_LABEL_TIP) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="linkPlayStore" class="control-label">
                                        <?= system_showText(LANG_SITEMGR_PLAY_STORE_LINK) ?>
                                    </label>
                                    <input type="text" class="form-control" id="linkPlayStore" name="linkPlayStore" value="<?= $content['linkPlayStore'] ?>">
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="AppStoreLabel" class="control-label">
                                        <?= system_showText(LANG_SITEMGR_APPLE_LABEL) ?>
                                    </label>
                                    <input type="text" class="form-control" id="AppStoreLabel"
                                           name="AppStoreLabel" value="<?= $content['AppStoreLabel'] ?>"
                                           placeholder="<?= system_showText(LANG_SITEMGR_APPLE_LABEL_TIP) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="linkAppleStore" class="control-label">
                                        <?= system_showText(LANG_SITEMGR_APPLE_STORE_LINK) ?>
                                    </label>
                                    <input type="text" class="form-control" id="linkAppleStore" name="linkAppleStore" value="<?= $content['linkAppleStore'] ?>">
                                </div>
                            </div>
                        </div>

                        <?php
                        $hasNeutralColor = false;

                        include INCLUDES_DIR . '/forms/form-design-settings.php';
                        ?>
                    </form>
                </div>
            </div>

            <form class="form" name="form_footer" id="form_footer">

                <div class="showLabels">
                    <div class="row">
                        <div class="col-sm-12">
                            <p>
                                <a role="button" class="arrow-toggle collapsed" data-toggle="collapse" href="#collapseShowLabelsFooter" aria-expanded="false" aria-controls="collapseShowLabelsFooter" tabindex="25">
                                    <?= system_showText(LANG_SITEMGR_WIDGET_SHOW_FOOTER_LABELS) ?>
                                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                    <i class="fa fa-chevron-up" aria-hidden="true"></i>
                                </a>
                            </p>

                            <div class="collapse" id="collapseShowLabelsFooter" style="height: auto;">
                                <div id="labelInputs">
                                    <input type="hidden" name="pageWidgetId" value="<?= $pageWidgetId ?>" />
                                    <? echo $widgetService->getGenericLabelInputs($content, $trans); ?>
                                </div>
                                <input type="hidden" name="saveWidgetForAllPages" value="1">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <div class="row">
                <div class="col-xs-12 text-right">
                    <button type="button" class="btn btn-lg"
                            data-dismiss="modal"><?= system_showText(LANG_SITEMGR_CANCEL); ?></button>
                    <button type="button" class="btn btn-primary btn-lg action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"
                            onclick="<?= DEMO_LIVE_MODE ? 'livemodeMessage(true, false);' : "saveWidget('footer','" . $pageWidgetClass . "');" ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>

                </div>
            </div>
        </div>

        <div class="alert alert-warning text-center">
            <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
            <?=system_showText(LANG_SITEMGR_CHANGES_WIDGET)?>
        </div>
    </div>
</div>
