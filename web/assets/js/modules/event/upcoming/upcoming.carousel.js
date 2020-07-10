var UpcomingSlider = function () {

    var sandbox = this;
    var STEP_SIZE_SCROLL = '74px';
    var DIRECTION_LEFT = '-=';
    var DIRECTION_RIGHT = '+=';
    this.container = $('.cards-upcoming-wrapper');
    this.items = $('.cards-upcoming-wrapper .calendar-sm');
    this.btnScrollToLeft = $('.upcoming-button[data-direction="previous"]');
    this.btnScrollToRight = $('.upcoming-button[data-direction="next"]');
    this.selectorIndicator = $('.selected-date');

    this.init = function () {
        sandbox.container.scroll(sandbox.onContainerScrolled);
        sandbox.btnScrollToLeft.on('click', sandbox.scrollToLeft);
        sandbox.btnScrollToRight.on('click', sandbox.scrollToRight);
        sandbox.items.on('click', sandbox.onItemClicked);
        sandbox.handleButtons();
    };

    this.scrollToLeft = function () {
        sandbox.scrollTo(DIRECTION_LEFT, STEP_SIZE_SCROLL);
    };

    this.scrollToRight = function () {
        sandbox.scrollTo(DIRECTION_RIGHT, STEP_SIZE_SCROLL);
    };

    this.scrollTo = function (direction, size) {
        var scrollValue = direction + size;
        sandbox.container.animate({ scrollLeft: scrollValue }, 400, "swing", sandbox.moveSelectorIndicator);
        sandbox.handleButtons();
    };

    this.onItemClicked = function () {
        var index = $(this).data('id');
        sandbox.setItemSelected(index);
        sandbox.moveSelectorIndicator();
    };

    this.setItemSelected = function (index) {
        sandbox.items.each(function () {
            var selected = $(this).data('id') == index;
            $(this).attr('is-active', selected);
        })
    };

    this.getElementSelected = function () {
        var selected;
        sandbox.items.each(function (index) {
            if ($(this).attr('is-active') === 'true') {
                selected = $(this);
                return;
            }
        });
        return selected;
    };

    this.moveSelectorIndicator = function () {
        var elementSelected = sandbox.getElementSelected();
        var elementPosition = elementSelected.position();
        var elementMargin = parseInt(elementSelected.css('margin-left'));
        var elementWidth = elementSelected.width();
        var indicatorWidth = sandbox.selectorIndicator.width();
        var positionOffset = elementPosition.left + elementMargin + (elementWidth / 2);
        var positionX = positionOffset - (indicatorWidth / 2);

        var offset = sandbox.container.position().left;
        var width = sandbox.container.outerWidth();

        var shouldHide = positionX < offset
            || (positionOffset - elementWidth) > width;

        sandbox.selectorIndicator.animate({ opacity: shouldHide ? 0 : 1 });

        if(!shouldHide)
            sandbox.selectorIndicator.css("transform", "translateX("+ positionX +"px) rotate(-45deg)");
    };

    this.onContainerScrolled = function () {
        sandbox.handleButtons();
    };

    this.handleButtons = function () {
        var width = sandbox.container.outerWidth();
        var scrollWidth = sandbox.container[0].scrollWidth;
        var scrollLeft = sandbox.container.scrollLeft();

        var canNotScrollToLeft = scrollLeft === 0;
        var canNotScrollToRight = scrollWidth - width === scrollLeft;

        sandbox.btnScrollToLeft.prop('disabled', canNotScrollToLeft);
        sandbox.btnScrollToRight.prop('disabled', canNotScrollToRight);
    };

}

var slider = new UpcomingSlider();

$(document).ready(function () {
    /* Binds */

    slider.init();

    var cardsUpcoming = $('.cards-upcoming-wrapper .calendar-sm');

    cardsUpcoming.on('click', function(){
        eDirectory.Event.upcomingEventsCarousel($(this));
    });

    if (cardsUpcoming.length) {
        eDirectory.Event.upcomingEventsCarousel($('[data-id=1]'));
    }

});
