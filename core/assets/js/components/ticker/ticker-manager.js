import * as utility from "Base/utility";
import Ticker from "Base/ticker/ticker";

/**
 * Jackpot ticker
 */
(function () {

    "use strict";

    // Activate Ticker on elements with 'data-ticker' attribute
    var tickerElems = document.querySelectorAll('[data-ticker]');

    // Default values
    var defaults = {
        step: 10,               // count/step added per interval
        interval: 100,           // ticking interval (milliseconds)
        start: 0,               // Ticker start value
        end: null,               // Ticker end value
        delay: 0,           // Delay before ticking start (milliseconds)
        prepend: '',            // Text to add before the value
        append: ''             // Text to add after the value
    };

    // Each Ticker elements
    utility.forEach(tickerElems, function (item) {

        if (item.getAttribute('data-ticker') === 'true') {

            var dataStep = parseFloat(item.getAttribute('data-ticker-step'), 10),
                step = isNaN(dataStep) ? defaults.step : dataStep;

            var dataStart = parseFloat(item.getAttribute('data-ticker-start'), 10),
                start = isNaN(dataStart) ? defaults.start : dataStart;

            var dataEnd = parseFloat(item.getAttribute('data-ticker-end'), 10),
                end = isNaN(dataEnd) ? defaults.end : dataEnd;

            var dataPrepend = item.getAttribute('data-ticker-prepend'),
                prepend = (dataPrepend === null) ? defaults.prepend : dataPrepend;

            var dataAppend = item.getAttribute('data-ticker-append'),
                append = (dataAppend === null) ? defaults.append : dataAppend;

            var dataDelay = parseInt(item.getAttribute('data-ticker-delay')),
                delay = isNaN(dataDelay) ? defaults.delay : dataDelay;

            var dataInterval = parseInt(item.getAttribute('data-ticker-interval')),
                interval = isNaN(dataInterval) ? defaults.interval : dataInterval;

            var value = start;

            // Jackpot ticker instance
            new Ticker(interval, delay, function () {

                value += step;

                if (end === null || (end !== null && value < end)) {
                    item.innerHTML = prepend + value.format(2) + append;
                } else {
                    item.innerHTML = prepend + end.format(2) + append;
                }
            });
        }
    });

    /**
    * Number.prototype.format(n, x)
    *
    * @param integer n: length of decimal
    * @param integer x: length of sections
    */
    Number.prototype.format = function (n, x) {
        var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
        return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
    };

})();
