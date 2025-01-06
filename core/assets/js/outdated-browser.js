/* eslint-disable */

/**
 * Browser's feature detection to show message for outdated browser
 */

 function detectIE() {
    var ua = window.navigator.userAgent;

    var msie = ua.indexOf('MSIE ');
    if (msie > 0) {
        // IE 10 or older => return version number
        return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
    }

    var trident = ua.indexOf('Trident/');
    if (trident > 0) {
        // IE 11 => return version number
        var rv = ua.indexOf('rv:');
        return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
    }

    var edge = ua.indexOf('Edge/');
    if (edge > 0) {
        // Edge (IE 12+) => return version number
        return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
    }

    // other browser
    return false;
}

document.onreadystatechange = function () {
    if (document.readyState === 'complete') {

        var testJSON = 'JSON' in window && 'parse' in JSON && 'stringify' in JSON,
            testQuerySelector = 'querySelector' in document && 'querySelectorAll' in document,
            testStorage = typeof Storage !== "undefined";

        if (!(testJSON && testQuerySelector && testStorage) || (detectIE()) && detectIE() < 11) {
            var notification,
                notificationContent,
                bodyElem = document.getElementsByTagName('body')[0];

            generateMarkup();

            // Add class to HTML tag if not supported
            addClass(bodyElem, "outdated-browser");

            createOverlay();
            centerModalVertically(notificationContent);

            if (!checkCookie('notificationCheckbox')) {
                showModal();
            } else {
                closeModal();
            }

            bindEvent();
        }

        // Event
        function bindEvent() {
            var closeBtn = document.getElementById('notification-close-btn');

            if (closeBtn) {
                closeBtn.onclick = function () {
                    closeModal();
                };
            }
        }

        function generateMarkup() {
            notification = document.createElement('div');
            notificationContent = document.createElement('div');

            var message = app.settings.outdated_browser.message.value;

            // Add class and ids
            addClass(notification, 'hidden');
            notification.id = "outdated-browser-notification";
            notificationContent.id = "notification-content";

            // Append
            notification.appendChild(notificationContent);
            bodyElem.appendChild(notification);

            if (message) {
                notificationContent.innerHTML = message;
            }
        }

        /**
         * Open Modal
         */
        function showModal() {
            notification.style.display = "block";
            notification.style.visibility = "visible";
        }

        /**
         * Close modal
         */
        function closeModal() {
            var checkbox = document.getElementById('notification-checkbox')
            if (checkbox && checkbox.checked) {
                setCookie('notificationCheckbox', 'checked', '1095');
            }
            notification.style.display = "none";
            notification.style.visibility = "hidden";
        }

        /**
         * Create Overlay
         */
        function createOverlay() {
            if (!document.getElementById('notification-overlay')) {
                var overlay = document.createElement("div");
                overlay.id = "notification-overlay";
                notification.insertBefore(overlay, notification.firstChild);
            }
        }

        /**
         * Function to vertically center modal
         */
        function centerModalVertically(elem) {
            // remove classname first to get proper height
            removeClass(elem.parentNode, "hidden");
            elem.style.marginTop = -(elem.clientHeight / 2) + "px";
            addClass(elem.parentNode, "hidden");
        }

        /**
         * Set Cookie
         */
        function setCookie(cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            var expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }

        /**
         * get Cookie
         */
        function getCookie(cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) === 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }

        /**
         * check cookie
         */
        function checkCookie(cname) {
            var name = getCookie(cname);
            if (name !== "") {
                return true;
            } else {
                return false;
            }
        }

        /**
         * has class
         */
        function hasClass(el, className) {
            if (el.classList) {
                return el.classList.contains(className);
            } else {
                return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
            }
        }

        /**
         * Add class function
         */
        function addClass(el, className) {
            if (el.classList) {
                el.classList.add(className);
            } else if (!hasClass(el, className)) {
                el.className += " " + className;
            }
        }

        /**
         * Remove class
         */
        function removeClass(el, className) {
            if (el.classList) {
                el.classList.remove(className);
            } else if (hasClass(el, className)) {
                var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
                el.className = el.className.replace(reg, ' ');
            }
        }
    }
};
