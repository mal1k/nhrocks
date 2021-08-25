<script>

    if ($("#myChart").length) {
        //This will get the first returned node in the jQuery collection.
        var ctx = $("#myChart").get(0).getContext("2d");
    }

    // Scripts for Edirectory v12
    // João Deroldo

    // Exted new jquery function
    $.fn.extend({
        toggleText: function(a, b){
            return this.text(this.text() == b ? a : b);
        }
    });

    $.fn.extend({
        toggleHtml: function(a, b){
            return this.html(this.html() == b ? a : b);
        }
    });

    // Reply functions
    var replyFunction = function () {
        var that = this;

        this.openReplyBox = function (el) {
            this.replyBlock = $("[data-id='"+el.data('ref')+"']").find(".reply-block");
            this.replyForm  = $("[data-id='"+el.data('ref')+"']").find(".reply-form");

            this.replyBlock.slideUp(400);
            this.replyForm.slideDown(400);
        }

        this.closeReplyBox = function (el) {
            this.replyBlock = $("[data-id='"+el.data('ref')+"']").find(".reply-block");
            this.replyForm  = $("[data-id='"+el.data('ref')+"']").find(".reply-form");

            this.replyBlock.slideDown(400);
            this.replyForm.slideUp(400);
        }

        this.toggleReplyBox = function (el) {
            this.replyBlock = $("[data-id='"+el.data('ref')+"']").find(".reply-block");
            this.replyForm  = $("[data-id='"+el.data('ref')+"']").find(".reply-form");
            this.buttonText = el.data("text");

            this.replyBlock.slideToggle(400);
            this.replyForm.slideToggle(400);
            el.toggleText(this.buttonText[0], this.buttonText[1]);
        }

        this.saveReplyReview = function (el) {
            var that = this;
            this.ref = $("#" + el.attr("id"));
            this.id  = $($("#" + el.attr("id"))).attr("id");
            this.id  = this.id.match(/([0-9]+)/);

            el.find(".button")
                .html('<?=system_showText(LANG_WAITLOADING);?>')
                .prop("disabled", "disabled");

            $.post("<?=DEFAULT_URL."/".MEMBERS_ALIAS."/ajax.php"?>", this.ref.serialize(), function(data) {
                var data = JSON.parse(data);
                if ($.trim(data.status) == "ok") {
                    $(".reply-message[data-type='success']")
                        .fadeIn(400)
                        .delay(2000)
                        .fadeOut(400, function(){
                            this.buttonText = $(".button-edit-reply[data-ref='"+that.id[0]+"']").data("text");
                            el.find(".button").html('<?=system_showText(LANG_BUTTON_SUBMIT);?>').prop("disabled", "");
                            that.closeReplyBox($(".button-edit-reply[data-ref='"+that.id[0]+"']"));
                            $(".button-edit-reply[data-ref='"+that.id[0]+"']").html(this.buttonText[0]);
                            $("[data-id='"+that.id[0]+"']").find(".reply-text").html(data.newReply);
                        });
                } else {
                    $(".reply-message[data-type='error']")
                        .html(data)
                        .fadeIn(400)
                        .delay(2000)
                        .fadeOut(400, function(){
                            el.find(".button").html('<?=system_showText(LANG_BUTTON_SUBMIT);?>').prop("disabled", "");
                        });
                }
            });
        }

        this.saveReplyLead = function (el) {
            var that = this;
            this.ref = $("#" + el.attr("id"));
            this.id  = $($("#" + el.attr("id"))).attr("id");
            this.id  = this.id.match(/([0-9]+)/);

            el.find(".button")
                .html('<?=system_showText(LANG_WAITLOADING);?>')
                .prop("disabled", "disabled");

            $.post("<?=DEFAULT_URL."/".MEMBERS_ALIAS."/ajax.php"?>", this.ref.serialize(), function(data) {
                if ($.trim(data) == "ok") {
                    $(".reply-message[data-type='success']")
                        .fadeIn(400)
                        .delay(2000)
                        .fadeOut(400, function(){
                            this.buttonText = $(".button-edit-reply[data-ref='"+that.id[0]+"']").data("text");
                            el.find(".button").html('<?=system_showText(LANG_BUTTON_SUBMIT);?>').prop("disabled", "");
                            that.closeReplyBox($(".button-edit-reply[data-ref='"+that.id[0]+"']"));
                            $(".button-edit-reply[data-ref='"+that.id[0]+"']").html(this.buttonText[0]);
                            $("[data-id='"+that.id[0]+"']").find(".reply-text").html(data.newReply);
                        });
                } else {
                    $(".reply-message[data-type='error']")
                        .fadeIn(400)
                        .delay(2000)
                        .fadeOut(400);
                }
            });
        }
    }

    var replyFunction = new replyFunction();

    function initializeDashboard() {
        $(".dial").knob({
            readOnly:   true,
            fgColor:    "#<?=$colorKnob;?>",
            bgColor:    "#DEE1E3",
            fontWeight: 300,
            thickness:  .2,
            width:      70,
            height:     70
        });

        $(".status, .floating-tip, .alert-new, #item_renewal").tooltip({
            animation: true,
            placement: "top"
        });

        if ($("#myChart").length) {
            //Get context with jQuery - using jQuery's .get() method.
            ctx = $("#myChart").get(0).getContext("2d");
            loadChart();
        }

        $(".panel-toggler").on("click", function(){
            var container = $(this).parent().parent();

            $(container).find(".panel-body").slideToggle(400);
            $(container).toggleClass("is-closed");
            $(this).toggleHtml('<i class="fa fa-minus"></i>', '<i class="fa fa-plus"></i>');
        });

        $(".button-edit-reply").on("click", function(){
            replyFunction.toggleReplyBox($(this));
        });

        $(".reply-form").on("submit", function(){
            if($(this).data("action") == 'review'){
                replyFunction.saveReplyReview($(this));
            } else if($(this).data("action") == 'lead'){
                replyFunction.saveReplyLead($(this));
            }
        });
    }

    $(function() {
        $("#alert").fadeOut(5000);
        initializeDashboard();
    });

    function showReply(id) {
        $('#review_reply'+id).css('display', '');
        $('#link_reply'+id).css('display', 'none');
        $('#cancel_reply'+id).css('display', '');
    }

    function hideReply(id) {
        $('#review_reply'+id).css('display', 'none');
        $('#link_reply'+id).css('display', '');
        $('#cancel_reply'+id).css('display', 'none');
    }

    function showLead(id) {
        $('#lead_reply'+id).css('display', '');
        $('#link_lead'+id).css('display', 'none');
        $('#cancel_lead'+id).css('display', '');
    }

    function hideLead(id) {
        $('#lead_reply'+id).css('display', 'none');
        $('#link_lead'+id).css('display', '');
        $('#cancel_lead'+id).css('display', 'none');
    }

    function saveReply(id) {
        $("#submitReply"+id).css("cursor", "default");
        $("#submitReply"+id).prop("disabled", "disabled");
        $("#submitReply"+id).html('<?=system_showText(LANG_WAITLOADING);?>');

        $.post("<?=DEFAULT_URL."/".MEMBERS_ALIAS."/ajax.php"?>", $("#formReply"+id).serialize(), function(data) {
            if ($.trim(data) == "ok") {
                $("#msgReviewE"+id).css("display", "none");
                $("#msgReviewS"+id).css("display", "");
                $("#msgReviewS"+id).fadeOut(5000);
            } else {
                $("#msgReviewE"+id).css("display", "");
                $("#msgReviewS"+id).css("display", "none");
            }
            $("#submitReply"+id).html('<?=system_showText(LANG_BUTTON_SUBMIT);?>');
            $("#submitReply"+id).prop("disabled", "");
            $("#submitReply"+id).css("cursor", "pointer");
        });
    }

    function saveLead(id) {
        $("#submitLead"+id).css("cursor", "default");
        $("#submitLead"+id).prop("disabled", "disabled");
        $("#submitLead"+id).html('<?=system_showText(LANG_WAITLOADING);?>');

        $.post("<?=DEFAULT_URL."/".MEMBERS_ALIAS."/ajax.php"?>", $("#formLead"+id).serialize(), function(data) {
            if ($.trim(data) == "ok") {
                $("#msgLeadE"+id).css("display", "none");
                $("#msgLeadS"+id).css("display", "");
                $("#msgLeadS"+id).fadeOut(5000);
                setTimeout("leadBox('hide', "+id+");", 4000);
                $("#title_replied"+id).css("display", "none");
                $("#new_replied"+id).css("display", "");
            } else {
                $("#msgLeadE"+id).html(data);
                $("#msgLeadE"+id).css("display", "");
                $("#msgLeadS"+id).css("display", "none");
            }
            $("#submitLead"+id).html('<?=system_showText(LANG_BUTTON_SUBMIT);?>');
            $("#submitLead"+id).prop("disabled", "");
            $("#submitLead"+id).css("cursor", "pointer");
        });
    }

    function reviewBox(option, id) {
        $("#reviews-list").children(".item-review").children(".review-detail").stop(true,true).slideUp();
        $("#reviews-list").children(".item-review").children(".review-summary").stop(true,true).slideDown().removeClass("new");
        if (option == "show") {
            $("#review-summary-"+id).slideUp();
            $("#review-detail-"+id).slideDown();
            setItemAsViewed("review", id);
        } else {
            $("#review-summary-"+id).slideDown();
            $("#review-detail-"+id).slideUp();
        }
    }

    <? /* ModStores Hooks */ ?>
    <? if(!HookFire("scripts_enhanced_leads")) { ?>
        function leadBox(option, id) {
            $("#leads-list").children(".item-review").children(".review-detail").stop(true,true).slideUp();
            $("#leads-list").children(".item-review").children(".review-summary").stop(true,true).slideDown().removeClass("new");
            if (option == "show") {
                $("#lead-summary-"+id).slideUp();
                $("#lead-detail-"+id).slideDown();
                setItemAsViewed("lead", id);
            } else {
                $("#lead-summary-"+id).slideDown();
                $("#lead-detail-"+id).slideUp();
            }
        }
    <? } ?>

    function dealBox(option, id) {
        $("#deals-list").children(".item-review").children(".review-detail").stop(true,true).slideUp();
        $("#deals-list").children(".item-review").children(".review-summary").stop(true,true).slideDown();
        if (option == "show") {
            $("#deal-summary-"+id).slideUp();
            $("#deal-detail-"+id).slideDown();
        } else {
            $("#deal-summary-"+id).slideDown();
            $("#deal-detail-"+id).slideUp();
        }
    }

    function changeDealStatus(option, id, promocode) {
        $.post("<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/<?=PROMOTION_FEATURE_FOLDER?>/deal.php",{action: option, promotion_id: promocode}, function() {
            if (option == "freeUpDeal") {
                $("#label_used"+id).css("display", "");
            } else {
                $("#label_used"+id).css("display", "none");
            }
        });
    }

    function setItemAsViewed(type, id) {
        $.post("<?=DEFAULT_URL."/".MEMBERS_ALIAS."/ajax.php"?>", {
            ajax_type: 'setItemAsViewed',
            type: type,
            id: id
        }, function () {});
    }

    function loadDashboard(item_type, item_id) {
        $.post("<?=DEFAULT_URL."/".MEMBERS_ALIAS."/ajax.php"?>", {
            ajax_type: 'load_dashboard',
            item_type: item_type,
            item_id: item_id
        }, function (ret) {
            $(".members-item").attr("is-active","false");
            $("#"+item_type+"_"+item_id).attr("is-active","true");
            scrollPage('.members-page');
            $("#dashboard").hide().html(ret).fadeIn(400);
            initializeDashboard();
        });
    }

    <? /* ModStores Hooks */ ?>
    <? HookFire("scripts_enhanced_lead_functions", [
        "screen" => &$screen,
        "letter" => &$letter
    ]); ?>

    function selectLegend(option, id, chartdata) {
        var countVisible = 0;

        if (option == "viewALL") {
                countVisible = 2;
            $("#optionLegend .chart-item").addClass("checked");
            $(".legend-ALL").addClass("is-visible");
            $("#optionLegend > .chart-item").not(".item-view-all").not(".is-visible").clone().appendTo("#controlLegend");
            $("#optionLegend > .chart-item").addClass("is-visible");
        } else {
            id: id
            chartdata: chartdata
            $newlegend = $(".legend-"+id).clone();

            if ($(".legend-"+id).hasClass("is-visible")) {

                //Check if there's at least one other legend selected to prevent empty chart
                $('#optionLegend .chart-item').each(function() {
                    if ($(this).hasClass("is-visible")) {
                        countVisible++;
                    }
                });

                if (countVisible > 1) {
                    $(".legend-"+id).removeClass("checked");
                    $(".legend-"+id).removeClass("is-visible");
                    $("#controlLegend").children(".legend-"+id).remove();
                    $(".legend-ALL").removeClass("checked");
                    $(".legend-ALL").removeClass("is-visible");
                }
            } else {
                countVisible = 2;
                $newlegend.appendTo("#controlLegend");
                $(".legend-"+id).addClass("checked");
                $(".legend-"+id).addClass("is-visible");
            }
        }
        if (countVisible > 1) {
            controlChart();
        }
    }

    function loadChart() {
        var data = {
            labels : chartLabels,
            datasets : initialReport
        };
        var steps = 5;
        var max = maxInitialReport;
        if (max < steps) {
            steps = max;
        }
        var options = {
            bezierCurve : false,
            scaleOverride: true,
            scaleSteps: steps,
            scaleStepWidth: Math.ceil(max / steps),
            scaleStartValue: 0,
        };
        new Chart(ctx).Line(data, options);
    }

    function controlChart() {

        var datasets = new Array();
        var max = 0;
        var thisHighest = 0;
        $('#optionLegend .chart-item').each(function() {
            if ($(this).hasClass("is-visible")) {
                var reportType = $(this).attr('report');
                if (reportType) {
                    datasets.push(window[reportType]);
                    thisHighest = Math.max.apply(Math, window[reportType].data);
                    if (thisHighest > max) {
                        max = thisHighest;
                    }
                }
            }
        });

        var steps = 5;
        if (max < steps) {
            steps = max;
        }
        var options = {
            bezierCurve : false,
            scaleOverride: true,
            scaleSteps: steps,
            scaleStepWidth: Math.ceil(max / steps),
            scaleStartValue: 0
        };

        var data = {
            labels : chartLabels,
            datasets : datasets
        };
        new Chart(ctx).Line(data, options);

    }

    function deleteItem(pText, pId, pForm) {
        bootbox.confirm(pText, function(result) {
            if (result) {
                $("input[name='hiddenValue']").attr('value', pId);
                document.getElementById(pForm).submit();
            }
        });
    }
</script>