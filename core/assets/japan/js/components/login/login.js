import * as utility from "Base/utility";
import Console from "Base/debug/console";
import Storage from "Base/utils/storage";
import Loader from "Japan/loader";
import Modal from "Base/modal";
var FormValidator = require("BaseVendor/validate");
import ValidatorExtension from "Base/validation/validator-extension";

export default function Login() {
    "use strict";
    var isSessionAlive = app.settings.login,
        timer = null,
        keepAlive = null,
        sessionTimer = (app.settings.login_config.login_session_time) ? app.settings.login_config.login_session_time : 30,
        keepSessionTime = ((1000 * 60) * sessionTimer),
        sessionFlag = 'pas.session.flag',
        remember = 'remember.username',
        singleSessionFlag = 'single.session.flag',
        real = 1,
        language = getLanguageMap(app.settings.lang),
        $form = document.getElementById('login-form'),
        Store = new Storage(),
        Load = new Loader(document.body, true);

    function construct() {
        setiApiConfOverride();
        iapiSetCallout('Logout', onLogout);
        iapiSetCallout('KeepAlive', onKeepAlive);
        sessionChecker();
        formValidator($form);
        bindEvents();
        loginSetCallout();
        rememberUsername();
    }
    construct();

    function bindEvents() {
        utility.addEventListener($form, 'submit', function (event) {
            event.preventDefault();
        });

        if (isSessionAlive !== null && isSessionAlive) {
            utility.addEventListener(document, 'click', function (event) {
                refreshSessionUponActivity();
            });

            utility.addEventListener(document, 'scroll', function (event) {
                refreshSessionUponActivity();
            });
        }
    }


    /**
     * Callback on login process
     */
    function onLogin() {
        return function (response) {
            Console.log('Playtech PAS Provider: onLogin');
            Console.log(response);

            if (0 === response.errorCode) {
                // Change Password
                if (response.sessionValidationData) {
                    response = response.sessionValidationData;
                    if (response.SessionValidationByPasswordChangeData) {
                        Store.set("pas.change.pass.dest", document.getElementById('JapanLoginForm_destination').value);
                        Store.set("pas.change.pass", 1);
                        document.getElementById('JapanLoginForm_destination').value = window.location.href;
                    } else {
                        Store.remove("pas.change.pass");
                        Store.remove("pas.change.pass.dest");
                    }
                }

                // Flag for detecting if the player is still logged-in on PAS
                Store.set(sessionFlag, '1');
                if ($form.querySelector('#JapanLoginForm_rememberUsername').checked) {
                    Store.set(remember, this.username);
                }

                if (response.sessionValidationData !== undefined &&
                    response.sessionValidationData.SessionValidationByTCVersionData !== undefined
                ) {
                    // Change the ValidateLoginSession callback to handle the TC validation
                    iapiSetCallout('ValidateLoginSession', onTCVersionValidation(this.username));
                    // Auto validate the TC version
                    iapiValidateTCVersion(response.sessionValidationData.SessionValidationByTCVersionData[0].termVersionReference, 1, 1);
                } else {
                    $form.submit();
                }
                return;
            } else {
                Load.hide();
                var login_pt_error = app.settings.login_config.login_pt_error_messages;

                // Empty username and password after validation error
                $form.querySelector('#JapanLoginForm_username').value = "";
                $form.querySelector('#JapanLoginForm_password').value = "";
                $form.querySelector('#JapanLoginForm_rememberUsername').checked = false;

                for (var key in login_pt_error) {
                    var error = login_pt_error[key].split("|");

                    if (parseInt(error[0]) === parseInt(response.errorCode)) {
                        $form.querySelector('.login-error-message').innerHTML = error[1];
                        return;
                    }
                }

                $form.querySelector('.login-error-message').innerHTML = response.playerMessage;

            }
        };
    }

    /**
     * Handle the TCVersion response during login
     */
    function onTCVersionValidation(username) {
        return function (response) {
            Console.log('Playtech PAS Provider: onTCVersionValidation');
            Console.log(response);
            if (0 === response.errorCode) {
                $form.submit();
                return;
            }
        };
    }

    /**
     * Logs out the PAS session
     */
    function doLogout() {
        Store.remove(sessionFlag);
        iapiLogout(0, 1);
        window.location.href = utility.url('/logout');
    }

    /**
     * Logout handler
     */
    function onLogout(response) {
        Console.log('Playtech PAS Provider: onLogout');
        Console.log(response);
        clearTimeout(timer);
    }

    /**
     * Call the keep alive iapi method, keepSessionTime is configured to 15mins
     * IMS default session timeout is configured to 30mins
     */
    function doKeepAlive() {
        iapiSetCallout('GetLoggedInPlayer', function (response) {
            Console.log(response);

            if (verifyCookie(response)) {
                if (isSessionAlive) {
                    Console.log('Playtech PAS Provider: doKeepAlive');
                    iapiKeepAlive(1, keepSessionTime);
                } else {
                    iapiLogout(0, 1);
                }
            }
        });

        // Trigger the session check
        doCheckSession();
    }

    /**
     * Keeptimeout handler
     */
    function onKeepAlive(response) {
        Console.log('Playtech PAS Provider: onKeepAlive');
        Console.log(response);
        if (response.errorCode !== 0) {
            clearTimeout(timer);
            Store.set(singleSessionFlag, response.errorCode);
            doLogout();
        }
    }

    /**
     * Checks the PAS session, checks if the player is logged-in in PT or not
     */
    function doCheckSession() {
        Console.log('Playtech PAS Provider: doCheckSession');
        iapiGetLoggedInPlayer(1);
    }

    /**
     * Persist session on post-login
     */
    function sessionPersist() {

        clearTimeout(timer);

        timer = setTimeout(function () {
            timer = null;
            // if done, do logout
            Store.set('pas.session.timeout', '104');

            iapiSetCallout('GetTemporaryAuthenticationToken', function (response) {
                if (response.errorCode !== 0) {
                    doLogout();
                } else {
                    doKeepAlive();
                    sessionPersist();
                }
            });

            iapiRequestTemporaryToken(1, iapiConf.systemId);
        }, keepSessionTime);

    }

    function refreshSessionUponActivity() {
        clearTimeout(keepAlive);
        // keep alive set for 10 secs

        keepAlive = setTimeout(function () {
            doKeepAlive();
            sessionPersist();
        }, 10000);
    }

    /**
     * Check the getLoggedInPlayer response
     */
    function verifyCookie(res) {
        Console.log('Playtech PAS Provider: verifyCookie', res);
        if (res.errorCode === 0 &&
            ((typeof res.username !== 'undefined' || res.username !== '') && res.username.length > 0)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Gets the language mapping
     */
    function getLanguageMap(lang) {
        var map = app.settings.pas.language_map;

        return typeof map[lang] !== 'undefined' ? map[lang] : lang;
    }

    /**
     * Override the default iapiConf settings from Playtech
     */
    function setiApiConfOverride() {
        for (var k in iapiConf) {
            if (typeof app.settings.pas.iapiconf_override !== 'undefined' &&
                typeof app.settings.pas.iapiconf_override[k] !== 'undefined' &&
                app.settings.pas.iapiconf_override[k] !== undefined
            ) {
                iapiConf[k] = app.settings.pas.iapiconf_override[k];
            }
        }
    }

    function loginSetCallout() {
        iapiSetCallout('Login', onLogin());

        if (!isSessionAlive) {
            iapiSetCallout('GetLoggedInPlayer', function (response) {
                this.username = $form.querySelector('#JapanLoginForm_username').value;
                this.username = this.username.toUpperCase();
                this.password = $form.querySelector('#JapanLoginForm_password').value;

                if (verifyCookie(response)) {
                    iapiLogout(0, 1);
                } else {
                    Console.log('Playtech PAS Provider: Authenticate');
                    if (this.username !== "" && this.password !== "") {
                        if (checkPlatform()) {
                            iapiSetClientPlatform(checkPlatform());
                            iapiSetDeviceType('mobile');
                        }
                        iapiLogin(this.username, this.password, real, language);
                    }
                }
            });
        }
    }

    /**
     * fix for iOS 13 ipad safari specific issue
     */
    function detectUA() {

        var isTouchDevice = 'ontouchstart' in document.documentElement;
        var userAgent = window.navigator.userAgent.toLowerCase();

        return isTouchDevice && userAgent.includes('macintosh');
    }

    function checkPlatform() {
        if (detectUA()) {
            return "mobile";
        }
    }

    function formValidator(form) {
        var validator = new FormValidator('JapanLoginForm', [{
            name: 'JapanLoginForm[username]',
            display: 'Username',
            rules: 'required',
            id: 'JapanLoginForm_username',
            args: {}
        }, {
            name: 'JapanLoginForm[password]',
            display: 'Password',
            rules: 'required',
            id: 'JapanLoginForm_password',
            args: {}
        }], function (errors, evt) {

            var message = "";

            if (errors.length > 0) {
                form.querySelector('.login-error-message').innerHTML = "";
                message = "";
                for (var i in errors) {
                    if (errors[i].display === "Username") {
                        message += app.settings.login_config.login_username_required + '<br />';
                    } else if (errors[i].display === "Password") {
                        message += app.settings.login_config.login_password_required + '<br />';
                    } else {
                        message += errors[i].message + '<br />';
                    }
                }

                form.querySelector('.login-error-message').innerHTML = message;

            } else {
                Load.show();
                // clear error messages upon submit
                $form.querySelector('.login-error-message').innerHTML = "";
                doCheckSession();
            }
        });

        var validatorEvent = [];
        new ValidatorExtension(validator, validatorEvent);
    }

    function sessionChecker() {
        if (isSessionAlive !== null && isSessionAlive) {
            // Persist session
            doKeepAlive();
            sessionPersist();
        } else if (Store.get(sessionFlag) === null && isSessionAlive) {
            isSessionAlive = false;
            doLogout();
        } else {
            doKeepAlive();
        }

        if (Store.get('single.session.flag') || Store.get('pas.session.timeout')) {
            ptErrorMessage();

            if (document.querySelector('.login-lightbox-trigger') !== 'undefined') {
                document.querySelector('.login-lightbox-trigger').click();
            }

            Store.remove('single.session.flag');
            Store.remove('pas.session.timeout');
        }
    }

    function rememberUsername() {
        if (Store.get(remember) !== null || Store.get(remember) !== 'undefined') {
            if ($form) {
                $form.querySelector('#JapanLoginForm_username').value = Store.get(remember);
            }
        }
    }

    function ptErrorMessage() {
        var login_pt_error = app.settings.login_config.login_pt_error_messages;
        var general_pt_error = app.settings.login_config.general_pt_error_messages;

        if (Store.get('single.session.flag') || Store.get('pas.session.timeout')) {
            for (var key in general_pt_error) {
                var error = general_pt_error[key].split("|");
                if (parseInt(error[0]) === parseInt(Store.get(singleSessionFlag)) || parseInt(error[0]) === parseInt(Store.get('pas.session.timeout'))) {
                    $form.querySelector('.login-error-message').innerHTML = error[1];
                }
            }
        } else {
            for (var keys in login_pt_error) {
                var errors = login_pt_error[keys].split("|");
                if (parseInt(errors[0]) === parseInt(Store.get(singleSessionFlag))) {
                    $form.querySelector('.login-error-message').innerHTML = error[1];
                }
            }
        }
    }
}



var Store = new Storage();

utility.ready(function () {
    if (!app.settings.login) {
        Store.remove('pas.session.flag');
    }

    if (Store.get('pas.session.flag') === null && !app.settings.login) {
        new Modal({
            selector: ".login-lightbox-trigger", // selector to trigger modal
            closeOverlayClick: true, // true/false - close modal on click on overlay
            closeTriggerClass: ".modal-close", // class to trigger to close the modal
            escapeClose: true, // close modal on escape key
            id: null, // modal id,
            onClose: null,
            maxHeight: 500
        });
    }

    Login();
});
