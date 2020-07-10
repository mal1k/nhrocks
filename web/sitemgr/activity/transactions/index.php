<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/activity/transactions/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (PAYMENT_FEATURE != "on") {
        header("Location:".DEFAULT_URL."/".SITEMGR_ALIAS."");
        exit;
    }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track("Accessed Transactions section");

    if ($_SERVER['REQUEST_METHOD'] == "POST" && $_POST["export_payment"]) {
        if (DEFAULT_DATE_FORMAT == "m/d/Y") {
            list($start_month, $start_day, $start_year) = explode("/", $_POST["date_start"]);
            list($end_month, $end_day, $end_year) = explode("/", $_POST["date_end"]);
        } else {
            list($start_day, $start_month, $start_year) = explode("/", $_POST["date_start"]);
            list($end_day, $end_month, $end_year) = explode("/", $_POST["date_end"]);
        }

        if(
            !is_numeric($start_month) ||
            !is_numeric($start_day) ||
            !is_numeric($start_year)
        ) {
            $message_export_payment = system_showText(LANG_SITEMGR_MSGERROR_INVALID_STARTDATE)." ".system_showText(LANG_SITEMGR_MSGERROR_PLEASETRYAGAIN);
        } elseif(
            !is_numeric($end_month) ||
            !is_numeric($end_day) ||
            !is_numeric($end_year)
        ){

            $message_export_payment = system_showText(LANG_SITEMGR_MSGERROR_INVALID_ENDDATE)." ".system_showText(LANG_SITEMGR_MSGERROR_PLEASETRYAGAIN);

        } elseif (
            ( $start_year == $end_year && $start_month == $end_month && $start_day > $end_day ) ||
            ( $start_year == $end_year && $start_month > $end_month ) ||
            ( $start_year > $end_year )
        ) {
            $message_export_payment = system_showText(LANG_MSG_END_DATE_GREATER_THAN_START_DATE)." ".system_showText(LANG_SITEMGR_MSGERROR_PLEASETRYAGAIN);

        } elseif(!checkdate($start_month, $start_day, $start_year)) {

            $message_export_payment = system_showText(LANG_SITEMGR_MSGERROR_INVALID_STARTDATE)." ".system_showText(LANG_SITEMGR_MSGERROR_PLEASETRYAGAIN);

        } elseif(!checkdate($end_month, $end_day, $end_year)){

            $message_export_payment = system_showText(LANG_SITEMGR_MSGERROR_INVALID_ENDDATE)." ".system_showText(LANG_SITEMGR_MSGERROR_PLEASETRYAGAIN);

        } elseif ($_POST["type"] == "invoice" || $_POST["type"] == "payment") {

            $start_date				= $start_year.$start_month.$start_day;
            $end_date				= $end_year.$end_month.$end_day;
            $csv_delimiter			= ($_POST["delimiter"] == "semicolon") ? ";" : ",";
            $foreign_tables			= array("Listing", "Event", "Article", "Banner", "Classified", "CustomInvoice");
            $foreign_fields			= array("listing_id","listing_title", "event_id","event_title", "article_id","article_title", "banner_id","banner_caption", "classified_id","classified_title", "custom_invoice_id");

            if($_POST["type"] == "payment"){
                $primary_table				= "Payment_Log";
                $primary_table_condition	= "WHERE '$start_date' <= DATE(transaction_datetime) AND DATE(transaction_datetime) <= '$end_date' AND hidden = 'n' ";
                $filename					= "payment_log.csv";
                $prefix_foreign_table		= "Payment_";
                $sufix_foreign_table		= "_Log";
                $foreign_key				= "payment_log_id";
                $date_field					= "transaction_datetime";
            }

            if($_POST["type"] == "invoice"){
                $primary_table				= "Invoice";
                $primary_table_condition	= "WHERE status != 'N' AND '$start_date' <= DATE(date) AND DATE(date) <= '$end_date'";
                $prefix_foreign_table		= "Invoice_";
                $sufix_foreign_table		= "";
                $filename					= "invoice_log.csv";
                $foreign_key				= "invoice_id";
            }

            if($_POST["account_id"]) $primary_table_condition .= " AND account_id=".$_POST["account_id"];

            $sql = "SELECT * FROM $primary_table $primary_table_condition";

            $dbMain = db_getDBObject(DEFAULT_DB, true);
            $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            $r = $db->query($sql);
            $total_records = mysqli_num_rows($r);
            $message_export_payment = "";
            if($total_records > PAYMENT_LIMIT) {
                $message_export_payment = system_showText(LANG_SITEMGR_EXPORT_MSGERROR_MAXIMUMRECORDS);
            } elseif( $total_records > 0 ) {
                $i=0;
                $max_label_len=0;
                // Retrieving records from Payment_Log
                while($row = mysqli_fetch_assoc($r)){

                    $y=0;
                    foreach($row as $key => $value){

                        if($i == 0 && $key!="return_fields") { $payment_label_arr[] = "\"".addslashes($key)."\""; }
                        if (string_substr($value,0,1)== '"') $value = " ".$value;
                        if (string_strpos($key, "date") !== false)
                            $payment_value_arr[$i][$y] = " ".format_date($value);
                        elseif($key!="return_fields")
                            $payment_value_arr[$i][$y] = " ".$value;

                        if($key == "transaction_subtotal")
                            $subtotal = $value;

                        if($key == "transaction_tax"){
                            $payment_value_arr[$i][$y] =  " ".payment_calculateTax($subtotal, $value, true, false);
                        }

                        if($key == "subtotal_amount")
                            $subtotal = $value;

                        if($key == "tax_amount")
                            $payment_value_arr[$i][$y] =  payment_calculateTax($subtotal, $value,true,false);

                        if($key == "id") $id_transaction = $value;
                        $y++;
                    }

                    if($i == 0) $payment_label_csv_content = implode($csv_delimiter, $payment_label_arr);
                    $payment_value_csv_content_aux = implode($csv_delimiter, $payment_value_arr[$i]);
                    $payment_value_csv_content_aux .= $csv_delimiter;
                    foreach ($foreign_tables as $table_log) {
                        $sql2 = "SELECT * FROM ".$prefix_foreign_table.$table_log.$sufix_foreign_table." WHERE $foreign_key = $id_transaction";
                        $dbMain = db_getDBObject(DEFAULT_DB, true);
                        $db2 = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
                        $r2 = $db2->query($sql2);
                        if($table_log == "Listing") $levelObj = new ListingLevel();
                        if($table_log == "Event") $levelObj = new EventLevel();
                        if($table_log == "Article") $levelObj = new ArticleLevel();
                        if($table_log == "Banner") $levelObj = new BannerLevel();
                        if($table_log == "Classified") {
                            $levelObj = new ClassifiedLevel();
                            $table_log = "Classified";
                        }

                        while($row2 = mysqli_fetch_assoc($r2)) {
                            unset($payment_item_value_arr);
                            $payment_item_value_arr = array();
                            foreach($row2 as $key2 => $value2){
                                $exclude_fields = array("items", "items_price", "level", $foreign_key, "subtotal", "tax");
                                if($value2 && !in_array($key2, $exclude_fields)) {
                                    switch ($key2) {
                                        case "title":
                                            $payment_item_value_arr[] = "Title: ".$value2;
                                        case "discount_id":
                                            $payment_item_value_arr[] = "Discount Code: ".$value2;
                                            break;
                                        case "level_label":
                                            $payment_item_value_arr[] = "Level: ".$value2;
                                            break;
                                        case "renewal_date":
                                            if (format_date($value2)) {
                                                $payment_item_value_arr[] = "Renewal Date: ".format_date($value2);
                                            }
                                            break;
                                        case "date":
                                            if (format_date($value2)) {
                                                $payment_item_value_arr[] = "Date: ".format_date($value2);
                                            }
                                            break;
                                        case "categories":
                                            $payment_item_value_arr[] = "Categories: ".$value2;
                                            break;
                                        case "extra_categories":
                                            $payment_item_value_arr[] = "Extra Categories: ".$value2;
                                            break;
                                        case "listingtemplate_title":
                                            $payment_item_value_arr[] = "Type: ".$value2;
                                            break;
                                        case "amount":
                                            if ($table_log == "CustomInvoice") $value2 = $row2["subtotal"];
                                            $payment_item_value_arr[] = "Amount: ".$value2;
                                            break;
                                        default:
                                            $payment_item_value_arr[] = $value2;
                                            break;
                                    }
                                }
                            }
                            $payment_value_csv_content_aux .= trim($table_log)." ".implode(" - ", $payment_item_value_arr) . " | ";
                        }

                    }
                    $payment_value_csv_content_aux = string_substr($payment_value_csv_content_aux,0,-2);
                    $payment_value_csv_content .= $payment_value_csv_content_aux."\r\n";
                    $i++;
                }

                $payment_csv_content = $payment_label_csv_content.$csv_delimiter."items".$csv_delimiter."\r\n";
                $payment_csv_content .= $payment_value_csv_content;

                if($payment_csv_content) {
                    header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT\r\n" );
                    header ( "Last-modified: " . gmdate("D,d M Y H:i:s") . " GMT\r\n" );
                    header ( "Cache-control: private\r\n" );
                    header ( "Content-type: application/csv\r\n" );
                    header ( "Content-disposition: attachment; filename=\"$filename\"\r\n" );
                    header ( "Pragma: public\r\n" );
                    echo $payment_csv_content;
                    exit;
                } else {
                    $message_export_payment = system_showText(LANG_SITEMGR_EXPORT_PAYMENT_NORECORD);
                }

            } else {

                $message_export_payment = system_showText(LANG_SITEMGR_EXPORT_PAYMENT_NORECORD);

            }
        }
    }

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$_GET  = format_magicQuotes($_GET);
    extract($_GET);
	extract($_POST);

    $url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

	$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/activity/transactions";
	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."";

    # ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
    $where = "hidden = 'n'";
    include(INCLUDES_DIR."/code/transaction_manage.php");

    // Page Browsing /////////////////////////////////////////
	$pageObj  = new pageBrowsing("Payment_Log", $screen, RESULTS_PER_PAGE, "transaction_datetime DESC, id DESC", "", "", $where);
	$transactions = $pageObj->retrievePage("array");

    $paging_url = DEFAULT_URL."/".SITEMGR_ALIAS."/activity/transactions/index.php";

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
	include(SM_EDIRECTORY_ROOT."/layout/sidebar-activity.php");

?>

    <main class="wrapper togglesidebar container-fluid" id="view-content-list">

        <?php
        require(SM_EDIRECTORY_ROOT."/registration.php");
        require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
        ?>

        <?// Content Control is subscribed by bulk update using the Css classes SHOW and HIDDEN.?>
        <div class="content-control hidden" id="bulkupdate">
            <div class="row">
                <?
                //Bulk Update Include
                include(INCLUDES_DIR."/forms/form-bulkupdate-transaction.php");
                ?>
            </div>
        </div>

        <div class="content-control" id="search-all">

            <div class="row">
                <form role="form" name="searchTop" class="form-inline" role="search" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="get">
                    <div class="col-md-4 col-xs-8 control-search">
                        <div class="control-searchbar">
                            <div class="bulk-check-all">
                                <label class="sr-only">Check all</label>
                                <input type="checkbox" id="check-all">
                            </div>
                            <div class="form-group">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control search" name="search_id" value="<?=$search_id?>" onblur="populateField(this.value, 'search_id');" placeholder="<?=system_showText(LANG_SITEMGR_LABEL_TRANSACTIONID);?>">
                                    <div class="input-group-btn">
                                        <!-- Button and dropdown menu -->
                                        <button type="submit" class="btn btn-default"><?=system_showText(LANG_SITEMGR_SEARCH);?></button>
                                        <button type="button" class="btn btn-default dropdown-toggle"  data-toggle="modal" data-target="#modal-search" href="#" >
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="col-md-5 col-sm-4 control-responsive">
                    <span class="btn btn-info btn-responsive" data-toggle="dropdown" title="Groups"><i class="icon-ion-ios7-folder-outline"></i></span>
                    <div class="dropdown-menu control-folders">
                        <div class="btn-group btn-group-sm">
                            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/activity/transactions"?>" class="btn btn-info <?=(string_strpos($_SERVER["PHP_SELF"], "transactions/index.php") !== false ? "active" : "")?>"><?=(system_showText(LANG_SITEMGR_TRANSACTIONS))?></a>
                            <?php if (PAYMENT_INVOICE_STATUS == "on") { ?>
                                <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/activity/invoices/"?>" class="btn btn-info <?=(string_strpos($_SERVER["PHP_SELF"], "/invoices/index.php") !== false ? "active" : "")?>"><?=ucfirst(system_showText(LANG_SITEMGR_INVOICE_PLURAL))?></a>
                            <?php } ?>
                            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/activity/custominvoices/"?>" class="btn btn-info <?=(string_strpos($_SERVER["PHP_SELF"], "/custominvoices/index.php") !== false ? "active" : "")?>"><?=ucfirst(system_showText(LANG_SITEMGR_CUSTOMINVOICE_PLURAL))?></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-12 control-add">
                    <div class="control-bar">
                        <a href="#" data-toggle="modal" data-target="#modal-payment" class="btn btn-sm btn-primary"><?=system_showText(LANG_SITEMGR_MENU_EXPORTPAYMENTRECORDS)?></a>
                    </div>
                </div>

            </div>

        </div>

        <div class="content-full">
            <? if ($transactions) { ?>
                <div class="list-content">
                    <? include(INCLUDES_DIR."/lists/list-transactions.php"); ?>

                    <div class="content-control-bottom pagination-responsive">
                        <? include(INCLUDES_DIR."/lists/list-pagination.php"); ?>
                    </div>
                </div>

                <div class="view-content">
                    <? include(SM_EDIRECTORY_ROOT."/activity/transactions/view-transaction.php"); ?>
                </div>

            <? } else {
                include(SM_EDIRECTORY_ROOT."/layout/norecords.php");
            } ?>
        </div>

    </main>

    <?php
    include(INCLUDES_DIR."/modals/modal-exportpayment.php");
    include(INCLUDES_DIR."/modals/modal-delete.php");
    include(INCLUDES_DIR."/modals/modal-bulk.php");
    include(INCLUDES_DIR."/modals/modal-search-transaction.php");
    ?>

<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
