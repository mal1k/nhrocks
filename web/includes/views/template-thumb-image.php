<?
/*
* # Unsplash Modal for eDirectory
* @copyright Copyright 2019 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/views/template-thumb-image.php
# ----------------------------------------------------------------------------------------------------

?>

<script type="text/template" id="template-photo">
    <div class="unsplash-item" id="%id%">
        <a href="javascript:;" class="unsplash-picture" data-download="%download_location%" data-regular="%regular%">
            <img src="%thumb%" alt="%description%">
        </a>
        <a href="%photographer_link%" target="_blank" class="unsplash-author">%photographer%</a>
    </div>
</script>
