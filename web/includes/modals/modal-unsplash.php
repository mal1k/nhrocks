<?
/*
* # Unsplash Modal for eDirectory
* @copyright Copyright 2019 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/modals/modal-unsplash.php
# ----------------------------------------------------------------------------------------------------

?>

<div class="modal custom-members-modal fade" id="modal-unsplash" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <?php
                    $classHeader = '';
                    if(string_strpos($_SERVER['REQUEST_URI'], SITEMGR_ALIAS) === false)
                        $class = 'button button-sm is-secondary';
                ?>
                <h4 class="modal-title"></h4>
                <button type="button" class="<?=$class;?> close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="section-unsplash">
                    <div class="unsplash-header">
                        <div class="form-group">
                            <label><strong><?= system_showText(LANG_SEARCH_PHOTOS) ?></strong> - <?= system_showText(LANG_PHOTOS_BY_UNSPLASH) ?></label>
                            <?php if(string_strpos($_SERVER['REQUEST_URI'], SITEMGR_ALIAS) === false){ ?>
                                <input type="text" name="query" class="input input-unsplash" placeholder="<?= system_showText(LANG_SEARCH_PHOTOS) ?>">
                            <?php } else { ?>
                                <input type="text" name="query" class="form-control input-unsplash" placeholder="<?= system_showText(LANG_SEARCH_PHOTOS) ?>">
                            <?php } ?>
                        </div>
                    </div>
                    <div class="unsplash-body">
                    </div>
                    <div class="unsplash-loadmore">
                        <?php
                        if(string_strpos($_SERVER['REQUEST_URI'], SITEMGR_ALIAS) !== false){
                            $classMore = 'btn btn-block btn-default';
                        } else {
                            $classMore = 'button button-md is-primary';
                        }
                        ?>
                        <button type="button" class="<?=$classMore?> btn-unsplash-more" full-width="true" data-page="1"><?= system_showText(LANG_LOAD_MORE) ?></button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <?php
                    if(string_strpos($_SERVER['REQUEST_URI'], SITEMGR_ALIAS) !== false){
                        $classFooter = ['btn btn-default', 'btn btn-primary btn-lg'];
                    } else {
                        $classFooter = ['button button-md is-secondary', 'button button-md is-primary'];
                    }
                ?>
                <button type="button" class="<?=$classFooter[0];?>" data-dismiss="modal"><?= system_showText(LANG_CANCEL) ?></button>
                <button type="button" class="<?=$classFooter[1];?> disabled btn-unsplash-save"><?= system_showText(LANG_MSG_SAVE_CHANGES); ?></button>
            </div>
        </div>
    </div>
</div>

<?php include INCLUDES_DIR.'/views/template-thumb-image.php'; ?>
