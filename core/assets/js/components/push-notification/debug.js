import * as utility from "Base/utility";

function pushnxDebug(enabled, notify) {
    "use strict";

    this.console = function (obj, title, message, type) {
        if (!enabled) {
            return;
        }

        var dateNow = new Date().toLocaleTimeString();

        type = type || 'log';
        message = message || false;
        title = title || 'DEBUG';

        console.groupCollapsed('[' + dateNow + '] ' + title);

        if (message) {
            console.log(message);
        }

        if (obj) {
            switch (type) {
                case 'log':
                    console.log(obj);
                    break;
                case 'warn':
                    console.warn(obj);
                    break;
                case 'error':
                    console.error(obj);
                    break;
                case 'info':
                    console.info(obj);
                    break;
                default:
                    console.log(obj);
            }
        }

        console.groupEnd();
    };

    this.notify = function (n) {
        var $container = document.getElementById('debug-notif') || false;

        if (!enabled || !notify) {
            if ($container) {
                $container.remove();
            }

            return;
        }

        if (!$container) {
            var div = document.createElement("div");

            div.id = 'debug-notif';
            div.style.position = 'absolute';
            div.style.zIndex = '99999';
            div.style.margin = '0 auto';
            div.style.width = "100%";
            div.style.background = "brown";
            div.style.color = "white";
            div.style.textAlign = 'center';
            div.style.padding = '5px';
            div.style.opacity = '0.7';

            div.innerHTML = n;

            var s = document.body.firstChild;
            s.parentNode.insertBefore(div, s);
        } else {
            $container.innerHTML += '<br/>' + n;
        }

        if ($container) {
            utility.addEventListener($container, "click", function () {
                this.remove();
            });
        }
    };
}

export default pushnxDebug;
