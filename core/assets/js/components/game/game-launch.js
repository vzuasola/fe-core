import * as utility from "Base/utility";
import Console from "Base/debug/console";

/**
 * Base game launcher class
 */
function GameLaunch(options) {
    this.options = options;
}

GameLaunch.prototype = {
    /**
     * List of supported events
     */
    events: {
        'preLogin': 'game_provider_prelogin',
        'postLogin': 'game_provider_postlogin',
        'invalidSession': 'game_provider_invalid_session',
        'serviceError': 'game_provider_service_error'
    },

    /**
     * A custom init method that will be called on document ready
     */
    init: function () {
        Console.log('Game Launch: Init');
    },

    /**
     * Authenticate using username and password
     *
     * @param string username
     * @param string password
     *
     * @return boolean|Promise
     */
    login: function (username, password) {
        Console.log('Game Launch: Login');
    },

    /**
     * Authenticate by token
     *
     * @param string username
     * @param string password
     *
     * @return boolean
     */
    authenticateByToken: function (token) {
        Console.log('Game Launch: Authenticate by token');
    },

    /**
     * Method invoked before launching a game
     *
     * @param array options
     */
    prelaunch: function (options) {
        Console.log('Game Launch: Prelaunching game');
    },

    /**
     * Launch a game
     *
     * @param array options
     *
     * @return boolean
     */
    launch: function (options) {
        Console.log('Game Launch: Launching game');
    },

    /**
     * Invoked when a player is logout
     *
     * @param array options
     *
     * @return boolean
     */
    logout: function () {
        Console.log('Game Launch: Logout');
    },

    /**
     * Inherited method
     * Triggers a supported event
     *
     * @param string name The event name
     * @param string provider The name of the provider
     * @param array options
     */
    triggerEvent: function (name, provider, options) {
        options = options || {};
        options['provider'] = provider;

        Console.log('Provider: ' + provider + ' invoked event ' + name);

        utility.triggerEvent(document, name, options);
    },
};

export default GameLaunch;
