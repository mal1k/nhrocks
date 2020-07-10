<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/forms/form-navigation.php
	# ----------------------------------------------------------------------------------------------------

?>

    <input type="hidden" id="divId" value=""/>

    <div class="row">
        <div class="col-md-12">
            <ul id="sortableNav" class="list-sortable list-lg">
                <?= $navbar ?>
                <li class="ui-sortable-handle ui-sortable-add" id="addNavBarItem">
                    <a class="sortable-add createItem" data-modalaux="header" href="javascript:void(0)">
                        <i class="fa fa-plus-circle" aria-hidden="true"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
