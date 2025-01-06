/**
 * Login form validation
 *
 * TODO
 * - Migrate validation to validate.js
 * - Make class OOP
 */

import detectIE from "Base/browser-detect";
import * as utility from "Base/utility";

var loginButton = document.getElementById('LoginForm_submit'),
    usernameField = document.getElementById('LoginForm_username'),
    passwordField = document.getElementById('LoginForm_password'),
    usernameLabel = document.querySelector('.ie8_username_placeholder'),
    passwordLabel = document.querySelector('.ie8_password_placeholder'),
    doc = document.body,
    ClientError = app.settings.loginFormConfig;

// IE8 Placeholder fix for 'Click' and 'Keyup' Events
// Below three functions needed to work in case of ie8
function UserLabelTrigger() {
    if ((usernameField.value !== '' || usernameField.value !== null) &&
        (passwordField.value === '' || passwordField.value === null)
    ) {
        utility.addClass(usernameLabel, 'hidden');

        setTimeout(function () {
            usernameField.focus();
        }, 10);

        utility.removeClass(passwordLabel, 'hidden');
    } else {
        utility.addClass(usernameLabel, 'hidden');

        setTimeout(function () {
            usernameField.focus();
        }, 10);
    }
}

function PassLabelTrigger() {
    if ((passwordField.value !== '' || passwordField.value !== null) &&
        (usernameField.value === '' || usernameField.value === null)
    ) {
        utility.addClass(passwordLabel, 'hidden');

        setTimeout(function () {
            passwordField.focus();
        }, 10);

        utility.removeClass(usernameLabel, 'hidden');
    } else {
        utility.addClass(passwordLabel, 'hidden');

        setTimeout(function () {
            passwordField.focus();
        }, 10);
    }
}

function OuterCLickTrigger() {
    if (usernameField.value === '' || usernameField.value === null) {
        utility.removeClass(usernameLabel, 'hidden');
    }

    if (passwordField.value === '' || passwordField.value === null) {
        utility.removeClass(passwordLabel, 'hidden');
    }

    if ((usernameField.value === '' || usernameField.value === null) &&
        (passwordField.value === '' || passwordField.value === null)
    ) {
        utility.removeClass(usernameLabel, 'hidden');
        utility.removeClass(passwordLabel, 'hidden');
    }
}

// Check if Field exist
if ((usernameField !== null) && (passwordField !== null)) {
    // Check if IE8 and IE9
    if (detectIE() === 8 || detectIE() === 9) {
        if ((usernameField.value === '' || usernameField.value === null) && (utility.hasClass(usernameLabel, 'hidden'))) {
            utility.removeClass(usernameLabel, 'hidden');
        }

        if ((passwordField.value === '' || passwordField.value === null) && (utility.hasClass(passwordLabel, 'hidden'))) {
            utility.removeClass(passwordLabel, 'hidden');
        }

        // Check both on keyup and click events
        utility.addEventListener(doc, 'keyup', function (e) {
            e = e || window.event;
            var target = e.target || e.srcElement;
            var code = (e.keyCode ? e.keyCode : e.which);

            if (code === 9) {
                if (target.id === 'LoginForm_username') {
                    UserLabelTrigger();
                } else if (target.id === 'LoginForm_password') {
                    PassLabelTrigger();
                } else {
                    OuterCLickTrigger();
                }
            }
        });

        utility.addEventListener(doc, 'click', function (e) {
            // Cross browser event
            e = e || window.event;
            // get srcElement if target is falsy (IE8)
            var target = e.target || e.srcElement;
            if (usernameLabel === target) {
                UserLabelTrigger();
            } else if (passwordLabel === target) {
                PassLabelTrigger();
            } else {
                OuterCLickTrigger();
            }
        });
    }

    // Username
    utility.addEventListener(usernameField, "focus", function () {
        usernameField.removeAttribute('placeholder');
    });
    utility.addEventListener(usernameField, "blur", function () {
        usernameField.setAttribute('placeholder', ClientError.username_placeholder);
    });

    // Password
    utility.addEventListener(passwordField, "focus", function () {
        passwordField.removeAttribute('placeholder');
    });
    utility.addEventListener(passwordField, "blur", function () {
        passwordField.setAttribute('placeholder', ClientError.password_placeholder);
    });

}

function addErrorMessage(ErrorMessage) {
    var loginErrorDivElement = document.querySelector('.login-error'),
        ErrorMessageElement = document.createTextNode(ErrorMessage);

    if (loginErrorDivElement === null) {
        var targetElement = document.querySelector('.loginform-textfield-wrapper'),
            loginErrorElement = document.createElement('div'),
            capsLockNotification = targetElement.querySelector(".capslock-notification");

        utility.addClass(loginErrorElement, "login-error");

        loginErrorElement.appendChild(ErrorMessageElement);

        // Remove capslock notification first if it's there
        if (capsLockNotification) {
            capsLockNotification.remove();
        }

        targetElement.appendChild(loginErrorElement);
    } else {
        loginErrorDivElement.innerHTML = "";
        loginErrorDivElement.appendChild(ErrorMessageElement);
    }
}

function loginForm() {
    var isLogin = app.settings.login;

    if (!isLogin && loginButton) {
        loginButton.onclick = function () {
            var usernameValue = utility.trim(usernameField.value);

            if ((usernameValue === '' || usernameValue === null) && (passwordField.value === '' || passwordField.value === null)) {
                addErrorMessage(ClientError.error_message_blank_passname);
                return false;
            } else if (usernameValue === '' || usernameValue === null) {
                addErrorMessage(ClientError.error_message_blank_username);
                return false;
            } else if (passwordField.value === '' || passwordField.value === null) {
                addErrorMessage(ClientError.error_message_blank_password);
                return false;
            } else {
                return true;
            }
        };
    }
}

utility.ready(loginForm);
