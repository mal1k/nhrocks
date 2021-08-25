var MixpanelHelper = {};

/**
 * Track an event.
 *
 * @param {String} event
 * @param {Object} properties
 * @param {Function} callback
 */
MixpanelHelper.track = function (event, properties, callback) {
    var _properties = properties || {};

    if (typeof mixpanel === "undefined") {
        return;
    }

    if (_properties.tag) {
        event = '[' + _properties.tag + '] ' + event;
        delete _properties.tag;
    }

    if (MIXPANEL_DISTINCTID) {
        mixpanel.identify(MIXPANEL_DISTINCTID);
        mixpanel.track(event, properties, callback);
    }

    if(callback) {
        setTimeout(callback, 500);
    }
};

/**
 * Get mixpanel distinct id.
 * @return string|null
 */
MixpanelHelper.getDistinctId = function () {
    if (typeof mixpanel === "undefined") {
        return null;
    }

    return mixpanel.get_distinct_id();
};

$(function () {

    if (typeof mixpanel === "undefined") {
        return;
    }

    var supports = [
        'click', 'blur', 'focus',
        'focusin', 'focusout', 'dblclick',
        'change', 'select', 'submit'
    ];

    $('body').on(supports.join(' '), '[data-mixpanel-event]', function (e) {
        var $el = $(e.currentTarget),
            trigger = $el.data('mixpanel-trigger') || 'click',
            properties = $el.data('mixpanel-properties') || {},
            event = $el.data('mixpanel-event');

        if (!event) {
            return;
        }

        if (trigger !== e.type) {
            return;
        }

        if (e.target.tagName === 'A') {
            e.preventDefault();
            return MixpanelHelper.track(event, properties, redirectAfterTrack.bind(null, $el));
        }

        MixpanelHelper.track(event, properties);
    });

    var redirectAfterTrack = function ($el) {
        var el_target = $el.attr('target') || '_self';

        if (typeof $el.attr('href') != "undefined") {
            window.open($el.attr('href'), el_target);
        }
    };
});
