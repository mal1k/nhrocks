var eDirectory = eDirectory || {};
eDirectory.Import = eDirectory.Import || {};

eDirectory.Import.Options = {
    file: null,
    type: null, // listing or event
    mapping: {},
    errors: [],
    total_itens: 0,
    total_errors: 0
};

Object.defineProperties(eDirectory.Import.Options, {
    hasHeader: {
        enumerable: true,
        get: function () {
            return document.querySelector('#header-checkbox').checked;
        }
    },
    active: {
        enumerable: true,
        get: function () {
            return document.querySelector('#checkbox-active').checked;
        }
    },
    featuredCategories: {
        enumerable: true,
        get: function () {
            return document.querySelector('#checkbox-featured').checked;
        }
    },
    overwrite: {
        enumerable: true,
        get: function () {
            return document.querySelector('#checkbox-overwrite').checked;
        }
    },
    updateUrl: {
        enumerable: true,
        get: function () {
            return document.querySelector('#checkbox-update-url').checked;
        }
    },
    level: {
        enumerable: true,
        get: function () {
            return document.querySelector('#select-level').value;
        }
    },
    account: {
        enumerable: true,
        get: function () {
            return document.querySelector('#select-account').value;
        }
    },
    csvSeparator: {
        enumerable: true,
        get: function () {
            return document.querySelector('#csv-delimiter').value;
        }
    }
});

/**
 * Send import options to server
 *
 * @param callback
 */
eDirectory.Import.save = function (callback) {
    var xhr = new XMLHttpRequest();
    var data = new FormData();

    for (var prop in eDirectory.Import.Options) {
        if (eDirectory.Import.Options.hasOwnProperty(prop)) {
            var value = eDirectory.Import.Options[prop];

            if (prop == 'file') {
                data.append('file', value.file);
                continue;
            }

            switch (typeof value) {
                case 'object':
                    data.append(prop, JSON.stringify(value));
                    break;
                case 'boolean':
                    data.append(prop, value ? '1' : '0');
                    break;
                default:
                    if (value !== null || value !== undefined && value.length > 0)
                        data.append(prop, value);
            }
        }
    }

    data.append('domainId', document.getElementById("edDomainId").value);
    xhr.open('POST', Routing.generate('import_finish'));
    xhr.responseType = 'json';
    xhr.onreadystatechange = function () {
        if (this.readyState == XMLHttpRequest.DONE && callback) {
            callback(typeof xhr.response == 'string' ? JSON.parse(xhr.response) : xhr.response);
        }
    };

    xhr.send(data);
};

window.addEventListener('load', function () {
    var ddZone = document.querySelector('#dd-zone');
    var filePicker = document.querySelector('#file-picker');
    var ftpFilePicker = document.querySelector('#ftp-file-picker');
    var inputFile = document.querySelector('#import-file');
    var buttonReupload = document.querySelector('#reupload-button');

    ddZone.ondragover = function (e) {
        e.preventDefault();
        e.stopPropagation();
        ddZone.querySelector('.dd-hover-file').style.visibility = 'visible';
    };
    ddZone.ondragleave = function (e) {
        if (e.target == ddZone) {
            ddZone.querySelector('.dd-hover-file').style.visibility = 'hidden';
            e.preventDefault();
            e.stopPropagation();
        }
    };
    ddZone.ondrop = function (e) {
        e.preventDefault();
        e.stopPropagation();
        ddZone.querySelector('.dd-hover-file').style.visibility = 'hidden';

        if (e.dataTransfer.files.length !== 1)
            return;

        setImportFileFromInput(e.dataTransfer.files[0]);
    };

    filePicker.onclick = function () {
        inputFile.click();
    };

    inputFile.onchange = function () {
        if (this.files.length !== 1)
            return;

        setImportFileFromInput(this.files[0]);

        this.value = '';
    };

    buttonReupload.addEventListener('click', function () {
        stepWizard.goTo(1);
    });

    if (ftpFilePicker) {
        ftpFilePicker.onclick = function () {
            var input = document.querySelector('[name="ftp_filename"]:checked');

            if (!input) return;

            setImportFileFromFTP(input.value, input.dataset.fileName, input.dataset.fileSize);
        };
    }

    document.querySelector('#checkbox-same-account').onchange = function () {
        if (this.checked) {
            document.querySelector('#select-account').selectize.enable();
        } else {
            document.querySelector('#select-account').selectize.disable();
        }
    };

    $('tr[data-radio]').on('click', function () {
        var radio = $(this).find('input[type=radio]')[0];
        radio.checked = true;
    });

    var stepWizard = new eDirectory.StepWizard('#styleguide');
    window.steps = stepWizard;

    stepWizard.initialize();

    stepWizard.on('onnext', function () {
        eDirectory.Import.Alert.clear();

        switch (this.currentStep) {
            case 2:
                stepWizard.disableNext();

                processFile(function (data, fields) {
                    var file = eDirectory.Import.Options.file;

                    if (eDirectory.Import.Config.block_import && file.length > eDirectory.Import.Config.available_items) {
                        stepWizard.enableBack();
                        stepWizard.back();
                        stepWizard.disableNext();

                        var message = LANG_JS_UPGRADEPLAN_MSG.replace(/%listing_limit%/, eDirectory.Import.Config.listing_limit);
                        message = message.replace(/%available_items%/, eDirectory.Import.Config.available_items);

                        eDirectory.Import.Alert.add(message);

                        return;
                    }

                    if ((file.extension == 'xls' || file.extension == 'xlsx') && file.length > eDirectory.Import.Config.xlsx_max_rows) {
                        stepWizard.enableBack();
                        stepWizard.back();
                        stepWizard.disableNext();
                        eDirectory.Import.Alert.add(
                            LANG_JS_IMPORT_XLSX_MAX_ROWS.replace(/%count%/, eDirectory.Import.Config.xlsx_max_rows)
                        );

                        return;
                    }

                    eDirectory.Import.Mapping.DOM.mount(data, fields);
                });
                break;
            case 3:
                eDirectory.Import.Mapping.Resume.hide();

                stepWizard.disableNext();
                stepWizard.hideStepIcon(stepWizard.currentStep);
                stepWizard.showLoading();

                eDirectory.Import.Mapping.Alert.clear();
                eDirectory.Import.Options.file.uploadAndValidate(function (result) {
                    stepWizard.enableBack();
                    stepWizard.hideLoading();

                    eDirectory.Import.Mapping.Resume.show(result.total_valid_itens, result.total_invalid_itens);

                    switch (result.status) {
                        case 'success':
                            stepWizard.enableNext();
                            stepWizard.next();
                            break;
                        case 'warning':
                            var message = result.total_invalid_itens > 1 ?
                                LANG_JS_IMPORT_ROWS_WONT_BE_IMPORTED.replace(/%count%/, result.total_invalid_itens) :
                                LANG_JS_IMPORT_ROW_WONT_BE_IMPORTED;

                            eDirectory.Import.Mapping.Alert.add(message, result.errors, result.status);

                            stepWizard.enableNext();
                            stepWizard.showStepIcon(stepWizard.currentStep, 'warning');
                            break;
                        case 'error':
                            eDirectory.Import.Mapping.Alert.add(
                                result.total_itens > 1 ?
                                    LANG_JS_IMPORT_ROWS_WONT_BE_IMPORTED.replace(/%count%/, result.total_itens) :
                                    LANG_JS_IMPORT_ROW_WONT_BE_IMPORTED,
                                result.errors,
                                result.status
                            );

                            stepWizard.showStepIcon(stepWizard.currentStep, 'error');
                            break;
                        case 'critical':
                            eDirectory.Import.Mapping.Alert.add(result.message, [], result.status);
                            return;
                    }

                    eDirectory.Import.Options.file.file = result.file;
                    eDirectory.Import.Options.errors = result.errors;
                    eDirectory.Import.Options.total_itens = result.total_itens;
                    eDirectory.Import.Options.total_errors = result.total_invalid_itens;
                });
                break;
            case 5:
                stepWizard.showLoading();
                stepWizard.disableBack();
                stepWizard.disableNext();

                eDirectory.Import.save(function (response) {
                    stepWizard.hideLoading();

                    if (response.status == 'pending') {
                        document.querySelector('div[data-import-pending]').style.display = 'block';
                    }

                    if (response.status == 'completed') {
                        document.querySelector('div[data-import-completed]').style.display = 'block';
                    }

                    if (response.status == 'pending' || response.status == 'completed') {
                        document.querySelector('div[data-import]').style.display = 'block';
                    }

                    if (response.status == 'error') {
                        eDirectory.Import.Alert.add(response.message, 'error', response.link);
                    }

                    document.querySelector('#cancel-import').style.display = 'none';
                    document.querySelector('#download-template').style.display = 'none';
                    stepWizard.disableStepCounter();
                    stepWizard.hideNextButton();
                    stepWizard.hideBackButton();
                });
                break;
        }

        if(stepWizard.currentStep !== 5)
            stepWizard.enableBack();
    });

    stepWizard.on('onback', function () {
        eDirectory.Import.Alert.clear();
        switch (this.currentStep) {
            case 1:
                stepWizard.disableBack();

                if (eDirectory.Import.Options.file) {
                    stepWizard.enableNext();
                }

                break;
            case 2:
                stepWizard.hideStepIcon(stepWizard.currentStep + 1);
                stepWizard.enableNext();
                break;
            case 3:
                if (eDirectory.Import.Options.total_errors == 0) {
                    stepWizard.back();
                }
                break;
        }
    });

    eDirectory.Import.Mapping.on('valid', function () {
        eDirectory.Import.Alert.clear();
        stepWizard.enableNext();
    });

    eDirectory.Import.Mapping.on('invalid', function (notMappedFields, fieldMappedTwice) {
        stepWizard.disableNext();

        eDirectory.Import.Alert.clear();

        notMappedFields.forEach(function (field) {
            var message = LANG_JS_IMPORT_MISSING_REQUIRED_MAPPING.replace(/%field%/, field);
            eDirectory.Import.Alert.add(message, 'error');
        });

        fieldMappedTwice.forEach(function (field) {
            var message = LANG_JS_IMPORT_FIELD_MAPPED_TWICE.replace(/%field%/, field);
            eDirectory.Import.Alert.add(message, 'error');
        });
    });

    /**
     * Update import file
     *
     * @param {File} file
     */
    var setImportFileFromInput = function (file) {
        eDirectory.Import.Alert.clear();
        stepWizard.disableNext();
        clearFileThumb();

        var importFile = new eDirectory.Import.File();
        importFile.file = file;
        importFile.name = file.name;
        importFile.size = file.size;

        if (eDirectory.Import.Config.extensions.indexOf(importFile.extension) === -1) {
            eDirectory.Import.Alert.add(LANG_JS_ALERT_FILEEXTENSION +
                eDirectory.Import.Config.extensions.join(', ') + '.', 'error');

            return;
        }

        var sizeInMB = importFile.size / 1000000;
        if (sizeInMB > eDirectory.Import.Config.size) {
            eDirectory.Import.Alert.add(LANG_JS_ALERT_FILESIZE +
                eDirectory.Import.Config.size + 'mb.', 'error');

            return;
        }

        eDirectory.Import.Options.file = importFile;
        updateFileThumb(importFile);

        var fr = new FileReader;
        fr.onload = function () {
            if (['xls', 'xlsx'].indexOf(importFile.extension) !== -1) {
                return importFile.content = new Uint8Array(this.result);
            }

            importFile.content = this.result;
        };

        if (['xls', 'xlsx'].indexOf(importFile.extension) !== -1) {
            fr.readAsArrayBuffer(file);
        } else {
            fr.readAsText(file);
        }

        onFileSelected();
    };

    var onFileSelected = function () {
        stepWizard.enableNext();

        if (eDirectory.Import.Options.file.extension == 'csv') {
            document.querySelector('#csv-options').style.display = 'block';
        } else {
            document.querySelector('#csv-options').style.display = 'none';
        }
    };

    var processFile = function (callback) {
        eDirectory.Import.FileParser.parse(eDirectory.Import.Options.file, {
            header: eDirectory.Import.Options.hasHeader,
            lines: eDirectory.Import.Config.preview_length,
            success: callback,
            delimiter: eDirectory.Import.Options.csvSeparator
        });
    };

    /**
     * @param {eDirectory.Import.File} file
     */
    var updateFileThumb = function (file) {
        $('#file-name').text(file.name);
        $('#file-thumb').removeClass().addClass('img-file ' + file.extension);
        $('#drag-message').hide();
        filePicker.querySelector('.state-file-empty').style.display = 'none';
        filePicker.querySelector('.state-file-picked').style.display = 'block';
    };

    var clearFileThumb = function () {
        $('#file-name').text('');
        $('#file-thumb').removeClass();
        $('#drag-message').show();
        filePicker.querySelector('.state-file-empty').style.display = 'block';
        filePicker.querySelector('.state-file-picked').style.display = 'none';
    };

    var setImportFileFromFTP = function (path, name, size) {
        clearFileThumb();
        eDirectory.Import.Alert.clear();

        var file = new eDirectory.Import.File();
        file.name = name;
        file.size = size;
        file.file = path;

        var xhr = new XMLHttpRequest();
        xhr.open('GET', path);

        if (['xls', 'xlsx'].indexOf(file.extension) !== -1) {
            xhr.responseType = 'arraybuffer';
        }

        $('#file-loader').show();
        xhr.onreadystatechange = function () {
            if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
                $('#file-loader').hide();

                if (['xls', 'xlsx'].indexOf(file.extension) !== -1) {
                    file.content = new Uint8Array(this.response);
                } else {
                    file.content = this.response;
                }

                if (eDirectory.Import.Config.extensions.indexOf(file.extension) === -1) {
                    eDirectory.Import.Alert.add(LANG_JS_ALERT_FILEEXTENSION +
                        eDirectory.Import.Config.extensions.join(', ') + '.', 'error');

                    return;
                }

                eDirectory.Import.Options.file = file;
                updateFileThumb(file);
                onFileSelected();
            }
        };

        xhr.send();
    };

    document.querySelector('[data-main-loader]').remove();
    document.querySelector('#styleguide-wrapper').style.display = 'block';
});
