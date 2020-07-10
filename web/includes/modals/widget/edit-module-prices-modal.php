<?php
$content = json_decode($content, true);
?>
<div class="modal-dialog" role="document">
    <div class="modal-content max-h">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"
                id="myModalLabel"><?= system_showText(LANG_SITEMGR_EDIT_WIDGET); ?> - <span
                    class="widgetTitle"><?= $widgetTitle ?></span></h4>
        </div>
        <div class="modal-body">
            <div class="alert" id="messageAlertModulePrices" style="display: none">
                <div></div>
            </div>
            <form id="form_moduleprices" name="form_moduleprices">
                <input type="hidden" id="pageWidgetId" name="pageWidgetId" value="<?= $pageWidgetId ?>">
                <input type="hidden" name="saveWidgetForAllPages" value="1">
                <input type="hidden" name="module" value="<?= $content['module'] ?>">

                <div class="form-group">
                    <label for="labelModuleOptions"
                           class="control-label"><?= system_showText(LANG_SITEMGR_TITLE) ?></label>
                    <input type="text" class="form-control" id="labelModuleOptions"
                           name="labelModuleOptions"
                           placeholder="<?= system_showText(LANG_SITEMGR_WIDGET_TYPE_TITLE) ?>"
                           value="<?= $content['labelModuleOptions'] ?>">
                </div>
                <div class="form-group">
                    <label for="labelDescription"
                           class="control-label"><?= system_showText(LANG_SITEMGR_LABEL_DESCRIPTION) ?></label>
                    <input type="text" class="form-control" id="labelDescription" name="labelDescription"
                           placeholder="<?= system_showText(LANG_SITEMGR_WIDGET_TYPE_DESCRIPTION) ?>"
                           value="<?= $content['labelDescription'] ?>">
                </div>

                <?php if($content['hasDesign']) { ?>
                    <input type="hidden" name="hasDesign" value="<?=$content['hasDesign']?>">
                <?php } ?>

                <?php
                $hasNeutralColor = true;

                include INCLUDES_DIR . '/forms/form-design-settings.php';
                ?>

                <div class="showLabels">
                    <div class="row">
                        <div class="col-sm-12">
                            <p>
                                <a class="pull-right" target="_blank"
                                   href="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/configuration/payment?option=<?= $content['module']?>">
                                    <?= system_showText(LANG_SITEMGR_WIDGET_CHANGE_PLANS_PRICING) ?>
                                </a>

                                   <a role="button" class="arrow-toggle collapsed" data-toggle="collapse"
                                   href="#collapseShowLabels" aria-expanded="false" aria-controls="collapseShowLabels"
                                   tabindex="25">
                                    <?= system_showText(LANG_SITEMGR_WIDGET_FEATURES_COLLAPSE) ?>
                                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                    <i class="fa fa-chevron-up" aria-hidden="true"></i>
                                </a>

                            </p>

                            <div class="collapse" id="collapseShowLabels" style="height: auto;">
                                <?
                                $moduleLevelClass = ucfirst($content['module']).'Level';
                                $levelObj = new $moduleLevelClass();
                                $levels = $levelObj->getValueName();
                                ?>
                                <div>
                                    <? foreach ($levels as $value => $name) { ?>
                                        <div class="form-group">
                                            <label for="level<?= $value ?>"
                                                   class="control-label"><?= ucfirst($name) ?></label>
                                            <textarea name="level<?= $value ?>" class="form-control" rows="5"
                                                      placeholder="<?= system_showText(LANG_SITEMGR_WIDGET_TYPE_PLAN_FEATURES) ?>"
                                                      id="level<?= $value ?>"><?= isset($content['level'.$value]) ? $content['level'.$value] : '' ?></textarea>
                                        </div>
                                    <? } ?>
                                </div>
                            </div>

                        </div>
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
                <div class="col-xs-6 text-right">
                    <button type="button" class="btn btn-lg"
                            data-dismiss="modal"><?= system_showText(LANG_SITEMGR_CANCEL); ?></button>
                    <button type="button" class="btn btn-primary btn-lg action-save"
                            data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"
                            onclick="<?= DEMO_LIVE_MODE ? "livemodeMessage" : "saveWidget('moduleprices')" ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
