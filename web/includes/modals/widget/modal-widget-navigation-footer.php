<!-- create navigation footer modal -->
<?php

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
$loadSitemgrLangs = true;
include '../../../conf/loadconfig.inc.php';

/**
 * Array with Modules and URL
 */
unset($array_main_pages);
$container = SymfonyCore::getContainer();
$navigationService = $container->get('navigation.service');

$auxArrayPages = $navigationService->getNavigationPages('Footer');
$customLink = array('name' => LANG_SITEMGR_NAVIGATION_CUSTOM_LINK, 'url' => 'custom');
$array_main_pages = $auxArrayPages['mainPages'];
$navigationService->removesDisabledModules('Footer', $array_main_pages);
$array_custom_pages = $auxArrayPages['customPages'];
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"
        id="myModalLabel"><?= system_showText(LANG_SITEMGR_EDIT_NAVIGATION) ?></h4>
</div>
<div class="modal-body">
    <div class="alert" id="messageAlertNavigation" data-modalaux="footer" style="display: none">
        <div></div>
    </div>
    <form class="form" id="form_navigation_item" data-modalaux="footer">
        <input type="hidden" id="divIdNav" />
        <input type="submit" style="display: none">
        <div class="form-group">
            <label for="navLabel"
                   class="control-label"><?= system_showText(LANG_SITEMGR_LINK_LABEL) ?>
                </label>
            <input type="text" class="form-control" id="navLabel"
                   placeholder="<?= system_showText(LANG_SITEMGR_LABEL_PAGETITLE) ?>">
        </div>
        <div class="form-group selectize">
            <label for="navLink"
                   class="control-label"><?= system_showText(LANG_SITEMGR_LABEL_PAGELINK) ?>
                </label>
            <select class="form-control navLink" id="navLink" data-modalaux="footer">
                <optgroup label="<?= system_showText(LANG_SITEMGR_MAIN_PAGES) ?>">
                    <?php for($j = 0, $jMax = count($array_main_pages); $j < $jMax; $j++) {
                        $selected = false;
                        if ($array_main_pages[$j]['page_id'] === $arrayOptions[$i]['page_id']) {
                            $selected = 'selected = "selected"';
                        } ?>

                        <option value="<?=$array_main_pages[$j]['page_id']?>" <?=($selected ?: '')?>>
                            <?=string_ucwords($array_main_pages[$j]['name'])?>
                        </option>
                    <?php } ?>
                </optgroup>
                <?php if ($array_custom_pages) { ?>
                    <optgroup label="<?= system_showText(LANG_SITEMGR_CUSTOM_PAGES) ?>">
                        <?php foreach ($array_custom_pages as $j => $jValue) {
                            $selected = false;
                            if ($array_custom_pages[$j]['page_id'] === $arrayOptions[$i]['page_id']) {
                                $selected = 'selected = "selected"';
                            } ?>

                            <option value="<?=$array_custom_pages[$j]['page_id']?>" <?=($selected ?: '')?>>
                                <?=string_ucwords($array_custom_pages[$j]['name'])?>
                            </option>
                        <?php } ?>
                    </optgroup>
                <?php } ?>
                <?php $selected = false;
                if ($customLink['url'] === $arrayOptions[$i]['link']) {
                    $selected = 'selected = "selected"';
                } ?>
                <optgroup label="<?=string_ucwords($customLink['name'])?>">
                    <option value="<?=$customLink['url']?>" <?=($selected ?: '')?>>
                        <?=string_ucwords($customLink['name'])?>
                    </option>
                </optgroup>
            </select>
        </div>
        <div id="navCustomLinkDiv" class="form-group">
            <label for="navCustomLink"
                   class="control-label"><?= system_showText(LANG_SITEMGR_NAVIGATION_CUSTOM_LINK) ?>
                :</label>
                <div class="<?=string_strpos( $_POST['url'], '://') === false ? 'input-group' : '' ?>">
                    <span class="input-group-addon <?=string_strpos( $_POST['url'],
                            '://') === false ? '' : 'hidden' ?>"><?= $container->get('pagetype.service')->getBaseUrl(\ArcaSolutions\WysiwygBundle\Entity\PageType::HOME_PAGE).'/' ?></span>
                    <input type="text" class="form-control navCustomLink" id="navCustomLink" data-modalaux="footer"
                           placeholder="<?= system_showText(LANG_SITEMGR_NAVIGATION_CUSTOM_LINK) ?>">
                </div>
            <div class="form-horizontal">
                <div class="radio-inline">
                    <label>
                        <input type="radio" name="navCustomLinkType" id="navCustomLinkType" value="internal" data-modalaux="footer" checked/>
                        <?=system_showText(LANG_SITEMGR_NAVIGATION_INTERNAL_PAGE)?>
                        <input type="hidden" id="internalValue" value="" data-modalaux="footer" />
                    </label>
                </div>
                <div class="radio-inline">
                    <label>
                        <input type="radio" name="navCustomLinkType" id="navCustomLinkType" value="external" data-modalaux="footer"/>
                        <?=system_showText(LANG_SITEMGR_NAVIGATION_EXTERNAL_LINK)?>
                        <input type="hidden" id="externalValue" value="" data-modalaux="footer" />
                    </label>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default btn-lg"
            data-dismiss="modal"><?= system_showText(LANG_SITEMGR_CANCEL) ?></button>
    <button type="button" id="saveNavButton" class="btn btn-primary btn-lg saveNavButton action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"
            data-modalaux="footer"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
</div>
