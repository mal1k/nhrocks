<?
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
	# * FILE: /includes/form/form_faq.php
	# ----------------------------------------------------------------------------------------------------

?>
<div class="faq-page">
    <div class="faq-header" data-bg="base" has-gap>
        <div class="container wrapper">
            <div class="faq-info">
                <h2 class="heading h-2">
                    <?=system_showText(LANG_FAQ_HELP);?>
                </h2>
            </div>
            <form name="faq" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="get" class="faq-search">
                <input type="search" class="input" id="search" placeholder="<?=system_showText(LANG_FAQ_TIP);?>">
                <button type="submit" class="button button-bg is-primary"><?=system_showText(LANG_BUTTON_SEARCH);?></button>
			</form>
        </div>
    </div>
    <div class="faq-content">
		<?php
			if ($faqs){
				foreach ($faqs as $faq){
		?>
		<div class="toggle-item" data-bg="neutral">
			<div class="toggle-header">
				<h3 class="heading h-3 toggle-title"><?=$faq["question"];?></h3>
				<button class="button button-toggle-title"><span class="fa fa-minus"></span></button>
			</div>
			<div class="toggle-content">
				<p class="paragraph"><?=trim(str_replace('"','',$faq["answer"]));?></p>
			</div>
		</div>
		<?php
				}
			} else {
		?>
		<div class="alert alert-warning"><?=system_showText(LANG_MSG_NO_RESULTS_FOUND);?></div>
		<?php } ?>
    </div>
</div>
