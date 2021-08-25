<?php

$videoId = $r ? $r : $k;

if (isset($video['url'])) {
    $image = '<img src='.system_getVideoiFrame($video['url'], 105, 70, true).' alt="Video Thumbnail"/>';
} else {
    $image = '&nbsp';
}

$sliderHtml .= <<<HTML
<li id="li$videoId" class="col5 col-sm-6 slideLi video-gallery-li">
    <input type="hidden" name="orderId" value="$videoId">
    <div class="row">
        <div class="col-md-2">
            <i class="fa fa-bars" aria-hidden="true"></i>
        </div>
            <div class="col-md-8 click-area" data-divid="$videoId">
                <a class="thumbnail add-new" href="#" tabindex="">
                    $image
                </a>
            </div>
        <div class="col-md-2">
            <a class="sortable-remove removeSlide" href="javascript:void(0)" data-slideid="$videoId" title="Remove" tabindex="">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</li>
HTML;
