$('.button-toggle-title').on('click', function () {
    var $button = $(this);
    var id = $button.data('id');
    var $element = $button.parent();

    if ($element.hasClass('is-open')) {
        $element.removeClass('is-open');
        $element.siblings('.toggle-content').slideUp(400);
    } else {
        $('.toggle-item').each(function () {
            if ($(this).find('.toggle-header').hasClass('is-open')) {
                $(this).find('.toggle-header').removeClass('is-open');
                $(this).find('.toggle-content').slideUp(400);
            }
        });

        $element.siblings('.toggle-content[data-parent-id="' + id + '"]').slideToggle(400);
        $element.toggleClass('is-open');
    }
});

$('.button-toggle-nav').on('click', function () {
    var $button = $(this);
    var parentIsSelected = $button.parent().hasClass('is-selected');
    var order = $button.data('ref');
    var id = $button.data('id');
    var nextOrder = order + 1;

    // Hide all next
    for (var i = nextOrder; i <= 4; i++) {
        var $columnElement = $('.toggle-nav[data-order="' + i + '"');
        $columnElement.fadeOut(400);
        $columnElement.find('.is-selected').each(function () {
            $(this).removeClass('is-selected');
        });
    }

    // Clean current column selection
    $('.toggle-nav[data-order="' + order + '"]').find('.is-selected').each(function () {
        $(this).removeClass('is-selected');
    });

    // Open next column if clicked item isn't selected (new selection)
    if (!parentIsSelected) {
        var $target = $('.toggle-nav[data-order="' + order + '"]');
        $button.parent().toggleClass('is-selected');
        $target.toggleClass('is-open');
        $target.siblings('.toggle-nav[data-order="' + nextOrder + '"][data-parent-id="' + id + '"]').animate({width: 'toggle'});
    }
});
