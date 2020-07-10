<?php

$videoId = $r ? $r : $k;
$videoURL = isset($video['url']) ? $video['url'] : '';
$videoDescription = isset($video['description']) ? $video['description'] : '';

$sliderInfoHtml = '
<div id="sliderInfo'.$videoId.'" class="row sliderInfo" '.($videoURL ? 'style="display: none"' : '').'>
    <form method="post" name="form_sliderInfo'.$videoId.'" id="form_sliderInfo'.$videoId.'">
        <input type="hidden" name="slideId" value="'.$videoId.'">
        <div class="col-md-6 ">
            <div class="form-group">
                <label for="video'.$videoId.'" class="control-label">'.system_showText(LANG_LABEL_VIDEO_URL).'</label>
                <input type="url" name="video_url" id="video'.$videoId.'" value="'.$videoURL.'" class="form-control form-group" placeholder="'.system_showText(LANG_HOLDER_VIDEO).'" onchange="autoEmbed(\'video'.$videoId.'\');">
                <div id="video'.$videoId.'Msg" class="alert alert-warning alert-video-modal fade in hidden" role="alert">'.system_showText(LANG_VIDEO_NOTFOUND).'</div>
            </div>
            <div class="form-group">
                <label for="video_description'.$videoId.'" class="control-label">'.system_showText(LANG_LABEL_VIDEO_DESCRIPTION).'</label>
                <textarea name="video_description" id="video_description'.$videoId.'" class="textarea-counter form-control" data-chars="80" data-msg="'.system_showText(LANG_MSG_CHARS_LEFT).'" maxlength="80" placeholder="'.system_showText(LANG_HOLDER_VIDEOCAPTION).'">'.$videoDescription.'</textarea>
            </div>
    
        </div>
    </form>
    <div class="col-md-6">
        <div class="form-thumbnails">
            <div class="video-preview">
                <div class="embed-responsive embed-responsive-16by9" id="video'.$videoId.'_frame">
                    '.($videoURL ? system_getVideoiFrame($videoURL) : '').'
                </div>
            </div>
        </div>
    </div>
    </div>
';
