/* Checks namespace existence */
if (typeof eDirectory == 'undefined') {
    eDirectory = {};
}

if (typeof eDirectory.Event == 'undefined') {
    eDirectory.Event = {};
}

/**
 * Used to prepare the parameters to use in the ajax
 *
 * @param target_div Element's Identification
 * @param max_success Total of days shown
 * @param max_executed Max attempts
 */
eDirectory.Event.upcomingEventsOneBlock = function (target_div, max_success, max_executed) {
    var today = $(target_div).data('today');

    if (!today) {
        return;
    }

    today = today.split('-');
    today = new Date(today[1] + '/' + today[2] + '/' + today[0]);

    eDirectory.Event.upcomingEventsAjaxOneBlock(target_div, today, max_success, max_executed, 0);
}

/**
 * Get events from a day
 *
 * @param target_div Element's Identification
 * @param date
 * @param max_success Total of days shown
 * @param max_executed Max attempts
 * @param success Number of days with events
 */
eDirectory.Event.upcomingEventsAjaxOneBlock = function (target_div, date, max_success, max_executed, success) {

    $(target_div).find('.cards-slider').html('<div id="loading" class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');

    $.get(Routing.generate('event_upcoming', {
            day: date.getDate(),
            month: date.getMonth() + 1, //Note: January is 0, February is 1, and so on. DO NOT CHANGE THIS LINE
            year: date.getFullYear(),
            wholeMonth: 1
        }))
        .done(function (data) {
            if (data.events.length > 0) {
                $(target_div).find('.cards-slider').html('');

                var events_items = [];
                for (var event in data.events) {
                    if (success < max_success) {
                        success++;
                        events_items.push(data.events[event]);
                    }
                }

                var events = $.templates('#upcoming-event-box').render(events_items);
                $(target_div).removeClass('hidden');
                $(target_div).find('.cards-slider').append(events);

                var $cards = $(target_div).find('.cards-slider').flickity({
                    imagesLoaded: true,
                    cellAlign: 'left',
                    contain: true,
                    arrowShape: {
                        x0: 25,
                        x1: 55, y1: 30,
                        x2: 65, y2: 20,
                        x3: 45
                    }
                });

                $cards.on('staticClick.flickity', function(event, pointer, cellElement, cellIndex){
                    $cards.flickity('selectCell', cellIndex);
                });
            }
        });
};

/**
 *  Upcoming Event for event home.
 *  Works like a calendar. Used in Upcoming Extension.
 *
 * @param target_div
 */
eDirectory.Event.upcomingEventsCarousel = function (target_div) {
    var day = $(target_div).data('day');

    if (!day) {
        return;
    }

    day = day.split('-');
    day = new Date(day[1] + '/' + day[2] + '/' + day[0]);

    /* div#id */
    var id = day.getDate() + '' + (day.getMonth() + 1) + '' + day.getFullYear(); //Note: January is 0, February is 1, and so on.
    /* div to add events */
    var block_container = $('.parent-cards-list');

    /* class used in view */
    block_container.find('.cards-list:visible').hide();

    /* day already searched */
    if (block_container.find('#' + id).length > 0) {
        block_container.find('#' + id).show();
        return;
    }

    block_container.append('<div id="loading" class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');
    $.get(Routing.generate('event_upcoming', {
            day: day.getDate(),
            month: day.getMonth() + 1, //Note: January is 0, February is 1, and so on. DO NOT CHANGE THIS LINE
            year: day.getFullYear()
        }))
        .done(function (data) {
            block_container.find('#loading').remove();

            /* check if a event was found */
            if (!(data.events.length > 0)) {
                return;
            }

            /* #id used here */
            var events = $.templates('#upcoming-event-carousel').render({data: data,id: id});
            /* block_container used here */
            $('.parent-cards-list').append(events);

            lazyLoadInstance.update();
        }).fail(function () {
        block_container.find('#loading').remove();
    });
};

/**
 *  Upcoming Event for event home.
 *  Works like a calendar. Used in Upcoming Extension.
 *
 * @param target_div
 */
eDirectory.Event.upcomingEventsCalendar = function (date) {
    var day = new Date(date);

    /* div#id */
    var id = day.getDate() + '' + (day.getMonth() + 1) + '' + day.getFullYear(); //Note: January is 0, February is 1, and so on.
    /* div to add events */
    var block_container = $('.calendar-events');

    /* class used in view */
    block_container.find('.cards-list-calendar').hide();

    /* day already searched */
    if (block_container.find('#' + id).length > 0) {
        block_container.find('#' + id).show();
        return;
    }

    block_container.addClass('is-loading');
    block_container.prepend('<i id="loading" class="fa fa-spinner fa-spin fa-3x"></i>');
    $.get(Routing.generate('event_upcoming', {
        day: day.getDate(),
        month: day.getMonth() + 1, //Note: January is 0, February is 1, and so on. DO NOT CHANGE THIS LINE
        year: day.getFullYear()
    }))
        .done(function (data) {
            block_container.find('#loading').remove();
            block_container.removeClass('is-loading');

            /* check if a event was found */
            if (!(data.events.length > 0)) {
                return;
            }

            /* #id used here */
            var events = $.templates('#upcoming-event-calendar').render({data: data,id: id});
            /* block_container used here */
            block_container.prepend(events);

            lazyLoadInstance.update();
        }).fail(function () {
        block_container.find('#loading').remove();
        block_container.removeClass('is-loading');
    });
};

/**
 *  Populate dates on change Month.
 *
 * @param date
 * @param eventDateObjs
 */
eDirectory.Event.populateDaysCalendar = function (date) {

    var day = new Date(date);

    var now = new Date(Date.now());

    if (day.getMonth() <= (now.getMonth())) {
        return;
    }

    $.get(Routing.generate('event_calendar', {
        day: 1,
        month: day.getMonth() + 1, //Note: January is 0, February is 1, and so on. DO NOT CHANGE THIS LINE
        year: day.getFullYear()
    })).done(function (data) {
        data.result.map(function (elem) {
            var date = new Date(elem.start + ' GMT+00:00');
            date.setDate(date.getDate() + 1);

            $('.day[data-date=' + date.getTime() + ']').removeClass('disabled').addClass('has-event');
        });
    });
};
