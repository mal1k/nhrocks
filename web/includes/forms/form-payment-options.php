<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/forms/form-payment-options.php
	# ----------------------------------------------------------------------------------------------------
?>


<div class="col-sm-7">
    <?php
        /* This will print the error and success message box only
         * if what was proccessed by POST was  related to this section */
        if( checkActiveTab( "currencyOptions", true ) )
        {
            unset( $_SESSION['PaymentOptions'] );
            MessageHandler::render();
        }
    ?>

	<div class="panel panel-default">
		<div class="panel-heading"><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_CURRENCY_HEADER)?></div>
		<div class="panel-body">
			<div class="row">
				<div class="form-group col-sm-4">
					<label for='currencySymbolInput'><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_CURRENCY_SYMBOL)?></label>
					<input id='currencySymbolInput' class="form-control" type="text" name="payment_currency_symbol" value="<?=$currency_symbol?>">
				</div>
                <?php if (PAYMENTSYSTEM_FEATURE === 'on') { ?>
                    <div class="form-group col-sm-4">
                        <label for='paymentCurrencyInput'><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_CURRENCY_COIN)?></label>
                        <input id='paymentCurrencyInput' class="form-control" type="text" name="payment_currency_code" value="<?=$payment_currency?>" maxlength="3">
                    </div>
                <?php } ?>
            </div>
		</div>
		<div class="panel-footer">
            <button class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>" type="submit" name="action" value="currencyOptions"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
		</div>
	</div>

    <?php if (PAYMENTSYSTEM_FEATURE === 'on') { ?>
        <div class="panel panel-default">
            <div class="panel-heading"><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_TAX_HEADER)?></div>
            <div class="panel-body">
                <div class="row">
                    <div class="form-group col-sm-4">
                        <div class="checkbox">
                            <label for='taxStatusInput'>
                                <br>
                                <input id='taxStatusInput' type="checkbox" name="payment_tax_status" <?= ($payment_tax_status == "on") ? "checked=\"checked\"" : "" ?>>
                                <?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_TAX_ENABLE)?>
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-sm-4">
                        <label for='taxLabelInput'><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_TAX_LABEL)?></label>
                        <input id='taxLabelInput' class="form-control" type="text" name="payment_tax_label" value="<?=$payment_tax_label?>">
                    </div>
                    <div class="form-group col-sm-4">
                        <label for='taxValueInput'>
                            <?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_TAX_VALUE)?>
                            <i class="form-tip icon-help10" data-toggle="tooltip" data-original-title="<?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_TAX_VALUE_TIP);?>"></i>
                        </label>
                        <input id='taxValueInput' class="form-control" type="number" step="any" name="payment_tax_value" value="<?=$payment_tax_value?>">
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>" type="submit" name="action" value="currencyOptions"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_MISC_HEADER)?></div>
            <div class="panel-body">
                <div class="row">
                    <div class="form-group col-sm-6">
                        <div class="checkbox">
                            <label for='invoicePaymentCheckbox'>
                                <input id='invoicePaymentCheckbox' type="checkbox" name="invoice_payment" <?= ($invoice_payment == "on") ? "checked=\"checked\"" : "" ?>>
                                <?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_MISC_INVOICE_ENABLE)?>
                                <i class="form-tip icon-help10" data-toggle="tooltip" data-original-title="<?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_MISC_INVOICE_TIP);?>"></i>
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <div class="checkbox">
                            <label for='manualPaymentCheckbox'>
                                <input id='manualPaymentCheckbox' type="checkbox" name="manual_payment" <?= ($manual_payment == "on") ? "checked=\"checked\"" : "" ?>>
                                <?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_MISC_MANUAL_ENABLE)?>
                                <i class="form-tip icon-help10" data-toggle="tooltip" data-original-title="<?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_MISC_MANUAL_TIP);?>"></i>
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label for="invoice_header"><?=system_showText(LANG_SITEMGR_INVOICE_HEADER)?></label>
                        <textarea class="form-control" name="invoice_header" id="invoice_header" rows="4"><?=$invoice_header?></textarea>
                        <p class="help-block small"><?=system_showText(LANG_SITEMGR_INVOICE_HEADER_TIP)?></p>
                    </div>
                    <div class="form-group col-sm-12">
                        <label for="invoice_footer"><?=system_showText(LANG_SITEMGR_INVOICE_FOOTER)?></label>
                        <textarea class="form-control" name="invoice_footer" id="invoice_footer" rows="4"><?=$invoice_footer?></textarea>
                        <p class="help-block small"><?=system_showText(LANG_SITEMGR_INVOICE_FOOTER_TIP)?></p>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>" type="submit" name="action" value="currencyOptions"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
            </div>
        </div>
    <?php } ?>
</div>
