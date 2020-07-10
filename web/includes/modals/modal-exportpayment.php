<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/modals/modal-exportpayment.php
	# ----------------------------------------------------------------------------------------------------
?>

    <div class="modal fade" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="modal-payment" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title"><?=system_showText(LANG_SITEMGR_MENU_EXPORTPAYMENTRECORDS)?></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <form name="export_payment" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                            <div class="col-sm-6 col-sm-offset-3">
                                <input type="hidden" name="export_payment" value="true">
                                <? include(INCLUDES_DIR."/forms/form-export-payment.php"); ?>
                            </div>
                            <div class="col-sm-6 col-sm-offset-3 text-center">
                                <button type="submit" name="btn_export_payment" value="Submit" class="btn btn-primary"><?=system_showText(LANG_SITEMGR_SUBMIT)?></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
