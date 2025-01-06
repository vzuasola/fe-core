/**
 * A flexible counter class to count anything you wish for
 *
 * @param int seconds The seconds to count
 * @param array options Available options
 *
 * Available options
 *        int refresh The refresh rate of the counter
 *        object storage A custom storage object where count data will be stored
 *
 *        closure onCount(this, int elapsed) A closure to invoke when a the counter is counting
 *        closure onReset A closure to invoke when a the counter is reset
 *        closure onBeforeStop(this) A closure to invoke when the counter is about to stop
 *        closure onStop(this) A closure to invoke when the counter has stop
 */
export default function Counter(seconds, options) {
    "use strict";

    var $this = this,
        $storage, // the storage class
        $counter; // the interval object

    /**
     *
     */
    function construct() {
        // Default options
        var defaults = {
            refresh: 1000, // the refresh rate in milliseconds
            storage: new VariableStorage(),
            onCount: false,
            onReset: false,
            onRestart: false,
            onBeforeStop: false,
            onStop: false,
        };

        // extend options
        options = options || {};

        for (var name in defaults) {
            if (options[name] === undefined) {
                options[name] = defaults[name];
            }
        }

        $storage = options.storage;
        $storage.set('elapsed', 0);
    }

    construct();

    /**
     *
     */
    this.start = function () {
        $counter = setInterval(count, options.refresh);
    };

    /**
     *
     */
    this.restart = function () {
        if (typeof options.onRestart === 'function') {
            options.onRestart($this, $storage.get('elapsed'));
        }

        $this.kill();
        $this.start();
    };

    /**
     * Interval function
     */
    function count() {
        var elapsed = $storage.get('elapsed');
        elapsed += 1;

        $storage.set('elapsed', elapsed);

        switch (true) {
            case elapsed >= seconds:
                if (typeof options.onBeforeStop === 'function') {
                    options.onBeforeStop($this);
                }

                $this.stop();
                break;
        }

        if (typeof options.onCount === 'function') {
            options.onCount($this, elapsed);
        }
    }

    /**
     *
     */
    this.reset = function () {
        if (typeof options.onReset === 'function') {
            options.onReset($this, $storage.get('elapsed'));
        }

        $storage.set('elapsed', 0);
    };

    /**
     *
     */
    this.kill = function () {
        clearInterval($counter);
        $storage.set('elapsed', 0);
    };

    /**
     *
     */
    this.stop = function () {
        clearInterval($counter);

        if (typeof options.onStop === 'function') {
            options.onStop($this);
        }
    };
}

/**
 * Variable storage, the default storage adapter
 */
function VariableStorage() {
    "use strict";

    var $store = {};

    this.get = function (index) {
        if (typeof $store[index] !== 'undefined') {
            return $store[index];
        }
    };

    this.set = function (index, value) {
        return $store[index] = value;
    };

    this.remove = function (index) {
        if (typeof $store[index] !== 'undefined') {
            delete $store[index];
        }
    };
}
