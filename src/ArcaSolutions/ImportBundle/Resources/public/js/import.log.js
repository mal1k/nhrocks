var eDirectory = eDirectory || {};
eDirectory.Import = eDirectory.Import || {};

eDirectory.Import.Log = function () {
    this.response = null;
    this.domainId = document.getElementById("edDomainId").value;
};

eDirectory.Import.Log.prototype.setStatus = function (logId, statusValue) {
    var postData = {
        'importId': logId,
        'statusValue': statusValue
    };

    this.response = function (dataFromAjax) {
        if (dataFromAjax) {
            location.reload();
        }
    };

    this.request(Routing.generate('import_update_status'), postData);
};

eDirectory.Import.Log.prototype.setPage = function (page) {
    this.response = function (dataFromAjax) {
        if (dataFromAjax) {
            $('.items').html('').html(dataFromAjax);
        }
    };

    this.request(Routing.generate('import_paginate', {page: page}), {}, 'html');
};

eDirectory.Import.Log.prototype.request = function (route, data, format) {
    var self = this;

    format = format || 'json';

    if (typeof self.response !== "function") {
        return;
    }

    if (null !== route && (null !== data && typeof data === "object")) {
        data.domainId = self.domainId;

        $.get(route, data, function (data) {
            self.response(data);
        }, format);
    }
};

eDirectory.Import.StatusUpdater = {
    start: function () {
        var self = this;
        var delay = 1000 * 5;

        setTimeout(self.update.bind(self, self.start.bind(self)), delay);
    },
    update: function (callback) {
        var ids = [];

        $('tr[data-import-id]').each(function (i, el) {
            ids.push(el.dataset.importId);
        });

        $.get(Routing.generate('import_status'), {importIds: ids}, function (response) {
            response.forEach(function (importObj) {
                $('li[data-option="' + importObj.id + '"]').remove();

                var el = $('tr[data-import-id="' + importObj.id + '"] [data-import-status]');
                el.text(importObj.status);
                el.removeClass();
                el.addClass('text-center ' + importObj.status_style);

                var $optionList = $('ul[data-option-list="' + importObj.id + '"]');
                var $dropdown = $optionList.parent('.dropdown');

                if (importObj.options.length == 0) {
                    $dropdown.find('[data-toggle]').remove();
                }

                importObj.options.forEach(function (opt) {

                    if ($dropdown.children('[data-toggle]').length == 0) {
                        $('<button type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> ' +
                            '<span class="caret"></span></button>').prependTo($dropdown);
                    }

                    var $li = $('<li>', {'data-option': importObj.id});
                    var $a = $('<a>', {
                        'data-import-log': importObj.id,
                        'data-import-value': opt.value,
                        'data-import-event': 'status',
                        href: '#',
                        text: opt.label
                    });

                    $optionList.append($li.append($a));
                });
            });

            callback();
        });
    }
};

eDirectory.Import.Log.prototype.listener = function () {
    var self = this;

    $(document).on('click', '[data-import-event]', function (e) {
        e.preventDefault();

        if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
            return;
        }

        var element = $(e.currentTarget);

        switch (element.data('import-event')) {
            case 'status':
                if ((importId = element.data('import-log')) && (statusValue = element.data('import-value'))) {
                    self.setStatus(importId, statusValue);
                }
                break;
            case 'pagination':
                if (importPage = element.data('import-page')) {
                    self.setPage(importPage);
                }
                break;
        }
    });
};

var importLog = new eDirectory.Import.Log();

window.addEventListener('load', function () {
    importLog.listener();
    eDirectory.Import.StatusUpdater.start();

    $('#log-table tr.line a[data-toggle]').on('click', function () {
        $(this).closest('tr.line').toggleClass('no-border');
    });
});

