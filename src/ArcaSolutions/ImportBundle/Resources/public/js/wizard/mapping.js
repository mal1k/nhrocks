var eDirectory = eDirectory || {};
eDirectory.Import = eDirectory.Import || {};
eDirectory.Import.Mapping = eDirectory.Import.Mapping || {};

(function () {
    var template = document.querySelector('.mapping-template');
    var container = document.querySelector('#mapping-container');
    var emitter = new EventEmitter();
    var columns = {};

    /**
     * On selectize change
     */
    var onchange = function () {
        updateMapping(this);
        checkRequiredFieldsAndRepeatedField();
        checkNonMatchedFields();
    };

    /**
     * Update mapping
     * @param select
     */
    var updateMapping = function (select) {
        if (select.value) {
            eDirectory.Import.Options.mapping[select.dataset.key] = select.value;
            select.parentElement.classList.remove('warning');
        } else {
            delete eDirectory.Import.Options.mapping[select.dataset.key];
            select.parentElement.classList.add('warning');
        }
    };

    /**
     * Checks if all the required fields are mapped.
     * Emmits the event 'valid' in case all required mapping is informed or 'invalid' otherwise
     */
    var checkRequiredFieldsAndRepeatedField = function () {
        var valid = true;
        var mapping = [];
        var notMapped = [];
        var repeatedMapping = [];
        var requiredFields = eDirectory.Import.Mapping.Required;

        for (var prop in eDirectory.Import.Options.mapping) {
            /* checks if a field is mapped twice */
            if (mapping.indexOf(eDirectory.Import.Options.mapping[prop]) !== -1) {
                var repeatedField = eDirectory.Import.Mapping.Fields[eDirectory.Import.Options.mapping[prop]];

                if (repeatedMapping.indexOf(repeatedField) === -1) {
                    repeatedMapping.push(eDirectory.Import.Mapping.FieldsTrans[eDirectory.Import.Options.mapping[prop]]);
                }

                valid = false;
            }

            mapping.push(eDirectory.Import.Options.mapping[prop]);
        }

        for (var prop in requiredFields) {
            if (requiredFields.hasOwnProperty(prop)) {
                if (mapping.indexOf(prop) === -1) {
                    valid = false;
                    notMapped.push(requiredFields[prop]);
                }
            }
        }

        if (valid) {
            return emitter.emit('valid');
        }

        emitter.emit('invalid', notMapped, repeatedMapping);
    };

    var checkNonMatchedFields = function () {
        var diff = columns.length - Object.keys(eDirectory.Import.Options.mapping).length;

        if (diff == 1)
            return eDirectory.Import.Alert.add(LANG_JS_IMPORT_MISSING_MAPPING_SINGULAR, 'warning');

        if (diff > 0)
            return eDirectory.Import.Alert.add(LANG_JS_IMPORT_MISSING_MAPPING_PLURAL.replace(/%count%/, diff), 'warning');
    };

    /**
     * Emit the following events:
     * `valid` On mapping valid
     * `invalid` On mapping invalid
     * @param event
     * @param callback
     */
    eDirectory.Import.Mapping.on = function (event, callback) {
        emitter.on(event, callback);
    };

    eDirectory.Import.Mapping.DOM = {
        /**
         * Mount the mapping step
         * @param {Array} data
         * @param {Array} headers
         */
        mount: function (data, headers) {
            container.innerHTML = '';
            eDirectory.Import.Options.mapping = {};

            if (headers) {
                this.mountWithHeaders(data, headers);
            } else {
                this.mountWithoutHeaders(data);
            }

            checkRequiredFieldsAndRepeatedField();
            checkNonMatchedFields();
        },

        mountWithHeaders: function (data, headers) {
            columns = headers;
            for (var i = 0; i < headers.length; i++) {
                var header = headers[i];
                var samples = [];

                for (var j = 0; j < data.length; j++) {
                    if (data[j][i] !== undefined) {
                        samples.push(data[j][i]);
                    }
                }

                this.add(headers.indexOf(header), header, samples, true);
            }
        },

        mountWithoutHeaders: function (data) {
            columns = Object.keys(data[0]);

            for (var j = 0; j < columns.length; j++) {
                var samples = [];

                for (var i = 0; i < data.length; i++) {
                    samples.push(data[i][j]);
                }

                var n = parseInt(columns[j]) + 1;
                var title = LANG_JS_IMPORT_COLUMN_PREFIX + ' ' + n;

                this.add(columns[j], title, samples);
            }
        },

        /**
         * Add mapping div
         * @param {Number} key
         * @param {String} title
         * @param {Array} samples
         * @param {boolean} tryToMatch
         */
        add: function (key, title, samples, tryToMatch) {
            var clone = template.cloneNode(true);
            clone.classList.remove('mapping-template');
            clone.style.display = 'flex';
            clone.querySelector('[data-mapping-title]').textContent = title;

            var sampleContainer = clone.querySelector('[data-mapping-sample]');
            samples.forEach(function (data) {
                var li = document.createElement('li');
                li.textContent = data;

                sampleContainer.appendChild(li);
            });

            var select = clone.querySelector('select');
            select.setAttribute('name', title);
            select.setAttribute('data-key', key.toString());

            var matched = tryToMatch ? this.match(title, select) : false;

            if (!matched) {
                clone.querySelector('[data-mapping-belongsto]').classList.add('warning');
            }

            var selectize = $(select).selectize();
            selectize.on('change', onchange);

            container.appendChild(clone);
        },

        /**
         * Tries to automap the field
         * @param field
         * @param select
         * @return boolean
         */
        match: function (field, select) {
            if (field.trim() === '') {
                return false;
            }

            var pattern = new RegExp("(^|\\b)(" + field + ")(\\b|$)", 'i');
            var fields = eDirectory.Import.Mapping.Fields;

            for (var i in fields) {
                var matches = fields[i].match(pattern);

                if (matches != null) {
                    select.value = i;
                    eDirectory.Import.Options.mapping[select.dataset.key] = select.value;
                    return true;
                }
            }

            return false;
        }
    };
})();

(function () {
    var container = document.querySelector('#alert-container');
    var template = document.querySelector('[data-mapping-alert]');

    eDirectory.Import.Mapping.Alert = {
        /**
         *
         * @param {String} message
         * @param {Array} submessages
         * @param {String} type
         */
        add: function (message, submessages, type) {
            var alert = template.cloneNode(true);
            var icon = null;
            var typeClass = null;
            var caretIcon = alert.querySelector('.fa-caret-down');
            submessages = submessages || [];

            switch (type) {
                case 'critical':
                case 'error':
                    icon = 'fa-times-circle';
                    typeClass = 'alert--error';
                    break;
                case 'warning':
                    icon = 'fa-exclamation-triangle';
                    typeClass = 'alert--warning';
                    break;
            }

            alert.querySelector('[data-alert-message]').textContent = message;

            if (submessages.length > 0) {
                var list = alert.querySelector('[data-alert-list]');

                caretIcon.style.display = 'block';

                submessages.forEach(function (content) {
                    var line = parseInt(content.line) + 1;
                    var li = document.createElement('li');

                    li.classList.add('list-group-item');

                    li.textContent = LANG_JS_IMPORT_ROW + ' ' + line + ': ' + content.message;
                    list.appendChild(li);
                });
            }

            alert.querySelector('[data-alert-message]').textContent = message;
            alert.querySelector('[data-alert-icon]').classList.add(icon);
            alert.style.display = 'block';
            alert.classList.add(typeClass);

            container.appendChild(alert);
        },
        clear: function () {
            container.innerHTML = '';
        }
    };

    var resumeContainer = document.querySelector('#mapping-resume');
    var reuploadButton = document.querySelector('#reupload-button');
    eDirectory.Import.Mapping.Resume = {
        /**
         * Shows resume div
         * @param totalValidItens
         * @param totalErrorItens
         */
        show: function (totalValidItens, totalErrorItens) {
            resumeContainer.innerHTML = '';
            reuploadButton.style.display = 'none';
            var message = document.createElement('p');
            message.classList.add('text-left');
            message.classList.add('alert');

            if (totalValidItens > 0) {
                var validCounter = document.createElement('span');
                validCounter.classList.add('success-message');
                validCounter.textContent = (totalValidItens > 1 ?
                    LANG_JS_IMPORT_ROWS_WILL_BE_IMPORTED.replace(/%count%/, totalValidItens) :
                    LANG_JS_IMPORT_ROW_WILL_BE_IMPORTED) + (totalErrorItens == 0 ? '.' : '');
                message.appendChild(validCounter);
            }

            if (totalValidItens > 0 && totalErrorItens > 0) {
                var andSpan = document.createElement('span');
                andSpan.textContent = ' ' + LANG_JS_AND + ' ';
                message.appendChild(andSpan);
            }

            if (totalErrorItens > 0) {
                var invalidCounter = document.createElement('span');
                invalidCounter.classList.add('error-message');
                invalidCounter.textContent = totalErrorItens > 1 ?
                    LANG_JS_IMPORT_ROWS_WONT_BE_IMPORTED.replace(/%count%/, totalErrorItens) :
                    LANG_JS_IMPORT_ROW_WONT_BE_IMPORTED;
                message.appendChild(invalidCounter);
            }

            if (totalValidItens == 0) {
                reuploadButton.style.display = 'block';
            }

            resumeContainer.appendChild(message);
            resumeContainer.style.display = 'block';
        },
        hide: function () {
            reuploadButton.style.display = 'none';
            resumeContainer.style.display = 'none';
        }
    };
})();
