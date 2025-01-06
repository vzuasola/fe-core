import * as utility from "Base/utility";

export default function marketingScriptEventHandler() {
    var MScripts = {};

    /**
     * Add custom method on global window object to support all marketing script loading
     *
     * @param {string} key
     *  Typical key used for determining the script to be applied
     * @param {function} callback
     *  Function to be triggered as callback
     */
    window.applyMarketingScript = function (key, callback) {
        if ('function' === typeof callback) {
            MScripts[key] = callback;
        } else {
            console.log('[MKTGScripts][' + key + '] is not a function/method. This will be ignored');
        }
    };

    /**
     * All Marketing script will be forced to execute during window.load only
     */
    utility.addEventListener(window, 'load', function () {
        for (var i in MScripts) {
            if ('function' === typeof MScripts[i]) {
                // Expose drew functions
                MScripts[i].apply(null, [utility]);
                console.log('[MKTGScripts][' + i + '] has been invoked successfully');
            }
        }
    });
}
