import * as utility from "Base/utility";
import GameLaunch from "Base/game/game-launch";
import * as gameLaunchUtil from  "./game-launch-util";
import Console from "Base/debug/console";
import xhr from "BaseVendor/reqwest";

/**
 * TGP game launching
 */
function TGPLaunch() {
    var requestFlag = 0,
        key = 'tgp';

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

            var endpoint = 'game/tgp/lobby';

            xhr({
                url: utility.url(endpoint),
                method: 'get',
                type: 'json',
                data: {
                    languageCode: gameLaunchUtil.getIcoreLanguageCode(key)
                }
            }).then(function (response) {
                if (response.gameurl) {
                    Console.log("TGP::launch - Feth game  success");
                    if (typeof options.onSuccess === 'function') {
                        options.onSuccess.apply(null, [response]);
                    }
                } else {
                    Console.log("TGP::launch - Feth game failed");
                    if (typeof options.onFail === 'function') {
                        options.onFail.apply(null, ['']);
                    }
                }
            }).fail(function (err, msg) {
                Console.log("TGP::launch - Feth game failed", err, msg);
                if (typeof options.onFail === 'function') {
                    options.onFail.apply(null, [err, msg]);
                }
            }).always(function (response) {
                requestFlag = 0;
            });
        }
    };

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


TGPLaunch.prototype = GameLaunch.prototype;

export default TGPLaunch;
