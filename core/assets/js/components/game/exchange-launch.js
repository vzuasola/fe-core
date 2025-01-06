import * as utility from "Base/utility";
import GameLaunch from "Base/game/game-launch";
import PopupWindow from "Base/utils/popup";

/**
 * Opus specific game launching
 */
function ExchangeLauncher() {
    var isSessionAlive = app.settings.login;
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
     * Launch a game
     *
     * @param array options
     *
     * @return boolean
     */
    this.launch = function (options) {

        if (typeof isSessionAlive !== 'undefined') {
            var currency = usercurrencycheck();
        }
        if (typeof isSessionAlive !== 'undefined' && currency) {
            popuplaunch();
        }
    };

    function popuplaunch() {
        var loaderUrl = utility.url('/game/loader'),
            uri = utility.url('/game/exchange/cookie'),
            windowObject = '';

        windowObject = PopupWindow(loaderUrl, 'ExchangeGame', {width: 1380, height: 800});
        setTimeout(function () {
            windowObject.location.href = uri;
        }, 5000);
    }

    function usercurrencycheck() {
        var currencyopus = app.settings.exchange_currency,
            usercurrency = app.settings.exchange_user_currencies,
            currencycheck = getCurrency(currencyopus);
        if (inArray(usercurrency, currencycheck)) {
            return true;
        }
        return false;
    }

    function inArray(usercurrency, currencycheck) {
        for (var i = 0; i < currencycheck.length; i++) {
            if (currencycheck[i] === usercurrency) {
                return  true;
            }
        }
        return false;
    }

    function getCurrency(currencyopus) {
        var strArray = currencyopus.split("\r\n");
        return strArray;
    }
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


ExchangeLauncher.prototype = GameLaunch.prototype;

export default ExchangeLauncher;
