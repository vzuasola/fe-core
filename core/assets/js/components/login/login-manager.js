import * as utility from "Base/utility";
import LoginSubmission from "Base/login/login-submission";
import "Base/login/validation";

(function () {
    var $form = document.getElementById('login-form'),
        $login = new LoginSubmission($form);

    /**
     *
     */
    function constructor() {
        // disable form on processing login hooks
        utility.addEventListener($form, LoginSubmission.prototype.events.preSubmit, onPreSubmit);

        // enable form after processing login hooks
        utility.addEventListener($form, LoginSubmission.prototype.events.postSubmit, onPostSubmit);

        utility.ready(function () {
            $login.init();
        });
    }

    constructor();

    /**
     *
     */
    function onPreSubmit(e) {
        isFormDisabled(true);
    }

    /**
     *
     */
    function onPostSubmit(e) {
        isFormDisabled(false);
    }

    /**
     * Toggles form disable state
     *
     * @param boolean state
     */
    function isFormDisabled(state) {
        var inputs = $form.getElementsByTagName('input');

        for (var i = 0; i < inputs.length; i++) {
            inputs[i].disabled = state;
        }

        var selects = $form.getElementsByTagName('select');

        for (i = 0; i < selects.length; i++) {
            selects[i].disabled = state;
        }

        var textareas = $form.getElementsByTagName('textarea');

        for (i = 0; i < textareas.length; i++) {
            textareas[i].disabled = state;
        }

        var buttons = $form.getElementsByTagName('button');

        for (i = 0; i < buttons.length; i++) {
            buttons[i].disabled = state;
        }
    }
})();
