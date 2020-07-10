$(function () {
    var Navigation = {
        showPane: function (paneId, panels) {
            for (var i = 0; i < panels.length; i++) {
                var pane = panels[i];

                if (pane.id === paneId) pane.classList.remove('hide');
                else pane.classList.add('hide');
            }
        }
    };

    var Location = {
        get: function (id, level, childLevel, callback) {
            $.get('/location.php', {
                type: 'byId',
                level: level,
                childLevel: childLevel,
                id: id,
                format: 'json'
            }, callback);
        },
        reset: function ($el) {
            var childLevel = $el.data('location-child-level');

            if (childLevel) {
                var $child = $('select[data-location-level="' + childLevel + '"]');
                $child[0].selectize.clearOptions();

                if ($child.data('location-child-level')) {
                    this.reset($child);
                }
            }
        }
    };

    var Validation = {
        validate: function (form) {
            var isValid = true;
            var requiredInputs = form.querySelectorAll('[required]');
            var widgetRuleType = $('input:radio[name=widget_rule_type]');
            var rulesChecked = widgetRuleType.is(':checked');

            if (rulesChecked === false && $('#card_module').val() !== '') {
                $('#widget_rule_type_alert').fadeIn('fast');

                isValid = false;
            }

            for (var i = 0; i < requiredInputs.length; i++) {
                var input = requiredInputs[i];
                var id = input.id;
                var $input = $('#' + id);
                var value = input.value.trim();
                var hide = $input.parents('.form-group').hasClass('hide');

                if (!hide && (value === null || value === '')) {
                    if ($input.parents('.selectize-input').length > 0) {
                        $input.parents('.selectize-input').addClass('has-error');
                    } else if ($input.siblings('.selectize-control').length > 0) {
                        $input.siblings('.selectize-control').find('.selectize-input').addClass('has-error');
                    } else {
                        $input.addClass('has-error');
                    }

                    isValid = false;
                    continue;
                }

                if ($input.parents('.selectize-input').length > 0) {
                    $input.parents('.selectize-input').removeClass('has-error');
                } else if ($input.siblings('.selectize-control').length > 0) {
                    $input.siblings('.selectize-control').find('.selectize-input').removeClass('has-error');
                } else {
                    $input.removeClass('has-error');
                }
            }

            var cardItensCount = $('#card_itens_count');

            var widgetRuleTypeValue = $('input:radio[name=widget_rule_type]:checked').val();

            if (widgetRuleTypeValue === 'custom') {
                if (cardItensCount.val() < 1)
                {
                    cardItensCount.addClass('has-error');
                    isValid = false;
                } else {
                    cardItensCount.removeClass('has-error');
                }
            } else if (widgetRuleTypeValue === 'individual') {
                if ($('.itemTitle').length === 0) {
                    $('#add-item-div').addClass('has-error');
                    isValid = false;
                } else {
                    $('.itemCard').each(function () {
                        if($(this).find('.itemTitle').val() === '') {
                            $(this).find('.card-item').addClass('has-error');
                            isValid = false;
                        }
                    });
                }
            }

            return isValid;
        }
    };

    var inputConfigs = {
        highlight: true,
        hint: false,
        minLength: 2,
        tabAutocomplete: false,
        classNames: {
            input: "tt-input",
            hint: "tt-hint",
            menu: "tt-menu",
            dataset: "tt-dataset",
            suggestion: "tt-suggestion",
            empty: "tt-empty",
            open: "tt-open",
            cursor: "tt-cursor",
            highlight: "tt-highlight"
        }
    };

    var itemSuggester = null;

    $(document).on('click','.add-card', function () {
        var form = document.querySelector('#widget-card-form');

        var $el = $(this);
        var content = $el.data('widget-content');

        $('#openWidgetId').val($el.data('widget-id'));
        $('#add-new-widget-modal').find('.tab-content').addClass('tab-content-opened');

        $('<input>', {
            type: 'hidden',
            id: 'card_type',
            name: 'card_type',
            value: content.cardType
        }).appendTo(form);

        if (content.banner !== false) {
            $('#banner_input_wrapper').removeClass('hide');
        }

        if (content.columns > 0) {
            $('#columns_input_wrapper select[name=\'card_columns\'] option').val(content.columns);
        } else {
            $('#columns_input_wrapper').removeClass('hide');
        }

        if (content.custom.quantity > 0) {
            $('#card_itens_count').val(content.custom.quantity);
        } else {
            $('#itens_count_input_wrapper').removeClass('hide');
        }

        if (content.cardType === 'horizontal-cards' || content.cardType === 'vertical-cards') {
            $('#banner_input_wrapper, #columns_input_wrapper, #itens_count_input_wrapper').removeClass('hide');
        } else {
            $('#banner_input_wrapper, #columns_input_wrapper, #itens_count_input_wrapper').addClass('hide');
        }

        if (content.cardType === 'horizontal-cards') {
            var selectColumns = $('#card_columns');
            selectColumns[0].selectize.removeOption('4');
        }

        var panels = document.querySelectorAll('.card-panel');

        Navigation.showPane('card-config-pane', panels);
    });

    $(document).on('click', '#open_category_tree', function () {
        var url = $(this).data('remote');
        var module = $('#card_module').val();

        $.get(url, {module: module}, function (response) {
            $('body').append(response);
        });
    });

    $(document).on('change','#card_link_page_id', function () {
        if (this.value === 'custom') {
            $('#custom-link-div').removeClass('hide');
            $('#custom_link').attr('required', 'required');
        } else {
            $('#custom-link-div').addClass('hide');
            $('#custom_link').removeAttr('required');
        }
    });

    $(document).on('change','input[name="widget_rule_type"]', function () {
        $('#widget_rule_type_alert').fadeOut('fast');
        if (this.value === 'individual') {
            $('#add-new-widget-modal .modal-dialog').addClass('modal-dialog-card');
            $('#card-pick-itens-pane').removeClass('hide');
            $('#card-custom-rules-pane').addClass('hide');

            $(document).find("#sortableItem").sortable({
                items: "> .itemCard"
            });

            $('.card-item .itemTitle').each(function () {
                $(this).data("prefill", 0);
                var itemDatasetConfigs = {
                    source: eDirectory.Search.Utility.createBloodhound(Routing.generate('search_suggest_card'), $('#card_module').val()),
                    async: true,
                    name: 'item',
                    displayKey: 'text',
                    limit: 10
                };

                itemSuggester = new eDirectory.Search.Suggest($(this), itemDatasetConfigs, inputConfigs, null, false);
                itemSuggester.initialize();
            });
            $('#card_order1, #card_itens_count').removeAttr('required');
        } else if (this.value === 'custom') {
            $('#add-new-widget-modal .modal-dialog').removeClass('modal-dialog-card');
            $('#card-pick-itens-pane').addClass('hide');
            $('#card-custom-rules-pane').removeClass('hide');
            $('#card_order1').attr('required', 'required');
            if($('#itemEditable').val()) {
                $('#card_itens_count').attr('required', 'required');
            }
        }
    });

    $(document).on('change', '#card_module', function () {
        $('.itemCard').each(function () {
            $(this).remove();
        });

        var selectedModule = this.value;

        switch (selectedModule) {
            case 'blog':
                $('#itens_filter_location_wrapper').addClass('hide');
            case 'article':
                $('#itens_filter_location_wrapper').addClass('hide');
            case 'promotion':
                $('#card-custom-rules-form').addClass('hide');
                break;
            default:
                $('#card-custom-rules-form').removeClass('hide');
                $('#itens_filter_location_wrapper').removeClass('hide');
                break;
        }

        var selectOrder1 = $('#card_order1');
        var selectOrder2 = $('#card_order2');

        if (selectedModule === 'listing') {
            if (selectOrder1[0].selectize.options['avg_reviews'] === undefined) {
                selectOrder1[0].selectize.addOption({
                    value: 'avg_reviews',
                    text: LANG_JS_AVERAGEREVIEWS,
                    name: LANG_JS_AVERAGEREVIEWS
                });
            }
            if (selectOrder2[0].selectize.options['avg_reviews'] === undefined) {
                selectOrder2[0].selectize.addOption({
                    value: 'avg_reviews',
                    text: LANG_JS_AVERAGEREVIEWS,
                    name: LANG_JS_AVERAGEREVIEWS
                });
            }
        } else {
            selectOrder1[0].selectize.removeOption('avg_reviews');
            selectOrder2[0].selectize.removeOption('avg_reviews');
        }

        if (selectedModule === 'listing' || selectedModule === 'event' || selectedModule === 'classified') {
            if (selectOrder1[0].selectize.options['level'] === undefined) {
                selectOrder1[0].selectize.addOption({value: 'level', text: LANG_JS_LEVEL, name: LANG_JS_LEVEL});
            }
            if (selectOrder2[0].selectize.options['level'] === undefined) {
                selectOrder2[0].selectize.addOption({value: 'level', text: LANG_JS_LEVEL, name: LANG_JS_LEVEL});
            }
        } else {
            selectOrder1[0].selectize.removeOption('level');
            selectOrder2[0].selectize.removeOption('level');
        }

        if (selectedModule === 'event') {
            if (selectOrder1[0].selectize.options['upcoming'] === undefined) {
                selectOrder1[0].selectize.addOption({
                    value: 'upcoming',
                    text: LANG_JS_UPCOMING,
                    name: LANG_JS_UPCOMING
                });
            }
            if (selectOrder2[0].selectize.options['upcoming'] === undefined) {
                selectOrder2[0].selectize.addOption({
                    value: 'upcoming',
                    text: LANG_JS_UPCOMING,
                    name: LANG_JS_UPCOMING
                });
            }
        } else {
            selectOrder1[0].selectize.removeOption('upcoming');
            selectOrder2[0].selectize.removeOption('upcoming');
        }

        var $inputs = $('[data-item-type]');
        $inputs.addClass('hide');
        $inputs.each(function (i, el) {
            var $el = $(el);
            if ($el.data('item-type') === selectedModule) {
                $el.removeClass('hide');
            }
        });

        $inputs = $('div[data-category-select]');
        $inputs.addClass('hide');
        $inputs.each(function (i, el) {
            var $el = $(el);
            if ($el.data('category-select') === selectedModule) {
                $el.removeClass('hide');
            }
        });
        if (selectedModule != '') {
            var widgetRule = $('#widget-rule-div');
            widgetRule.removeClass('hide');
        } else {
            $('#widget-rule-div').addClass('hide');
            $('#card-pick-itens-pane').addClass('hide');
            $('#card-custom-rules-pane').addClass('hide');
        }

        if($(this).val() !== '') {
            this.parentElement.querySelector('.selectize-input').classList.remove('has-error');
        } else {
            this.parentElement.querySelector('.selectize-input').classList.add('has-error');
        }
    });

    $(document).on('change','select[data-location-level]', function () {
        var $el = $(this);
        var id = $el.val();
        var childLevel = $el.data('location-child-level');

        if (!id) {
            $('[data-location-level="' + childLevel + '"]').addClass('hide');
            return Location.reset($el);
        }

        if (!childLevel) {
            return;
        }

        Location.get(id, $el.data('location-level'), childLevel, function (response) {
            var $child = $('select[data-location-level="' + childLevel + '"]');
            var selectize = $child[0].selectize;

            selectize.clearOptions();

            response.forEach(function (location) {
                selectize.addOption({value: location.id, text: location.name});
            });

            if (response.length === 0) {
                $('[data-location-level="' + childLevel + '"]').addClass('hide');
                while (childLevel < 5) {
                    childLevel++;
                    $('[data-location-level="' + childLevel + '"]').addClass('hide');
                }
            } else {
                $('[data-location-level="' + childLevel + '"]').removeClass('hide');

            }
        });
    });

    $(document).on('blur', '#custom_link', function () {
        var type = $('input:checked[name="custom_link_type"]').val();
        var input = $(this),
            val = $.trim(input.val());
        if (val && !val.match(/^http([s]?):\/\/.*/) && type === "external") {
            val = 'http://' + val;
            input.val(val.trim());
        }
        if (val && type === "internal") {
            easyFriendlyUrl(val, 'custom_link', 'a-zA-Z0-9/.:', '-');
        }
    });

    $(document).on('change', 'input[name="custom_link_type"]', function () {
        var type = $(this).val();
        var modal = $(this).data('modalaux');
        var input = $('#custom_link[data-modalaux=\'' + modal + '\']'),
            val = input.val();
        var cardInternalType = $('#cardInternalValue[data-modalaux=\'' + modal + '\']');
        var cardExternalType = $('#cardExternalValue[data-modalaux=\'' + modal + '\']');
        var cardInternalValue = cardInternalType.val();
        var cardExternalValue = cardExternalType.val();

        if (type === "internal") {
            $('#custom-link-div .input-group-addon').removeClass('hide');
            $('#custom_link_div').addClass('input-group');
            cardExternalType.val(val);
            input.val(cardInternalValue);
        }
        if (type === "external") {
            $('#custom-link-div .input-group-addon').addClass('hide');
            $('#custom_link_div').removeClass('input-group');
            cardInternalType.val(val);
            input.val(cardExternalValue);
        }
    });

    $(document).on('click', '.removeItem', function () {
        var divId = $(this).data('id');
        $('#' + divId).remove();
    });

    $(document).on('click','#bt_save_card', function () {
        var form = document.querySelector('#widget-card-form');

        if (Validation.validate(form)) {
            return saveWidget('card')
        } else {
            $('.tab-content').animate({
                scrollTop: $("#widget-card-form").offset().top
            }, 500);
            $('#edit-widget-modal').animate({
                scrollTop: $("#widget-card-form").offset().top
            }, 500);
        }
    });

    $(document).on('change','#card_order1', function () {
        var select = $('#card_order2');


        if ($(this).attr('data-lastoption') !== '') {
            select[0].selectize.addOption({
                value: $(this).attr('data-lastoption'),
                text: $(this).attr('data-lastlabel'),
                name: $(this).attr('data-lastlabel')
            });
        }

        select[0].selectize.removeOption(this.value);

        $(this).attr('data-lastoption', $(this).val());
        $(this).attr('data-lastlabel', ($('#card_order1_div .item').text()).trim());
    });

    $(document).on('change','#card_order2', function () {
        var select = $('#card_order1');

        if ($(this).attr('data-lastoption') !== '') {
            select[0].selectize.addOption({
                value: $(this).attr('data-lastoption'),
                text: $(this).attr('data-lastlabel'),
                name: $(this).attr('data-lastlabel')
            });
        }

        select[0].selectize.removeOption(this.value);

        $(this).attr('data-lastoption', $(this).val());
        $(this).attr('data-lastlabel', ($('#card_order2_div .item').text()).trim());
    });

    $(document).on('change','#link_label', function () {
        var pageLink = $("#card_link_page_id");
        var pageLinkLabel = $("label[for='card_link_page_id']");
        var pageLinkLabelText = pageLinkLabel.text();

        if ($(this).val() === '') {
            pageLinkLabel.text(pageLinkLabelText.replace(' *',''));
            pageLink.removeAttr('required');
            return;
        }

        if(pageLinkLabelText.indexOf(' *') === -1) {
            pageLinkLabel.text(pageLinkLabelText + ' *');
            pageLink.attr('required', 'required');
        }
    });

    $(document).on('change','#widget_title', function () {
        if ($(this).val() !== '') {
            $(this).removeClass('has-error');
        } else {
            $(this).addClass('has-error');
        }
    });

    $(document).on('change','#card_columns, #card_order1', function () {
        if($(this).val() !== '') {
            this.parentElement.querySelector('.selectize-input').classList.remove('has-error');
        } else {
            this.parentElement.querySelector('.selectize-input').classList.add('has-error');
        }
    });

    $(document).on('change','#card_itens_count', function () {
        if ($(this).val() > 0) {
            $(this).removeClass('has-error');
        } else {
            $(this).addClass('has-error');
        }
    });
});
