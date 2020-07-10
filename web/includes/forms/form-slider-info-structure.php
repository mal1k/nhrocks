<?php

$sliderId = $r ? $r : $sliders[$i]->getId();
$openWindow = $sliders[$i]->getTarget() === 'self' ? '' : 'checked=checked';
setting_get('sitemgr_language', $sitemgr_language);
$sitemgrLanguage = substr($sitemgr_language, 0, 2);
$photosHTML = '';

if(!empty(UNSPLASH_ACCESS_KEY)) {
    $langSearchPhotos = system_showText(LANG_SEARCH_PHOTOS);
    $langPhotosByUnsplash = system_showText(LANG_PHOTOS_BY_UNSPLASH);
    $langLoadMore = system_showText(LANG_LOAD_MORE);
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
    <div class="section-unsplash">
        <div class="unsplash-header">
            <div class="form-group">
                <label><strong>$langSearchPhotos</strong> - $langPhotosByUnsplash</label>
                <input type="text" name="query" class="form-control input-unsplash" placeholder="$langSearchPhotos">
            </div>
        </div>
        <div class="unsplash-body">
            $photosHTML
        </div>
        <div class="unsplash-loadmore">
            <button type="button" class="btn btn-block btn-default btn-unsplash-more" data-page="1">$langLoadMore</button>
        </div>
    </div>
HTML;

}

$pageId = $sliders[$i]->getPage() ? $sliders[$i]->getPage()->getId() : '';
$imagePath = '';
if($sliders[$i]->getImage()){
    $imagePath = ($sliders[$i]->getImage()->getUnsplash() ?: $sliders[$i]->getImagePath());
}

if ($imagePath) {
    $image = <<<HTML
<div class="edit-hover unsplash-preview">
    <a href="#" class="sliderImageButton bgGenericImageButton" data-imageinput="$sliderId" title="{$sliders[$i]->getTitle()}" id="imgSlider$sliderId" style="background-image: url({$imagePath})">
    </a>
</div>
{$photosHTML}
HTML;
} else {
    $image = <<<HTML
<div class="new">
    <a class="thumbnail add-new sliderImageButton bgGenericImageButton" data-imageinput="$sliderId" id="imgSlider$sliderId" href="#" tabindex="">
        <div class="caption">
            <h6><i class="fa fa-plus-circle" aria-hidden="true"></i > {$translator->trans('Upload Image', [],
        'widgets', /** @Ignore */
        $sitemgrLanguage)}</h6 >
        </div >
    </a>
</div>
{$photosHTML}
HTML;
}

$sliderInfoHtml .= <<<HTML
<div id="sliderInfo$sliderId" class="row sliderInfo" style="display: none">
<form method="post" name="form_sliderInfo$sliderId" id="form_sliderInfo$sliderId">
    <input type="hidden" name="slideId" value="$sliderId">
    <input type="hidden" name="imageId" id="imageId$sliderId" value="{$sliders[$i]->getImageId()}">
    <div class="col-md-6 ">
        <div class="form-group">
            <label for="idx_title_$sliderId" class="control-label">{$translator->trans('Title', [], 'widgets',
    /** @Ignore */
    $sitemgrLanguage)}</label>
            <input type="text" class="form-control" id="idx_title_$sliderId" name="title" placeholder="" value="{$sliders[$i]->getTitle()}" maxlength="60">
        </div>
        <div class="form-group">
            <label for="idx_summary_$sliderId" class="control-label">{$translator->trans('Summary Description', [],
    'widgets', /** @Ignore */
    $sitemgrLanguage)}</label>
            <textarea class="form-control" id="idx_summary_$sliderId" name="summary" rows="5" placeholder="" maxlength="200">{$sliders[$i]->getSummary()}</textarea>
        </div>
        <div class="form-group selectize">
            <label for="navLink_$sliderId"
                   class="control-label">{$translator->trans('Link', [],
    'widgets', /** @Ignore */
    $sitemgrLanguage)}
                </label>
            <select class="form-control navLink" name="navLink" id="navLink$sliderId" data-modalaux="header" data-divid="$sliderId">
                <option style="display:none" value="">{$translator->trans('Select an Option', [],
    'widgets', /** @Ignore */
    $sitemgrLanguage)}</option>
                    <optgroup label="{$translator->trans('Main Pages', [],
    'widgets', /** @Ignore */
    $sitemgrLanguage)}">
HTML;

for ($j = 0, $jMax = count($array_main_pages); $j < $jMax; $j++) {
    $selected = '';
    if ($array_main_pages[$j]['page_id'] == $pageId) {
        $selected = 'selected = "selected"';
    }

    $mainPageName = ucwords($array_main_pages[$j]['name']);

    $sliderInfoHtml .= <<<HTML
                        <option value="{$array_main_pages[$j]['page_id']}" $selected>
                            $mainPageName
                        </option>
HTML;
}
$sliderInfoHtml .= <<<HTML
            </optgroup>
HTML;

if ($array_custom_pages) {
    $sliderInfoHtml .= <<<HTML
                    <optgroup label="{$translator->trans('Custom Pages', [],
        'widgets', /** @Ignore */
        $sitemgrLanguage)}">
HTML;
    foreach ($array_custom_pages as $j => $jValue) {
        $selected = '';
        if ($array_custom_pages[$j]['page_id'] == $pageId) {
            $selected = 'selected = "selected"';
        }

        $customPageName = $array_custom_pages[$j]['name'];

        $sliderInfoHtml .= <<<HTML
                            <option value="{$array_custom_pages[$j]['page_id']}" $selected>
                                $customPageName
                            </option>
HTML;
    }
    $sliderInfoHtml .= <<<HTML
            </optgroup>
HTML;
}
$selected = '';
$style = 'style="display: none;"';
if ($sliders[$i]->getLink()) {
    $selected = 'selected = "selected"';
    $style = 'style="display: block;"';
}

$customLinkName = ucwords($customLink['name']);

$inputGroup = string_strpos($sliders[$i]->getLink(), '://') ? '' : 'input-group';
$hidden = string_strpos($sliders[$i]->getLink(), '://') ? 'hidden' : '';
$checkedInternal = string_strpos($sliders[$i]->getLink(), '://') ? '' : 'checked';
$checkedExternal = string_strpos($sliders[$i]->getLink(), '://') ? 'checked' : '';
$linkValue = $sliders[$i]->getLink() ?: '';

$sliderInfoHtml .= <<<HTML
                <optgroup label="{$customLinkName}">
                    <option value="{$customLink['url']}" $selected>
                        $customLinkName
                    </option>
                </optgroup>
            </select>
        </div>
        <div id="sliderCustomLinkDiv$sliderId" class="form-group" $style>
            <label for="sliderCustomLink$sliderId"
                   class="control-label">{$translator->trans('Custom Link', [], 'widgets',
    /** @Ignore */
    $sitemgrLanguage)}
                :</label>
                <div class="$inputGroup" id="InputGroup$sliderId">
                    <span id="url$sliderId" class="input-group-addon $hidden">$baseUrl</span>
                    <input type="text" class="form-control sliderCustomLink" name="sliderCustomLink" id="sliderCustomLink$sliderId" value="$linkValue" data-divid="$sliderId"
                            placeholder="{$translator->trans('Custom Link', [], 'widgets',
    /** @Ignore */
    $sitemgrLanguage)}">
                </div>
            <div class="form-horizontal">
                <div class="radio-inline">
                    <label>
                        <input type="radio" name="sliderCustomLinkType" id="sliderCustomLinkType" value="internal" data-divid="$sliderId" $checkedInternal/>
                        {$translator->trans('Internal Page', [], 'widgets',
    /** @Ignore */
    $sitemgrLanguage)}
                        <input type="hidden" id="sliderInternalValue$sliderId" value=""/>
                    </label>
                </div>
                <div class="radio-inline">
                    <label>
                        <input type="radio" name="sliderCustomLinkType" id="sliderCustomLinkType" value="external" data-divid="$sliderId" $checkedExternal/>
                        {$translator->trans('External Link', [], 'widgets',
    /** @Ignore */
    $sitemgrLanguage)}
                        <input type="hidden" id="sliderExternalValue$sliderId" value=""/>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="checkbox">
                <label for="idx_window_$sliderId" class="control-label">
                    <input class="required" type="checkbox" value="1" name="openWindow" id="idx_window_$sliderId" $openWindow>
                    {$translator->trans('Open in a new window', [], 'widgets', /** @Ignore */
    $sitemgrLanguage)}
                </label>
            </div>
        </div>
    </div>
</form>
<div class="col-md-6">
    <div class="form-thumbnails">
        <div class="upload-logo">
            <form id="form_slider_$sliderId">
                <input type="file" class="hide" id="slideImage$sliderId" name="slideImage" data-slider="$sliderId" onChange="saveSlider(this);">
            </form>
            <div id="image-background$sliderId" class="img-background text-center">
                $image
            </div>
            <p>
                <small class="help-block">{$translator->trans('We recommend using an image that is %dimension% px to cover the entire site, and account for larger screen sizes. Please make sure images are at least 1200 pixels wide and keep roughly the same aspect ratio.',
    ['%dimension%' => IMAGE_THEME_BACKGROUND_W . ' x ' . IMAGE_THEME_BACKGROUND_H], 'widgets', /** @Ignore */
    $sitemgrLanguage)}</small>
            </p>
        </div>
    </div>
</div>
</div>

HTML;
