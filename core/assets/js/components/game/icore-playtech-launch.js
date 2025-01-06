import * as utility from "Base/utility";
import GameLaunch from "Base/game/game-launch";
import * as gameLaunchUtil from  "./game-launch-util";
import Console from "Base/debug/console";
import xhr from "BaseVendor/reqwest";

/**
 * Playtech specific game launching
 */
function ICorePlaytechLauncher() {
    var requestFlag = 0,
        key = 'dafabetgames';

    /**
     * A custom init method that will be called on document ready
     */
    this.init = function () {
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
        if (!requestFlag) {
            requestFlag = 1;
            var data = {
                languageCode: gameLaunchUtil.getIcoreLanguageCode(key),
                playMode: options.playmode,
                extGameId: options.extGameId || '',
            };

            // Override onFail method for Playtech
            options.onFail = gameLaunchUtil.showErrorMessage;
            var codePath = '';
            if (options.gamecode) {
                codePath = '/' + options.gamecode;
            }

            var endpoint = 'ajax/game/url/playtech' + codePath;

            xhr({
                url: utility.url(endpoint),
                method: 'get',
                type: 'json',
                data: data
            }).then(function (response) {
                if (response.gameUrlParams.Username !== undefined) {
                    Console.log("ICorePlaytech::launch - Feth game success");
                    doPasIntegration(response.gameUrlParams, options);
                } else {
                    Console.log("ICorePlaytech::launch - Fetch game failed");
                    if (typeof options.onFail === 'function') {
                        options.onFail.apply(null, [options.providerProduct, response]);
                    }
                }
            }).fail(function (err, msg) {
                Console.log("ICorePlaytech::launch - Fetch game failed", err, msg);
                if (typeof options.onFail === 'function') {
                    options.onFail.apply(null, [err, msg]);
                }
            }).always(function (response) {
                requestFlag = 0;
            });
        }
    };

    function doPasIntegration(xhrResponse, options) {
        setiApiConfOverride(options.casino);
        doPasLogin(xhrResponse, options);
    }

    function doPasLogin(xhrResponse, options) {
        iapiSetClientParams(iapiConf['clientType'], 'waitlogin=1');
        Console.log('ICore Playtech Game Provider: Authenticating');
        // xhrResponse.language might not be available on icore response, if not, it will cause the PAS integration to fail
        // We'll fallback to our own site's langauge mapping instead
        var lang = xhrResponse.language || gameLaunchUtil.getIcoreLanguageCode(key);

        // Set the callback for the PAS login
        iapiSetCallout('Login', doCreateUrl(xhrResponse, options, lang));
        iapiLogin(
            xhrResponse.Username,
            xhrResponse.SecureToken + '@' + xhrResponse.PlayerId,
            1,
            lang
        );
    }

    function doCreateUrl(xhrResponse, options, lang) {
        return function () {
            if (iapiConf['clientUrl_' + iapiConf['clientType']]) {
                var url = iapiConf['clientUrl_' + iapiConf['clientType']];
                if (xhrResponse.ExtGameId) {
                    url = iapiAddUrlParams(url, 'game=' + xhrResponse.ExtGameId);
                }

                if (xhrResponse.RealPlay) {
                    url = iapiAddUrlParams(url, 'preferedmode=' + ('1' === xhrResponse.RealPlay ? 'real' : 'offline'));
                }

                if (lang) {
                    url = iapiAddUrlParams(url, 'language=' + lang);
                }

                if (options.tablename) {
                    url = iapiAddUrlParams(url, 'launch_alias=' + options.tablename);
                }

                if (iapiConf['clientType']) {
                    url = iapiAddUrlParams(url, 'clientType=' + iapiConf['clientType']);
                }

                if (iapiConf['clientPlatform']) {
                    url = iapiAddUrlParams(url, 'clientPlatform=' + iapiConf['clientPlatform']);
                }

                if ((options.type !== undefined && options.type === 'html5') || iapiConf['ngm'] !== undefined) {
                    url = iapiAddUrlParams(url, 'ngm=1');
                }

                // set additional client params
                if (iapiClientParams['clientParams_' + iapiConf['clientType']]) {
                    url = iapiAddUrlParams(url, iapiClientParams['clientParams_' + iapiConf['clientType']]);
                }

                if (iapiLoginModeFlash) {
                    url = '';
                }
            }

            if (url) {
                Console.log("ICorePlaytech::launch - Feth game success", url);
                if (typeof options.onSuccess === 'function') {
                    options.onSuccess.apply(null, [{gameurl:url}]);
                }
            } else {
                Console.log('ICorePlaytech::launch - Feth game failed', url);
                if (typeof options.onFail === 'function') {
                    options.onFail.apply(null, [url]);
                }
            }
        };
    }

    /**
     * Override the default iapiConf settings from Playtech
     */
    function setiApiConfOverride(casino) {
        for (var k in iapiConf) {
            if (typeof app.settings.icore_games[casino].iapiConfOverride !== 'undefined' &&
                typeof app.settings.icore_games[casino].iapiConfOverride[k] !== 'undefined' &&
                app.settings.icore_games[casino].iapiConfOverride[k] !== undefined
            ) {
                iapiConf[k] = app.settings.icore_games[casino].iapiConfOverride[k];
            }
        }
    }

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
     * Authenticate using username and password
     *
     * @param string username
     * @param string password
     *
     * @return boolean|promise
     */
    this.login = function (username, password) {
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

// inheritance
ICorePlaytechLauncher.prototype = GameLaunch.prototype;

export default ICorePlaytechLauncher;
