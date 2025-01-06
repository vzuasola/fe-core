import * as utility from "Base/utility";
import Modal from "Base/modal";
import detectIE from "Base/browser-detect";
import Storage from "Base/utils/storage";
import CheckboxStyler from "Base/checkbox-styler";
import Loader from "Base/loader";
import bLazy from "Base/lazy-load-responsive";

export default function LoginForm(options) {
    "use strict";

    var loginFormContainerId = 'loginForm',
        loginFormId = 'login-form',
        loginLightBoxId = 'loginFormLightBox',
        loginViaKey = 'loginVia',
        loginLightBoxTrigger = 'data-login',
        rememberUsername = 'rememberUsername',
        usernameInStorage = 'usernameInStorage',
        $modalObj = new Modal({
            id: loginLightBoxId
        }),
        $this = this,
        $storage = new Storage(),
        isSessionAlive = app.settings.login,
        triggerElement,
        rememberField,
        rememberCheckbox,
        usernameField,
        loader;

    var defaults = {
        onClose: null,
        onSubmit: null
    };

    /**
     * Initialize login form
     */
    function construct() {
        resetStorage();

        if (!isSessionAlive) {
            extendOptions();
            checkSubmission();
            bindEvents();
        } else {
            disableTriggers();
        }


        // Remember Username field
        rememberField = document.querySelector(".remember-username-field");

        if (rememberField) {
            // Login loader
            loader = new Loader(document.body, true, 1);

            rememberCheckbox = new CheckboxStyler(rememberField);
            usernameField = document.getElementById('LoginForm_username');
        }
    }

    construct();

    /**
     * Extend options
     */
    function extendOptions() {
        options = options || {};
        for (var name in defaults) {
            if (options[name] === undefined) {
                options[name] = defaults[name];
            }
        }
    }

    /**
     * Reset session
     */
    function resetStorage() {
        // remove login via to prevent lightbox from showing
        // up again upon login
        if (isSessionAlive) {
            $storage.remove(loginViaKey);
        }
    }

    /**
     * Check form submission
     */
    function checkSubmission() {
        var form = $storage.get(loginViaKey);
        if (form && form === 'lightbox') {
            setTimeout(openLightBox, 50);
        }
    }

    /**
     * Show the form depends on the login via
     */
    function showForm(isLightBox) {
        var $form = document.getElementById(loginFormId),
            $modal = document.getElementById(loginLightBoxId);

        if ($form) {
            var lightboxBlurb = $form.querySelector('.login-form-lightbox-blurb'),
                hr = $form.querySelector('hr'),
                buttons = $form.querySelectorAll('.btn'),
                inlineFormSubmitted = $storage.get(loginViaKey) === 'form',
                rememberUsername = $form.querySelector('.remember-username');

            resetFields();

            if (isLightBox) {
                if (inlineFormSubmitted) {
                    resetErrors();
                }
                utility.removeClass(lightboxBlurb, 'hidden');
                utility.removeClass(hr, 'hidden');
                utility.removeClass(lightboxBlurb, 'hidden');
                utility.forEach(buttons, function (el, i) {
                    utility.removeClass(el, 'btn-small');
                    utility.addClass(el, 'btn-block');
                });
                utility.removeClass($form, 'login-form');
                utility.addClass($form, 'login-form-lightbox');
                $modal.querySelector('.modal-body').appendChild($form);
                utility.addClass($modal, 'modal-active');
                // Show remember name element
                utility.removeClass(rememberUsername, "hidden");
            } else {
                utility.addClass(lightboxBlurb, 'hidden');
                utility.addClass(hr, 'hidden');
                utility.forEach(buttons, function (el, i) {
                    utility.addClass(el, 'btn-small');
                    utility.removeClass(el, 'btn-block');
                });
                utility.removeClass($form, 'login-form-lightbox');
                utility.addClass($form, 'login-form');
                var container = document.getElementById(loginFormContainerId);
                if (container) {
                    container.appendChild($form);
                }
                utility.removeClass($modal, 'modal-active');
                // Hide remember name element
                utility.addClass(rememberUsername, "hidden");
            }

            $storage.remove(loginViaKey);
        }
    }

    /**
     * Bind events
     */
    function bindEvents() {
        var $form = document.getElementById(loginFormId);

        utility.addEventListener($form, 'submit', function (e) {
            // save the submitted form on local storage
            if (utility.hasClass($form, 'login-form-lightbox')) {
                $storage.set(loginViaKey, 'lightbox');

                // Show loader on submit
                if (rememberField) {
                    loader.show();
                }
            } else {
                $storage.set(loginViaKey, 'form');
            }

            if (options.onSubmit && typeof options.onSubmit === 'function') {
                options.onSubmit();
            }

            setRememberToStorage();

            if (rememberField) {
                // Set username value for username storage
                if ($storage.get(rememberUsername) === "true") {
                    $storage.set(usernameInStorage, usernameField.value);
                } else {
                    $storage.remove(usernameInStorage);
                }
            }
        });


        utility.addEventListener(document.body, 'click', function (event) {
            // Cross browser event
            event = event || window.event;
            // get srcElement if target is falsy (IE8)
            var target = event.target || event.srcElement;

            if (target.getAttribute(loginLightBoxTrigger) === 'lightbox' || target.parentNode.getAttribute(loginLightBoxTrigger) === 'lightbox') {
                utility.preventDefault(event);
                triggerElement = target;
                openLightBox();
            }
        });

        utility.addEventListener(document.body, 'click', function (event) {
            var $modal = document.getElementById(loginLightBoxId),
                overlay = $modal.querySelector('.modal-overlay'),
                closeBtn = $modal.querySelector('.modal-close');

            event = event || window.event;
            var target = event.target || event.srcElement;

            if (closeBtn === target || overlay === target) {
                event.preventDefault();
                closeLightBox();
            }
        });

        utility.addEventListener(document.body, 'keydown', function (event) {
            event = event || window.event;

            if (event.keyCode === 27) {
                closeLightBox();
            }
        });
    }

    /**
     * Reset the fields
     */
    function resetFields() {
        var $form = document.getElementById(loginFormId);

        if ($form) {
            var username = $form.querySelector('input[type=text]'),
                password = $form.querySelector('input[type=password]'),
                usernameLabel = document.querySelector('.ie8_username_placeholder'),
                passwordLabel = document.querySelector('.ie8_password_placeholder');

            // if not ie8 or ie9 just remove the value
            if (username) {
                username.value = '';
            }

            if (password) {
                password.value = '';
            }

            if (detectIE() === 8 || detectIE() === 9) {
                if ((username.value === '' || username.value === null) && (utility.hasClass(usernameLabel, 'hidden'))) {
                    utility.removeClass(usernameLabel, 'hidden');
                }

                if ((password.value === '' || password.value === null) && (utility.hasClass(passwordLabel, 'hidden'))) {
                    utility.removeClass(passwordLabel, 'hidden');
                }
            }
        }
    }

    /**
     * Disable triggers
     */
    function disableTriggers() {
        utility.addEventListener(document.body, 'click', function (event) {
            // Cross browser event
            event = event || window.event;
            // get srcElement if target is falsy (IE8)
            var target = event.target || event.srcElement;

            if (target.getAttribute(loginLightBoxTrigger) === 'lightbox') {
                utility.preventDefault(event);
            }
        });
    }

    /**
     * Close lightbox
     */
    function closeLightBox() {
        var $modal = document.getElementById(loginLightBoxId),
            $form = document.getElementById(loginFormId);

        resetErrors();
        if ($modal) {
            showForm(false);

            setRememberToStorage();
        }

        if (options.onClose && typeof options.onClose === 'function') {
            options.onClose($form, triggerElement);
        }
    }

    /**
     * Remove validation errors
     */
    function resetErrors() {
        var $form = document.getElementById(loginFormId),
            error = $form ? $form.querySelector('.login-error') : null,
            errorCapslock = $form ? $form.querySelector('.capslock-notification') : null;

        if (error) {
            error.remove();
        }

        if (errorCapslock) {
            errorCapslock.remove();
        }
    }

    /**
     * Open lightbox
     */
    function openLightBox() {
        var $modal = document.getElementById(loginLightBoxId);
        if ($modal) {
            showForm(true);

            if (detectIE() === 8) {
                $modalObj.centerModalContent();
            }

            // Revalidate lazy-load
            bLazy.revalidate();

            if (rememberField) {
                if ($storage.get(rememberUsername) === "true") {
                    rememberField.checked = true;
                } else {
                    rememberField.checked = false;
                }

                rememberCheckbox.checker(rememberField);
            }

            setUsernameFromStorage();
        }

    }

    function setRememberToStorage() {
        if (rememberField) {
            (rememberField.checked === true) ? $storage.set(rememberUsername, "true") : $storage.set(rememberUsername, "false");
        }
    }

    function setUsernameFromStorage() {
        if (rememberField && $storage.get(rememberUsername) === "true" && $storage.get(usernameInStorage)) {
            // Get username value from storage
            usernameField.value = $storage.get(usernameInStorage);
        }
    }

    /**
     * Public method to open lightbox
     */
    $this.openLightBox = function () {
        openLightBox();
    };

    return this;
}
