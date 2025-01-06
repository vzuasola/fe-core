import * as utility from "Base/utility";
import GameLaunch from "Base/game/game-launch";
import PopupWindow from "Base/utils/popup";
import ModalUtil from "Base/utils/modal";


/**
 * Opus specific game launching
 */
function OpusLauncher() {
    var isSessionAlive = app.settings.login, modalUtil = new ModalUtil();
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
        var realplay = options.real;
        var provider = options.provider;
        if (typeof isSessionAlive !== 'undefined') {
            if (provider) {
                if (!isCountrySupported(provider)) {
                    modalUtil.show('game-rcl-lightbox');
                } else if (isCountrySupported(provider) && !usercurrencycheck() && realplay !== 'undefined') {
                    modalUtil.show('game-ucl-lightbox');
                } else if (usercurrencycheck() && realplay !== 'undefined') {
                    popuplaunch(options);
                }
            }
        }

        if (provider) {
            if (isCountrySupported(provider) && typeof realplay === 'undefined' ) {
                popuplaunch(options);
            } else if (!isCountrySupported(provider) && typeof realplay === 'undefined') {
                modalUtil.show('game-rcl-lightbox');
            }
        }
    };

    function popuplaunch(options) {
        var uri = utility.url('/game/opus/authenticate');
        PopupWindow(uri, options.title ? options.title : 'KenoGame', {width: 1050, height: 600});
    }

    function usercurrencycheck() {
        var currencyopus = app.settings.opus_currency;
        var usercurrency = app.settings.opus_currencies;
        var currencycheck = getCurrency(currencyopus);
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

    function isCountrySupported(provider) {
        var country = document.getElementsByTagName('body')[0].getAttribute('data-geoip');
        if (country) {
            var countries = app.settings.game_provider[provider].countries.split("\r\n");
            for (var i = 0; i < countries.length; i++) {
                if (countries[i] === country) {
                    return false;
                }
            }
        }
        return true;
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


OpusLauncher.prototype = GameLaunch.prototype;

export default OpusLauncher;
