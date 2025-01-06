import * as utility from "Base/utility";
import GameLaunch from "Base/game/game-launch";
import ModalUtil from "Base/utils/modal";
import PopupWindow from "Base/utils/popup";
import reqwest from "BaseVendor/reqwest";

/**
 * Video Racing specific game launching
 */
function VideoRacingLauncher() {
    var isSessionAlive = app.settings.login,
        modalUtil = new ModalUtil(),
        path = utility.url('ajax/games/video_racing'),
        popupWindow = null,
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
        if (typeof isSessionAlive !== 'undefined') {
            var provider = options.provider;
            if (provider) {
                if (!isCountrySupported(provider)) {
                    modalUtil.show('game-rcl-lightbox');
                } else if (isCountrySupported(provider) && !isCurrencySupported()) {
                    modalUtil.show('game-ucl-lightbox');
                } else if (isCurrencySupported()) {
                    launchGame();
                }
            }
        }
    };

    function launchGame() {
        var languageCode = getICoreLanguage(), loaderUrl = utility.url('/game/loader');
        popupWindow = PopupWindow(loaderUrl, 'VRGame', {width: 1330, height: 800});

        reqwest({
            url: path,
            type: 'json',
            data: {
                languageCode: languageCode
            },
            complete: function (response) {
                if (!response.lobbyUrl) {
                    modalUtil.show('game-videoracing-fallback-error');
                    popupWindowClose();
                    return;
                }
                setTimeout(function () {
                    popupWindow.location.href = response.lobbyUrl;
                }, 5000);
                popupWindowFocus();
            }
        });
    }

    function popupWindowClose() {
        if (popupWindow.close) {
            popupWindow.close();
        }
    }

    function popupWindowFocus() {
        if (popupWindow.focus) {
            popupWindow.focus();
        }
    }

    function isCurrencySupported() {
        currency = utility.getCookie('currency');
        if (currency) {
            currencies = app.settings.icore_games.video_racing.currencies.split("\r\n");
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

    function getICoreLanguage() {
        var languages = app.settings.icore_games.video_racing.languages.split("\r\n");
        var siteLanguage = app.settings.lang;
        for (var i = 0; i < languages.length; i++) {
            var langCode = languages[i].split("|")[0];
            if (siteLanguage === langCode) {
                return languages[i].split("|")[1];
            }
        }
        return "";
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


VideoRacingLauncher.prototype = GameLaunch.prototype;

export default VideoRacingLauncher;
