import * as utility from "Base/utility";
import LoginSubmission from "Base/login/login-submission";

/**
 * Game launcher manager
 *
 * Example usage:
 *
 * var gameLauncher = new GameLauncher();
 * gameLauncher.setProvider('playtech', new PlaytechLauncher());
 * gameLauncher.init();
 */
export default function GameLauncher(options) {
    "use strict";

    var providers = {};

    GameLauncher.prototype = {
        /**
         * Support for statically calling the launch method
         */
        launch: function (provider, options) {
            options['provider'] = provider;

            invoke(provider, 'prelaunch', [options]);
            invoke(provider, 'launch', [options]);
        }
    };

    /**
     *
     */
    this.setProvider = function (id, provider) {
        providers[id] = provider;
    };

    /**
     *
     */
    this.init = function () {
        bindEvents();
    };

    /**
     *
     */
    function bindEvents() {
        utility.ready(function () {
            broadcast('init');
        });

        utility.addEventListener(document, 'click', onClick);

        attachLoginEvents();
    }

    /**
     * Attach login event hooks
     */
    function attachLoginEvents() {
        var method = 'login',
            event = LoginSubmission.prototype.events.preSubmit;

        for (var key in providers) {

            if (typeof providers[key][method] === 'function') {

                var promise = function (options) {
                    var form = options['form'];

                    var provider = this.provider,
                        username = form.querySelector('#LoginForm_username').value,
                        password = form.querySelector('#LoginForm_password').value;

                    username = username.toUpperCase();

                    return providers[provider][method](username, password);
                }.bind({
                    provider: key,
                });

                LoginSubmission.prototype.setEvent(event, promise);
            }
        }
    }

    /**
     *
     */
    function onClick(e) {
        var target = utility.getTarget(e);

        if (target.getAttribute('data-game-launch') === 'true' &&
            target.getAttribute('data-game-provider')
        ) {
            var provider = target.getAttribute('data-game-provider');
            var options = getOptionsByElement(target);

            options['provider'] = provider;

            invoke(provider, 'prelaunch', [options]);
            invoke(provider, 'launch', [options]);
        }
    }

    /**
     *
     */
    function getOptionsByElement(element) {
        var options = {};
        var attributes = utility.getAttributes(element);

        for (var attr in attributes) {
            if (attr.indexOf('data-game', 0) === 0) {
                var key = attr.replace('data-game-', '');
                options[key] = attributes[attr];
            }
        }

        return options;
    }

    /**
     *
     */
    function invoke(id, method, args) {
        if (typeof providers[id][method] === 'function') {
            args = args || [];
            providers[id][method].apply(providers[id], args);
        }
    }

    /**
     *
     */
    function broadcast(method, args) {
        for (var key in providers) {
            if (typeof providers[key][method] === 'function') {
                args = args || [];
                providers[key][method].apply(providers[key], args);
            }
        }
    }
}
