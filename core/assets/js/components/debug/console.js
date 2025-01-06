/**
 *
 */
export default (function () {
    var Console = {},
        isDebug = app.settings.debug;

    /**
     * Log to console
     */
    Console.log = function (object) {
        if (isDebug) {
            try {
                console.log.apply(console, arguments);
            } catch (e) {
                console.log(object);
            }
        }
    };

    return Console;
})();
