<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/listing/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	//include("../../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	/*sess_validateSMSession();
	permission_hasSMPerm();

	mixpanel_track("Accessed Manage Listings section");

	$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER;
	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
	$sitemgr = 1;

	$url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));
    $manageOrder = system_getManageOrderBy($_POST["order_by"] ? $_POST["order_by"] : $_GET["order_by"], "Listing", $fields);

	extract($_GET);
	extract($_POST);

    $manageModule = "referedby";*/
    //$manageModuleFolder = LISTING_FEATURE_FOLDER;

    # ----------------------------------------------------------------------------------------------------
	# MANAGE MOBULDE BACKEND - SEARCH / BULK UPDATE / DELETE
	# ----------------------------------------------------------------------------------------------------
    //include(INCLUDES_DIR."/code/admin-manage-module.php");

	// Page Browsing /////////////////////////////////////////
	/*unset($pageObj);
	$pageObj = new pageBrowsing("ReferedBy", $screen, RESULTS_PER_PAGE, ($_GET["newest"] ? "id DESC" : $manageOrder), "title", $letter, $where, $fields);
	$listings = $pageObj->retrievePage();
	$paging_url = DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/index.php";*/

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	//include(SM_EDIRECTORY_ROOT."/layout/header.php");

    # ----------------------------------------------------------------------------------------------------
	# NAVBAR
	# ----------------------------------------------------------------------------------------------------
	//include(SM_EDIRECTORY_ROOT."/layout/navbar.php");

    # ----------------------------------------------------------------------------------------------------
	# SIDEBAR
	# ----------------------------------------------------------------------------------------------------
	//include(SM_EDIRECTORY_ROOT."/layout/sidebar-content.php");

?>
    <?php
        require('db.php');
        include ('header.php');
        ?>

        <?php include ('sidebar.php'); ?>
            <main class="wrapper togglesidebar container-fluid" id="view-content-list">
                <div class="form">
                    
                    <div class="control-bar">
                        <a class="btn btn-sm btn-primary" id="add-categories"   href="add.php" tabindex="44"><i class="icon-cross8"></i> Insert New Record</a>
                    </div>
                    
                    
                    
                    <h2>All Refered By</h2>
                    <table width="100%" border="1" style="border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th class="content-item"  style="text-align:center;"><span class="item-title">S.No</span></th>
                                <th class="content-item"  style="text-align:center;"><span class="item-title">Name</span></th>
                                <th class="content-item"  style="text-align:center;"><span class="item-title">Organization</span></th>
                                <th class="content-item"  style="text-align:center;"><span class="item-title">Link</span></th>
                                <th class="content-item"  style="text-align:center;"><span class="item-title">Edit</span></th>
                                <th class="content-item"  style="text-align:center;"><span class="item-title">Delete</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
        $count=1;
        $sel_query="Select * from ReferedBy ORDER BY id desc;";
        $result = mysqli_query($con,$sel_query);
        while($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td class="content-item" align="center">
                                        <?php echo $count; ?>
                                    </td>
                                    <td class="content-item" align="center">
                                        <?php echo $row["name"]; ?>
                                    </td>
                                     <td class="content-item" align="center">
                                        <?php echo $row["organization"]; ?>
                                    </td>
                                    <td class="content-item" align="center">
                                        <?php echo $row["link"]; ?>
                                    </td>
                                    <td class="content-item" align="center">
                                        <a href="edit.php?id=<?php echo $row["id"]; ?>">Edit</a>
                                    </td>
                                    <td class="content-item" align="center">
                                        <a href="delete.php?id=<?php echo $row["id"]; ?>">Delete</a>
                                    </td>
                                </tr>
                                <?php $count++; } ?>
                        </tbody>
                    </table>
                </div>
            </main>
            </body>

            </html>
            <!--  <main class="wrapper togglesidebar container-fluid" id="view-content-list">

        <?php
      //  require(SM_EDIRECTORY_ROOT."/registration.php");
      // require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
        ?>

        <?// Content Control is subscribed by bulk update using the Css classes SHOW and HIDDEN.?>
        <div class="content-control hidden" id="bulkupdate">
            <div class="row">
                <?
                //Bulk Update Include
      //          include(INCLUDES_DIR."/forms/form-bulkupdate.php");
                ?>
            </div>
        </div>

        <? //include(SM_EDIRECTORY_ROOT."/layout/submenu-content.php"); ?>

        <div class="content-full">
            <? /*if ($listings) { ?>
                <div class="list-content">
                    <? include(INCLUDES_DIR."/lists/list-module.php"); ?>

                    <div class="content-control-bottom pagination-responsive">
                        <? include(INCLUDES_DIR."/lists/list-pagination.php"); ?>
                    </div>
                </div>

                <div class="view-content">
                    <? include(SM_EDIRECTORY_ROOT."/content/view-module.php"); ?>
                </div>

            <? } else {
                include(SM_EDIRECTORY_ROOT."/layout/norecords.php");
            }*/ ?>
        </div>

    </main>-->

            <?
    /*include(INCLUDES_DIR."/modals/modal-delete.php");
    include(INCLUDES_DIR."/modals/modal-settings.php");
    include(INCLUDES_DIR."/modals/modal-bulk.php");
    include(INCLUDES_DIR."/modals/modal-search-module.php");*/
    ?>

<style>
 .content-item, .content-item-noview {
    border: 1px solid #e6e6e6!important;
    
}
</style>

                <?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $modalSettingsPath = DEFAULT_URL."/".SITEMGR_ALIAS."/content/settings-module.php?manageModule=referedby";
	$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/general.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
