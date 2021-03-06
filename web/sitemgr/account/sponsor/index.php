<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/account/sponsor/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track("Accessed Sponsor Accounts section");

	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
	$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/account/sponsor/index.php";

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$_GET = format_magicQuotes($_GET);
	extract($_GET);
	$_POST = format_magicQuotes($_POST);
	extract($_POST);

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        //Delete account
        if ($action == "delete") {
            mixpanel_track("Deleted a Sponsor Account");
            $account = new Account($id);
            $account->delete();
            $message = 3;
            header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/account/sponsor/index.php?message=".$message."&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : "")."");
            exit;
        } else {
        //Forgot password
            mixpanel_track("Sent forgot password email to a Sponsor");
            $section = "members";
            include(INCLUDES_DIR."/code/forgot_password.php");
        }
	}

    //Search (Contact)
    $sql_where[] = "is_sponsor = 'y'";
    if ($search_username) {
        $search_term = explode(" ", $search_username);
        $auxWhere = array();
        foreach ($search_term as $term) {
            $auxWhere[] = "email LIKE ".db_formatString('%'.$term.'%');
            $auxWhere[] = "first_name LIKE ".db_formatString('%'.$term.'%');
            $auxWhere[] = "last_name LIKE ".db_formatString('%'.$term.'%');
            $auxWhere[] = "company LIKE ".db_formatString('%'.$term.'%');
        }

        $sql_where[] = "id IN (SELECT account_id FROM Contact WHERE (".implode($auxWhere, " OR ")."))";
    }

    $where_clause = implode(" AND ", $sql_where);

	$url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

	// Page Browsing ////////////////////////////////////////
	$pageObj = new pageBrowsing("Account", $screen, RESULTS_PER_PAGE, "lastlogin DESC, username", "username", $letter, $where_clause, "*", false, false, true);
	$accounts = $pageObj->retrievePage();

	$paging_url = DEFAULT_URL."/".SITEMGR_ALIAS."/account/sponsor/index.php";

	# PAGES DROP DOWN ----------------------------------------------------------------------------------------------
	$pagesDropDown = $pageObj->getPagesDropDown($_GET, $paging_url, $screen, system_showText(LANG_SITEMGR_PAGING_GOTOPAGE)." ", "this.form.submit();");
	# --------------------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/header.php");

    # ----------------------------------------------------------------------------------------------------
	# NAVBAR
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/navbar.php");

    # ----------------------------------------------------------------------------------------------------
	# SIDEBAR
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/sidebar-accounts.php");

?>

    <main class="wrapper-dashboard togglesidebar container-fluid" id="view-content-list">

        <?php
        require(SM_EDIRECTORY_ROOT."/registration.php");
        require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
        ?>

        <div class="content-control">
            <div class="row">
                <div class="col-md-4 col-sm-8 col-xs-6 control-search">
                    <div class="control-searchbar">
                        <form class="form-inline" name="account" action="<?=system_getFormAction($_SERVER["PHP_SELF"]);?>" role="search" method="get">
                            <div class="form-group">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search_username" value="<?=$search_username?>" class="form-control search" placeholder="<?=system_showText(LANG_SITEMGR_SEARCH_ACC);?>">
                                    <div class="input-group-btn">
                                        <!-- Button -->
                                        <button type="submit" class="btn btn-default">
                                            <span class="hidden-xs"><?=system_showText(LANG_SITEMGR_SEARCH);?></span>
                                            <i class="visible-xs icon-ion-ios7-search"></i>
                                        </button>

                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
                <div class="col-md-5 col-sm-4 col-xs-6 control-responsive">
                    <a class="btn btn-primary btn-responsive" title="<?=system_showText(LANG_SITEMGR_ADD_SPONSOR);?>" href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/account/sponsor/sponsor.php"?>"><i class="icon-cross8"></i></a>
                </div>

                <div class="col-md-3 col-sm-12 control-add">
                    <div class="control-bar">
                        <a class="btn btn-sm btn-primary" id="add-sponsor" href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/account/sponsor/sponsor.php"?>"><i class="icon-cross8"></i> <?=system_showText(LANG_SITEMGR_ADD_SPONSOR);?></a>
                    </div>
                </div>

            </div>
        </div>

        <div class="content-full">
            <? if ($accounts) { ?>
            <div class="list-content">
                <? include(INCLUDES_DIR."/lists/list-accounts.php"); ?>

                <div class="content-control-bottom pagination-responsive">
                    <?include(INCLUDES_DIR."/lists/list-pagination.php");?>
                </div>
            </div>
            <div class="view-content">
                <? include(SM_EDIRECTORY_ROOT."/account/view-account.php"); ?>
            </div>
            <? } else {
                include(SM_EDIRECTORY_ROOT."/layout/norecords.php");
            } ?>
        </div>

    </main>

    <?
    include(INCLUDES_DIR."/modals/modal-delete.php");
    include(INCLUDES_DIR."/modals/modal-forgot.php");

	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/manage-account.php";
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
