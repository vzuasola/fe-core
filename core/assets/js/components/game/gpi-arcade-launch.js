import * as utility from "Base/utility";
import GameLaunch from "Base/game/game-launch";
import Console from "Base/debug/console";
import xhr from "BaseVendor/reqwest";

/**
 * GPI arcade game launching
 */
function GpiArcadeLaunch() {
    var requestFlag = 0;

    /**
     * Launch a game
     *
     * @param array options
     *
     * @return boolean
     */
    this.launch = function (options) {
        if (!requestFlag) {
            requestFlag = 1;

            var endpoint = 'game/gpi-arcade/' + (options.gamecode || '');

            xhr({
                url: utility.url(endpoint),
                method: 'get',
                type: 'json',
                data: {
                    languageCode: getLanguageCode()
                }
            }).then(function (response) {
                if (response.gameurl) {
                    Console.log("GPI Arcade::launch - Feth game  success");
                    if (typeof options.onSuccess === 'function') {
                        options.onSuccess.apply(null, [response]);
                    }
                } else {
                    Console.log("GPI Arcade::launch - Feth game failed");
                    if (typeof options.onFail === 'function') {
                        options.onFail.apply(null, ['']);
                    }
                }
            }).fail(function (err, msg) {
                Console.log("GPI Arcade::launch - Feth game failed", err, msg);
                if (typeof options.onFail === 'function') {
                    options.onFail.apply(null, [err, msg]);
                }
            }).always(function (response) {
                requestFlag = 0;
            });
        }
    };

    function getLanguageCode() {
        var lang = app.settings.lang,
            map = app.settings.gpi_arcade.languages || {};
        return typeof map[lang] !== 'undefined' ? map[lang] : lang;
    }

    /**
     * A custom init method that will be called on document ready
     */
    this.init = function () {
        return true;
    };

    /**
     * Authenticate using username and password
     *
     * @param string password
     *
     * @return boolean
     */
    this.login = function (username, password) {
        return true;
    };

    /**
     * Authenticate by token
     *
     * @param string username
     * @param string password
     *
     * @return boolean
     */
    this.authenticateByToken = function (token) {
        return true;
    };

    /**
     * Invoked when a player is logout
     *
     * @param array options
     *
     * @return boolean
     */
    this.logout = function () {
        return true;
    };
}


GpiArcadeLaunch.prototype = GameLaunch.prototype;

export default GpiArcadeLaunch;
