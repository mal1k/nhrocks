function sendCoverImageUnsplash(form_id, path, acc_id, action, unplash_link) {
    $.ajax({
        type: 'POST',
        url: DEFAULT_URL + "/" + path + "?action=ajax&type=" + action + "&domain_id=" + DOMAIN_ID + "&account_id=" + acc_id + "&module=" + form_id,
        data: {
            unsplash: unplash_link
        },
        dataType: "html",
        success: function (response) {
            strReturn = response.split("||");

            if (strReturn[0] == "ok") {
                $("#returnMessage").hide();
                $("#coverimage").hide().fadeIn('slow').html(strReturn[1]);
                if (action == "deleteCover") {
                    $("#buttonReset").addClass("hidden");
                } else {
                    $("#buttonReset").removeClass("hidden");
                }
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
    });

}

function sendImageUnsplash(formField, type, imgField, parentId, imgHidden, idMessage, unplash_link, download) {

    $.get(download);

    $.ajax({
        type: 'POST',
        url: DEFAULT_URL + '/' + SITEMGR_ALIAS + '/design/page-editor/custom.php?domain_id=' + DOMAIN_ID + '&action=upload&type=' + type,
        data: {
            unsplash: unplash_link
        },
        dataType: "json",
        success: function (response) {
            if (response.success == false) {
                $('#' + formField).parents().find('#' + idMessage)
                    .addClass('alert-danger').removeClass('alert-success')
                    .find('div').html(response.message)
                    .parent().show();
            } else {
                var imgElement = $('#' + imgField);

                // Remove new image structure and create a image tag
                if (imgElement.length === 0) {
                    var parentField = $('#' + parentId);

                    parentField.fadeOut('slow', function () {
                        $(this)
                            .find('div.new').removeClass('new').addClass('edit-hover').addClass('unsplash-preview')
                            .find('a.add-new').removeClass('thumbnail add-new')
                            .find('div.caption').remove();

                        var imageField = parentField.find('.bgGenericImageButton');
                        imageField.css('background-image', 'url('+response.url+')');
                        var sliderId = imageField.data('imageinput');
                        var sliderInfoImage = $('#li'+sliderId).find('.add-new img');

                        if(sliderInfoImage.length === 0) {
                            $('#li'+sliderId).find('.add-new').empty().append('<img src="' + response.url + '" alt="eDirectory">');
                        } else {
                            sliderInfoImage.attr('src',response.url);
                        }

                        onImgLoad(parentField, function () {
                            $(this).fadeIn('slow');
                        });
                    });
                } else {
                    // Change image
                    imgElement.fadeOut('slow', function () {
                        $(this)
                            .closest('div.new').removeClass('new').addClass('edit-hover').addClass('unsplash-preview')
                            .find('a.add-new').removeClass('thumbnail add-new')
                            .find('div.caption').remove();

                        // $(this).attr('src', response.url);
                        $(this).css('background-image', 'url(' + response.url + ')');
                        var sliderId = $(this).data('imageinput');
                        var sliderInfoImage = $('#li' + sliderId).find('.add-new img');

                        if (sliderInfoImage.length === 0) {
                            $('#li' + sliderId).find('.add-new').empty().append('<img src="' + response.url + '" alt="eDirectory">');
                        } else {
                            sliderInfoImage.attr('src', response.url);
                        }

                        onImgLoad(imgElement, function () {
                            $(this).fadeIn('slow');
                        });
                    });
                }

                // Update field when return the id from database
                if (imgHidden && response.code && response.url) {
                    if (imgHidden == 'unsplash') {
                        $('#' + imgHidden).val(response.url);
                    } else {
                        $('#' + imgHidden).val(response.code);
                    }
                }

                $("#" + idMessage).hide();
            }
        }
    });
}

$(document).on('click', '.btn-unsplash', function (e) {
    e.preventDefault();

    $.ajax({
        type: 'GET',
        url: DEFAULT_URL + '/includes/code/unsplash.php',
        dataType: "json",
        success: function (response) {
            if (response.length) {
                $.each(response, function (index, photo) {
                    var item = $('#template-photo').html();
                    item = item.replace(/\%id\%/g, photo.id);
                    item = item.replace(/\%download_location\%/g, photo.download_location);
                    item = item.replace(/\%regular\%/g, photo.regular);
                    item = item.replace(/\%thumb\%/g, photo.thumb);
                    item = item.replace(/\%description\%/g, photo.description);
                    item = item.replace(/\%photographer\%/g, photo.photographer);
                    item = item.replace(/\%photographer_link\%/g, photo.photographer_link);
                    $('.section-unsplash').find('.unsplash-body').append(item);
                });
                $('.section-unsplash').find('.btn-unsplash-more').show();
            } else {
                $('.section-unsplash').find('.btn-unsplash-more').hide();
                $('.section-unsplash').find('.unsplash-body').append('<div class="unsplash-text">' + LANG_JS_NO_ITEMS_FOUND + '</div>');
            }
        },
        complete: function () {
            $('#modal-unsplash').modal('show');
        }
    });
});

$(document).on('click', '.section-unsplash.active .btn-unsplash-more', function (e) {
    e.preventDefault();
    var page = parseInt($(this).attr('data-page')) + 1;
    var query = $('.section-unsplash.active').find('.input-unsplash').val();

    $.ajax({
        type: 'GET',
        url: DEFAULT_URL + '/includes/code/unsplash.php',
        data: {
            page: page,
            query: query,
        },
        dataType: "json",
        success: function (response) {
            $.each(response, function (index, photo) {
                var item = $('#template-photo').html();
                item = item.replace(/\%id\%/g, photo.id);
                item = item.replace(/\%download_location\%/g, photo.download_location);
                item = item.replace(/\%regular\%/g, photo.regular);
                item = item.replace(/\%thumb\%/g, photo.thumb);
                item = item.replace(/\%description\%/g, photo.description);
                item = item.replace(/\%photographer\%/g, photo.photographer);
                item = item.replace(/\%photographer_link\%/g, photo.photographer_link);
                $('.section-unsplash.active').find('.unsplash-body').append(item);
            });

            $('.section-unsplash.active .btn-unsplash-more').attr('data-page', page);
        }
    });
});

var typingTimer;
var doneTypingInterval = 500;

$(document).on('click', '.section-unsplash', function (e) {

    var $div = $(this);

    $('.section-unsplash').each(function (index) {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        }
    });

    if (!$div.hasClass('active')) {
        $div.addClass('active');
    }
});

$(document).on('keyup', '.section-unsplash.active .input-unsplash', function (e) {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(doneTyping, doneTypingInterval);
});

$(document).on('keydown', '.section-unsplash.active .input-unsplash', function (e) {
    clearTimeout(typingTimer);
});

function doneTyping() {
    var query = $('.section-unsplash.active').find('.input-unsplash').val();
    $('.section-unsplash.active').find('.btn-unsplash-more').data('page', 1);
    $('.section-unsplash.active').find('.unsplash-body').html('<div class="unsplash-text">' + LANG_JS_LOADING + ' <i class=\'fa fa-refresh fa-spin\'></i></div>');
    if (!$('.section-unsplash.active').find('.btn-unsplash-save').hasClass('disabled')) {
        $('.section-unsplash.active').find('.btn-unsplash-save').addClass('disabled');
    }

    $.ajax({
        type: 'GET',
        url: DEFAULT_URL + '/includes/code/unsplash.php',
        data: {
            query: query,
        },
        dataType: "json",
        success: function (response) {
            $('.section-unsplash.active').find('.unsplash-body').html('');

            if (response.length) {
                $.each(response, function (index, photo) {
                    var item = $('#template-photo').html();
                    item = item.replace(/\%id\%/g, photo.id);
                    item = item.replace(/\%download_location\%/g, photo.download_location);
                    item = item.replace(/\%regular\%/g, photo.regular);
                    item = item.replace(/\%thumb\%/g, photo.thumb);
                    item = item.replace(/\%description\%/g, photo.description);
                    item = item.replace(/\%photographer\%/g, photo.photographer);
                    item = item.replace(/\%photographer_link\%/g, photo.photographer_link);
                    $('.section-unsplash.active').find('.unsplash-body').append(item);
                });
                $('.section-unsplash.active').find('.btn-unsplash-more').show();
            } else {
                $('.section-unsplash.active').find('.btn-unsplash-more').hide();
                $('.section-unsplash.active').find('.unsplash-body').append('<div class="unsplash-text">' + LANG_JS_NO_ITEMS_FOUND + '</div>');
            }
        },
    });
}

$(document).on('hidden.bs.modal', '#modal-unsplash', function (e) {
    setTimeout(function () {
        $('#modal-unsplash').find('.input-unsplash').val('');
        $('#modal-unsplash').find('.section-unsplash.active > .unsplash-body, .section-unsplash > .unsplash-body').html('');
        $('#modal-unsplash').find('.section-unsplash.active > .btn-unsplash-more, .section-unsplash > .btn-unsplash-more').data('page', 1);
        $('#modal-unsplash').find('.section-unsplash.active > .btn-unsplash-more, .section-unsplash > .btn-unsplash-more').show();
        if (!$('#modal-unsplash').find('.btn-unsplash-save').hasClass('disabled')) {
            $('#modal-unsplash').find('.btn-unsplash-save').addClass('disabled');
        }
    }, 250);
});

$(document).on('click', '.unsplash-item', function (e) {
    $('.selected').removeClass('selected');
    $(this).addClass('selected');
    $('.btn-unsplash-save').removeClass('disabled');
});

$(document).on('click', '.btn-unsplash-save', function (e) {
    var error = true;
    var $selected = null;
    var form_id = $('form').attr('id');
    var account_id = $('#account_id').attr('data-value');
    var path = PATH;

    $('.unsplash-item').each(function (index) {
        if ($(this).hasClass('selected')) {
            error = false;
            $selected = $(this);
        }
    });

    if (error == false) {
        var download = $selected.find('.unsplash-picture').attr('data-download');
        var link = $selected.find('.unsplash-picture').attr('data-regular');

        $('#modal-unsplash').modal('toggle');
        $.get(download);

        sendCoverImageUnsplash(form_id, path, account_id, 'createCover', link)
    }
});

$(document).on('click', '.img-background .thumb-image-modal', function (e) {
    var download = $(this).data('download');
    var unplash_link = $(this).data('regular');
    var sliderId = $(this).parents('.upload-logo').find('input[name=slideImage]').data('slider');

    var formField = 'form_slider_' + sliderId;
    var imgField = 'imgSlider' + sliderId;
    var parentId = 'image-background' + sliderId;
    var imgHidden = 'imageId' + sliderId;
    var idMessage = 'messageAlertSlider';
    var type = 'slide';

    sendImageUnsplash(formField, type, imgField, parentId, imgHidden, idMessage, unplash_link, download);
});

$(document).on('click', '#image-background-generic .thumb-image-modal', function (e) {
    var download = $(this).data('download');
    var unplash_link = $(this).data('regular');

    var formField = 'form_generic_image';
    var type = 'image';
    var imgField = 'bgGenericImage';
    var parentId = 'image-background-generic';
    var imgHidden = 'unsplash';
    var idMessage = 'messageAlertGenericImage';

    sendImageUnsplash(formField, type, imgField, parentId, imgHidden, idMessage, unplash_link, download);
});
