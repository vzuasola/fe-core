import * as utility from "Base/utility";
import GameLaunch from "Base/game/game-launch";
import * as gameLaunchUtil from  "./game-launch-util";
import Console from "Base/debug/console";
import xhr from "BaseVendor/reqwest";

/**
 * PTPlus Gaming game launching
 */
function PTPlusLaunch() {
    var requestFlag = 0,
        key = 'ptplus';

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
            var data = {
                languageCode: gameLaunchUtil.getIcoreLanguageCode(key),
            };

            var endpoint = 'ajax/game/url/ptplus';
            if (options.isLobby === true) {
                endpoint = 'ajax/game/lobby/ptplus';
            }

            if (options.gamecode) {
                endpoint += '/' + options.gamecode;
            }

            if (options.providerProduct) {
                data.providerProduct = options.providerProduct;
            }

            if (options.extGameId && options.extGameId !== '') {
                data.extGameId = options.extGameId;
            }

            xhr({
                url: utility.url(endpoint),
                method: 'get',
                type: 'json',
                data: data
            }).then(function (response) {
                if (response.gameurl) {
                    Console.log("PTPlus Game::launch - Feth game success");
                    if (typeof options.onSuccess === 'function') {
                        options.onSuccess.apply(null, [response]);
                    }
                } else {
                    Console.log("PTPlus Game::launch - Feth game failed");
                    // Override to ErrorMessageLightbox
                    options.onFail = gameLaunchUtil.showErrorMessage;
                    options.onFail.apply(null, [options.providerProduct, response]);
                }
            }).fail(function (err, msg) {
                Console.log("PTPlus Game::launch - Feth game failed", err, msg);
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

PTPlusLaunch.prototype = GameLaunch.prototype;

export default PTPlusLaunch;
