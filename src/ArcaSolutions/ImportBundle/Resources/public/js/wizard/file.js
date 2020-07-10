var eDirectory = eDirectory || {};
eDirectory.Import = eDirectory.Import || {};

/**
 * @constructor
 */
eDirectory.Import.File = function () {
    var _self = this,
        _extensionPattern = /\.([A-Za-z0-9]{1,6})$/;

    /**
     * Import Log ID
     * @type {Number}
     */
    _self.id = null;

    /**
     * File name
     * @type {String}
     */
    _self.name = null;

    /**
     * File raw content
     * @type {String|Uint8Array}
     */
    _self.content = null;

    /**
     * File size in bytes
     * @type {Number}
     */
    _self.size = null;

    /**
     * File parse errors
     * @type {Array}
     */
    _self.errors = [];

    /**
     * Js file to be processed
     * @type {File|String}
     */
    _self.file = null;

    /**
     * Parsed data from file
     * @type {Object}
     */
    _self.data = null;

    /**
     * Total file rows
     * @type {Number}
     */
    _self.length = null;

    Object.defineProperties(this, {
        extension: {
            enumerable: true,
            get: function () {
                var matches = this.name.match(_extensionPattern);

                if (matches[1] !== undefined) {
                    return matches[1];
                }

                return null;
            }
        }
    });

    /**
     * Upload file to server
     * @param {Function} callback
     */
    this.uploadAndValidate = function (callback) {
        if (!_self.file) {
            return null;
        }

        var formData = new FormData();
        var hasHeader = eDirectory.Import.Options.hasHeader ? 1 : 0;
        formData.append('file', _self.file);
        formData.append('hasHeader', hasHeader);
        formData.append('mapping', JSON.stringify(eDirectory.Import.Options.mapping));
        formData.append('type', eDirectory.Import.Options.type);
        formData.append('domainId', document.getElementById("edDomainId").value);

        if (eDirectory.Import.Options.csvSeparator)
            formData.append('separator', eDirectory.Import.Options.csvSeparator);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', Routing.generate('import_file_upload'), true);
        xhr.responseType = 'json';
        xhr.onreadystatechange = function () {
            if (this.readyState === XMLHttpRequest.DONE && callback) {
                callback(typeof xhr.response == 'string' ? JSON.parse(xhr.response) : xhr.response);
            }
        };

        xhr.send(formData);
    };
};

eDirectory.Import.FileParser = {
    /**
     * Parse import file
     * @param {eDirectory.Import.File} file
     * @param {Object} options
     */
    parse: function (file, options) {
        var parser = null;

        switch (file.extension) {
            case 'csv':
                parser = eDirectory.Import.CsvFileParser;
                break;
            case 'xls':
            case 'xlsx':
                parser = eDirectory.Import.XlsFileParser;
                break;
        }

        parser.parse(file, options);
    }
};

eDirectory.Import.CsvFileParser = {
    /**
     * Parses csv file
     *
     * @param {eDirectory.Import.File} file
     * @param {Object} options
     * @see https://github.com/mholt/PapaParse
     */
    parse: function (file, options) {
        options.header = options.header || false;

        Papa.parse(file.content, {
            complete: function (result) {
                var normalized = [];

                result.data.forEach(function (obj) {
                    var values = [];

                    for (var prop in obj) {
                        if (obj.hasOwnProperty(prop)) values.push(obj[prop]);
                    }

                    normalized.push(values);
                });

                file.length = file.content.match(/(?:"(?:[^"]|"")*"|[^,\n]*)(?:,(?:"(?:[^"]|"")*"|[^,\n]*))*\n/g).length - 1;
                options.success(normalized, result.meta.fields);
            },
            header: options.header,
            preview: options.lines,
            delimiter: options.delimiter,
            skipEmptyLines: true
        });
    }
};

eDirectory.Import.XlsFileParser = {
    /**
     * Parse xls and xlsx files
     *
     * @param {eDirectory.Import.File} file
     * @param {Object} options
     * @see https://github.com/sheetjs/js-xlsx
     */
    parse: function (file, options) {
        options.type = file.content instanceof Uint8Array ? 'array' : 'binary';

        var parseOptions = {header: 1, blankrows: false};
        var workbook = XLSX.read(file.content, {type: options.type});
        var worksheet = workbook.Sheets[workbook.Workbook.Sheets[0].name];

        if (options.lines) {
            options.lines = options.header ? options.lines + 1 : options.lines;
            //parseOptions.range = worksheet['!ref'].replace(/[0-9]+$/, options.lines);
        }

        var json = XLSX.utils.sheet_to_json(worksheet, parseOptions);
        var headers = null;

        if (options.header) {
            headers = json[0];
            json = json.slice(1);
        }

        file.length = json.length;

        if (options.lines) json = json.slice(0, options.lines);

        if (options.success) options.success(json, headers);
    }
};
