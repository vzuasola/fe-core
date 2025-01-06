import * as utility from "Base/utility";
import GameLaunch from "Base/game/game-launch";
import ModalUtil from "Base/utils/modal";
import PopupWindow from "Base/utils/popup";

/**
 * Kiron Virtual specific game launching
 */
function KironVirtualsLauncher() {
    var isSessionAlive = app.settings.login,
        modalUtil = new ModalUtil(),
        currencies = null,
        currency = null;

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
                } else if (isCountrySupported(provider) && !isCurrencySupported()) {
                    modalUtil.show('game-ucl-lightbox');
                } else if (isCurrencySupported()) {
                    launchGame();
                }
            }
        } else if (typeof realplay === 'undefined') {
            if (provider) {
                if (!isCountrySupported(provider)) {
                    modalUtil.show('game-rcl-lightbox');
                } else {
                    launchGame();
                }
            }
        }
    };

    function launchGame() {
        var loaderUrl = utility.url('/game/loader'),
            uri = utility.url('/launch/virtuals'),
            windowObject = '';

        windowObject = PopupWindow(loaderUrl, 'KironVirtuals', {width: 1380, height: 800});
        setTimeout(function () {
            windowObject.location.href = uri;
        }, 5000);
    }

    function isCurrencySupported() {
        currency = utility.getCookie('currency');
        if (currency) {
            currencies = app.settings.kiron_virtual_sports.currencies.split("\r\n");
            for (var i = 0; i < currencies.length; i++) {
                if (currencies[i] === currency) {
                    return true;
                }
            }
        }
        return false;
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

KironVirtualsLauncher.prototype = GameLaunch.prototype;

export default KironVirtualsLauncher;
