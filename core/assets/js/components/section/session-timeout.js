import * as utility from "Base/utility";
import reqwest from "BaseVendor/reqwest";
import Counter from "Base/utils/counter";
import Storage from "Base/utils/storage";
import SessionStorage from "Base/utils/session-storage";
import SessionLightbox from "Base/session/session-lightbox";
import NotificationLightbox from "Base/session/notification-lightbox";
import detectIE from "Base/browser-detect";
import Console from "Base/debug/console";

var crosstab = require("BaseVendor/crosstab");

/**
 * Class that handles Session timeout
 *
 * TODO Expose an init public method rather than self initializing on the
 * constructor
 */
function SessionTimeout() {
    "use strict";

    var $timeout = 300, // default timeout
        $sessionLightboxTimeout = 15, // default timeout
        $id = 'modal-session-timeout',
        $destroy = false, // a flag to check if the session is already being destroyed

        // dependencies
        $storage,
        $sessionStorage,
        $counter,
        $sessionLightbox,
        $confirmLightbox;

    /**
     * Constructor
     */
    function construct() {
        $storage = new Storage();
        $sessionStorage = new SessionStorage();

        if (typeof app !== 'undefined' &&
            typeof app.settings !== 'undefined' &&

            typeof app.settings.login !== 'undefined' &&
            app.settings.login === true &&

            typeof app.settings.sessionTimeout !== 'undefined' &&
            app.settings.sessionTimeout
        ) {
            // if login, init using this
            init();
        } else {
            // for pre login, just do a minor initialization
            initNotLogin();
        }
    }

    construct();

    /**
     * Initialize the session timeout script
     */
    function init() {
        $timeout = app.settings.sessionTimeout || $timeout;

        $counter = new Counter($timeout, {
            onRestart: onCounterRestart,
            onBeforeStop: onCounterBeforeStop,
            onStop: onCounterStop,
        });

        $counter.start();

        $sessionLightbox = new SessionLightbox($sessionLightboxTimeout, {
            onConfirm: onSessionLightboxConfirm,
            onCancel: onSessionLightboxCancel,
            onTimeout: onSessionLightboxTimeout,
        });

        attachEvents();
    }

    /**
     * Initialize when prelogin
     */
    function initNotLogin() {
        // do not fire notification lightbox if this tab was just killed on
        // the background
        if ($sessionStorage.get('session.destroy.background')) {
            $sessionStorage.remove('session.destroy.background');

            Console.log('Session: Killed on Background');
            return;
        }

        if ($storage.get('session.confirm.show')) {
            $storage.remove('session.confirm.show');

            $confirmLightbox = new NotificationLightbox();
            $confirmLightbox.show();
        }
    }

    /**
     *
     */
    function onCounterBeforeStop(counter) {
        Console.log('Session: Almost Done');

        $sessionLightbox.show();
    }

    /**
     *
     */
    function onCounterStop(counter) {
        Console.log('Session: Done');
    }

    /**
     *
     */
    function onCounterRestart() {
        Console.log('Session: Restart');
    }

    /**
     *
     */
    function onSessionLightboxTimeout() {
        Console.log('Session: Destroy');

        $storage.set('session.confirm.show', true);
        $counter.kill();

        destroySession();
    }

    /**
     *
     */
    function onSessionLightboxConfirm() {
        Console.log('Session: Destroy');

        $counter.kill();

        destroySession();
    }

    /**
     *
     */
    function onSessionLightboxCancel() {
        Console.log('Session: Restarting');
        $counter.restart();

        broadcast('session.reset');
    }

    /**
     * Destroys the session
     */
    function destroySession() {
        // encapuslate this destory session with a flag, we do not want two AJAX
        // calls killing the session, one is enough
        if (!$destroy) {
            $destroy = true;

            // kill session of all tabs
            broadcast('session.destroy');

            var modal = document.getElementById($id),
                from = modal.getAttribute('data-on-timeout'),
                logout = utility.url('/logout');

            if (from) {
                if (from.indexOf("?") > -1) {
                    from = from.replace("?", "&");
                }
                logout = logout + '?from=' + from;
            }

            // we send a sneaky AJAX so that the user cannot just simply cancel the
            // logout
            reqwest({
                url: logout,
                method: 'get',
            });

            // redirect the user
            setTimeout(function () {
                window.location.replace(logout);
            }, 500);
        }
    }

    /**
     * It's like destorying the session but silently
     */
    function destroySessionSilent() {
        // encapuslate this destory session with a flag, we do not want to AJAX
        // calls killing the session, one is enough
        if (!$destroy) {
            $destroy = true;

            // do not show the lighbox for tabs destroyed silently
            $sessionStorage.set('session.destroy.background');

            var modal = document.getElementById($id),
                from = modal.getAttribute('data-on-timeout'),
                logout = utility.url('/logout');

            if (from) {
                if (from.indexOf("?") > -1) {
                    from = from.replace("?", "&");
                }
                logout = logout + '?from=' + from;
            }

            // redirect the user
            setTimeout(function () {
                window.location.replace(logout);
            }, 500);
        }
    }

    /**
     * Attaches the document events
     */
    function attachEvents() {
        // broadcast immediately as the page loads
        utility.ready(function () {
            Console.log('Session Init');
            broadcast('session.reset');
        });

        document.onclick = function () {
            broadcast('session.reset');
        };

        document.onscroll = function () {
            broadcast('session.reset');
        };

        document.onkeypress = function () {
            broadcast('session.reset');
        };

        crosstab.on('session.reset', function (event) {
            $sessionLightbox.hide();
            $counter.restart();
        });

        crosstab.on('session.destroy', function (event) {
            $sessionLightbox.hide();
            $counter.kill();

            destroySessionSilent();
        });
    }

    /**
     * Broadcast the event to other tabs
     */
    function broadcast(event) {

        try {
            crosstab.broadcast(event);
            // force IE 8 to reiterate the command because IE8 is shitty
            if (detectIE() === 8) {
                setTimeout(function () {
                    crosstab.broadcast(event);
                }, 300);
            }
        } catch (error) {
            // do nothing
        }
    }
}

new SessionTimeout();

