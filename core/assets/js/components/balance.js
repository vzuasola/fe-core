import xhr from "BaseVendor/reqwest";
import * as utility from "Base/utility";

/**
 * Load Balance
 *
 * @deprecated use Base/balance/balance-view
 */
function LoadBalance(triggerEl, opts) {
    var _this = this;

    _this.el = triggerEl || null;
    _this.defaults = {
        path: utility.url('ajax/total-balance'),
        errorMessage: 'Error retrieving.',
        debug: false
    };
    _this.opts = opts || _this.defaults;
    _this.textContent = [];
    _this.autoRefreshTime = 200000; // 200 seconds
    _this.timerId = null;

    this.setDefaults = function () {
        for (var key in _this.defaults) {
            if (typeof _this.opts[key] === 'undefined') {
                _this.opts[key] = _this.defaults[key];
            }
        }
    };

    this.getBalance = function () {
        _this.logMe('Get Balance started');

        // Reset the timer
        if (_this.timerId !== null) {
            clearTimeout(_this.timerId);
        }

        if (_this.el !== null) {
            document.querySelector('.refresh-loading').style.display = 'inline-block';
            _this.el.style.display = 'none';
        }

        xhr({
            url: _this.opts.path,
            type: 'json'
        }).then(function (res) {
            if (res.balance !== null) {
                document.getElementById('player-balance').innerHTML = res.balance;
            } else {
                document.getElementById('player-balance').innerHTML = _this.opts.errorMessage;
            }
        }).fail(function (err, msg) {
            // Any request failure will have the error retrieving value
            document.getElementById('player-balance').innerHTML = _this.opts.errorMessage;
        }).always(function (res) {
            _this.logMe('Get Balance completed');

            if (_this.el !== null) {
                _this.el.style.display = 'inline-block';
                document.querySelector('.refresh-loading').style.display = 'none';
            }

            _this.autoRefresh();
        });
    };

    this.autoRefresh = function () {
        _this.logMe('Auto refresh triggered');
        _this.timerId = setTimeout(_this.getBalance, _this.autoRefreshTime);
    };

    this.logMe = function (msg) {
        if (_this.opts.debug) {
            var d = new Date(),
                dl = [
                    d.toDateString(),
                    d.toTimeString().slice(0, 8)
                ];

            console.log('[DEBUG][' + dl.join(' - ') + ']\n \t+ ' + msg);
        }
    };

    this.init = function () {
        try {
            if (_this.el === null || typeof _this.el === 'undefined') {
                throw new Error('No trigger/refresh element detected');
            }

            utility.addEventListener(_this.el, 'click', _this.getBalance);
        } catch (err) {
            console.log('[LoadBalance instantiation error]', err);
        }

        _this.setDefaults();
        _this.getBalance();
    };

    this.init();
}

utility.ready(function () {
    document.getElementById('player-balance') !== null && new LoadBalance(
        document.querySelector('.refresh-balance'), {
            errorMessage: app.settings.balanceError
        }
    );
});
