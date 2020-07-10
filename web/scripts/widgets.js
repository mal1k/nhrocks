function serialize() {
    var listElement = $("#sortWidgets").sortable("toArray"),
        content = [];

    listElement.forEach(function (element) {
        var inputs = {};
        $("div #" + element).find('input').each(function () {
            inputs[this.name] = this.value;
        });
        content.push(inputs);
    });

    $("#serializedPost").val(JSON.stringify(content));
}

function JS_widget_submit() {
    serialize();
    $('#form_widgets').find('[type="submit"]').trigger('click');
}

function resetSaveButton() {
    var btn = $('.action-save');
    btn.button('reset');
}

function widgetActionsAjax(url, type, data, contentType, loadAnimation) {
    if (loadAnimation || loadAnimation === undefined) {
        $('#loading_ajax').fadeIn('fast');
    }

    var options = {
        url: url,
        data: data,
        cache: false,
        processData: false,
        type: type
    };

    if (contentType) {
        options.contentType = false;
    }

    return $.ajax(options).done(function () {
        if (loadAnimation || loadAnimation === undefined) {
            $('#loading_ajax').fadeOut('fast');
        }
    });
}

// Serialize form to array and stringify it if necessary
function serializeForm(form, stringify) {
    var serialized, removeIndexes = [];

    serialized = $("#" + form).serializeArray();

    serialized.forEach(function (element) {
        var genericInput = $('.genericInput[name="' + element.name + '"]');

        var label = genericInput.data('label');
        if(label !== undefined) {
            element.label = label;
        }

        var type = genericInput.data('type');
        if(type !== undefined) {
            element.type = type;

            if (type === 'link') {
                serialized.forEach(function (newElement, index) {
                    switch (newElement.name) {
                        case 'target':
                            element.target = newElement.value;
                            removeIndexes.push(index);
                            break;
                        case 'customLink':
                            element.customLink = newElement.value;
                            removeIndexes.push(index);
                            break;
                        case 'openWindow':
                            element.openWindow = newElement.value;
                            removeIndexes.push(index);
                            break;
                    }
                });

                for (var i = removeIndexes.length -1; i >= 0; i--)
                    serialized.splice(removeIndexes[i],1);
            }


        }
    });

    if (stringify) {
        serialized = JSON.stringify(serialized);
    }

    return serialized;
}

function serializeCardForm() {
    var $form = $('#widget-card-form');
    var data = new FormData($form[0]);

    var content = {
        cardType: data.get('card_type'),
        widgetTitle: data.get('widget_title'),
        widgetLink: {
            label: data.get('link_label'),
            page_id: data.get('card_link_page_id'),
            link: data.get('card_link_page_id') === 'custom' ? data.get('custom_link') : null
        },
        module: data.get('card_module'),
        banner: data.get('card_banner'),
        columns: data.get('card_columns'),
        backgroundColor: data.get('backgroundColor')
    };

    var rules = $('input[name=widget_rule_type]:checked').val();

    if (rules === 'custom') {
        content.custom = {
            level: data.getAll('card_levels[' + data.get('card_module') + '][]'),
            order1: data.get('card_order1'),
            order2: data.get('card_order2'),
            quantity: data.get('card_itens_count'),
            categories: data.getAll('card_categories[' + data.get('card_module') + ']'),
            locations: {
                location_1: data.get('location1'),
                location_2: data.get('location2'),
                location_3: data.get('location3'),
                location_4: data.get('location4'),
                location_5: data.get('location5')
            }
        };
    }

    if (rules === 'individual') {
        content.items = data.getAll('item_ids[]');
        if ($('#itemEditable').val() == '') {
            content.custom = {
                quantity: data.get('card_itens_count')
            };
        }
    }

    return JSON.stringify(content);
}

function saveWidget(modal, widget = '') {

    var error = false;

    if (widget == 'call-to-action') {

        var $inputButtonText = $('.form-call-to-action').find('#placeholderCallToAction');
        var $inputButtonLink = $('.form-call-to-action').find('#placeholderLink');
        var $inputCustomLink = $('.form-call-to-action').find('#customLink');
        var $inputTargetLink = $('.form-call-to-action').find('input:radio.linkTarget:checked');
        var $form = $('.form-call-to-action');

        $form.find('.alert').html('').removeClass('alert-danger').hide();

        if ($inputButtonText.val() !== '') {
            if (!($inputButtonLink.val())) {
                $form.find('.alert').html(LANG_JS_CALL_TO_ACTION_BUTTON_LINK_REQUIRED).addClass('alert-danger').show();
                error = true;
            }

            if ($inputButtonLink.val() === 'custom' && $inputTargetLink.val() === 'external' && !$inputCustomLink.val().match(/^http([s]?):\/\/.*/)) {
                var link = 'http://' + $inputCustomLink.val();
                $inputCustomLink.val(link);
            }

            if ($inputButtonLink.val() === 'custom' && $inputTargetLink.val() === 'external' && !($inputCustomLink.val().match(/((https?):\/\/)(www\.)?(?!(www\.))((?!\-\-)([^ .](?!\-\.)){1,}\.((?!\-\-)(?!\-\.)[^ /]){2,})(\:[^ .]*)?(\/[^ .]*)?(\?[^ .]*)?/))) {
                $form.find('.alert').html(LANG_JS_CALL_TO_ACTION_BUTTON_LINK_INVALID).addClass('alert-danger').show();
                error = true;
            }
        }

        if ($inputButtonLink.val() !== '') {
            if (!($inputButtonText.val())) {
                $form.find('.alert').html(LANG_JS_CALL_TO_ACTION_BUTTON_TEXT_REQUIRED).addClass('alert-danger').show();
                error = true;
            }
        }

    } else if (widget == 'download-our-apps-bar') {

        var $inputLabelAvailablePlayStore = $('.form-download-our-apps-bar').find('#labelAvailablePlayStore');
        var $inputLinkPlayStore = $('.form-download-our-apps-bar').find('#linkPlayStore');

        var $inputLabelAvailableAppleStore = $('.form-download-our-apps-bar').find('#labelAvailableAppleStore');
        var $inputLinkAppleStore = $('.form-download-our-apps-bar').find('#linkAppleStore');

        var $form = $('.form-download-our-apps-bar');

        $form.find('.alert').html('').removeClass('alert-danger').hide();

        if ($inputLabelAvailablePlayStore.val() !== '') {
            if (!($inputLinkPlayStore.val())) {
                $form.find('.alert').html(LANG_JS_PLAYSTORE_LINK_REQUIRED).addClass('alert-danger').show();
                error = true;
            }

            if (!$inputLinkPlayStore.val().match(/^http([s]?):\/\/.*/)) {
                var link = 'http://' + $inputLinkPlayStore.val();
                $inputLinkPlayStore.val(link);
            }

            if (!($inputLinkPlayStore.val().match(/((https?):\/\/)(www\.)?(?!(www\.))((?!\-\-)([^ .](?!\-\.)){1,}\.((?!\-\-)(?!\-\.)[^ /]){2,})(\:[^ .]*)?(\/[^ .]*)?(\?[^ .]*)?/))) {
                $form.find('.alert').html(LANG_JS_PLAYSTORE_LINK_INVALID).addClass('alert-danger').show();
                error = true;
            }
        }

        if ($inputLinkPlayStore.val() !== '') {
            if (!($inputLabelAvailablePlayStore.val())) {
                $form.find('.alert').html(LANG_JS_PLAYSTORE_TEXT_REQUIRED).addClass('alert-danger').show();
                error = true;
            }
        }

        if ($inputLabelAvailableAppleStore.val() !== '') {
            if (!($inputLinkAppleStore.val())) {
                $form.find('.alert').html(LANG_JS_IOS_LINK_REQUIRED).addClass('alert-danger').show();
                error = true;
            }

            if (!$inputLinkAppleStore.val().match(/^http([s]?):\/\/.*/)) {
                var link = 'http://' + $inputLinkAppleStore.val();
                $inputLinkAppleStore.val(link);
            }

            if (!($inputLinkAppleStore.val().match(/((https?):\/\/)(www\.)?(?!(www\.))((?!\-\-)([^ .](?!\-\.)){1,}\.((?!\-\-)(?!\-\.)[^ /]){2,})(\:[^ .]*)?(\/[^ .]*)?(\?[^ .]*)?/))) {
                $form.find('.alert').html(LANG_JS_IOS_LINK_INVALID).addClass('alert-danger').show();
                error = true;
            }
        }

        if ($inputLinkAppleStore.val() !== '') {
            if (!($inputLabelAvailableAppleStore.val())) {
                $form.find('.alert').html(LANG_JS_IOS_TEXT_REQUIRED).addClass('alert-danger').show();
                error = true;
            }
        }
    } else if (widget == 'footer') {

        var $inputPlayStoreLabel = $('.form-footer').find('#playStoreLabel');
        var $inputLinkPlayStore = $('.form-footer').find('#linkPlayStore');

        var $inputAppStoreLabel = $('.form-footer').find('#AppStoreLabel');
        var $inputLinkAppleStore = $('.form-footer').find('#linkAppleStore');

        var $form = $('.form-footer');

        $form.find('.alert').html('').removeClass('alert-danger').hide();

        if ($inputPlayStoreLabel.val() !== '') {
            if (!($inputLinkPlayStore.val())) {
                $form.find('.alert').html(LANG_JS_PLAYSTORE_LINK_REQUIRED).addClass('alert-danger').show();
                error = true;
            }

            if (!$inputLinkPlayStore.val().match(/^http([s]?):\/\/.*/)) {
                var link = 'http://' + $inputLinkPlayStore.val();
                $inputLinkPlayStore.val(link);
            }

            if (!($inputLinkPlayStore.val().match(/((https?):\/\/)(www\.)?(?!(www\.))((?!\-\-)([^ .](?!\-\.)){1,}\.((?!\-\-)(?!\-\.)[^ /]){2,})(\:[^ .]*)?(\/[^ .]*)?(\?[^ .]*)?/))) {
                $form.find('.alert').html(LANG_JS_PLAYSTORE_LINK_INVALID).addClass('alert-danger').show();
                error = true;
            }
        }

        if ($inputLinkPlayStore.val() !== '') {
            if (!($inputPlayStoreLabel.val())) {
                $form.find('.alert').html(LANG_JS_PLAYSTORE_TEXT_REQUIRED).addClass('alert-danger').show();
                error = true;
            }
        }

        if ($inputAppStoreLabel.val() !== '') {
            if (!($inputLinkAppleStore.val())) {
                $form.find('.alert').html(LANG_JS_IOS_LINK_REQUIRED).addClass('alert-danger').show();
                error = true;
            }

            if (!$inputLinkAppleStore.val().match(/^http([s]?):\/\/.*/)) {
                var link = 'http://' + $inputLinkAppleStore.val();
                $inputLinkAppleStore.val(link);
            }

            if (!($inputLinkAppleStore.val().match(/((https?):\/\/)(www\.)?(?!(www\.))((?!\-\-)([^ .](?!\-\.)){1,}\.((?!\-\-)(?!\-\.)[^ /]){2,})(\:[^ .]*)?(\/[^ .]*)?(\?[^ .]*)?/))) {
                $form.find('.alert').html(LANG_JS_IOS_LINK_INVALID).addClass('alert-danger').show();
                error = true;
            }
        }

        if ($inputLinkAppleStore.val() !== '') {
            if (!($inputAppStoreLabel.val())) {
                $form.find('.alert').html(LANG_JS_IOS_TEXT_REQUIRED).addClass('alert-danger').show();
                error = true;
            }
        }
    }

    var modalOpen = $('.wysiwyg.in');
    var backgroundColor = modalOpen.find("input:radio[name=backgroundColor]");

    if(backgroundColor.length){
        if(modalOpen.find("input:radio[name=backgroundColor]:checked").length === 0) {
            modalOpen.find('.alert').first().html(LANG_JS_BACKGROUND_COLOR_REQUIRED).addClass('alert-danger').show();
            error = true;
        }
    }

    if(error){
        $('.modal').animate({
            scrollTop: $(".alert-danger").offset().top
        }, 500);
        return false;
    }

    var serializedContent = "", serializedNavbar = "", serializedSocialLinks = "", customHtml = "",
        pageId = $('#pageId').val(), widgetId = $('#openWidgetId').val(), divId = $.widgetDivId,
        fileFav = document.getElementById('favIconInput') ? document.getElementById('favIconInput').files[0] : '',
        fileLogo = document.getElementById('logoImageInput') ? document.getElementById('logoImageInput').files[0] : '',
        fileImageSearch = document.getElementById('bgImageInput') ? document.getElementById('bgImageInput').files[0] : '',
        fileGenericImage = document.getElementById('bgImageGenericInput') ? document.getElementById('bgImageGenericInput').files[0] : '',
        tempWidgetId = document.getElementById('tempWidgetId') ? $('#tempWidgetId').val() : '',
        selectedDomainId = $('#selectedDomainId').val(),
        pageWidgetId = $('#pageWidgetId').val(),
        // REQUEST INFO
        url = "../../../includes/code/widgetActionAjax.php",
        type = "POST";

    if (modal == 'generic') {
        serializedContent = serializeForm('form_generic', true);
    } else if (modal == 'header' || modal == 'header-with-phone') {

        if (!isNavigationValid('form_navigation')) {
            return false;
        }

        serializedContent = serializeForm('form_header', false);
        serializedNavbar = serializeForm('form_navigation', true);
        serializedSocialLinks = serializeForm('form_social_header', true);
        $.each(serializeForm('datainfoDivHeader', false), function (index, obj) {
            serializedContent.push({name: obj.name, value: obj.value});
        });

        serializedContent = JSON.stringify(serializedContent);

        if (!isContentValid(serializedContent)) {
            //return false;
        }
    } else if (modal == 'footer' || modal == 'footer-with-social-media' || modal == 'footer-with-logo' || modal == 'footer-with-newsletter') {
        if (!isNavigationValid('form_navigation_footer')) {
            return false;
        }

        serializedContent = serializeForm('form_footer', false);
        if (modal == 'footer' || modal == 'footer-with-logo') {
            serializedNavbar = serializeForm('form_navigation_footer', true);
        }
        serializedSocialLinks = serializeForm('form_social', true);
        $.each(serializeForm('form_mobile', false), function (index, obj) {
            serializedContent.push({name: obj.name, value: obj.value});
        });
        $.each(serializeForm('form_newsletter_footer', false), function (index, obj) {
            serializedContent.push({name: obj.name, value: obj.value});
        });
        $.each(serializeForm('datainfoDivFooter', false), function (index, obj) {
            serializedContent.push({name: obj.name, value: obj.value});
        });

        serializedContent = JSON.stringify(serializedContent);
    } else if (modal == 'downloadapp') {
        serializedContent = serializeForm('form_downloadapp', false);
        serializedContent.push({
            name: 'checkboxOpenWindow',
            value: $("#checkboxOpenWindow").is(':checked') ? '_blank' : ''
        });
        serializedContent = JSON.stringify(serializedContent);
    } else if (modal == 'search') {
        serializedContent = serializeForm('form_search', true);
    } else if (modal == 'genericimage') {
        serializedContent = serializeForm('form_genericimage', true);
    } else if (modal == 'contactform') {
        serializedContent = serializeForm('form_contactform', true);
    } else if (modal == 'moduleprices') {
        serializedContent = serializeForm('form_moduleprices', true);
    } else if (modal == 'customcontent') {
        serializedContent = serializeForm('form_customcontent', true);
        CKEDITOR.instances.customHtml.updateElement();
        customHtml = CKEDITOR.instances.customHtml.getData();
    } else if (modal === 'card') {
        serializedContent = serializeCardForm();
    }

    var data = new FormData();
    data.append('favicon_file', fileFav);
    data.append('header_image', fileLogo);
    data.append('contentArr', serializedContent);
    data.append('navbarArr', serializedNavbar);
    data.append('modal', modal);
    data.append('socialLinks', serializedSocialLinks);
    data.append('background_image', fileImageSearch);
    data.append('background_image_generic', fileGenericImage);
    data.append('customHtml', customHtml);
    data.append('pageId', pageId);
    data.append('widgetId', widgetId);
    data.append('domain_id', selectedDomainId);
    data.append('pageWidgetId', pageWidgetId);
    data.append('tempWidgetId', tempWidgetId);

    var request = widgetActionsAjax(url, type, data, true);

    request.done(function (data) {
        var objData = jQuery.parseJSON(data), rand = Math.floor((Math.random() * 10) + 1),
            msgSuccess = '', msgError = '', msgErrorAux = '', successAlert = $('#successAlert'),
            errorAlert = $('#errorAlert');

        if (objData.success) {
            msgSuccess = objData.message;
        }

        if (objData.favicon && objData.favicon.success) {
            $("#favIconImg").attr('src', objData.favicon.url);
        }

        if (objData.logoImage && objData.logoImage.success) {
            $("#logoImage").attr('src', objData.logoImage.url + '?' + rand).show();
        }

        if (objData.bgImage && objData.bgImage.success) {
            $("#bgImage").attr('src', objData.bgImage.url + '?' + rand).show();
        }

        if (objData.errorMessage) {
            msgError = '<ul><li>';
            msgErrorAux = objData.errorMessage.join('</li><li>');
            msgError = msgError + msgErrorAux + '</li></ul>';
            errorAlert.children('div').html(msgError).alert();
            errorAlert.fadeTo(3000, 500).slideUp(500, function () {
                errorAlert.slideUp(500);
            });
        }

        if (msgSuccess) {
            successAlert.children('div').html(msgSuccess).alert();
            successAlert.fadeTo(3000, 500).slideUp(500, function () {
                successAlert.slideUp(500);
            });
        }

        var content = JSON.parse(serializedContent);

        if (objData.isNewWidget && objData.newWidgetId) {
            if (modal === 'card') {
                content.widgetPageId = objData.newWidgetId;

                addWidgetToDom(widgetId, content);
            } else {
                $('#' + divId + ' #pageWidgetIdInput').val(objData.newWidgetId);
                $('#' + divId + ' .edit-info').data('pagewidget', objData.newWidgetId);
                $('#' + divId + ' .editWidgetButton').data('pagewidget', objData.newWidgetId);
            }
        } else {
            $('#' + divId + ' [data-widget-title]').text(content.widgetTitle);
        }

        var addNewWidgetModal = $('#add-new-widget-modal');

        if ((addNewWidgetModal.data('bs.modal') || {}).isShown) {
            addNewWidgetModal.modal('toggle');
        } else {
            $('#edit-widget-modal').modal('toggle');
        }
    }).always(function () {
        resetSaveButton();
    });

    return serializedContent;
}

function saveSliderContent() {
    var serializedContent = serializeForm('form_slider', false),
        serializedSlider = serializeForm('form_slider_info', false),
        deletedSlides = $('#deletedSlides').val(),
        selectedDomainId = $('#selectedDomainId').val(),
        slidetype = $('#slidetype').val(),
        divId = $.widgetDivId,
        pageId = $('#pageId').val(),
        widgetId = $('#openWidgetId').val(),
        tempWidgetId = document.getElementById('tempWidgetId') ? $('#tempWidgetId').val() : '',
        // REQUEST INFO
        data = new FormData(),
        url = "../../../includes/code/widgetActionAjax.php", request, type = "POST";

    $.each(serializeForm('form_generic', false), function (index, obj) {
        serializedContent.push({name: obj.name, value: obj.value});
    });

    serializedContent = JSON.stringify(serializedContent);

    // Labels Info
    data.append('contentArr', serializedContent);
    // Slide ids to be deleted
    data.append('deletedSlides', deletedSlides);

    data.append('domain_id', selectedDomainId);
    data.append('pageId', pageId);
    data.append('widgetId', widgetId);
    data.append('slidetype', slidetype);
    data.append('tempWidgetId', tempWidgetId);

    // Get Each slide info
    var sliders = [];
    var sliderForm = '';
    var sliderError = [];
    var form = '';
    var navLink = '';
    var navCustomLink = '';
    var customLinkType = '';

    $.each(serializedSlider, function (index, slider) {
        sliderForm = 'form_sliderInfo' + slider.value;
        form = $('#' + sliderForm);

        if (slidetype == 'image' || slidetype === 'content') {
            if (form.find("input[name='imageId']").val() == '') {
                sliderError.push(LANG_JS_SLIDER_WITHOUT_IMAGE);
            }
            navLink = form.find(".navLink").val();
            navCustomLink = form.find(".sliderCustomLink");
            customLinkType = form.find('input[name=\'sliderCustomLinkType\']:checked');
            if (!(navCustomLink.val()) && navLink === "custom") {
                sliderError.push(LANG_JS_SLIDER_CUSTOM_LINK_EMPTY);
            }
            if (!(navCustomLink.val().match(/((https?):\/\/)(www\.)?(?!(www\.))((?!\-\-)([^ .](?!\-\.)){1,}\.((?!\-\-)(?!\-\.)[^ /]){2,})(\:[^ .]*)?(\/[^ .]*)?(\?[^ .]*)?/)) && customLinkType.val() === 'external') {
                sliderError.push(LANG_JS_NAVIGATION_VALID_EXTERNAL_LINK);
            }
        }
        sliders[index] = serializeForm(sliderForm, false);
    });

    if (sliderError.length > 0) {
        var messages = $('#messageAlertSlider').html('');
        var displayedMessages = [];

        var objModalDiv = $('#edit-widget-modal');

        /* Scroll to the top of modal */
        objModalDiv.animate({scrollTop: '0px'}, 500);

        $.each(sliderError, function (x, message) {
            if ($.inArray(message, displayedMessages) === -1) { // Prevents duplicated messages
                messages.append($('<div>').addClass('alert alert-danger').html(message));
                displayedMessages.push(message);
            }
        });

        messages.fadeIn('slow', function () {
            $(this).fadeTo(3000, 500).slideUp(500, function () {
                messages.slideUp(500);
            });
        });

        // Restore buttons
        resetSaveButton();
        var newSliderButton = $('.createSliderItem');
        if ($('#sortableSlider').find('li.slideLi').size() < newSliderButton.data('maxslides')) {
            newSliderButton.fadeIn('slow');
        }

        return true;
    }

    data.append('sliderJson', JSON.stringify(sliders));

    request = widgetActionsAjax(url, type, data, true);

    request.done(function (data) {
        var objData = jQuery.parseJSON(data),
            msgSuccess = '', msgError = '', msgErrorAux = '', successAlert = $('#successAlert'),
            errorAlert = $('#errorAlert');

        if (objData.success) {
            msgSuccess = objData.message;
        }

        if (objData.errorMessage) {
            msgError = '<ul><li>';
            msgErrorAux = objData.errorMessage.join('</li><li>');
            msgError = msgError + msgErrorAux + '</li></ul>';
            errorAlert.children('div').html(msgError).alert();
            errorAlert.fadeTo(3000, 500).slideUp(500, function () {
                errorAlert.slideUp(500);
            });
        }

        if (msgSuccess) {
            successAlert.children('div').html(msgSuccess).alert();
            successAlert.fadeTo(3000, 500).slideUp(500, function () {
                successAlert.slideUp(500);
            });
        }

        if (objData.isNewWidget && objData.newWidgetId) {
            $('#' + divId + ' #pageWidgetIdInput').val(objData.newWidgetId);
            $('#' + divId + ' .edit-info').data('pagewidget', objData.newWidgetId);
            $('#' + divId + ' .editWidgetButton').data('pagewidget', objData.newWidgetId);
        }

        $('#edit-widget-modal').modal('toggle');
    }).always(function () {
        resetSaveButton();
    });
}

/*
 * NAVIGATION JS
 */

function addListeners() {
    $('.removeNavItem').click(function () {
        var divId = $(this).data('id');
        $('#' + divId).remove();
    });
}

function resetNavigation(modal) {
    var url = "../../../includes/code/widgetNavigationAjax.php",
        selectedDomainId = $('#selectedDomainId').val(),
        type = "GET",
        data = "area=" + modal + "&reset=1" + "&domain_id=" + selectedDomainId;

    var request = widgetActionsAjax(url, type, data, false);

    request.done(function (data) {
        $('#sortableNav').html(data);
        addListeners();
    });
}

function isContentValid(content) {
    var error = false;

    $.each(JSON.parse(content), function (index, obj) {
        if (obj.value === '' && obj.name !== 'pageWidgetId') {
            error = LANG_JS_NAVIGATION_LOGIN_LABEL_EMPTY;
        }
    });

    if (error) {
        var objModalDiv = $('#edit-widget-modal');

        /* Scroll to the top of modal */
        objModalDiv.animate({scrollTop: '0px'}, 500);

        /* reset the save button */
        setTimeout(function () {
            objModalDiv.find('.action-save').button('reset');
        }, 500);

        /* Show the error message */
        var messages = objModalDiv.find('div#messageAlertHeader');
        messages.find('div').text(error)
            .parent('div').addClass('alert-danger').show();

    }

    return !error;
}

function isNavigationValid(form) {
    var error = false,
        formNavigation = $('#edit-widget-modal').find('#' + form);

    /** Validates empty labels */
    $.each(formNavigation.find('.navTitle'), function (i, item) {
        if ($(item).val() == '') {
            error = LANG_JS_NAVIGATION_LABEL_EMPTY;
        }
    });

    /** validates duplicated links */
    $.each(formNavigation.find("[id^=link_]"), function (i, item) {
        var link = $(item).val(),
            count = 0;

        if (link == '') {
            error = LANG_JS_NAVIGATION_LINK_EMPTY;
        }

        jQuery.grep(formNavigation.find("[id^=link_]"), function (itemLink) {
            if (link == $(itemLink).val()) {
                count++;
                if (count > 1) {
                    error = LANG_JS_NAVIGATION_DUPLICATED_LINK;
                }
            }
        });
    });

    if (error) {
        var objModalDiv = $('#edit-widget-modal');

        /* Scroll to the top of modal */
        objModalDiv.animate({scrollTop: '0px'}, 500);

        /* reset the save button */
        setTimeout(function () {
            objModalDiv.find('.action-save').button('reset');
        }, 500);

        /* Show the error message */
        var messages = objModalDiv.find('div#messageAlertHeader');
        messages.find('div').text(error)
            .parent('div').addClass('alert-danger').show();

    }

    return !error;
}

/*
 * SLIDER JS
 */
$('#livemodeMessage').click(function (e) {
    e.preventDefault();

    livemodeMessage(true, false);
});

function hideSliderInfo() {
    $(".sliderInfo").hide();
}

function showSlideInfo(divId) {
    $("#sliderInfo" + divId).fadeIn();
    $('#navLink' + divId).selectize();
}

function changeActiveSlide(divId) {
    $("#sortableSlider li").removeClass("active");
    $("#li" + divId).addClass("active");
}

function addListenersSlider() {
    $(document).off('click', '.sliderImageButton');

    $(document).on('click', '.sliderImageButton', function () {
        var imageInput = $(this).data('imageinput');
        $('#slideImage' + imageInput).trigger('click');
    });

    $(document).on('click', '.removeSlide', function () {
        var deletedInput = $('#deletedSlides'),
            slideId = $(this).data('slideid'),
            itemDelete = $('#li' + slideId);

        if (deletedInput.val()) {
            deletedInput.val(deletedInput.val() + ',' + slideId);
        } else {
            deletedInput.val(slideId);
        }

        if (itemDelete.hasClass('active')) {
            if (itemDelete.prev() && itemDelete.prev().hasClass('slideLi')) {
                // Trigger click on the next item if exists
                $("#" + itemDelete.prev().attr('id') + " .click-area").trigger('click');
            } else if (itemDelete.next() && itemDelete.next().hasClass('slideLi')) {
                // Trigger click on the previous item if exists
                $("#" + itemDelete.next().attr('id') + " .click-area").trigger('click');
            }
        }

        itemDelete.remove();
        $('#sliderInfo' + slideId).remove();
        $('#sliderInfoDiv').find('#sliderInfo' + slideId).remove();

        var newSliderButton = $('.createSliderItem');
        if ($('#sortableSlider').find('li.slideLi').size() < newSliderButton.data('maxslides')) {
            newSliderButton.fadeIn('slow');
        }
    });
}

function addWidgetToDom(widgetId, content) {
    var url = '../../../includes/code/widgetGetAjax.php',
        selectedDomainId = $('#selectedDomainId').val(),
        type = 'GET',
        data = 'widgetId=' + widgetId + "&domain_id=" + selectedDomainId;

    content = content || {};

    var request = widgetActionsAjax(url, type, data, false, false);
    request.done(function (data) {
        var $item = $(data);

        if (content) {
            if (content.widgetTitle) {
                $item.find('[data-widget-title]').text(content.widgetTitle);
            }

            if (content.widgetPageId) {
                $item.find('#pageWidgetIdInput').val(content.widgetPageId);
            }
        }

        //to insert in a specific position
        if ($.widgetPosition !== undefined) {
            $.widgetPosition.before($item);
            delete $.widgetPosition;
        } else {
            //insert at the end of the page
            $('#sortWidgets').append($item);

            //Scroll page to the item added
            var height = $('.sortableDiv').get(0).scrollHeight;
            $('main').animate({scrollTop: height + 'px'}, 500);
        }

        $('#changed').val(1);
        $('#add-new-widget-modal').modal('hide');
    });
}

/*
 * ADD WIDGET TO PAGE JS
 */

$(document).ready(function () {

    $(document).find("#sortWidgets").sortable({
        update: function (event, ui) {
            $("#changed").val(1);
        }
    });

    $(document).on("click", ".createItem", function () {
        var modal = $(this).data('modalaux'),
            url = "../../../includes/code/widgetNavigationAjax.php",
            selectedDomainId = $('#selectedDomainId').val(),
            type = "GET",
            data = "area=" + modal + "&domain_id=" + selectedDomainId;

        var request = widgetActionsAjax(url, type, data, false);
        request.done(function (data) {
            $('#addNavBarItem').before(data);
            addListeners();
        });
    });

    $(document).on('click', '.editNavItem', function () {
        var divId = $(this).data('id'),
            modal = $(this).data('modalaux'),
            labelObj = $('#navigation_text_' + divId),
            linkObj = $('#link_' + divId),
            customObj = $('#custom_' + divId),
            url;

        if (modal == 'footer') {
            url = '/includes/modals/widget/modal-widget-navigation-footer.php';
        } else {
            url = '/includes/modals/widget/modal-widget-navigation.php';
        }

        $('#edit-navigation-link-modal').show('modal').find('.modal-content').load(url, function () {
            $('#navLabel').val(labelObj.val());
            $('#divIdNav').val(divId);
            if (customObj.val() == 0) {
                $('#navCustomLinkDiv').hide();
                $('#navLink').val(linkObj.val());
            } else {
                $('#navLink').val('custom');
                $('#navCustomLinkDiv').show();
                if (linkObj.val() && linkObj.val().match(/^http([s]?):\/\/.*/)) {
                    $("#navCustomLinkType[value=external]").attr('checked', 'checked');
                    $(".input-group-addon").addClass('hidden');
                    $("#navCustomLink").parent().removeClass('input-group');
                } else {
                    $("#navCustomLinkType[value=internal]").attr('checked', 'checked');
                    $("#navCustomLink").parent().addClass('input-group');
                    $(".input-group-addon").removeClass('hidden');
                }
                $('#navCustomLink').val(linkObj.val());
            }
            $('#edit-navigation-link-modal').modal({show: true});
            $('.navLink').selectize();
        });
    });

    $(document).on('click', '.saveNavButton', function () {
        var modal = $(this).data('modalaux'),
            divId = $('#divIdNav').val(),
            labelObj = $('#navigation_text_' + divId),
            linkObj = $('#link_' + divId),
            customObj = $('#custom_' + divId),
            selectedOption = $('#navLink[data-modalaux=\'' + modal + '\']').val(),
            customLinkType = $('input[name=\'navCustomLinkType\']:checked');

        /* Update inputs that will be used while saving the header modal */
        labelObj.val($('#navLabel').val());

        /* Update with the selected value of dropdown */
        customObj.val(0);
        linkObj.val(selectedOption);

        if (selectedOption == 'custom') {
            customObj.val(1);
            var navCustomLink = $('#navCustomLink[data-modalaux=\'' + modal + '\']');
            if (!(navCustomLink.val())) {
                navCustomLink.prop('required', true);
                $('#form_navigation_item[data-modalaux=\'' + modal + '\']').find('[type="submit"]').trigger('click');
                return;
            }
            if (!(navCustomLink.val().match(/((https?):\/\/)(www\.)?(?!(www\.))((?!\-\-)([^ .](?!\-\.)){1,}\.((?!\-\-)(?!\-\.)[^ /]){2,})(\:[^ .]*)?(\/[^ .]*)?(\?[^ .]*)?/)) && customLinkType.val() === 'external') {
                $('#messageAlertNavigation[data-modalaux=\'' + modal + '\']').find('div').text(LANG_JS_NAVIGATION_VALID_EXTERNAL_LINK).parent('div').addClass('alert-danger').show();
                return;
            }
            linkObj.val(navCustomLink.val());
        }

        if (!(selectedOption)) {
            $('#messageAlertNavigation[data-modalaux=\'' + modal + '\']').find('div').text(LANG_JS_NAVIGATION_LINK_EMPTY).parent('div').addClass('alert-danger').show();
            return;
        }

        if (!(customLinkType.length)) {
            $('#messageAlertNavigation[data-modalaux=\'' + modal + '\']').find('div').text(LANG_JS_NAVIGATION_CUSTOM_LINK_TYPE_EMPTY).parent('div').addClass('alert-danger').show();
            return;
        }

        $('#edit-navigation-link-modal').modal('hide');
        resetSaveButton();
    });

    $(document).on('change', '.navLink', function () {
        var divId = $(this).data('divid');
        var modal = $(this).data('modalaux');
        if ($(this).val() == 'custom') {
            if (divId) {
                $('#sliderCustomLinkDiv' + divId).slideDown();
            } else {
                $('#navCustomLinkDiv').slideDown();
            }
        } else {
            if (divId) {
                $('#sliderCustomLinkDiv' + divId).slideUp();
            } else {
                $('#navCustomLinkDiv').slideUp();
            }
        }
    });

    $(document).on('blur', '.navCustomLink', function () {
        var type = $('.radio-inline input:checked').val();
        var input = $(this),
            val = $.trim(input.val());
        if (val && !val.match(/^http([s]?):\/\/.*/) && type === "external") {
            val = 'http://' + val;
        }
        input.val(val.trim());
        if (val && type === "internal") {
            easyFriendlyUrl(val, 'navCustomLink', 'a-zA-Z0-9/.:', '-');
        }
    });

    $(document).on('blur', '.sliderCustomLink', function () {
        var divId = $(this).data('divid');
        var type = $('.radio-inline input:checked[data-divid=\'' + divId + '\']').val();
        var input = $(this),
            val = $.trim(input.val());
        if (val && !val.match(/^http([s]?):\/\/.*/) && type === "external") {
            val = 'http://' + val;
        }
        input.val(val.trim());
        if (val && type === "internal") {
            easyFriendlyUrl(val, 'sliderCustomLink' + divId, 'a-zA-Z0-9/.:', '-');
        }
    });

    $(document).on('change', '#navCustomLinkType', function () {
        var type = $(this).val();
        var modal = $(this).data('modalaux');
        var input = $('#navCustomLink[data-modalaux=\'' + modal + '\']'),
            val = input.val();
        var internalType = $('#internalValue[data-modalaux=\'' + modal + '\']');
        var externalType = $('#externalValue[data-modalaux=\'' + modal + '\']');
        var internalValue = internalType.val();
        var externalValue = externalType.val();

        if (type === "internal") {
            $("#navCustomLink").parent().addClass('input-group');
            $(".input-group-addon").removeClass('hidden');
            externalType.val(val);
            input.val(internalValue);
        }
        if (type === "external") {
            $(".input-group-addon").addClass('hidden');
            $("#navCustomLink").parent().removeClass('input-group');
            internalType.val(val);
            input.val(externalValue);
        }
    });

    $(document).on('change', '#sliderCustomLinkType', function () {
        var divId = $(this).data('divid');
        var type = $(this).val();
        var input = $('#sliderCustomLink' + divId),
            val = input.val();
        var internalType = $('#sliderInternalValue' + divId);
        var externalType = $('#sliderExternalValue' + divId);
        var internalValue = internalType.val();
        var externalValue = externalType.val();

        if (type === "internal") {
            input.parent().addClass('input-group');
            $('#url' + divId).removeClass('hidden');
            externalType.val(val);
            input.val(internalValue);
        }
        if (type === "external") {
            $('#url' + divId).addClass('hidden');
            input.parent().removeClass('input-group');
            internalType.val(val);
            input.val(externalValue);
        }
    });

    var successSaved = $('#alert-save');

    successSaved.alert();
    successSaved.fadeTo(3000, 500).slideUp(500, function () {
        successSaved.slideUp(500);
    });

    /* Open modal add new widget */
    $('#add-new-widget-modal').on('hidden.bs.modal', function () {
        $(this).find('.modal-content').html('');
    });

    $('#edit-widget-modal').on('hidden.bs.modal', function () {
        $(this).html('');
    });

    $(document).on('click', '.btn-new-widget', function (e) {
        e.preventDefault();
        $('#loading_ajax').fadeIn('fast');

        $('#add-new-widget-modal').modal('show').find('.modal-content').load($('#new-widget').attr('href'), function () {
            var pageWidget = [];
            $('.edit-info').each(function (i) {
                pageWidget.push($(this).data('title'));
            });

            var listOfForbiddenWidgets = [];
            var widgetFound = [];
            $.each($.widgetDuplicated, function (group, widgets) {
                widgetFound = pageWidget.filter(function (el) {
                    return widgets.indexOf(el) != -1;
                });

                if (widgetFound.length) {
                    $.merge(listOfForbiddenWidgets, widgets);
                }
            });

            $('.addWidget').each(function () {
                /* The variable $.widgetDuplicate was created in widget.php file */
                if ($.inArray($(this).data('title'), listOfForbiddenWidgets) >= 0) {
                    $(this).addClass('unavailable');
                    $(this).removeClass('addWidget');
                    $(this).removeClass('linkWidget');
                }
            });

            $('.tab-content').find('.tab-pane').each(function (i) {
                if ($(this).find('.row').children('.col-md-6').length == 0) {
                    var field = $(this).find('.row').children('.col-md-12');
                    field.removeClass('hide');
                }
            });
            $('#loading_ajax').fadeOut('fast');
        });
    });

    $(document).on('click', '.addWidget', function (e) {
        e.preventDefault();

        addWidgetToDom($(this).data('widgetid'));
    });

    /*
     * Please always use the form id (in the modal file) this way:
     * "form_" + modalArr[1]
     * modalArr[1] will be always a reference to which modal is opened
     */
    $(document).on('click', '.editWidgetButton', function (e) {
        e.preventDefault();

        $.widgetDivId = $(this).data('divid');

        var modalFullName = $(this).data('modal'),
            divId = $(this).data('divid'),
            editInfo = $('#' + divId + ' .edit-info');

        // REQUEST INFO
        var modalArr = modalFullName.split('-'),
            pageWidgetId = editInfo.data('pagewidget') ? editInfo.data('pagewidget') : $('#' + divId + ' #pageWidgetIdInput').val(),
            widgetId = editInfo.data('widgetid'),
            selectedDomainId = $('#selectedDomainId').val(),
            url = "/includes/code/widgetActionAjax.php",
            data = "?pageWidgetId=" + pageWidgetId
                + "&modal=" + modalArr[1]
                + "&widgetId=" + widgetId
                + "&domain_id=" + selectedDomainId
                + "&modalFullName=" + modalFullName
                + "&action=edit";

        $('#loading_ajax').fadeIn('fast');
        $('#edit-widget-modal').show('modal').load(url + data, function (result) {
            $('[data-toggle="tooltip"]').tooltip();
            //Set data divdid on save button
            $('#edit-widget-modal .btn-primary').data('divid', divId);
            $('#edit-widget-modal').find('[id^=messageAlert]').removeClass('alert-danger').hide();

            $("#openWidgetId").val(widgetId);

            if (modalArr[1] == 'header') {
                $('span.page-title').text($('#type').val());
                addListeners();
            } else if (modalArr[1] == 'footer') {
                addListeners();
            } else if (modalArr[1] == 'slider' || modalArr[1] == 'video' || modalArr[1] == 'leadgenform') {
                $('#deletedSlides').val('');
                if ($("#sortableSlider").html()) {
                    var newSliderButton = $('.createSliderItem');
                    if ($('#sortableSlider').find('li.slideLi').size() >= newSliderButton.data('maxslides')) {
                        newSliderButton.fadeOut('slow');
                    }
                    addListenersSlider();
                }
            } else if (modalArr[1] == 'cards') {
                addSuggest();
            }
            //Initialize sortables
            $(function () {
                $(document).find("#sortableNav").sortable({
                    items: "> .sortableNav"
                });
                $(document).find("#sortableSlider").sortable().disableSelection().on("click", ".click-area", function () {
                    var divId = $(this).data('divid');
                    changeActiveSlide(divId);
                    hideSliderInfo();
                    showSlideInfo(divId);
                });
            });

            if ($('.textarea-counter').length) {
                $('.textarea-counter').each(function () {
                    var options = {
                        'maxCharacterSize': $(this).attr('data-chars'),
                        'displayFormat': '<p class="help-block text-right">#left ' + $(this).attr('data-msg') + '</p>'
                    };
                    $(this).textareaCount(options);
                });
            }

            $('#edit-widget-modal').find('.selectize > select').selectize();

            $('#edit-widget-modal').modal({show: true});
            $('#loading_ajax').fadeOut('fast');
        });
    });

    $(document).on('click', '.removeWidgetButton', function (e) {
        e.preventDefault();

        var divId = $(this).data('divid');
        var editInfo = $('#' + divId + ' .edit-info');
        var pageWidgetId = editInfo.data("pagewidget") ? editInfo.data("pagewidget") : null;

        $('#remove-widget-modal').find('.confirmRemoval').attr('onClick', 'removeWidget(' + divId + ', ' + pageWidgetId + ')');
    });

    $(document).on('click', '.logoImageButton', function () {
        $(this).parents('form').find('input[type="file"]').trigger('click');
    });

    $(document).on('click', '.favIconButton', function (e) {
        e.preventDefault();
        $('#favIconInput').trigger('click');
    });

    $(document).on('click', '.bgImageButton', function () {
        $('#bgImageInput').trigger('click');
    });

    $(document).on('click', '.bgGenericImageButton', function () {
        $('#bgImageGenericInput').trigger('click');
    });

    $(document).on('click', '#saveSliderWidget', function (e) {
        e.preventDefault();

        saveSliderContent();
    });

    $(document).on('click', '.createSliderItem', function (e) {
        e.preventDefault();

        var maxSlides = $(this).data('maxslides');
        var totSlides = $('#sortableSlider').find('li.slideLi').size();
        var slideType = $(this).data('slidetype');

        if (totSlides < maxSlides) {
            var url = '../../../includes/code/widgetSliderAjax.php',
                selectedDomainId = $('#selectedDomainId').val(),
                type = 'GET',
                data = 'domain_id=' + selectedDomainId + '&slideType=' + slideType;

            var request = widgetActionsAjax(url, type, data, false);
            request.done(function (data) {
                var objData = jQuery.parseJSON(data);

                if (totSlides < maxSlides) {
                    $('.noSlidesAlert').hide();
                    $('#addNavBarItem').before(objData.slider);
                    $('#sliderInfoDiv').append(objData.sliderInfo);
                    $('#li' + objData.newId + ' .click-area').trigger('click');

                    // Remove the button
                    if (totSlides + 1 >= maxSlides) {
                        $('.createSliderItem').fadeOut('slow');
                    }
                }

                if ($('#form_sliderInfo' + objData.newId + ' .textarea-counter').length) {
                    $('#form_sliderInfo' + objData.newId + ' .textarea-counter').each(function () {
                        var options = {
                            'maxCharacterSize': $(this).attr('data-chars'),
                            'displayFormat': '<p class="help-block text-right">#left ' + $(this).attr('data-msg') + '</p>'
                        };
                        $(this).textareaCount(options);
                    });
                }
            });
        }
    });

    $(document).on("click", ".widget-color-list .color-item", function () {
        $(".widget-color-list .color-item").each(function () {
            $(this).removeClass("is-selected");
        });

        $(this).addClass("is-selected");
        $(this).find("input").attr("checked", "checked");
    });
});

//On click of the "plus" button to add a new widget
$(document).on('click', '.add-plus-circle-widget', function (e) {
    $.widgetPosition = $(this).parent('div');
});

function removeWidget(divId, pageWidgetId) {
    $('#' + divId).remove();

    var // REQUEST INFO
        url = '../../../includes/code/widgetActionAjax.php',
        selectedDomainId = $('#selectedDomainId').val(),
        data = 'pageWidgetId=' + pageWidgetId + '&removeWidget=1' + "&domain_id=" + selectedDomainId,
        type = "POST",
        request;

    request = widgetActionsAjax(url, type, data, false);

    request.done(function (data) {
        var objData = jQuery.parseJSON(data);

        if (objData.success) {
            var msgSuccess = objData.message, successAlert = $('#successAlert');

            $('#remove-widget-modal').modal('toggle');
            $('#remove-widget-modal .confirmRemoval').prop('onClick', null);

            if (msgSuccess) {
                successAlert.children('div').html(msgSuccess).alert();
                successAlert.fadeTo(3000, 500).slideUp(500, function () {
                    successAlert.slideUp(500);
                });
            }
        }
    });
}

$(document).on('click', '.linkWidget', function (e) {
    window.location.href = DEFAULT_URL + "/" + SITEMGR_ALIAS + "/promote/newsletter/";
});

$('.resetPageButton').on('click', function (e) {
    e.preventDefault();

    $('#modal-reset-page').modal('show');
});

$(document).on('click', '.confirmation-save', function (e) {
    e.preventDefault();

    $('<input>').attr({
        type: 'hidden',
        name: 'replica',
        id: 'replica',
        value: $(this).data('replica')
    }).appendTo('#form_widgets');

    JS_widget_submit();
});

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

$(document).on("click", ".add-item", function (e) {
    e.preventDefault();

    $(this).removeClass('has-error');
    var url = "../../../includes/code/widgetCardAjax.php",
        selectedDomainId = $('#selectedDomainId').val(),
        type = "GET",
        data = "&domain_id=" + selectedDomainId;

    var request = widgetActionsAjax(url, type, data, false);
    request.done(function (data) {
        var addItem = $('#addItem');

        addItem.before(data);

        var newItem = addItem.prev().find('.itemTitle');

        newItem.data("prefill", 0);
        var itemDatasetConfigs = {
            source: eDirectory.Search.Utility.createBloodhound(Routing.generate('search_suggest_card'), $('#card_module').val()),
            async: true,
            name: 'item',
            displayKey: 'text',
            limit: 10,
        };

        itemSuggester = new eDirectory.Search.Suggest(newItem, itemDatasetConfigs, inputConfigs, null, false);
        itemSuggester.initialize();
    });

    $('#card-pick-itens-pane').animate({
        scrollTop: $("#addItem").offset().top
    }, 500);
});

$(document).on('change', '#placeholderLink', function () {
    if ($(this).val() == 'custom') {
        $('#sectionLinkCustom').slideDown();
    } else {
        $('#sectionLinkCustom').slideUp();
    }
});

$(document).on('blur', '#customLink', function () {
    var $input = $(this);
    var type = $('.radio-inline input:checked').val();
    var val = $.trim($input.val());
    if (val && !val.match(/^http([s]?):\/\/.*/) && type === "external") {
        val = 'http://' + val;
    }

    if ($input.val(val.trim()) && val && type === "internal") {
        easyFriendlyUrl(val, 'customLink', 'a-zA-Z0-9/.:', '-');
    }
});

$(document).on('change', '.linkTarget', function () {
    var $input = $('#customLink');
    var $internalType = $('#linkInternalValue');
    var $externalType = $('#linkExternalValue');
    var type = $(this).val();
    var val = $input.val();
    var internalValue = $internalType.val();
    var externalValue = $externalType.val();

    if (type === "internal") {
        $('#linkUrl').removeClass('hidden');
        $input.parent().addClass('input-group');
        $externalType.val(val);
        $input.val(internalValue);
    }
    if (type === "external") {
        $('#linkUrl').addClass('hidden');
        $input.parent().removeClass('input-group');
        $internalType.val(val);
        $input.val(externalValue);
    }
});

function addSuggest() {
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
            limit: 10,
        };

        itemSuggester = new eDirectory.Search.Suggest($(this), itemDatasetConfigs, inputConfigs, null, false);
        itemSuggester.initialize();
    });
}

(function () {
    if (!FormData.prototype.hasOwnProperty('get')) {
        FormData.prototype.get = function (id) {
            var el = document.querySelector('[name="' + id + '"]');

            return el ? el.value : null;
        };
    }

    if (!FormData.prototype.hasOwnProperty('getAll')) {
        FormData.prototype.getAll = function (id) {
            var elements = document.querySelectorAll('[name="' + id + '"]');

            if (!elements || elements.length === 0) {
                return null;
            }

            var values = [];
            for (var i = 0; i < elements.length; i++) {
                var e = elements[i];

                if ((e.type === 'checkbox' || e.type === 'radio')) {
                    if (e.checked) {
                        values.push(e.value);
                    }

                    continue;
                }

                if (e.value !== null && e.value !== '') {
                    values.push(e.value);
                }
            }

            return values;
        };
    }
})();
