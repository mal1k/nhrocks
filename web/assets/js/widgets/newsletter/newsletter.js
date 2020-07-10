$(document).ready(function() {
    $('#formNewsletter, #formNewsletterFooter').submit(function (e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('.button[data-loading]');
        var $alert = $form.find('.alert-message');

        $alert.each(function (key) {
            $(this).attr('is-visible', false).html('');
        });

        $.post(Routing.generate('web_newsletter'), $form.serialize(), function (data) {
            if (data.success) {
                $form.find('.alert-message[data-type=success]').attr({'is-visible': true}).html(data.message);

                $form.find('input').each(function (key) {
                    $(this).val('');
                });
            } else {
                $.each(data.errors, function (key, error) {
                    if (error.field && $form.find('input[name=' + error.field + ']').length) {
                        $form.find('.alert-message[data-field=' + error.field + ']').attr('is-visible', true).html(error.message);
                    }
                });
            }

            btnReset($btn);
        });
    });
});