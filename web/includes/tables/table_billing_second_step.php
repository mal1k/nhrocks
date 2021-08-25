<?php

	/*==================================================================*\
	######################################################################
	#                                                                    #
	# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
	#                                                                    #
	# This file may not be redistributed in whole or part.               #
	# eDirectory is licensed on a per-domain basis.                      #
	#                                                                    #
	# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
	#                                                                    #
	# http://www.edirectory.com | http://www.edirectory.com/license.html #
	######################################################################
	\*==================================================================*/

	# ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/tables/table_billing_second_step.php
	# ----------------------------------------------------------------------------------------------------

	$max_item_sum = 20;
	$stop_payment = false;

	# ----------------------------------------------------------------------------------------------------
	# LISTINGS
	# ----------------------------------------------------------------------------------------------------
	if(count($bill_info['listings']) > $max_item_sum){

	?>
		<div class="form-edit-alert">
			<?=system_showText(str_replace('[MAX_ITEMS_MODULE]', $max_item_sum.' '.LISTING_FEATURE_NAME_PLURAL, LANG_MSG_PROCCESS_MAXITEMS));?> <br />
			<?=system_showText(LANG_MSG_PROCCESS_AGAIN);?>
		</div>
        <?php
		$arr_size = count($bill_info['listings']);
		for($i=0; $i < $arr_size; $i++){
			$dump = array_pop($bill_info['listings']);
		}

		$stop_payment = true;
	}
	?>

<?php if ($bill_info['listings']) { ?>
		<table class="table table-bordered table-striped">
			<tr>
				<th><?=system_showText(LANG_LISTING_NAME);?></th>
				<th width="100"><?=system_showText(LANG_LABEL_EXTRA_CATEGORY);?></th>
				<th width="100"><?=system_showText(LANG_LABEL_LEVEL);?></th>
                <?php if (PAYMENT_FEATURE == 'on') { ?>
                    <?php if ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on')) { ?>
						<th width="120"><?=system_showText(LANG_LABEL_DISCOUNT_CODE)?></th>
                    <?php } ?>
                <?php } ?>
				<th width="70"><?=system_showText(LANG_LABEL_RENEWAL);?></th>
				<th width="60"><?=system_showText(LANG_LABEL_PRICE_PLURAL);?></th>
			</tr>
            <?php foreach($bill_info['listings'] as $id => $info) { ?>
				<tr>
					<td><?=system_showTruncatedText($info['title'], 35);?><?=($info['listingtemplate']? '<span class="itemNote"> ('.$info['listingtemplate'].')</span>' : '');?></td>
					<td style="text-align:center"><?=$info['extra_category_amount']?></td>
					<td><?=string_ucwords($info['level'])?></td>
                    <?php if (PAYMENT_FEATURE == 'on') { ?>
                        <?php if ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on')) { ?>
							<td style="text-align:center"><?= $info['discount_id'] ? $info['discount_id'] : system_showText(LANG_NA)?></td>
                        <?php } ?>
                    <?php } ?>
					<td><?=format_date($info['renewal_date'])?></td>
					<td><?=PAYMENT_CURRENCY_SYMBOL.$info['total_fee'];?></td>
				</tr>
            <?php } ?>
		</table>
<?php } ?>

<?php
	# ----------------------------------------------------------------------------------------------------
	# EVENTS
	# ----------------------------------------------------------------------------------------------------
	if(count($bill_info['events']) > $max_item_sum){

	?> 
		<div class="form-edit-alert">
			<?=system_showText(str_replace('[MAX_ITEMS_MODULE]', $max_item_sum.' '.EVENT_FEATURE_NAME_PLURAL, LANG_MSG_PROCCESS_MAXITEMS));?> <br />
			<?=system_showText(LANG_MSG_PROCCESS_AGAIN);?>
		</div>
        <?php
		$arr_size = count($bill_info['events']);
		for($i=0; $i < $arr_size; $i++){
			$dump = array_pop($bill_info['events']);
		}

		$stop_payment = true;

	}
	?>

<?php if ($bill_info['events']) { ?>
		<table class="table table-bordered table-striped">
			<tr>
				<th><?=system_showText(LANG_EVENT_NAME);?></th>
				<th width="100"><?=system_showText(LANG_LABEL_LEVEL);?></th>
                <?php if (PAYMENT_FEATURE == 'on') { ?>
                    <?php if ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on')) { ?>
						<th width="120"><?=system_showText(LANG_LABEL_DISCOUNT_CODE)?></th>
                    <?php } ?>
                <?php } ?>
				<th width="70"><?=system_showText(LANG_LABEL_RENEWAL);?></th>
				<th width="60"><?=system_showText(LANG_LABEL_PRICE_PLURAL);?></th>
			</tr>
            <?php foreach($bill_info['events'] as $id => $info) { ?>
				<tr>
					<td><?=system_showTruncatedText($info['title'], 35);?></td>
					<td><?=string_ucwords($info['level'])?></td>
                    <?php if (PAYMENT_FEATURE == 'on') { ?>
                        <?php if ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on')) { ?>
							<td style="text-align:center"><?= $info['discount_id'] ? $info['discount_id'] : system_showText(LANG_NA)?></td>
                        <?php } ?>
                    <?php } ?>
					<td><?=format_date($info['renewal_date'])?></td>
					<td><?=PAYMENT_CURRENCY_SYMBOL.$info['total_fee'];?></td>
				</tr>
            <?php } ?>
		</table>
<?php } ?>

<?php
	# ----------------------------------------------------------------------------------------------------
	# BANNERS
	# ----------------------------------------------------------------------------------------------------
	if(count($bill_info['banners']) > $max_item_sum){
	?>
		<div class="form-edit-alert">
			<?=system_showText(str_replace('[MAX_ITEMS_MODULE]', $max_item_sum.' '.BANNER_FEATURE_NAME_PLURAL, LANG_MSG_PROCCESS_MAXITEMS));?> <br />
			<?=system_showText(LANG_MSG_PROCCESS_AGAIN);?>
		</div>
        <?php

		$arr_size = count($bill_info['banners']);
		for($i=0; $i < $arr_size; $i++){
			$dump = array_pop($bill_info['banners']);
		}

		$stop_payment = true;

	}
	?>

<?php if ($bill_info['banners']) { ?>
		<table class="table table-bordered table-striped">
			<tr>
				<th><?=system_showText(LANG_BANNER_NAME)?></th>
				<th width="100"><?=system_showText(LANG_LABEL_LEVEL);?></th>
                <?php if (PAYMENT_FEATURE == 'on') { ?>
                    <?php if ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on')) { ?>
						<th width="120"><?=system_showText(LANG_LABEL_DISCOUNT_CODE)?></th>
                    <?php } ?>
                <?php } ?>
				<th width="70"><?=system_showText(LANG_LABEL_RENEWAL);?></th>
				<th width="60"><?=system_showText(LANG_LABEL_PRICE_PLURAL);?></th>
			</tr>
            <?php foreach($bill_info['banners'] as $id => $info) { ?>
				<tr>
					<td><?=system_showTruncatedText($info['caption'], 35);?></td>
					<td><?=string_ucwords($info['level'])?></td>
                    <?php if (PAYMENT_FEATURE == 'on') { ?>
                        <?php if ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on')) { ?>
							<td style="text-align:center"><?= $info['discount_id'] ? $info['discount_id'] : system_showText(LANG_NA)?></td>
                        <?php } ?>
                    <?php } ?>
					<td><?=format_date($info['renewal_date'])?></td>
					<td><?=PAYMENT_CURRENCY_SYMBOL.$info['total_fee'];?></td>
				</tr>
            <?php } ?>
		</table>
<?php } ?>

<?php
	# ----------------------------------------------------------------------------------------------------
	# CLASSIFIEDS
	# ----------------------------------------------------------------------------------------------------
	if(count($bill_info['classifieds']) > $max_item_sum){

	?>
		<div class="form-edit-alert">
			<?=system_showText(str_replace('[MAX_ITEMS_MODULE]', $max_item_sum.' '.CLASSIFIED_FEATURE_NAME_PLURAL, LANG_MSG_PROCCESS_MAXITEMS));?> <br />
			<?=system_showText(LANG_MSG_PROCCESS_AGAIN);?>
		</div>
        <?php

		$arr_size = count($bill_info['classifieds']);
		for($i=0; $i < $arr_size; $i++){
			$dump = array_pop($bill_info['classifieds']);
		}

		$stop_payment = true;

	}
	?>

<?php if ($bill_info['classifieds']) { ?>
		<table class="table table-bordered table-striped">
			<tr>
				<th><?=system_showText(LANG_CLASSIFIED_NAME);?></th>
				<th width="100"><?=system_showText(LANG_LABEL_LEVEL);?></th>
                <?php if (PAYMENT_FEATURE == 'on') { ?>
                    <?php if ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on')) { ?>
						<th width="120"><?=system_showText(LANG_LABEL_DISCOUNT_CODE)?></th>
                    <?php } ?>
                <?php } ?>
				<th width="70"><?=system_showText(LANG_LABEL_RENEWAL);?></th>
				<th width="60"><?=system_showText(LANG_LABEL_PRICE_PLURAL);?></th>
			</tr>
            <?php foreach($bill_info['classifieds'] as $id => $info) { ?>
				<tr>
					<td><?=system_showTruncatedText($info['title'], 35);?></td>
					<td><?=string_ucwords($info['level'])?></td>
                    <?php if (PAYMENT_FEATURE == 'on') { ?>
                        <?php if ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on')) { ?>
							<td style="text-align:center"><?= $info['discount_id'] ? $info['discount_id'] : system_showText(LANG_NA)?></td>
                        <?php } ?>
                    <?php } ?>
					<td><?=format_date($info['renewal_date'])?></td>
					<td><?=PAYMENT_CURRENCY_SYMBOL.$info['total_fee'];?></td>
				</tr>
            <?php } ?>
		</table>
<?php } ?>

<?php
	# ----------------------------------------------------------------------------------------------------
	# ARTICLES
	# ----------------------------------------------------------------------------------------------------
	if(count($bill_info['articles']) > $max_item_sum){

	?>
		<div class="form-edit-alert">
			<?=system_showText(str_replace('[MAX_ITEMS_MODULE]', $max_item_sum.' '.ARTICLE_FEATURE_NAME_PLURAL, LANG_MSG_PROCCESS_MAXITEMS));?> <br />
			<?=system_showText(LANG_MSG_PROCCESS_AGAIN);?>
		</div>
        <?php

		$arr_size = count($bill_info['articles']);
		for($i=0; $i < $arr_size; $i++){
			$dump = array_pop($bill_info['articles']);
		}

		$stop_payment = true;

	}
	?>

<?php if ($bill_info['articles']) { ?>
		<table class="table table-bordered table-striped">
			<tr>
				<th><?=system_showText(LANG_ARTICLE_NAME)?></th>
                <?php if (PAYMENT_FEATURE == 'on') { ?>
                    <?php if ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on')) { ?>
						<th width="120"><?=system_showText(LANG_LABEL_DISCOUNT_CODE)?></th>
                    <?php } ?>
                <?php } ?>
				<th width="70"><?=system_showText(LANG_LABEL_RENEWAL);?></th>
				<th width="60"><?=system_showText(LANG_LABEL_PRICE_PLURAL);?></th>
			</tr>
            <?php foreach($bill_info['articles'] as $id => $info) { ?>
				<tr>
					<td><?=system_showTruncatedText($info['title'], 35);?></td>
                    <?php if (PAYMENT_FEATURE == 'on') { ?>
                        <?php if ((CREDITCARDPAYMENT_FEATURE == 'on') || (PAYMENT_INVOICE_STATUS == 'on')) { ?>
							<td style="text-align:center"><?= $info['discount_id'] ? $info['discount_id'] : system_showText(LANG_NA)?></td>
                        <?php } ?>
                    <?php } ?>
					<td><?=format_date($info['renewal_date'])?></td>
					<td><?=PAYMENT_CURRENCY_SYMBOL.$info['total_fee'];?></td>
				</tr>
            <?php } ?>
		</table>
<?php } ?>

<?php
	# ----------------------------------------------------------------------------------------------------
	# CUSTOM INVOICES
	# ----------------------------------------------------------------------------------------------------
	if(count($bill_info['custominvoices']) > $max_item_sum){

	?>
		<div class="form-edit-alert">
			<?=system_showText(str_replace('[MAX_ITEMS_MODULE]', $max_item_sum.' '.LANG_CUSTOM_INVOICES, LANG_MSG_PROCCESS_MAXITEMS));?> <br />
			<?=system_showText(LANG_MSG_PROCCESS_AGAIN);?>
		</div>
        <?php

		$arr_size = count($bill_info['custominvoices']);
		for($i=0; $i < $arr_size; $i++){
			$dump = array_pop($bill_info['custominvoices']);
		}

		$stop_payment = true;

	}
	?>

<?php if ($bill_info['custominvoices']) { ?>
		<table class="table table-bordered table-striped">
			<tr>
				<th><?=system_showText(LANG_LABEL_TITLE)?></th>
				<th width="120"><?=system_showText(LANG_LABEL_ITEMS)?></th>
				<th width="70"><?=system_showText(LANG_LABEL_DATE)?></th>
				<th width="60"><?=system_showText(LANG_LABEL_AMOUNT)?></th>
			</tr>
            <?php foreach($bill_info['custominvoices'] as $id => $info) { ?>
				<tr>
					<td><?=system_showTruncatedText($info['title'], 35);?></td>
					<td><a data-toggle="modal" data-target="#modal-custominvoice-<?=$info['id']?>" class="link-table" style="cursor: pointer;"><?=ucfirst(system_showText(LANG_VIEWITEMS))?></a></td>
					<td><?=format_date($info['date'])?></td>
					<td><?=PAYMENT_CURRENCY_SYMBOL.$info['subtotal'];?></td>
				</tr>
            <?php } ?>
		</table>
    <?php
		foreach($bill_info['custominvoices'] as $id => $info) {
			include INCLUDES_DIR.'/modals/modal-custominvoice.php';
		}

	} ?>

<?php if (!$stop_payment) { ?>

    <?php
		# ----------------------------------------------------------------------------------------------------
		# TOTAL BILL
		# ----------------------------------------------------------------------------------------------------
		if($bill_info['total_bill']){
			?>
			<table class="table table-bordered">
                <?php if ($payment_tax_status || $bill_info['tax_amount'] > 0) { ?>
					<tr>
						<th class="text-right"><?=system_showText(LANG_SUBTOTALAMOUNT);?> &nbsp;</th>
						<td class="text-right">
							<strong><?=PAYMENT_CURRENCY_SYMBOL.$bill_info['total_bill'];?></strong>
						</td>
					</tr>
					<tr>
						<th class="text-right"><?=$payment_tax_label.' ('.$bill_info['tax_amount'].'%)';?> &nbsp;</th>
						<td class="text-right">
							<strong><?=PAYMENT_CURRENCY_SYMBOL.payment_calculateTax($bill_info['total_bill'], $bill_info['tax_amount'], true, false);?></strong>
						</td>
					</tr>
                <?php } ?>
				<tr>
					<th class="text-right"><?=system_showText(LANG_LABEL_TOTAL_PRICE);?> &nbsp;</th>
					<td class="active text-right">
						<strong><?=PAYMENT_CURRENCY_SYMBOL.format_money($bill_info['amount']);?></strong>
					</td>
				</tr>
			</table>
            <?php
		}
		?>

    <?php if (($payment_method == 'invoice') && (PAYMENT_INVOICE_STATUS == 'on')) { ?>
			<div class="payment-action">
				<button class="button button-md is-outline" type="button" onclick="javascript:history.back(-1);"><?=system_showText(LANG_LABEL_BACK);?></button>
				<a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/billing/invoice.php?id=<?=$bill_info['invoice_number']?>" target="_blank" class="button button-md is-primary"><?=system_showText(LANG_LABEL_PAY_BY_INVOICE);?></a>
			</div>
    <?php } else { ?>
			<br>
		<?php
			$payment_process = 'billing';
			include INCLUDES_DIR.'/forms/form_billing_'.$payment_method.'.php';
		}
    }
