import * as utility from "Base/utility";

function pushnxAlert(option) {
    "use strict";

    var pushnx_debug = option.debug;
    var pushnx_global = option.global;

    var newMsg = 0;

    this.newMessageAlert = function (count) {
        if (pushnx_global.notify) {
            var $pnx = document.getElementById("pushnxLightbox").className;

            if ($pnx.match('modal-active')) {
                newMsg = 0;
            }

            if (newMsg > 0) {
                newMsg = newMsg + count;
            } else {
                newMsg = count;
            }

            utility.triggerEvent(document, 'pushnx.new.message', {
                count: newMsg
            });

            pushnx_debug.notify('triggered pushnx.new.message: ' + newMsg);
        }
    };

    this.updateMessageAlert = function (count) {
        if (pushnx_global.counter) {
            utility.triggerEvent(document, 'pushnx.count.message', {
                count: count
            });

            pushnx_debug.notify('triggered pushnx.count.message: ' + count);
        }
    };
}

export default pushnxAlert;
