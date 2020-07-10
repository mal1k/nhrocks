var eDirectory = eDirectory || {};

/**
 * Step wizard component
 *
 * @param {String} selector
 * @event onnext Fires when going to the next step
 * @event onback Fires when going to the previous step
 * @event finish Fires when finishes the last step
 * @see https://github.com/Olical/EventEmitter
 * @constructor
 */
eDirectory.StepWizard = function (selector) {
    var _self = this;
    _self.currentStep = 1;

    var _container = document.querySelector(selector);
    var _steps = {};
    var _stepsCounter = {};
    var _btnNext = _container.querySelector('[data-next]');
    var _btnBack = _container.querySelector('[data-back]');
    var _loading = document.querySelector('[data-step-loader]');
    var _nextIsEnabled = true;
    var _backIsEnabled = true;

    /**
     * Register events and create the default state
     */
    _self.initialize = function () {
        var query = _container.querySelectorAll('[data-step]');

        for (var i = 0; i < query.length; i++) {
            var step = query[i];
            _steps[step.dataset.step] = step;

            if (step.dataset.step != _self.currentStep) {
                step.style.display = 'none';
            }
        }

        if (_self.currentStep == 1) {
            _self.hideBackButton();
        }

        query = _container.querySelectorAll('[data-step-counter]');
        for (var i = 0; i < query.length; i++) {
            var step = query[i].dataset.stepCounter;
            _stepsCounter[step] = query[i];
            _stepsCounter[step].addEventListener('click', _self.goTo.bind(null, step));
        }

        _btnNext.addEventListener('click', function () {
            _self.next();
            this.blur();
        });

        _btnBack.addEventListener('click', function () {
            _self.back();
            this.blur();
        });

        _self.disableBack();
        _self.disableNext();
    };

    /**
     * Advance or back the step counter element
     *
     * @param {Number} from Current step
     * @param {Number} to Step
     * @private
     */
    var _moveCounter = function (from, to) {
        var toElement = _stepsCounter[to];
        var fromElement = _stepsCounter[from];

        if (toElement) {
            toElement.classList.remove('visited');
        }

        if (to < from && fromElement) {
            fromElement.classList.remove('current');
            fromElement.classList.remove('visited');
            return;
        }

        if (fromElement)
            fromElement.classList.add('visited');

        if (toElement)
            toElement.classList.add('current');
    };

    /**
     * Go to next step
     */
    _self.next = function () {
        if (!_nextIsEnabled)
            return;

        if (_self.currentStep >= _steps.length)
            return;

        _hideStep(_self.currentStep);

        _moveCounter(_self.currentStep, ++_self.currentStep);

        _showStep(_self.currentStep);

        if (_self.currentStep == _steps.length) {
            _self.hideNextButton();
        } else {
            _self.showNextButton();
            _self.showBackButton();
        }

        _self.emit('onnext', _self);
    };

    /**
     * Go one step back
     */
    _self.back = function () {
        if (!_backIsEnabled)
            return;

        if (_self.currentStep == 1)
            return;

        _self.emit('beforeback', _self);

        _hideStep(_self.currentStep);

        _moveCounter(_self.currentStep, --_self.currentStep);

        _showStep(_self.currentStep);

        if (_self.currentStep == 1) {
            _self.hideBackButton();
        }

        _self.emit('onback', _self);
    };

    /**
     * Disable next button
     */
    _self.disableNext = function () {
        if (_nextIsEnabled) {
            _btnNext.classList.add('btn--disabled');
            _nextIsEnabled = false;
        }
    };

    /**
     * Enable next button
     */
    _self.enableNext = function () {
        if (!_nextIsEnabled) {
            _btnNext.classList.remove('btn--disabled');
            _nextIsEnabled = true;
        }
    };

    /**
     * Enable back button
     */
    _self.enableBack = function () {
        if (!_backIsEnabled) {
            _btnBack.classList.remove('btn--disabled');
            _backIsEnabled = true;
        }
    };

    /**
     * Disable back button
     */
    _self.disableBack = function () {
        if (_backIsEnabled) {
            _btnBack.classList.add('btn--disabled');
            _backIsEnabled = false;
        }
    };

    /**
     * Show step warning
     *
     * @param {Number} step
     * @param {String} type
     */
    _self.showStepIcon = function (step, type) {
        var el = document.querySelector('i[data-step-warning="' + step + '"]');
        el.classList.add(type);
        el.style.display = 'inline-block';
    };

    /**
     * Hide step warning
     *
     * @param {Number} step
     */
    _self.hideStepIcon = function (step) {
        var el = document.querySelector('i[data-step-warning="' + step + '"]');
        el.classList.remove('error');
        el.classList.remove('warning');
        el.style.display = 'none';
    };

    /**
     * Hides step content
     *
     * @param {Number} step
     * @private
     */
    var _hideStep = function (step) {
        var el = _steps[step];

        if (el) el.style.display = 'none';
    };

    /**
     * Shows step content
     *
     * @param {Number} step
     * @private
     */
    var _showStep = function (step) {
        var el = _steps[step];

        if (el) el.style.display = 'block';
    };

    _self.showNextButton = function () {
        _btnNext.style.display = 'block';
    };

    _self.hideNextButton = function () {
        _btnNext.style.display = 'none';
    };

    _self.showBackButton = function () {
        _btnBack.style.display = 'block';
    };

    _self.hideBackButton = function () {
        _btnBack.style.display = 'none';
    };

    _self.showLoading = function () {
        _self.hideLoading();

        var step = _steps[_self.currentStep];
        var clone = _loading.cloneNode(true);
        clone.style.display = 'block';
        step.appendChild(clone);
    };

    _self.hideLoading = function () {
        var step = _steps[_self.currentStep];
        var loader = step.querySelector('[data-step-loader]');

        if (loader) {
            loader.remove();
        }
    };

    _self.goTo = function (step) {
        while (_self.currentStep != step) {
            if (step > _self.currentStep && !_nextIsEnabled) return;
            if (step < _self.currentStep && !_backIsEnabled) return;

            step > _self.currentStep ? _self.next() : _self.back();
        }
    };

    _self.disableStepCounter = function () {
        var el = document.querySelector('#step-counter');
        el.outerHTML = el.outerHTML;
    };
};

eDirectory.StepWizard.prototype = new EventEmitter();

