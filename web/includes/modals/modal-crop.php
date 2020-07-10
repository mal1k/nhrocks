<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/modals/modal-crop.php
	# ----------------------------------------------------------------------------------------------------

?>

    <div class="modal custom-members-modal fade" id="modal-crop" tabindex="-1" role="dialog" aria-labelledby="modal-crop" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><?=system_showText(LANG_LABEL_IMAGE_CROP)?></h4>
                    <button type="button" class="button button-sm is-secondary" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?=system_showText(LANG_CLOSE);?></span></button>
                </div>
                <?php if(string_strpos($_SERVER['REQUEST_URI'], SITEMGR_ALIAS) !== false){ ?>
                <div class="modal-body">
                    <?php
                        $imageUploader->buildCrop();
                    ?>
                </div>
                <?php } else { ?>
                    <?php $imageUploader->buildCrop(); ?>
                <?php } ?>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
