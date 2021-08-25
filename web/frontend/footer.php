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
    # * FILE: /frontend/footer.php
    # ----------------------------------------------------------------------------------------------------

    //Links to twitter, facebook and linkedin
    setting_get('twitter_account', $setting_twitter_link);
    setting_get('setting_facebook_link', $setting_facebook_link);
    setting_get('setting_linkedin_link', $setting_linkedin_link);
    setting_get('setting_instagram_link', $setting_instagram_link);
    setting_get('setting_pinterest_link', $setting_pinterest_link);

    //Contact Us info
    /*
     * Address information should follow this format:
     * Line 1: Company name
     * Line 2: Address
     * Line 3: City, State Zip Code (city and state are separated by comma, and the zip code is preceded by a space
     * Line 4: country
     */
    $city = setting_get('contact_city', $contact_city);
    $state = setting_get('contact_state', $contact_state);
    $zipcode = setting_get('contact_zipcode', $contact_zipcode);

    $addressInfo = [];
    $addressInfo[] = setting_get('contact_company', $contact_company); //line 1
    $addressInfo[]  = setting_get('contact_address', $contact_address); //line 2
    if ($city || $state || $zipcode) {
        $auxLine = $city.($state ? ', '.$state : '').($zipcode ? ' '.$zipcode : '');
        $addressInfo[] = $auxLine; //line 3
    }
    $addressInfo[] = setting_get('contact_country', $contact_country); //line 4

    $addressInfo = array_filter($addressInfo);

    $contact_address = implode('<br>', $addressInfo);

    setting_get('contact_phone', $contact_phone);

    $container = SymfonyCore::getContainer();
    $widgetInfo = $container->get('widget.service')->getWidgetInfo(\ArcaSolutions\WysiwygBundle\Entity\Widget::FOOTER_TYPE);
    $widgetFileName = $widgetInfo['twig'];
    $widgetContent = $widgetInfo['content'];

    $translator = $container->get('translator');
    $imagine_filter = $container->get('liip_imagine.cache.manager');

    echo $container->get('twig')->render('@Web/modal-login.html.twig');

    include EDIRECTORY_ROOT."/frontend/widgets/$widgetFileName.php";

?>

            <!-- Auxiliary vars -->
            <script>
                DEFAULT_URL = "<?=DEFAULT_URL?>";
                MEMBERS_ALIAS = "<?=MEMBERS_ALIAS?>";
                DATEPICKER_FORMAT = '<?=(DEFAULT_DATE_FORMAT === 'm/d/Y' ? 'mm/dd/yyyy' : 'dd/mm/yyyy')?>';
                DATEPICKER_LANGUAGE = '<?=EDIR_LANGUAGE?>';
            </script>

            <!-- Core Scripts -->

            <!-- Modernizr -->
            <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/modernizr.custom.13060.js"></script>

            <!-- jQuery -->
            <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/jquery-1.11.1.min.js"></script>

            <!-- jQuery - Sortable package only -->
            <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/jquery-ui-1.11.1.min.js"></script>

            <!-- Bootstrap -->
            <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap.min.js"></script>

            <!-- External Plugins -->

            <!--Bootstrap Date Picker-->
            <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-datepicker-master/bootstrap-datepicker.js"></script>
            <? if (EDIR_LANGUAGE !== 'en_us') { ?>
                <script src="<?=language_getDatePickPath(EDIR_LANGUAGE, SELECTED_DOMAIN_ID, false, true);?>"></script>
            <? } ?>

            <!--Moment-->
            <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/moment/moment-with-locales.min.js"></script>

            <!-- Jquery Time Picker-->
            <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-timepicker/bootstrap-timepicker.js"></script>

            <!-- Bootstrap bootbox-->
            <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-bootbox/bootbox.min.js"></script>
            <script src="<?=DEFAULT_URL?>/assets/js/lib/smartbanner/jquery.smartbanner.js"></script>

            <!-- Lazyload -->
            <script src="<?=DEFAULT_URL?>/assets/js/lib/lazyload.min.js"></script>

            <script src="<?=DEFAULT_URL?>/assets/js/modal/modal.js"></script>

            <!-- Bootstrap bootbox Locales-->
            <script>
                bootbox.setDefaults({
                    /**
                     * @optional String
                     * @default: en
                     * which locale settings to use to translate the three
                     * standard button labels: OK, CONFIRM, CANCEL
                     */
                    locale: "<?=EDIR_LANGUAGE?>"
                });
            </script>

            <!-- Additional scripts -->
            <script src="<?=language_getFilePath(EDIR_LANGUAGE, true);?>"></script>
            <script src="<?=DEFAULT_URL?>/scripts/specialChars.js"></script>
            <script src="<?=DEFAULT_URL?>/scripts/common.js"></script>
            <script src="<?=DEFAULT_URL?>/scripts/categorytree.js"></script>
            <script src="<?=DEFAULT_URL?>/scripts/orders.js"></script>
            <script src="<?=DEFAULT_URL?>/assets/js/widgets/newsletter/newsletter.js"></script>

            <!-- Bootstrap file style-->
            <script src="/scripts/jquery/auto_upload/js/file_uploads.js"></script>

            <?php
            $scriptFunct = true;
            include INCLUDES_DIR.'/code/smartbanner.php';
            ?>

            <script src="/bundles/fosjsrouting/js/router.js"></script>
            <script src="/js/routing?callback=fos.Router.setData"></script>

            <script>

                $(document).ready(function() {

                    <? if ($advertiseItem === 'event') { ?>
                    $(".date-input").datepicker({
                        format: DATEPICKER_FORMAT,
                        language: DATEPICKER_LANGUAGE,
                        autoclose: true,
                        todayHighlight: true
                    });
                    <? } ?>

                    <? if ($advertiseItem === 'listing') { ?>
                        <? if ( is_numeric($listingtemplate_id) && $listingtemplate_id ) { ?>
                            loadCategoryTree('template', 'listing_', 'ListingCategory', 0, '<?=$listingtemplate_id?>', '<?=DEFAULT_URL?>',<?=SELECTED_DOMAIN_ID?>);
                        <? } else {  ?>
                            loadCategoryTree('all', 'listing_', 'ListingCategory', 0, 0, '<?=DEFAULT_URL?>',<?=SELECTED_DOMAIN_ID?>);
                        <? } ?>
                    <? } ?>

                    <? if ($advertiseItem) { ?>
                        orderCalculate();
                    <? } ?>

                });

                function orderCalculate() {

                    var xmlhttp;

                    try {
                        xmlhttp = new XMLHttpRequest();
                    } catch (e) {
                        try {
                            xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
                        } catch (e) {
                            try {
                                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                            } catch (e) {
                                xmlhttp = false;
                            }
                        }
                    }

                    if (document.getElementById("check_out_payment")) document.getElementById("check_out_payment").className = "isHidden";
                    $("#check_out_payment_2").removeClass("isVisible").addClass("isHidden");
                    if (document.getElementById("check_out_free")) document.getElementById("check_out_free").className = "isHidden";
                    $("#check_out_free_2").removeClass("isVisible").addClass("isHidden");
                    if (document.getElementById("loadingOrderCalculate")) document.getElementById("loadingOrderCalculate").style.display = "";
                    if (document.getElementById("loadingOrderCalculate")) document.getElementById("loadingOrderCalculate").innerHTML = "<?=system_showText(LANG_WAITLOADING)?>";
                    if (xmlhttp) {
                        xmlhttp.onreadystatechange = function() {
                            if (xmlhttp.readyState == 4) {
                                if (xmlhttp.status == 200) {
                                    var price = xmlhttp.responseText;
                                    var arrPrice = price.split("|");
                                    var html = "";
                                    var tax_status = '<?=$payment_tax_status;?>';
                                    var tax_info = "";
                                    <? if ((PAYMENT_FEATURE === 'on') && ((CREDITCARDPAYMENT_FEATURE === 'on') || (PAYMENT_INVOICE_STATUS === 'on'))) { ?>
                                    if (arrPrice[0] > 0) {
                                        if (tax_status == "on") {
                                            html += "<strong><?=system_showText(LANG_SUBTOTALAMOUNT)?>: </strong><?=PAYMENT_CURRENCY_SYMBOL?>"+arrPrice[0].substring(0, arrPrice[0].length-2)+"."+arrPrice[0].substring(arrPrice[0].length-2, arrPrice[0].length);
                                            html += "<br /><strong><?=system_showText(LANG_TAXAMOUNT)?>: </strong><?=PAYMENT_CURRENCY_SYMBOL?>"+arrPrice[1].substring(0, arrPrice[1].length-2)+"."+arrPrice[1].substring(arrPrice[1].length-2, arrPrice[1].length);
                                            html += "<br /><strong><?=system_showText(LANG_TOTALPRICEAMOUNT)?>: </strong><?=PAYMENT_CURRENCY_SYMBOL?>"+arrPrice[2].substring(0, arrPrice[2].length-2)+"."+arrPrice[2].substring(arrPrice[2].length-2, arrPrice[2].length);
                                            tax_info = "<?='+'.$payment_tax_value.'% '.$payment_tax_label;?> (" + "<?=PAYMENT_CURRENCY_SYMBOL?>"+arrPrice[2].substring(0, arrPrice[2].length-2)+"."+arrPrice[2].substring(arrPrice[2].length-2, arrPrice[2].length) + ")";
                                        } else {
                                            html += "<strong><?=system_showText(LANG_TOTALPRICEAMOUNT)?>: </strong><?=PAYMENT_CURRENCY_SYMBOL?>"+arrPrice[0].substring(0, arrPrice[0].length-2)+"."+arrPrice[0].substring(arrPrice[0].length-2, arrPrice[0].length);
                                        }
                                        $("#free_item").attr("value", "0");
                                        if (document.getElementById("check_out_payment")) document.getElementById("check_out_payment").className = "isVisible";
                                        $("#check_out_payment_2").addClass("isVisible").removeClass("isHidden");
                                        if (document.getElementById("payment-method")) document.getElementById("payment-method").className = "isVisible";
                                        if (document.getElementById("checkoutpayment_total")) document.getElementById("checkoutpayment_total").innerHTML = html;
                                        $("#checkout_steps").addClass("isVisible").removeClass("isHidden");
                                    } else {
                                        $("#free_item").attr("value", "1");
                                        if (document.getElementById("check_out_free")) document.getElementById("check_out_free").className = "isVisible";
                                        $("#check_out_free_2").addClass("isVisible").removeClass("isHidden");
                                        if (document.getElementById("payment-method")) document.getElementById("payment-method").className = "isHidden";
                                        if (document.getElementById("checkoutfree_total")) document.getElementById("checkoutfree_total").innerHTML = "<strong><?=system_showText(LANG_TOTALPRICEAMOUNT)?>: </strong><?=system_showText(LANG_FREE)?>";
                                        $("#checkout_steps").addClass("isHidden").removeClass("isVisible");
                                    }
                                    <? } else { ?>
                                    $("#free_item").attr("value", "1");
                                    if (document.getElementById("check_out_free")) document.getElementById("check_out_free").className = "isVisible";
                                    $("#check_out_free_2").addClass("isVisible").removeClass("isHidden");
                                    if (document.getElementById("payment-method")) document.getElementById("payment-method").className = "isHidden";
                                    if (arrPrice[0] > 0) {
                                        if (tax_status == "on") {
                                            html += "<strong><?=system_showText(LANG_SUBTOTALAMOUNT)?>: </strong><?=PAYMENT_CURRENCY_SYMBOL?>"+arrPrice[0].substring(0, arrPrice[0].length-2)+"."+arrPrice[0].substring(arrPrice[0].length-2, arrPrice[0].length);
                                            html += "<br /><strong><?=system_showText(LANG_TAXAMOUNT)?>: </strong><?=PAYMENT_CURRENCY_SYMBOL?>"+arrPrice[1].substring(0, arrPrice[1].length-2)+"."+arrPrice[1].substring(arrPrice[1].length-2, arrPrice[1].length);
                                            html += "<br /><strong><?=system_showText(LANG_TOTALPRICEAMOUNT)?>: </strong><?=PAYMENT_CURRENCY_SYMBOL?>"+arrPrice[2].substring(0, arrPrice[2].length-2)+"."+arrPrice[2].substring(arrPrice[2].length-2, arrPrice[2].length);
                                            tax_info = "<?='+'.$payment_tax_value.'% '.$payment_tax_label;?> (" + "<?=PAYMENT_CURRENCY_SYMBOL?>"+arrPrice[2].substring(0, arrPrice[2].length-2)+"."+arrPrice[2].substring(arrPrice[2].length-2, arrPrice[2].length) + ")";
                                        } else {
                                            html += "<strong><?=system_showText(LANG_TOTALPRICEAMOUNT)?>: </strong><?=PAYMENT_CURRENCY_SYMBOL?>"+arrPrice[0].substring(0, arrPrice[0].length-2)+"."+arrPrice[0].substring(arrPrice[0].length-2, arrPrice[0].length);
                                        }
                                        if (document.getElementById("checkoutfree_total")) document.getElementById("checkoutfree_total").innerHTML = html;
                                    } else {
                                        if (document.getElementById("checkoutfree_total")) document.getElementById("checkoutfree_total").innerHTML = "<strong><?=system_showText(LANG_TOTALPRICEAMOUNT)?>: </strong><?=system_showText(LANG_FREE)?>";
                                    }
                                    <? } ?>
                                    if (document.getElementById("loadingOrderCalculate")) document.getElementById("loadingOrderCalculate").style.display = "none";
                                    if (document.getElementById("loadingOrderCalculate")) document.getElementById("loadingOrderCalculate").innerHTML = "";
                                }
                            }
                        }
                        var get_level = "";
                        if (document.order_item.level) get_level = "&level=" + document.order_item.level.value;

                        var get_categories = "";
                        if (document.order_item.feed) get_categories = "&categories=" + document.order_item.feed.length;

                        var get_listingtemplate_id = "";
                        if (document.order_item.select_listingtemplate_id) get_listingtemplate_id = "&listingtemplate_id=" + document.order_item.select_listingtemplate_id.value;

                        var get_discount_id = "";
                        if (document.order_item.discount_id) get_discount_id = (document.order_item.discount_id ? document.order_item.discount_id.value : 0);

                        var get_type = "";
                        if (document.order_item.type) get_type = "&type=" + document.order_item.type.value;

                        var get_renewal_period = "";
                        if ($('#renewal_radio').val()) {
                            get_renewal_period = $('#renewal_radio').val();
                        } else {
                            get_renewal_period = $('input:radio[name=renewal_period]:checked').val();
                        }

                        xmlhttp.open("GET", "<?=DEFAULT_URL;?>/ordercalculateprice.php?item=<?=$advertiseItem?>&item_id=<?=$unique_id;?>"+get_level+get_categories+get_listingtemplate_id+"&discount_id="+get_discount_id+get_type+"&renewal_period="+get_renewal_period, true);
                        xmlhttp.send(null);
                    }
                }

                <? if (LISTINGTEMPLATE_FEATURE === 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE === 'on' && $advertiseItem === 'listing') { ?>
                var previous;
                (function () {
                    $("#listing-template").focus(function () {
                        // Store the current value on focus, before it changes
                        previous = this.value;
                    });
                })();

                function templateSwitch(template) {
                    feed = document.order_item.feed;

                    if (feed.length > 0) {
                        if (!isNaN(feed.options[0].value)) {
                            bootbox.confirm('<?=system_showText(LANG_CONFIRM_CHANGELISTINGTYPE)?>', function (result) {
                                if (result) {
                                    //Remove selecte categories
                                    removeOptions(feed);
                                    confirmTemplate(template);
                                } else {
                                    //Reset selected option
                                    $("#listing-template").val(previous);
                                }
                            });
                        } else {
                            confirmTemplate(template);
                        }
                    } else {
                        confirmTemplate(template);
                    }
                }

                function removeOptions(obj) {
                    while (obj.options.length) {
                        obj.remove(0);
                    }
                }

                function confirmTemplate(template) {
                    if (!template) template = 0;
                    <?=$jsVarsType;?>
                    document.order_item.listingtemplate_id.value = template;
                    if (document.getElementById("title_label")) {
                        document.getElementById("title_label").innerHTML = eval("title_template_" + template);
                    }
                    orderCalculate();
                    loadCategoryTree('template', 'listing_', 'ListingCategory', 0, template, '<?=DEFAULT_URL?>',<?=SELECTED_DOMAIN_ID?>);
                    updateFormAction();
                }
                <? } ?>

                <? if ($advertiseItem === 'banner') { ?>
                function typeSwitch(type) {
                    document.order_item.type.value = type;
                    orderCalculate();
                    updateFormAction();
                }
                <? } ?>

                function updateFormAction() {
                    var levelValue = "";
                    var titleValue = "";
                    var templateValue = "";
                    var categValue = "";
                    var discountValue = "";
                    var packageValue = "";
                    var packageID = "";
                    var startDateValue = "";
                    var endDateValue = "";
                    var expirationValue = "";
                    var advertiseItem = "<?=$advertiseItem?>";
                    var get_renewal_period = "";

                    //Get level/type
                    if (document.order_item.level) {
                        levelValue = "level=" + document.order_item.level.value;
                    } else if (document.order_item.type) {
                        levelValue = "type=" + document.order_item.type.value;
                    }

                    //Get Title/Caption
                    if (document.order_item.title) {
                        titleValue = "&title=" + urlencode(document.order_item.title.value);
                    } else if (document.order_item.caption) {
                        titleValue = "&caption=" + urlencode(document.order_item.caption.value);
                    }

                    //Get Template ID
                    if (document.order_item.select_listingtemplate_id) {
                        templateValue = "&listingtemplate_id=" + document.order_item.select_listingtemplate_id.value;
                    }

                    //Get Discount
                    if (document.order_item.discount_id) {
                        discountValue = "&discount_id=" + (document.order_item.discount_id ? document.order_item.discount_id.value : 0);
                    }

                    //Get Start Date (event)
                    if (document.order_item.start_date) {
                        startDateValue = "&start_date=" + document.order_item.start_date.value;
                    }

                    //Get End Date (event)
                    if (document.order_item.end_date) {
                        endDateValue = "&end_date=" + document.order_item.end_date.value;
                    }

                    <? if ($advertiseItem === 'listing') { ?>

                    //Get Categories
                    feed = document.order_item.feed;
                    var return_categories = "";

                    for (i = 0; i < feed.length; i++) {
                        if (!isNaN(feed.options[i].value)) {
                            if (return_categories.length > 0) {
                                return_categories = return_categories + "," + feed.options[i].value;
                            } else {
                                return_categories = return_categories + feed.options[i].value;
                            }
                        }
                    }
                    if (return_categories.length > 0) {
                        categValue = "&return_categories=" + return_categories;
                    }

                    <? } ?>

                    //Get package
                    if ($("#using_package").val() == "y") {
                        packageID = $("#aux_package_id").val();
                        packageValue = "&package_id="+packageID;
                    } else if (advertiseItem == "article") {
                        packageID = "skipPackageOffer";
                        packageValue = "&package_id="+packageID;
                    }

                    if ($('#renewal_radio').val()) {
                        get_renewal_period = $('#renewal_radio').val();
                    } else {
                        get_renewal_period = $('input:radio[name=renewal_period]:checked').val();
                    }

                    if (document.formDirectory != undefined)	document.formDirectory.action = "<?=$formloginaction?>&query=" + levelValue + templateValue + titleValue + categValue + discountValue + packageValue + startDateValue + endDateValue + expirationValue;

                    <? if ($googleEnabled || $facebookEnabled) { ?>

                    $.get(DEFAULT_URL + "/ordercalculateprice.php", {

                        renewal_period:     get_renewal_period,

                        item:               "<?=$advertiseItem?>",

                        item_id:            "<?=$unique_id;?>",

                        <? if ($advertiseItem === 'banner') { ?>

                        type:              document.order_item.type.value,

                        caption:            document.order_item.caption.value,

                        <? } else { ?>

                        level:              document.order_item.level.value,

                        title:              document.order_item.title.value,

                        <? } ?>

                        <? if ($advertiseItem === 'listing') { ?>

                        <? if (LISTINGTEMPLATE_FEATURE === 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE === 'on' && system_showListingTypeDropdown($listingtemplate_id)) { ?>

                        listingtemplate_id: document.order_item.select_listingtemplate_id.value,

                        <? } ?>

                        return_categories:  return_categories,

                        <? } elseif ($advertiseItem === 'event') { ?>

                        start_date:         document.order_item.start_date.value,

                        end_date:           document.order_item.end_date.value,

                        <? } ?>

                        discount_id:        (document.order_item.discount_id ? document.order_item.discount_id.value : 0),

                        package_id:         packageID
                    }, function () {});

                    <? } ?>
                }

                <? if ($advertiseItem === 'listing') { ?>

                function JS_addCategory(id) {
                    seed = document.order_item.seed;
                    feed = document.order_item.feed;
                    var text = unescapeHTML($("#liContent"+id).html());
                    var flag = true;
                    for (i = 0; i < feed.length; i++) {
                        if (feed.options[i].value == id) {
                            flag = false;
                        }
                        if (!feed.options[i].value || feed.options[i].value == "empty") {
                            feed.remove(feed.options[i]);
                        }
                    }
                    if (text && id && flag) {
                        feed.options[feed.length] = new Option(text, id);
                        $('#categoryAdd'+id).after("<span class=\"categorySuccessMessage\"><?=system_showText(LANG_MSG_CATEGORY_SUCCESSFULLY_ADDED)?></span>").css('display', 'none');
                        $('.categorySuccessMessage').fadeOut(5000);
                        orderCalculate();
                        updateFormAction();
                    } else {
                        if (!flag) {
                            $('#categoryAdd'+id).after("</a> <span class=\"categoryErrorMessage\"><?=system_showText(LANG_MSG_CATEGORY_ALREADY_INSERTED)?></span> </li>");
                        } else {
                            ('#categoryAdd'+id).after("</a> <span class=\"categoryErrorMessage\"><?=system_showText(LANG_MSG_SELECT_VALID_CATEGORY)?></span> </li>");
                        }
                    }

                    $('#removeCategoriesButton').show();

                }

                <? } ?>

                function JS_submit() {
                    disableButtons();
                    <? if ($advertiseItem === 'listing') { ?>
                    feed = document.order_item.feed;
                    return_categories = document.order_item.return_categories;
                    if (return_categories.value.length > 0) {
                        return_categories.value = "";
                    }
                    for (i = 0; i < feed.length; i++) {
                        if (!isNaN(feed.options[i].value)) {
                            if (return_categories.value.length > 0) {
                                return_categories.value = return_categories.value + "," + feed.options[i].value;
                            } else {
                                return_categories.value = return_categories.value + feed.options[i].value;
                            }
                        }
                    }
                    <? } ?>
                }

                function getFacebookImage() {

                    $("#image_fb").html("<img src=\"" + DEFAULT_URL + "/assets/images/structure/img_loading.gif\" alt=\"\" />").addClass('loading-picture');

                    $.post(DEFAULT_URL + "/<?=MEMBERS_ALIAS?>/ajax.php", {
                        ajax_type: 'getFacebookImage',
                        id: '<?=sess_getAccountIdFromSession();?>'
                    }, function(newImage) {
                        var eURL = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- ./?%&=]*)?/
                        var arrInfo = newImage.split("[FBIMG]");
                        var imgSize = "";

                        if (arrInfo[0] && eURL.exec(arrInfo[0])) {
                            $("#facebook_image").val(arrInfo[0]);
                            if (arrInfo[1] && arrInfo[2]) {
                                var w = parseInt(arrInfo[1]);
                                var h = parseInt(arrInfo[2]);

                                imgSize = " width=\"" + w + "\" ";
                                imgSize += " height=\"" + h + "\" ";
                            } else {
                                imgSize = " width=\"<?=PROFILE_MEMBERS_IMAGE_WIDTH?>\" ";
                                imgSize += " height=\"<?=PROFILE_MEMBERS_IMAGE_HEIGHT?>\" ";
                            }
                            $("#image_fb").html("<img src=\"" + arrInfo[0] + "\" " + imgSize + " alt=\"\" />");
                            if ($("#message").text() == "<?=system_showText(LANG_MSGERROR_ERRORUPLOADINGIMAGE);?>") {
                                $("#message").removeClass("errorMessage");
                                $("#message").text("");
                            }
                        } else if (!eURL.exec(arrInfo[0])) {
                            $("#facebook_image").val("");
                            $("#image_fb").html('<img class="user-picture" width="100" height="100" src="/assets/images/user-image.png">');
                            $("#message").removeClass("successMessage");
                            $("#message").removeClass("informationMessage");
                            $("#message").addClass("errorMessage");
                            $("#message").text("<?=system_showText(LANG_MSGERROR_ERRORUPLOADINGIMAGE);?>");
                        }
                    });
                }

                function profileStatus(check_id) {
                    var check = $("#" + check_id).data("value");

                    $.post(DEFAULT_URL + "/profile/edit.php", {
                        action: "changeStatus",
                        has_profile: check,
                        account_id: '<?=sess_getAccountIdFromSession();?>',
                        ajax: true
                    });

                }

                function validateFriendlyURL(friendly_url, current_acc) {

                    $("#URL_ok").css("display", "none");
                    $("#URL_notok").css("display", "none");

                    if (friendly_url) {

                        $("#loadingURL").css("display", "");

                        $.get(DEFAULT_URL + "/check_friendlyurl.php", {
                            type: "profile",
                            friendly_url: friendly_url,
                            current_acc : current_acc
                        }, function (response) {
                            if ($.trim(response) == "ok") {
                                $("#urlSample").html(friendly_url);
                                $("#URL_ok").css("display", "");
                                $("#URL_notok").css("display", "none");
                            } else {
                                $("#URL_ok").css("display", "none");
                                $("#URL_notok").css("display", "");
                            }
                            $("#loadingURL").css("display", "none");
                        });
                    } else {
                        $("#URL_ok").css("display", "none");
                        $("#URL_notok").css("display", "none");
                    }
                }

                function removePhoto() {
                    $.post(DEFAULT_URL + "/profile/edit.php", {
                        action: "removePhoto",
                        account_id: '<?=sess_getAccountIdFromSession();?>',
                        ajax: true
                    }, function(){
                        $("#facebook_image").attr("value", "");
                        $("#linkRemovePhoto").addClass('hidden');
                        $("#image_fb").html('<img class="user-picture" width="100" height="100" src="/assets/images/user-image.png">');
                    });
                }

                function uploadProfilePicture() {
                    $("#account").vPB({
                        url: DEFAULT_URL + "/profile/edit.php?action=uploadPhoto&ajax=1",
                        success: function(response)
                        {
                            strReturn = response.split("||");

                            if (strReturn[0] == "ok") {
                                $("#returnMessage").hide();
                                $("#image_fb").hide().fadeIn('slow').html(strReturn[1]);
                                $('#linkRemovePhoto').removeClass('hidden');
                            } else {
                                $("#returnMessage").removeClass("alert-success");
                                $("#returnMessage").removeClass("alert-warning");
                                $("#returnMessage").addClass("alert-warning");
                                $("#returnMessage").html(strReturn[1]);
                                $("#returnMessage").show();
                            }
                            btn = $('.action-save');
                            btn.button('reset');
                        }
                    }).submit();
                }

                <?php if($claimPage){ ?>
                    /*
                        @todo Refatorar essa abordagem
                    */
                    var moveSelectedArrow = function(el){
                        var navLeft  = el.find(".modal-nav").offset().left;
                        var linkLeft = el.find(".modal-nav-link.active").offset().left;
                        el.find(".modal-nav .selected-arrow").css("left", Math.round((linkLeft - navLeft)) + 24);
                    };

                    var changeTabContent = function(el, tab){
                        setContentHeight(el);
                        el.find(".content-tab").removeClass("active");
                        el.find("#"+tab).addClass("active");
                    };

                    var setContentHeight = function(el){
                        var activeItem = el.find(".modal-nav-link.active").data('tab');
                        var itemHeight = el.find("#"+activeItem).height();
                        el.find(".modal-body").height(itemHeight);
                    };

                    moveSelectedArrow($(".modal-sign-claim"));
                    setContentHeight($(".modal-sign-claim"));
                <?php } ?>

            </script>

            <script src="/assets/js/utility/main.js"></script>

            <?php if($hasDeal) { ?>
                <script>
                    $(document).ready(function () {
                        /*
                         * Download coupon
                         */
                        $(document).on('click', '#download-cupom', function () {
                            var dealsCoupon = document.getElementsByClassName('deals-coupon');
                            var fileName = $(this).attr('data-file-name');

                            html2canvas(document.querySelector(".deals-coupon"), {
                                scrollX: dealsCoupon.scrollX,
                                scrollY: dealsCoupon.scrollY,
                                x: dealsCoupon.offsetX,
                                y: dealsCoupon.offsetY,
                                width: dealsCoupon.innerWidth,
                                height: dealsCoupon.innerHeight,
                                backgroundColor: '#808080'
                            }).then(canvas => {
                                download(canvas.toDataURL('image/png'), (fileName !== '' ? fileName : 'file') + '.png', 'image/png');
                            });
                        });
                    });
                </script>

                <script src="<?=DEFAULT_URL?>/assets/js/lib/html2canvas.js"></script>
                <script src="<?=DEFAULT_URL?>/assets/js/lib/download.js"></script>
            <?php }

            if ( class_exists('JavaScriptHandler') )
            {
                JavaScriptHandler::render();
            }

            ?>

        </body>

    </html>
