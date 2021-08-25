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
	# * FILE: /includes/views/view_invoice.php
	# ----------------------------------------------------------------------------------------------------

	header("Content-Type: text/html; charset=".EDIR_CHARSET, TRUE);

	setting_get("contact_company", $invoice_company);
	setting_get("contact_address", $invoice_address);
	setting_get("contact_city", $invoice_city);
	setting_get("contact_state", $invoice_state);
	setting_get("contact_country", $invoice_country);
	setting_get("contact_zipcode", $invoice_zipcode);
	setting_get("contact_phone", $invoice_phone);
	setting_get("contact_email", $invoice_email);
	setting_get("payment_tax_status", $payment_tax_status);
	setting_get("payment_tax_value", $payment_tax_value);
	setting_get("payment_tax_label", $payment_tax_label);
	setting_get("invoice_header", $invoice_header);
	setting_get("invoice_footer", $invoice_footer);
	?>

<html>

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=EDIR_CHARSET;?>">

        <style>
            .invoice-body
            { text-align: center; }

            /**
            * Invoice Structure
            *
            * @section		invoice
            * @subsection	structure
            */

            .base-invoice
            { background: #FFF; border: 1px solid #CCC; color: #333; font: 11px/15px Arial, Helvetica, sans-serif; margin: 0 auto; padding: 5px; }

            .base-invoice td
            { padding: 10px; }

            .base-invoice p, .base-invoice table
            { font: 11px/15px Arial, Helvetica, sans-serif; margin: 0; padding: 0; }

            .base-invoice p, .base-invoice table img {
                max-width: 180px;
                width: auto !important;
                height:auto !important;
                max-height: 100%;
            }
            .base-invoice h1, .base-invoice table h1
            { color: #333; font: bold 15pt/15px Arial, Helvetica, sans-serif; text-align: right; }

            .base-invoice div
            { font-weight: bold; }

            .base-invoice th
            { text-align: left; }

            .base-invoice h2
            { font-size: 10pt; text-align: center; }

            /**
            * Invoice Bill
            *
            * @section		invoice
            * @subsection	bill
            */

            .invoice-bill
            { background-color: #F7F7F7; border: 1px solid #EEE; padding: 5px; width: auto; }

            .invoice-bill p
            { font-weight: normal; margin: 0; }

            /**
            * Invoice Content
            *
            * @section		invoice
            * @subsection	content
            */

            .invoice-content
            { background-color: #F7F7F7; font: 11px/15px Arial, Helvetica, sans-serif; width: 100%; }

            .invoice-content th
            { background-color: #FFF; border-bottom: 1px solid #CCC; color: #333; padding: 5px; text-align: left; }

            .invoice-content td.invoice-detailbelow
            { background: #FFF; border-top: none; border-bottom: none; padding: 0; padding-top: 5px; }

            .invoice-content td span.itemNote
            { display: block; color: #999; padding-left: 45px; }

            .invoice-content th.invoice-total
            { background-color: #F2F2F2; padding: 0; padding-left: 10px; }

            .invoice-content th.invoice-total span
            { font-weight: normal; }

            .invoice-content td
            { border: none; border-bottom: 1px solid #CCC; }
        </style>

	</head>

	<body class="invoice-body">

		<table border="0" cellpadding="2" cellspacing="2" class="base-invoice" id="table_invoice">
			<tr>
				<td colspan="2" style="padding: 0;">

					<table width="100%" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td>
                                <?php if ($invoice_header) {
                                    echo nl2br($invoice_header);
                                } else { ?>
								<p><?=$invoice_company?></p>
								<p><?=$invoice_address?></p>
								<p><?=$invoice_city?>, <?=$invoice_state?> <?=$invoice_zipcode?></p>
								<p><?=$invoice_country?></p>
								<p><?=$invoice_phone?></p>
								<p><?=$invoice_email?></p>
                                <?php } ?>
							</td>
                            <? if (image_LogoUploaded()) { ?>
							<td width="250" align="center">
                                <img src="<?=image_getLogoImage()?>" alt="<?=system_showText(LANG_LABEL_INVOICELOGO)?>" title="<?=system_showText(LANG_LABEL_INVOICELOGO)?>">
							</td>
                            <? } ?>
							<td width="150">
								<h1><?=string_strtoupper(system_showText(LANG_LABEL_INVOICE));?></h1>
							</td>
						</tr>
					</table>

				</td>
			</tr>
			<tr>
				<td colspan="2">

					<div class="invoice-bill" style=" float:left;">
						<p><b><?=system_showText(LANG_BILLTO);?>:</b></p>
						<p>
							<?=$contactObj->getString("first_name")?> <?=$contactObj->getString("last_name")?><br>
							<?= ( $contactObj->getString("company") ) ? $contactObj->getString("company")."<br>" : ""?>
							<?= ( $contactObj->getString("address") ) ? $contactObj->getString("address")."&nbsp;" : ""?>
							<?= ( $contactObj->getString("address2") ) ? $contactObj->getString("address2")."<br>" : ""?>
							<?= ( $contactObj->getString("city") ) ? $contactObj->getString("city").", " : ""?>
							<?= ( $contactObj->getString("state") ) ? $contactObj->getString("state")."&nbsp;" : ""?>
							<?= ( $contactObj->getString("zip") ) ? $contactObj->getString("zip") : ""?>
						</p>
						<? if ( $invoice_company ) { ?>
							<br><p><strong><?=system_showText(LANG_PAYABLETO);?>:</strong><br> <?=$invoice_company?>
						<? } ?>
					</div>

					<div class="invoice-bill" style=" float:right;">
						<p><b><?=system_showText(LANG_ISSUINGDATE);?>:</b> <?=format_date($invoiceObj->getString("date"),DEFAULT_DATE_FORMAT,"datetime")?></p>
						<p><b><?=system_showText(LANG_EXPIREDATE);?>:</b> <?=format_date($invoiceObj->getString("expire_date"),DEFAULT_DATE_FORMAT,"date")?></p>
						<p><b><?=system_showText(LANG_LABEL_INVOICENUMBER);?>:</b> <?=$invoiceObj->getString("id")?></p>
					</div>

				</td>
			</tr>

			<tr>
				<td colspan="2">

					<table border="0" cellspacing="0" cellpadding="0" class="invoice-content">
						<tr>
							<th width="300">
								<?=system_showText(LANG_LABEL_ITEM);?>
							</th>
							<? if (count($arr_invoice_listing)) { ?>
								<th nowrap="nowrap">
									<?=system_showText(LANG_LABEL_EXTRA_CATEGORY);?>
								</th>
							<? } ?>
							<th width="80" nowrap="nowrap">
								<?=system_showText(LANG_LABEL_LEVEL);?>
							</th>
							<th nowrap="nowrap">
								<?=system_showText(LANG_LABEL_DISCOUNT_CODE);?>
							</th>
							<th nowrap="nowrap">
								<?=system_showText(LANG_LABEL_AMOUNT);?>
							</th>
						</tr>
						<? if ($item_example===true) {?>
							<tr>
								<td><?=ucfirst(system_showText(LANG_SITEMGR_LABEL_ITEM))?> - <?=ucfirst(system_showText(LANG_SITEMGR_MSGERROR_LANG_TITLEISREQUIRED1))?></td>
								<? if (count($arr_invoice_listing)) { ?>
									<td>0</td>
								<? } ?>
								<td>0</td>
								<td nowrap="nowrap">abcde</td>
								<td nowrap="nowrap" style="vertical-align: top;">FREE</td>
							</tr>
						<? } ?>
						<?
						for($i=0; $i < count($arr_invoice_listing); $i++){
							//get the listing level name

							$level_name = ucfirst($arr_invoice_listing[$i]["level_label"]);
							?>
							<tr>
								<td><b><?=system_showText(LANG_LISTING_FEATURE_NAME)?>:</b> <?=$arr_invoice_listing[$i]["listing_title"]?><?=($arr_invoice_listing[$i]["listingtemplate"]?"<span class=\"itemNote\">(".$arr_invoice_listing[$i]["listingtemplate"].")</span>":"");?></td>
								<td><?=intval($arr_invoice_listing[$i]["extra_categories"])?></td>
								<td><?=$level_name?></td>
								<td><? if (trim($arr_invoice_listing[$i]["discount_id"]) != "") echo $arr_invoice_listing[$i]["discount_id"]; else echo " ".system_showText(LANG_NA)." "; ?></td>
								<td nowrap="nowrap">
									<?=PAYMENT_CURRENCY_SYMBOL." ".format_money($arr_invoice_listing[$i]["amount"]);?>
								</td>
							</tr>
						<? } ?>
						<?
						for($i=0; $i < count($arr_invoice_event); $i++){
							//get the event level name
							$eventLevel = new EventLevel(true);
							$level_name = ucfirst($arr_invoice_event[$i]["level_label"]);
							?>
							<tr>
								<td <? if (count($arr_invoice_listing)) { echo "colspan=\"2\""; } ?>><b><?=system_showText(LANG_EVENT_FEATURE_NAME)?>:</b> <?=$arr_invoice_event[$i]["event_title"]?></td>
								<td><?=$level_name?></td>
								<td><? if (trim($arr_invoice_event[$i]["discount_id"]) != "") echo $arr_invoice_event[$i]["discount_id"]; else echo " ".system_showText(LANG_NA)." "; ?></td>
								<td nowrap="nowrap">
									<?=PAYMENT_CURRENCY_SYMBOL." ".format_money($arr_invoice_event[$i]["amount"]);?>
								</td>
							</tr>
						<? } ?>
						<?
						for($i=0; $i < count($arr_invoice_banner); $i++){
							//get the banner level name
							$bannerLevel = new BannerLevel(true);
							$level_name   = ucfirst($arr_invoice_banner[$i]["level_label"]);
							?>
							<tr>
								<td <? if (count($arr_invoice_listing)) { echo "colspan=\"2\""; } ?>><b><?=system_showText(LANG_BANNER_FEATURE_NAME)?>:</b> <?=$arr_invoice_banner[$i]["banner_caption"]?> </td>
								<td><?=$level_name?></td>
								<td><? if (trim($arr_invoice_banner[$i]["discount_id"]) != "") echo $arr_invoice_banner[$i]["discount_id"]; else echo " ".system_showText(LANG_NA)." "; ?></td>
								<td nowrap="nowrap">
									<?=PAYMENT_CURRENCY_SYMBOL." ".format_money($arr_invoice_banner[$i]["amount"]);?>
								</td>
							</tr>
						<? } ?>
						<?
						for($i=0; $i < count($arr_invoice_classified); $i++) {
							$classifiedLevel = new ClassifiedLevel(true);
							$level_name = ucfirst($arr_invoice_classified[$i]["level_label"]);
							?>
							<tr>
								<td <? if (count($arr_invoice_listing)) { echo "colspan=\"2\""; } ?>><b><?=system_showText(LANG_CLASSIFIED_FEATURE_NAME)?>:</b> <?=$arr_invoice_classified[$i]["classified_title"]?></td>
								<td><?=$level_name?></td>
								<td><? if (trim($arr_invoice_classified[$i]["discount_id"]) != "") echo $arr_invoice_classified[$i]["discount_id"]; else echo " ".system_showText(LANG_NA)." "; ?></td>
								<td nowrap="nowrap">
									<?=PAYMENT_CURRENCY_SYMBOL." ".format_money($arr_invoice_classified[$i]["amount"]);?>
								</td>
							</tr>
						<? } ?>
						<?
						for($i=0; $i < count($arr_invoice_article); $i++){
							$articleLevel = new ArticleLevel(true);
							$level_name = ucfirst($articleLevel->getName($arr_invoice_article[$i]["level"]));
							?>
							<tr>
								<td <? if (count($arr_invoice_listing)) { echo "colspan=\"2\""; } ?>><b><?=system_showText(LANG_ARTICLE_FEATURE_NAME)?>:</b> <?=$arr_invoice_article[$i]["article_title"]?></td>
								<td><?=$level_name?></td>
								<td><? if (trim($arr_invoice_article[$i]["discount_id"]) != "") echo $arr_invoice_article[$i]["discount_id"]; else echo " ".system_showText(LANG_NA)." "; ?></td>
								<td nowrap="nowrap">
									<?=PAYMENT_CURRENCY_SYMBOL." ".format_money($arr_invoice_article[$i]["amount"]);?>
								</td>
							</tr>
						<? } ?>
						<? for($i=0; $i < count($arr_invoice_custominvoice); $i++){?>
							<?
							$customInvoiceObj = new CustomInvoice($arr_invoice_custominvoice[$i]["custom_invoice_id"]);

							$custom_invoice_items = $arr_invoice_custominvoice[$i]["items"];
							$custom_invoice_items = explode("\n", $custom_invoice_items);

							$custom_invoice_items_price = $arr_invoice_custominvoice[$i]["items_price"];
							$custom_invoice_items_price = explode("\n", $custom_invoice_items_price);

							if ($custom_invoice_items && $custom_invoice_items_price) {
								foreach ($custom_invoice_items as $key => $each_item) {
									$custom_invoice_items_desc[] = $each_item." - ".$custom_invoice_items_price[$key];
								}
							}
							?>
							<tr>
								<td <? if (count($arr_invoice_listing)) { echo "colspan=\"4\""; } else { echo "colspan=\"3\""; } ?>><?=$arr_invoice_custominvoice[$i]["title"].":<br>"?><?=($custom_invoice_items_desc) ? implode("<br>", $custom_invoice_items_desc) : ""?></td>
								<td nowrap="nowrap" style="vertical-align: top;">
									<?=PAYMENT_CURRENCY_SYMBOL." ".format_money($arr_invoice_custominvoice[$i]["subtotal"]);?>
								</td>
							</tr>
							<?
							unset($customInvoiceObj, $custom_invoice_items_desc, $custom_invoice_items_price);
							?>
						<? } ?>

							<? for($i=0; $i < count($arr_invoice_package); $i++){?>
							<?
							$packageObj = new Package($arr_invoice_package[$i]["package_id"]);

							$package_items = $arr_invoice_package[$i]["items"];
							$package_items = explode("\n", $package_items);

							$package_items_price = $arr_invoice_package[$i]["items_price"];
							$package_items_price = explode("\n", $package_items_price);

							if ($package_items && $package_items_price) {
								foreach ($package_items as $key => $each_item) {
									$package_items_desc[] = $each_item;
								}
							}
							?>
							<tr>
								<td <? if (count($arr_invoice_listing)) { echo "colspan=\"4\""; } else { echo "colspan=\"3\""; } ?>><?=$arr_invoice_package[$i]["package_title"].":<br>"?><?=($package_items_desc) ? implode("<br>", $package_items_desc) : ""?></td>
								<td nowrap="nowrap" style="vertical-align: top;">
									<?=PAYMENT_CURRENCY_SYMBOL." ".format_money($arr_invoice_package[$i]["subtotal"]);?>
								</td>
							</tr>
							<?
							unset($packageObj, $package_items_desc, $package_items_price);
							?>
						<? } ?>


						<tr>
							<td <? if (count($arr_invoice_listing)) { echo "colspan=\"3\""; } else { echo "colspan=\"2\""; } ?> class="invoice-detailbelow">

                                <?php if ($invoice_footer) {
                                    echo nl2br($invoice_footer);
                                } else {
									if ($invoice_company) {
										echo system_showText(LANG_LABEL_MAKE_CHECKS_PAYABLE)." ".$invoice_company;
									}
								?>
								<br>
								<b><?=system_showText(LANG_QUESTIONS);?>:</b> <?=system_showText(LANG_PLEASECALL);?> <?=$invoice_phone?>
								<br>
								<strong style="font-size: 8pt;"><?=system_showText(LANG_MSG_THANK_YOU);?></strong>
                                <?php } ?>
							</td>
							<th class="invoice-total">
								<? if ($payment_tax_status == "on" || $invoiceObj->getString("tax_amount") > 0) { ?>
									<?=system_showText(LANG_LABEL_SUBTOTAL);?>: <br>
									<?=$payment_tax_label."(".$invoiceObj->getString("tax_amount")."%)";?>: <br>
								<? } ?>
								<?=system_showText(LANG_LABEL_TOTAL);?>:
							</th>
                            <th class="invoice-total">
								<? if ($payment_tax_status == "on") {?>
									<span><?=PAYMENT_CURRENCY_SYMBOL?> <?=format_money($invoiceObj->getString("subtotal_amount"));?></span><br>
									<span><?=PAYMENT_CURRENCY_SYMBOL?> <?=payment_calculateTax($invoiceObj->getString("subtotal_amount"), $invoiceObj->getString("tax_amount"), true, false);?></span><br>
                                <? } ?>
								<span><?=PAYMENT_CURRENCY_SYMBOL?> <?=format_money($invoiceObj->getString("amount"))?></span>
                            </th>
						</tr>
					</table>

				</td>
			</tr>
			<tr>
				<td colspan="2" class="invoice-detailbelow" align="center">
				<?=$invoice_notes = str_replace("\n", "<br>", $invoice_notes);?>
				</td>
			</tr>
		</table>

		<? if (string_strpos($url_base, "/".MEMBERS_ALIAS."")) {?>
			<? if ($_GET["operation"] == "print") { ?>
				<script type="text/javascript">
					window.onload = window.setTimeout('print_action()',5000);
					function print_action() {
						print();
					}
				</script>
			<? } else { ?>
				<p id="print"><a href="javascript:void(0);" onClick="document.getElementById('print').style.display='none';window.print();document.getElementById('print').style.display='block'" style="color:#000000; font: bold 10pt Verdana, Arial, Helvetica, sans-serif;"><?=system_showText(LANG_MSG_CLICK_TO_PRINT_INVOICE);?></a></p>
			<? } ?>
		<? } ?>

	</body>

</html>
