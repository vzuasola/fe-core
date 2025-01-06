import * as utility from "Base/utility";
import Console from "Base/debug/console";
import Storage from "Base/utils/storage";
import Loader from "Japan/loader";
import Modal from "Base/modal";
var FormValidator = require("BaseVendor/validate");
import ValidatorExtension from "Base/validation/validator-extension";

export default function ChangePassword() {
    "use strict";
    var $cpform = document.getElementById('cp-form'),
        Store = new Storage(),
        Load = new Loader(document.body, true);

    function construct() {
        formValidatorChangePassword($cpform);
        setiApiConfOverride();
        bindEvents();
    }
    construct();

    function bindEvents() {
        if (Store.get("pas.change.pass")) {
            utility.addEventListener($cpform, 'submit', function (event) {
                event.preventDefault();
            });
        }
    }

    function onChangePassword() {
        return function (response) {
            Console.log('Playtech PAS Provider: onChangePassword');
            Console.log(response);
            if (0 === response.errorCode) {
                Store.remove("pas.change.pass");
                Store.remove("pas.change.pass.dest");
                $cpform.submit();
                return;
            } else {
                Load.hide();
                var login_pt_error = app.settings.login_config.login_pt_error_messages;

                // Empty username and password after validation error
                $cpform.querySelector('#JapanChangePassForm_old_password').value = "";
                $cpform.querySelector('#JapanChangePassForm_password').value = "";
                $cpform.querySelector('#JapanChangePassForm_confirm_password').value = "";

                for (var key in login_pt_error) {
                    var error = login_pt_error[key].split("|");

                    if (parseInt(error[0]) === parseInt(response.errorCode)) {
                        $cpform.querySelector('.change-pass-error-message').innerHTML = error[1];
                        return;
                    }
                }

                $cpform.querySelector('.change-pass-error-message').innerHTML = response.playerMessage;

            }
        };
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

    function formValidatorChangePassword(form) {
        var validator = new FormValidator('JapanChangePassForm', [{
            name: 'JapanChangePassForm[old_password]',
            display: 'Old Password',
            rules: 'required',
            id: 'JapanChangePassForm_old_password',
            args: {}
        }, {
            name: 'JapanChangePassForm[password]',
            display: 'New Password',
            rules: 'required',
            id: 'JapanChangePassForm_password',
            args: {}
        }, {
            name: 'JapanChangePassForm[confirm_password]',
            display: 'Confirm New Password',
            rules: 'required|matches',
            id: 'JapanChangePassForm_confirm_password',
            args: {
                matches: ['JapanChangePassForm_password']
            }
        }], function (errors, evt) {

            var message = "";

            if (errors.length > 0) {
                $cpform.querySelector('.change-pass-error-message').innerHTML = "";
                message = "";

                for (var i in errors) {
                    if (errors[i].display === "Old Password") {
                        message += app.settings.login_config.change_pass_old_password_error + '<br />';
                    } else if (errors[i].display === "New Password") {
                        message += app.settings.login_config.change_pass_new_password_error + '<br />';
                    } else if (errors[i].display === "Confirm New Password") {
                        message += app.settings.login_config.change_pass_confirm_new_password_error + '<br />';
                    } else {
                        message += errors[i].message + '<br />';
                    }
                }

                $cpform.querySelector('.change-pass-error-message').innerHTML = message;

            } else {
                Load.show();
                // clear error messages upon submit
                $cpform.querySelector('.change-pass-error-message').innerHTML = "";
                changePassword();
            }
        });

        var validatorEvent = [];
        new ValidatorExtension(validator, validatorEvent);
    }

    function changePassword() {
        // Change the ValidateLoginSession callback to handle the change password validation
        iapiSetCallout('ValidateLoginSession', onChangePassword());

        var oldPassword = $cpform.querySelector('#JapanChangePassForm_old_password').value;
        var newPassword = $cpform.querySelector('#JapanChangePassForm_password').value;
        // Change Password iapi Call
        iapiValidatePasswordChange(oldPassword, newPassword, 1, 1);
    }
}

var Store = new Storage();

utility.ready(function () {

    if (Store.get("pas.change.pass") && app.settings.login) {
        new Modal({
            selector: ".change-pass-trigger", // selector to trigger modal
            closeOverlayClick: false, // true/false - close modal on click on overlay
            escapeClose: false, // close modal on escape key
            id: "change-pass", // modal id,
            onClose: null,
            maxHeight: 500
        });

        document.getElementById('JapanChangePassForm_destination').value = Store.get("pas.change.pass.dest");
        document.querySelector('.change-pass-trigger').click();
    } else {
        Store.remove("pas.change.pass");
    }

    ChangePassword();
});
