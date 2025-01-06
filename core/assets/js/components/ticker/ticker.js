/**
 * Ticker constructor function
 *
 * @param object options
 */
export default function Ticker(interval, delay, callback) {
    "use strict";

    this.timer = null;
    this.interval = interval;
    this.callback = callback;

    this.start(delay);
}

Ticker.prototype.start = function (delay) {
    var $this = this;

    if (this.timer) {
        return;
    }

    this.timer = setTimeout(function () {
        $this.tick();
    }, delay || 0);
};

Ticker.prototype.tick = function (called) {
    var $this = this,
        nextTick = called ? $this.interval - (new Date - called) : 0;

    this.timer = setTimeout(function () {
        $this.callback();
        $this.tick(new Date);
    }, nextTick < 0 ? 0 : nextTick);
};

Ticker.prototype.stop = function () {
    clearTimeout(this.timer);
    this.timer = null;
};
