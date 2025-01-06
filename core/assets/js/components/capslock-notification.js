import * as utility from "Base/utility";

/**
 * Caps lock notification
 *
 * @param Node input password element
 */
export default function capsLockNotification(input) {
    if (!app.settings.login) {
        var fieldWrapper = utility.findParent(input, ".loginform-textfield-wrapper");

        bindEvents();

        // Disable default caps lock tooltip for IE's
        document.msCapsLockWarningOff = true;
    }

    function bindEvents() {
        // Check first if caps is enabled.
        if (input && utility.hasClass(input, "caps-lock-enabled")) {
            utility.addEventListener(input, "keypress", function (e) {
                e = e || window.event;
                var keyCode = e.keyCode ? e.keyCode : e.which;

                if (isCapsLockOn(e)) {
                    showNotification();
                } else {
                    hideNotificationKeyCode(keyCode);
                }
            });

            /**
             * Hide notification upon pressing "Caps lock" key when "Caps lock" is currently turned on
             */
            utility.addEventListener(document, "keydown", function (e) {
                e = e || window.event;
                var keyCode = e.keyCode ? e.keyCode : e.which;

                if (keyCode === 20 && document.querySelector(".capslock-notification")) {
                    hideNotification();
                }
            });
        }
    }

    /**
     * Caps lock checker
     */
    function isCapsLockOn(e) {
        var keyCode = e.keyCode ? e.keyCode : e.which;
        var shiftKey = e.shiftKey ? e.shiftKey : ((keyCode === 16) ? true : false);
        return (((keyCode >= 65 && keyCode <= 90) && !shiftKey) || ((keyCode >= 97 && keyCode <= 122) && shiftKey));
    }

    /**
     * Show notification
     */
    function showNotification() {
        var messageContainer = fieldWrapper.querySelector(".capslock-notification"),
            loginErrorElement = fieldWrapper.querySelector(".login-error");

        // Remove login validation error first if it's there
        if (loginErrorElement) {
            loginErrorElement.remove();
        }

        if (!messageContainer) {
            messageContainer = document.createElement("div");

            utility.addClass(messageContainer, "capslock-notification");

            messageContainer.innerHTML = app.settings.capsLockNotification;

            fieldWrapper.appendChild(messageContainer);
        }
    }

    /**
     * Hide notification
     */
    function hideNotification() {
        var messageContainer = fieldWrapper.querySelector(".capslock-notification");

        if (messageContainer) {
            messageContainer.remove();
        }
    }

    /**
     * Hide notification with keycode handles mozilla issue
     * which triggers the backspace as key press event
     */
    function hideNotificationKeyCode(keyCode) {
        var messageContainer = fieldWrapper.querySelector(".capslock-notification");

        // keycode 8 is for backspace
        // keycode 46 is for delete
        if (messageContainer && keyCode !== 8 && keyCode !== 46) {
            messageContainer.remove();
        }
    }
}
