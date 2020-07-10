var cardsHeight = function () {
    $('.card-centralized').each(function(){
        var titleHeight = $(this).find('.title').height();
        var titleMargin = parseInt($(this).find('.title').css('margin-bottom'));

        var contentHeight = $(this).find('.content').outerHeight();
        var contentPadding = parseInt($(this).find('.content').css('padding-top'));

        var bottomValue = (contentHeight - titleHeight - titleMargin - contentPadding - 5) * -1;
        
        $(this).find('.content').css("bottom", bottomValue);
    });
};

$(document).ready(function() {
    cardsHeight();
});

$(window).on("resize", function() {
    cardsHeight();
});