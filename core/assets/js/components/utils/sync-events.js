/**
 * Class for executing synchronous events
 */
function SyncEvents(options) {
    this.options = options;
}

SyncEvents.prototype = {
    /**
     * Trigger an array of encapsulated promises
     *
     * @param array events An array of encapsulated promises
     * @param array data Extra data to pass to the encapuslated promises
     */
    execute: function (events, data) {
        events = events || [];

        var loopback = function (index) {
            if (index < events.length) {
                var cast = events[index](data);

                cast = Promise.resolve(cast);

                cast.then(function () {
                    loopback(index + 1);
                });

                cast['catch'](function () {
                    loopback(index + 1);
                });
            }
        };

        loopback(0);
    },

    /**
     *
     */
    executeWithArgs: function (events, args) {
        events = events || [];

        var loopback = function (index) {
            if (index < events.length) {
                var cast = events[index].apply(null, args);

                cast = Promise.resolve(cast);

                cast.then(function () {
                    loopback(index + 1);
                });

                cast['catch'](function () {
                    loopback(index + 1);
                });
            }
        };

        loopback(0);
    },

    /**
     *
     */
    executeWithArgsWithException: function (events, args) {
        events = events || [];

        var loopback = function (index) {
            if (index < events.length) {
                var cast = events[index].apply(null, args);
                cast = Promise.resolve(cast);

                cast.then(function () {
                    loopback(index + 1);
                }).catch(function () {
                    // Do nothing
                });
            }
        };

        loopback(0);
    }
};

export default SyncEvents;
