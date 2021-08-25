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
	# * FILE: /sponsors/layout/footer.php
	# ----------------------------------------------------------------------------------------------------

    $container = SymfonyCore::getContainer();
    $widgetInfo = $container->get('widget.service')->getWidgetInfo(\ArcaSolutions\WysiwygBundle\Entity\Widget::FOOTER_TYPE);
    $widgetContent = $widgetInfo['content'];
?>

    </main><!-- Close container MAIN-->

    <footer class="footer" data-type="1" is-inverse="<?=$widgetContent['backgroundColor'] === 'base' ? 'true' : 'false'?>">
        <div class="footer-bar">
            <div class="container">
                <div class="wrapper">
                    <div class="footer-copyright">
                        <? setting_get("footer_copyright", $footer_copyright); echo $footer_copyright; ?>
                     </div>
                    <?php if (BRANDED_PRINT == "on") { ?>
                    <div class="footer-powered"><?=system_showText(LANG_POWEREDBY)?>
                        <a href="https://www.edirectory.com<?=(string_strpos($_SERVER["HTTP_HOST"], ".com.br") !== false ? ".br" : "")?>" class="edirectory-link" target="_blank" <?=(trim(EDIRECTORY_TITLE) ? "title=\"".EDIRECTORY_TITLE."\"" : "")?> rel="nofollow">
                            <img src="/assets/images/edirectory-logo.svg" alt="<?=(trim(EDIRECTORY_TITLE) ? EDIRECTORY_TITLE : "&nbsp;")?>">
                        </a>
                    </div>
                        <? } ?>
                    </div>
                </div>
            </div>
    </footer>

        <?
        // GOOGLE ANALYTICS FEATURE
        if (GOOGLE_ANALYTICS_ENABLED == "on") {
            $google_analytics_page = "members";
            include(INCLUDES_DIR."/code/google_analytics.php");
        }
        ?>


    <!-- Auxiliary vars -->
    <script>
        DEFAULT_URL = "<?=DEFAULT_URL?>";
        MEMBERS_ALIAS = "<?=MEMBERS_ALIAS?>";
        DATEPICKER_FORMAT = '<?=(DEFAULT_DATE_FORMAT == "m/d/Y" ? "mm/dd/yyyy" : "dd/mm/yyyy")?>';
        DATEPICKER_LANGUAGE = '<?=EDIR_LANGUAGE?>';
        PATH = "<?= $_SERVER['PHP_SELF'] ?>";
        DOMAIN_ID = "<?=SELECTED_DOMAIN_ID?>";
        DATEPICKER_TIME_FORMAT = '<?=(CLOCK_TYPE === '12' ? 'hh:mm a' : 'HH:mm')?>';
    </script>

    <!-- Core Scripts -->

    <!-- Modernizr -->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/modernizr.custom.13060.js"></script>

    <!-- jQuery -->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/jquery-1.11.1.min.js"></script>

    <!-- jQuery - Sortable package only -->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/jquery-ui-1.11.1.min.js"></script>

    <!-- jQuery - Text Area Counter -->
    <script src="<?=DEFAULT_URL?>/scripts/jquery/jquery.textareaCounter.plugin.js"></script>

    <!-- Bootstrap -->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap.min.js"></script>

    <!-- Additional scripts -->
    <script src="<?=language_getFilePath(EDIR_LANGUAGE, true);?>"></script>
    <script src="<?=DEFAULT_URL?>/scripts/specialChars.js"></script>
    <script src="<?=DEFAULT_URL?>/scripts/banner.js"></script>
    <script src="<?=DEFAULT_URL?>/scripts/common.js"></script>

    <?php
    /* ModStores Hooks */
    if (!HookFire("sitemgr_custom_js_locationjs")) { ?>
        <script src="<?=DEFAULT_URL?>/scripts/location.js"></script>
    <? } ?>
    <script src="<?=DEFAULT_URL?>/scripts/jquery/jquery.knob.js"></script>
    <script src="<?=DEFAULT_URL?>/scripts/Chart.js"></script>

    <!-- External Plugins -->

    <!--Bootstrap Date Picker-->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-datepicker-master/bootstrap-datepicker.js"></script>
    <? if (EDIR_LANGUAGE != "en_us") { ?>
        <script src="<?=language_getDatePickPath(EDIR_LANGUAGE, SELECTED_DOMAIN_ID, false, true);?>"></script>
    <? } ?>

    <!--Moment-->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/moment/moment-with-locales.min.js"></script>

    <!-- Jquery Time Picker-->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-timepicker/bootstrap-timepicker.js"></script>

    <!-- Bootstrap file style-->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-filestyle/bootstrap-filestyle.js"></script>

    <!--Selectize-->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/selectize.js-master/selectize.min.js"></script>

    <!-- Bootstrap bootbox-->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-bootbox/bootbox.min.js"></script>

    <!-- jQuery - jScroll -->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/jquery.jscroll.min.js"></script>

    <script src="<?=DEFAULT_URL?>/assets/js/widgets/plans/main-modules-prices.js"></script>

    <!-- Bootstrap bootbox Locales-->
    <script>
        bootbox.setDefaults({
            /**
             * @optional String
             * @default: en
             * which locale settings to use to translate the three
             * standard button labels: OK, CONFIRM, CANCEL
             */
            locale: "<?=$edirlanguageArr[0]?>"
        });
    </script>

    <? if (!empty($_SESSION[SM_LOGGEDIN])) { ?>

    <script>
        function sitemgrSection() {
            location = "<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/sitemgraccess.php?logout";
        }
    </script>

    <? } ?>

    <script>

        function choosePlan(obj) {
            $(".choose-plan").html("<?=system_showText(LANG_LABEL_CHOOSEPLAN)?>");
            $(".choose-plan").removeClass("disabled");
            obj.html("<?=system_showText(LANG_LABEL_SELECTED)?>");
            obj.addClass("disabled");
            $("#level").val(obj.attr("data-level"));
            $("#buttonContinue").parent().removeClass("hidden");
            <?php if (string_strpos($_SERVER['HTTP_REFERER'], "/".ALIAS_ADVERTISE_URL_DIVISOR) !== false) { ?>
            $('html, body').animate({scrollTop: $(".level-price-actions").offset().top},'slow');
            <?php } ?>
        }

        $(function() {

            <? if (sess_getAccountIdFromSession()) { ?>
            //Update Billing Notification
            $.post("<?=DEFAULT_URL."/".MEMBERS_ALIAS."/ajax.php"?>", {
                ajax_type: 'getunpaidItems'
            }, function (ret) {
                if (ret > 0) {
                    $(".sponsor-notify-billing").html(ret);
                    $(".sponsor-notify-billing").fadeIn(function(){
                        $(this).css('display', 'inline-block');
                    });
                }
            });

            <? } ?>

            $(".choose-plan").click( function() {
                choosePlan($(this));
            });

        });

        <? if (is_numeric($level)) { ?>
        $(document).ready(function() {
            $(".choose-plan").each(function () {
                if ($(this).attr('data-level') == <?=$level?>) {
                    choosePlan($(this));
                }
            });
        });
        <? } ?>

        $(".more-label").on("click", function(){
            $(".more-label").toggleClass("is-open");
            $(".more-content").fadeToggle("fast", function(){
                if ($(this).is(':visible'))
                    $(this).css('display','flex');
            });
        });

        $(".navbar-toggler").on("click", function(){
            $(".search-mobile").slideUp(400);
            $(".navbar-mobile").slideToggle(400, function(){
                $(".navbar-toggler").toggleClass("is-open");
            });
        });

    </script>

    <script>
        $(document).ready(function(){
            $(".button-toggle-title").on("click", function(){
                var $el = $(this).parent();
                $el.next().slideToggle(400);
                $el.toggleClass("is-open");
            });
        });
    </script>

    <!--Custom javascripts for admin section -->
    <? if ($customJS && file_exists($customJS)) {
        include($customJS);
    } ?>

    <!-- Main Script -->
    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/adminpanel.js"></script>

    <?
    if (DEMO_LIVE_MODE && file_exists(EDIRECTORY_ROOT."/frontend/livebar.php"))
    {
        include(EDIRECTORY_ROOT."/frontend/livebar.php");
    }

    if ( class_exists( "JavaScriptHandler" ) )
    {
        JavaScriptHandler::render();
    }
    ?>

    <?php
    /* ModStores Hooks */

    HookFire("sponsorfoorter_after_render_js", [
        "feedName"          => &$feedName,
        "id"                => &$id,
        "account_id"        => &$account_id,
        "customJS"          => &$customJS,
        "members"           => &$members,
        "message"           => &$message,
        "screen"            => &$screen,
        "url_redirect"      => &$url_redirect,
        "url_base"          => &$url_base,
        "search_page"       => &$search_page,
        "sitemgr"           => &$sitemgr,
        "url_search_params" => &$url_search_params,
        "errorPage"         => &$errorPage,
    ]);
    ?>

	</body>

</html>
