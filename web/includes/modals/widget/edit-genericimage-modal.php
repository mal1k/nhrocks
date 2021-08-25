<!-- edit generic image modal -->
<?php
$content = json_decode($content, true);
$container = SymfonyCore::getContainer();

if(!empty($content['imageId'])) {
    $image = new Image($content['imageId']);
    $imagePath = $image->getPath();
}

$langSearchPhotos = system_showText(LANG_SEARCH_PHOTOS);
$langPhotosByUnsplash = system_showText(LANG_PHOTOS_BY_UNSPLASH);
$langLoadMore = system_showText(LANG_LOAD_MORE);
$photosHTML = '';
if(!empty(UNSPLASH_ACCESS_KEY)){
    $photos = image_getUnsplash();
    foreach ($photos as $photo) {
        $photosHTML .= <<<HTML
<div class="unsplash-item" id="{$photo['id']}">
    <a href="javascript:;" class="unsplash-picture thumb-image-modal" data-download="{$photo['download_location']}" data-regular="{$photo['regular']}">
        <img src="{$photo['thumb']}" alt="{$photo['description']}">
    </a>
    <a href="{$photo['photographer_link']}" target="_blank" class="unsplash-author">{$photo['photographer']}</a>
</div>
HTML;
    }

$photosHTML = <<<HTML
<hr/>
<div class="section-unsplash">
    <div class="unsplash-header">
        <div class="form-group">
            <label><strong>$langSearchPhotos</strong> - $langPhotosByUnsplash</label>
            <input type="text" name="query" class="form-control input-unsplash" placeholder="$langSearchPhotos">
        </div>
    </div>
    <div class="unsplash-body">
        {$photosHTML}
    </div>
    <div class="unsplash-loadmore">
        <button type="button" class="btn btn-block btn-default btn-unsplash-more" data-page="1">$langLoadMore</button>
    </div>
</div>
HTML;
}


?>
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"
                id="myModalLabel"><?= system_showText(LANG_SITEMGR_EDIT_WIDGET); ?> - <span
                    class="widgetTitle"><?= $widgetTitle ?></span></h4>
        </div>
        <div class="modal-body">
            <div class="alert" id="messageAlertGenericImage" style="display: none">
                <div></div>
            </div>

            <div class="row">
                <div class="<?= count($content) > 1? 'col-md-6' : 'col-md-12'; ?>">
                    <h5><?= system_showText(LANG_SITEMGR_COLOR_BACKGROUNDIMAGE); ?></h5>
                    <div class="row">
                        <div class="col-md-12">
                            <form id="form_generic_image" name="form_image">
                                <input id="bgImageGenericInput" name="background_image_generic" type="file"
                                       style="display: none;"
                                       onchange="saveImage('form_generic_image', 'image', 'bgGenericImage', 'image-background-generic', 'imageId', 'messageAlertGenericImage')">
                            </form>
                            <div id="image-background-generic" class="img-background text-center">
                                <?php
                                if (!empty($imagePath || !empty($content['unsplash']))) {
                                    ?>
                                    <div class="edit-hover unsplash-preview">
                                        <a href="#" class="bgGenericImageButton" tabindex="198" id="bgGenericImage" title="eDirectory" style="background-image: url(<?= (!empty($content['unsplash']) ? $content['unsplash'] : $imagePath) ?>)">
                                            <?php /*<img id="bgGenericImage" src="<?= (!empty($content['unsplash']) ? $content['unsplash'] : $imagePath) ?>" alt="eDirectory"> */ ?>
                                        </a>
                                    </div>
                                <?php } else { ?>
                                    <div class="new">
                                        <a class="thumbnail add-new bgGenericImageButton" href="#">
                                            <div class="caption">
                                                <h6><i class="fa fa-plus-circle"
                                                       aria-hidden="true"></i> <?= system_showText(LANG_SITEMGR_ADD_SEARCH_IMAGE) ?>
                                                </h6>
                                            </div>
                                        </a>
                                    </div>
                                <?php } ?>
                                <?php echo $photosHTML; ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <p>
                                <small class="help-block"><?= str_replace('[dimension]',
                                        IMAGE_THEME_BACKGROUND_W.' x '.IMAGE_THEME_BACKGROUND_H,
                                        system_showText(LANG_SITEMGR_BACKGROUND_TIP)); ?></small>
                            </p>
                        </div>
                    </div>
                    <div id="loading_backgroundimage"
                         class="alert alert-loading alert-block text-center hidden">
                        <img src="<?= DEFAULT_URL; ?>/<?= SITEMGR_ALIAS ?>/assets/img/loading-64.gif">
                    </div>
                </div>
                <form class="form form-<?=$pageWidgetClass?>" name="form_genericimage" id="form_genericimage">
                    <input type="hidden" name="pageWidgetId" value="<?= $pageWidgetId ?>" />
                    <input type="hidden" id="unsplash" name="unsplash" value="<?= $content['unsplash'] ?>" />
                    <div id="labelInputs" class="col-md-6">
                        <div class="alert" style="display: none;"></div>
                        <?
                        $trans = json_decode($trans, true);

                        echo $widgetService->getGenericLabelInputs($content, $trans);
                        ?>

                        <?php
                        $hasNeutralColor = false;

                        include INCLUDES_DIR . '/forms/form-design-settings.php';
                        ?>

                        <? if (count($content) > 1 && !$exclusiveWidget) { ?>
                            <hr>
                            <div class="form-group checkbox">
                                <label>
                                    <input type="checkbox" class="inputCheck" name="saveWidgetForAllPages" value="1">
                                    <?= system_showText(LANG_SITEMGR_LABEL_SAVE_WIDGET_FOR_ALL_PAGES) ?>
                                </label>
                            </div>
                        <? } ?>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <div class="row">
                <div class="col-xs-6 text-left">
                </div>
                <div class="col-xs-6 text-right widget-modal-buttons">
                    <button type="button" class="btn btn-lg"
                            data-dismiss="modal"><?= system_showText(LANG_SITEMGR_CANCEL); ?></button>
                    <button type="button" class="btn btn-primary btn-lg action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"
                            onclick="<?= DEMO_LIVE_MODE ? 'livemodeMessage(true, false);' : "saveWidget('genericimage','" . $pageWidgetClass . "');" ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
