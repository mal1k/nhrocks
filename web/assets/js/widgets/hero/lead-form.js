$(document).ready(function(){

    $('#leadgenslider-form').submit(function (e) {
        e.preventDefault();

        var $form = $(this);
        var data = $form.serialize();
        var $btn = $form.find('.button[data-loading]');

        $form.find('[type=submit]').attr('disabled', 'disabled');
        $form.find('.alert').addClass('hide');

        var request = $.post(this.action, data);

        request
            .then(function () {
                $form[0].reset();
                $form.find('.alert-success').removeClass('hide');
            })
            .fail(function () {
                $form.find('.alert-danger').removeClass('hide');
            })
            .always(function () {
                btnReset($btn);
            });
    });

    $('#leadgenslider-submit').on('click', function(e) {
        var form = $('#leadgenslider-form');
        var checkboxDivs = form.find('[data-type=checkbox]');
        checkboxDivs.each(function (checkboxDiv) {
            if ($(checkboxDivs[checkboxDiv]).data('required') === true) {
                var checkBoxes = $(checkboxDivs[checkboxDiv]).find('input[type=checkbox]:checked');
                if (checkBoxes.length === 0) {
                    var lastCheckBox = $(checkboxDivs[checkboxDiv]).find('input[type=checkbox]').last();
                    lastCheckBox.attr('required', 'required');
                    lastCheckBox[0].setCustomValidity('Please make sure you have selected at least one of the boxes');
                } else {
                    var requiredCheckBox = $(checkboxDivs[checkboxDiv]).find('input[type=checkbox][required]');
                    requiredCheckBox.removeAttr('required');
                    requiredCheckBox[0].setCustomValidity('');
                }
            }
        });

        if ($(form).valid) {
            form.submit();
        }
    });

    if($(".hero-lead-form").attr("active-slider") == "true"){
        var heroSlider = $('.hero-lead-form');

        heroSlider.on('ready.flickity', function(){
            if($(window).width() >= 992){
                var leadGenFormHeight = ($(".leadgen-form").height() + 40) / 2;
            } else {
                var leadGenFormHeight = ($(".leadgen-form").height() + 160) / 2;
            }

            $(".hero-leadgen .carousel-cell").css("padding-top", leadGenFormHeight);
            $(".hero-leadgen .carousel-cell").css("padding-bottom", leadGenFormHeight + 40);
        });

        heroSlider.flickity({
            autoPlay: true,
            pauseAutoPlayOnHover: true,
            wrapAround: true,
            imagesLoaded: true,
            arrowShape: {
                x0: 25,
                x1: 55, y1: 30,
                x2: 65, y2: 20,
                x3: 45
            }
        });

        heroSlider.flickity('resize');
    } else {
        if($(window).width() >= 992){
            if($(".hero-default .slider-content").length != 0){
                var leadGenFormHeight = ($(".leadgen-form").height() + 40) / 2;
            } else {
                var leadGenFormHeight = ($(".leadgen-form").height() + 160) / 2;
            }
        } else {
            var leadGenFormHeight = ($(".leadgen-form").height() + 160) / 2;
        }

        $(".hero-leadgen .carousel-cell").css("padding-top", leadGenFormHeight);
        $(".hero-leadgen .carousel-cell").css("padding-bottom", leadGenFormHeight);
    }
});
