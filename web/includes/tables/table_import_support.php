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
	# * FILE: /includes/tables/table_import_support.php
	# ----------------------------------------------------------------------------------------------------

?>

<tr>
	<td>
		<?=$import["id"];?>
	</td>
	<td>
		<?=format_date($import["createdAt"])?>
	</td>
	<td>
		<fieldset title="<?=$import["filename"];?>">
			<?=system_showTruncatedText($import["filename"], 23);?>
		</fieldset>
	</td>
	<td>
        <?=$import["status"]?>
	</td>
    <td>
        <i style="cursor:pointer" class="icon-ion-ios7-help-outline" title="<?=import_getLogTip($import["status"]);?>"></i>
    </td>

</tr>
