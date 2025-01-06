import * as utility from "Base/utility";
import GameLaunch from "Base/game/game-launch";
import ModalUtil from "Base/utils/modal";
import PopupWindow from "Base/utils/popup";
import reqwest from "BaseVendor/reqwest";

/**
 * Opus specific game launching
 */
function FishHunterLauncher() {
    var isSessionAlive = app.settings.login,
        modalUtil = new ModalUtil(),
        path = utility.url('ajax/games/fish_hunter'),
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

            if (isCurrencySupported()) {
                launchGame();
            } else {
                modalUtil.show('modal-game-ucl');
            }
        }
    };

    function launchGame() {
        var languageCode = getICoreLanguage();
        var defaults = {
            "width": 800,
            "height": 500,
            "resizable": 1,
            "scrollbars": 1
        };
        popupWindow = PopupWindow("", 'icoreServicesWindow', defaults);

        reqwest({
            url: path,
            type: 'json',
            data: {
                languageCode: languageCode
            },
            complete: function (response) {
                if (!response.lobbyUrl) {
                    modalUtil.show('modal-fish-hunter-fallback');
                    popupWindowClose();
                    return;
                }

                popupWindow.location.replace(decodeURIComponent(response.lobbyUrl));
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
            currencies = app.settings.icore_games.fish_hunter.currencies.split("\r\n");
            for (var i = 0; i < currencies.length; i++) {
                if (currencies[i] === currency) {
                    return true;
                }
            }
        }
        return false;
    }

    function getICoreLanguage() {
        var languages = app.settings.icore_games.fish_hunter.languages.split("\r\n");
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


FishHunterLauncher.prototype = GameLaunch.prototype;

export default FishHunterLauncher;
