import * as utility from "Base/utility";
import "Base/login/validation";
import SyncEvents from "Base/utils/sync-events";

/**
 * Generic base class
 */
function LoginSubmissionBase(options) {
    this.options = options;
}

LoginSubmissionBase.prototype = {
    /**
     * Available events
     */
    events: {
        'preSubmit': 'login_pre_submit',
        'postSubmit': 'login_post_submit',
    },

    /**
     * Static event store
     */
    eventStore: {},

    /**
     * Set an event
     */
    setEvent: function (event, promise) {
        if (typeof LoginSubmissionBase.prototype.eventStore[event] === 'undefined') {
            LoginSubmissionBase.prototype.eventStore[event] = [];
        }

        LoginSubmissionBase.prototype.eventStore[event].push(promise);
    },
};

/**
 * Handles submission of login form
 *
 * @param node form The form element
 */
function LoginSubmission(form) {
    var $this = this,
        $syncEvents = new SyncEvents();

    /**
     *
     */
    this.init = function () {
        bindEvents();

        $this.setEvent(LoginSubmission.prototype.events.preSubmit, onEventLogin);
    };

    /**
     *
     */
    function bindEvents() {
        utility.addEventListener(form, 'submit', onSubmit);
    }

    /**
     * Triggers a synchronous event
     */
    function triggerEvent(event) {
        var events = LoginSubmission.prototype.eventStore,
            options = {};

        options['form'] = form;

        if (typeof events[event] !== 'undefined') {
            $syncEvents.execute(events[event], options);
        }
    }

    /**
     *
     */
    function onSubmit(e) {
        utility.triggerEvent(form, LoginSubmission.prototype.events.preSubmit);
        triggerEvent(LoginSubmission.prototype.events.preSubmit);

        e.preventDefault();
    }

    /**
     *
     */
    function onEventLogin() {
        return new Promise(function (resolve, reject) {
            resolve();

            utility.triggerEvent(form, LoginSubmission.prototype.events.postSubmit);

            form.submit();
        });
    }
}

LoginSubmission.prototype = LoginSubmissionBase.prototype;

export default LoginSubmission;
