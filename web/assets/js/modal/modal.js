$(document).ready(function () {
    var clearModalFields = function(modalOpen) {
        modalOpen.find('input[type!="hidden"]').val('');
        modalOpen.find('textarea').val('');
        modalOpen.find('span[data-rating]').removeClass('active');
        modalOpen.find('.review-rating').each(function() {
            $(this).val('');
        });
        modalOpen.find('.alert-warning').remove();
        clearCaptcha(modalOpen);
    };

    var clearCaptcha = function(modalOpen) {
        $('.g-recaptcha').each(function(captcha) {
            grecaptcha.reset(captcha);
        });
        var captcha = modalOpen.find('img[title="captcha"]');
        if(captcha.length !== 0) {
            var captchaId = captcha.first().attr('id');
            eval('reload_' + captchaId + '()');
        }
        modalOpen.find('#reviews_reviewCaptcha').val('');
    };

    $(document).on("click", "[data-modal]", function(e){
        e.preventDefault();
        var $target = $(this).data('modal');

        if($target == 'deal'){
            var actualContent = $('[data-modal="deal"]').html();
            var loadingText = $('[data-modal="deal"]').data('loading');

            $('[data-modal="deal"]').html(loadingText);
        }

        if($target !== "close"){
            var id = $(this).data('id');
            var module = $(this).data('module');
            var ajax = $(this).data('ajax');
            switch($target) {
                case 'login' :
                    var el = $(".modal-sign[is-page!='true']");
                    el.toggleClass('is-open').fadeToggle(400);
                    el.find("input[name='action']").val('');
                    el.find("input[name='item_id']").val('');
                    moveSelectedArrow(el);
                    setContentHeight(el);
                    break;
                case 'bookmark' :
                    var bookmark_target = $(this);
                    var bookmark_id = bookmark_target.data('id');
                    $.get(Routing.generate('web_bookmark', {id: id, module: module}), function (response) {
                        if ('login' === response.status) {
                            var el = $(".modal-sign[is-page!='true']");
                            el.toggleClass('is-open').fadeToggle(400);
                            el.find("input[name='action']").val('bookmark');
                            el.find("input[name='item_id']").val(id);
                            moveSelectedArrow(el);
                            setContentHeight(el);
                        } else if ('pinned' === response.status) { /* pinned */
                            $('a[data-modal="bookmark"][data-id=' + bookmark_id +'] i').removeClass('fa-bookmark-o').addClass('fa-bookmark');
                        } else if ('unpinned' === response.status) { /* unpinned */
                            $('a[data-modal="bookmark"][data-id=' + bookmark_id +'] i').addClass('fa-bookmark-o').removeClass('fa-bookmark');
                        }
                    });
                    break;
                case 'contact' :
                    if(ajax) {
                        $.get(Routing.generate(module + '_sendmail', {id: id, ajax: 'ajax'}), function (response) {
                            if(response.item) {
                                var modal = $(".modal-" + $target);
                                if(response.item.coverImage) {
                                    modal.find('.modal-default').removeClass('custom-close-color');
                                    modal.find('.modal-header').show();
                                    modal.find('.modal-header').css('background-image', 'url(' + response.item.coverImage + ')');
                                    modal.find('.modal-default').addClass('has-coverimage');
                                } else {
                                    modal.find('.modal-header').hide();
                                    modal.find('.modal-default').removeClass('has-coverimage');
                                }

                                if(response.item.logoImage) {
                                    if(!response.item.coverImage) {
                                        modal.find('.modal-default').addClass('custom-close-color');
                                        modal.find('.modal-header').show();
                                        modal.find('.modal-header').css('background-image', '');
                                    }
                                    modal.find('.modal-picture').show();
                                    modal.find('#send-email-logo').attr('src', response.item.logoImage);
                                    modal.find('.modal-info').addClass('has-picture');
                                } else {
                                    modal.find('.modal-picture').hide();
                                    modal.find('.modal-info').removeClass('has-picture');
                                }
                                modal.find('#send-email-logo').attr('alt', response.item.title);
                                modal.find('#send-email-title').html(response.item.title);
                                modal.find('#send-email').attr('action', response.item.actionUrl);
                                modal.toggleClass('is-open').fadeToggle(400);
                                moveSelectedArrow(modal);
                                setContentHeight(modal);
                            }
                        });
                        break;
                    }

                    var el = $(".modal-" + $target);
                    el.toggleClass('is-open').fadeToggle(400);
                    moveSelectedArrow(el);
                    setContentHeight(el);
                    break;
                case 'review':
                    if(ajax) {
                        $.get(Routing.generate('web_add_review', {id: id, ajax: 'ajax'}), function (response) {
                            if(response.status === 'login') {
                                var el = $(".modal-sign[is-page!='true']");
                                el.toggleClass('is-open').fadeToggle(400);
                                el.find("input[name='action']").val('review');
                                el.find("input[name='item_id']").val(id);
                                moveSelectedArrow(el);
                                setContentHeight(el);
                            } else if(response.item) {
                                var modal = $(".modal-" + $target);
                                if(response.item.coverImage) {
                                    modal.find('.modal-default').removeClass('custom-close-color');
                                    modal.find('.modal-header').show();
                                    modal.find('.modal-header').css('background-image', 'url(' + response.item.coverImage + ')');
                                    modal.find('.modal-default').addClass('has-coverimage');
                                } else {
                                    modal.find('.modal-header').hide();
                                    modal.find('.modal-default').removeClass('has-coverimage');
                                }

                                if(response.item.logoImage) {
                                    if(!response.item.coverImage) {
                                        modal.find('.modal-default').addClass('custom-close-color');
                                        modal.find('.modal-header').show();
                                        modal.find('.modal-header').css('background-image', '');
                                    }
                                    modal.find('.modal-picture').show();
                                    modal.find('#review-logo').attr('src', response.item.logoImage);
                                    modal.find('.modal-info').addClass('has-picture');
                                } else {
                                    modal.find('.modal-picture').hide();
                                    modal.find('.modal-info').removeClass('has-picture');
                                }
                                modal.find('#review-logo').attr('alt', response.item.title);
                                modal.find('#review-title').html(response.item.title);
                                modal.find('#review').attr('action', response.item.actionUrl);
                                modal.toggleClass('is-open').fadeToggle(400);
                                moveSelectedArrow(modal);
                                setContentHeight(modal);
                            }
                        });
                        break;
                    }

                    $.get(Routing.generate('web_add_review', {id: id}), function (response) {
                        if(response.status === 'login') {
                            var el = $(".modal-sign[is-page!='true']");
                            el.toggleClass('is-open').fadeToggle(400);
                            el.find("input[name='action']").val('review');
                            el.find("input[name='item_id']").val(id);
                            moveSelectedArrow(el);
                            setContentHeight(el);
                        } else {
                            var el = $(".modal-" + $target);
                            el.toggleClass('is-open').fadeToggle(400);
                            moveSelectedArrow(el);
                            setContentHeight(el);
                        }
                    });
                    break;
                case 'deal' :
                    $.get(Routing.generate('deal_redeem', {id:id}), function (response) {
                        if ('login' === response.status) {
                            var el = $(".modal-sign[is-page!='true']");
                            el.toggleClass('is-open').fadeToggle(400);
                            el.find("input[name='action']").val('redeem');
                            el.find("input[name='item_id']").val(id);
                            $('[data-modal="deal"]').html(actualContent);
                            moveSelectedArrow(el);
                            setContentHeight(el);
                        } else {
                            var modal = $(".modal-" + $target);

                            if(response.item.coverImage) {
                                modal.find('.modal-default').removeClass('custom-close-color');
                                modal.find('.modal-header').show();
                                modal.find('.modal-header').css('background-image', 'url(' + response.item.coverImage + ')');
                                modal.find('.modal-default').addClass('has-coverimage');
                            } else {
                                modal.find('.modal-header').hide();
                                modal.find('.modal-default').removeClass('has-coverimage');
                            }

                            if(response.item.logoImage) {
                                if(!response.item.coverImage) {
                                    modal.find('.modal-default').addClass('custom-close-color');
                                    modal.find('.modal-header').show();
                                    modal.find('.modal-header').css('background-image', '');
                                }
                                modal.find('.modal-picture').show();
                                modal.find('#redeem-logo').attr('src', response.item.logoImage);
                                modal.find('.modal-info').addClass('has-picture');
                            } else {
                                modal.find('.modal-picture').hide();
                                modal.find('.modal-info').removeClass('has-picture');
                            }

                            modal.find('#deal-endDate').html(response.item.endDate);
                            modal.find('#deal-dealValue').html(response.item.dealValue);
                            modal.find('#deal-realValue').html(response.item.realValue);
                            modal.find('#deal-listingTitle').html(response.item.listingTitle);
                            modal.find('#deal-name').html(response.item.dealName);
                            modal.find('#download').attr('download', response.item.download);
                            if(response.redeem) {
                                modal.find('#user-name').html(response.user.name);
                                modal.find('#code').html(response.redeem.redeemCode);
                            }
                            
                            modal.toggleClass('is-open').fadeToggle(400);
                            moveSelectedArrow(modal);
                            setContentHeight(modal);
                            
                            $('[data-modal="deal"]').html(actualContent);
                        }
                    });
                    break;
                default:
                    var el = $(".modal-" + $target);
                    el.toggleClass('is-open').fadeToggle(400);
                    moveSelectedArrow(el);
                    setContentHeight(el);
                    break;
            }
        } else {
            var modalOpen = $(".modal-default[is-page!='true'].is-open, .details-modal[is-page!='true'].is-open");
            modalOpen.toggleClass('is-open').fadeToggle(400).promise().done(function() {
                clearModalFields(modalOpen);
            });
        }
    });

    $(document).on('mousedown', ".details-modal[is-page!='true'], .modal-sign[is-page!='true']", function(e){      
        window.clickStartedInModal = $(e.target).is('.modal-content *, .modal-default *');     
    });
    
    $(document).on('mouseup', ".details-modal[is-page!='true'], .modal-sign[is-page!='true']", function(e){
        if(!$(e.target).is('.modal-content *, .modal-default *') && window.clickStartedInModal) {
            window.preventModalClose = true;
        }           
    });

    $(".details-modal[is-page!='true'], .modal-sign[is-page!='true']").on("click", function (e) {
        if(window.preventModalClose){
            window.preventModalClose = false;
            return false;
        } else {
            if (e.target == e.currentTarget) {
                $(this).removeClass('is-open').fadeOut(400).promise().done(function() {
                    clearModalFields($(this));
                });
            }
        }
    });

    $(document).on('click', '.google-button, .facebook-button', function(e) {
        var action = $("input[name='action']").first();
        var id = $("input[name='item_id']").first();
        if(action.val() === 'bookmark') {
            document.cookie = 'open_bookmark=' + id.val() + ';path=/';
        } else if (action.val() === 'review') {
            document.cookie = 'open_review=' + id.val() + ';path=/';
        } else if (action.val() === 'redeem') {
            document.cookie = 'open_redeem=' + id.val() + ';path=/';
        }
    });

    $(document).on('submit', '.details-modal form', function (e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(this);

        formData.append('info', form.data("info"));

        form.find('.alert-warning').remove();
        var $submitButton = form.find('button[type="submit"]');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            dataType: 'json',
            data: formData,
            contentType: false,
            processData: false,
        }).done(function (data) {

            var modalOpen = $('.details-modal.is-open');

            if (data.status) {
                modalOpen.addClass('is-sent');
                modalOpen.fadeOut(2000, function() {
                    $(this).removeClass('is-open').removeClass('is-sent');
                });

                //reset fields
                clearModalFields(modalOpen);
                //reset error messages
                $('.modalError').html('');
            } else {
                if(data.error == null) {
                    if(data.content) {
                        modalOpen.find('.modal-return-error').html(
                            '<i class="fa fa-warning"></i>'+data.content
                        );
                    }

                    modalOpen.addClass('is-sent-error');
                    modalOpen.fadeOut(4000, function () {
                        $(this).removeClass('is-open').removeClass('is-sent-error');
                    });

                    //reset fields
                    clearModalFields(modalOpen);
                    //reset error messages
                    $('.modalError').html('');
                } else {
                    var modalError = modalOpen.find('.modalError');
                    modalError.append('<p class="alert alert-warning">' + data.error + '</p>\n');
                    modalError.find('.alert').fadeOut(6000, function() {
                        $(this).remove();
                    });

                    clearCaptcha(modalOpen);
                }
            }

            var btnReset = function ($button) {
                if ($button.length) {
                    var content = $button.attr('data-content');

                    $button.removeClass('is-loading');
                    $button.html(content);
                    $button.removeAttr('disabled');
                }
            };

            btnReset($submitButton);
        });

        return false;
    });

    var moveSelectedArrow = function(el){
        var $modalNav = el.find(".modal-nav");
        var $modalNavLinkActive = el.find(".modal-nav-link.active");
        var navLeft = 0;
        var linkLeft = 0;
        if ($modalNav.length) {
            navLeft = $modalNav.offset().left;
        }
        if ($modalNavLinkActive.length) {
            linkLeft = $modalNavLinkActive.offset().left;
        }
        if (navLeft && linkLeft) {
            el.find(".modal-nav .selected-arrow").css("left", Math.round((linkLeft - navLeft)) + 24);
        }
    };

    var changeTabContent = function(el, tab){
        el.find(".content-tab").removeClass("active");
        el.find("#"+tab).addClass("active");
        setContentHeight(el);
    };

    var setContentHeight = function(el){
        var itemHeight = el.find('.content-tab.active').height();
        el.find(".modal-body").height(itemHeight);
    };

    $(".modal-sign").each(function () {
        if ($(this).length != 0 && !$(this).hasClass("profile-login-modal") && !$(this).hasClass("keep-style")){
            moveSelectedArrow($(this));
            setContentHeight($(this));
        }
    });

    $(document).on("click", ".modal-nav-link", function(e){
        e.preventDefault();

        var el = $(this).closest('.modal-sign');

        el.find(".modal-nav-link").removeClass('active');

        if($(this).hasClass('active')){
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }

        moveSelectedArrow(el);
        changeTabContent(el, $(this).data('tab'));
    });

    var clearModalCookies = function(){
        document.cookie = 'open_bookmark=0; expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
        document.cookie = 'open_review=0; expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
        document.cookie = 'open_redeem=0; expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
    };

    if (typeof Cookies == 'function'){
        if (Cookies.get('open_bookmark')) {
            var bookmarkButton = $('a[data-modal="bookmark"][data-id=' + Cookies.get('open_bookmark') + ']:visible').first();
            $("html, body").animate({scrollTop: bookmarkButton.offset().top - ($(window).height() - bookmarkButton.height()) / 2}, 500);

            if ($('a[data-modal="bookmark"][data-id=' + Cookies.get('open_bookmark') + ']:visible i').first().hasClass('fa-bookmark-o')) {
                bookmarkButton.click();
                $('a[data-modal="bookmark"][data-id=' + Cookies.get('open_bookmark') + '] i.fa-bookmark-o').removeClass('fa-bookmark-o').addClass('fa-bookmark');
            }
            clearModalCookies();
        }

        if (Cookies.get('open_redeem')) {
            var redeemButton = $('a[data-modal="deal"][data-id=' + Cookies.get('open_redeem') + ']').first();
            $("html, body").animate({scrollTop: redeemButton.offset().top - ($(window).height() - redeemButton.height()) / 2}, 500);
            redeemButton.click();
            clearModalCookies();
        }

        if (Cookies.get('open_review')) {
            var reviewButton = $('a[data-modal="review"][data-id=' + Cookies.get('open_review') + ']').first();
            $("html, body").animate({scrollTop: reviewButton.offset().top - ($(window).height() - reviewButton.height()) / 2}, 500);
            reviewButton.click();
            clearModalCookies();
        }
    }

});
