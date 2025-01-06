import * as drew from "Base/utility";
import GameLaunch from "Base/game/game-launch";
import Console from "Base/debug/console";
import Logger from "Base/logger";

/**
 * Playtech PAS specific game launching
 */
function PASLauncher() {
    var $this = this,
        provider = 'pas',
        settings = app.settings.pas,
        isSessionAlive = app.settings.login,
        siteLang = app.settings.lang,
        sessionKey = 'ProviderPasToken';

    /**
     * A custom init method that will be called on document ready
     */
    this.init = function () {
        setiApiConfOverride();
    };

    /**
     * Launch a game
     *
     * @param array options
     *
     * @return boolean
     */
    this.launch = function (options) {
        // Trigger override prior to launch
        setiApiConfOverride();
        // dynamic values
        var isPasAlive = false,
            language = getLanguageMap(siteLang),
            gameId = options.code,
            clientType = options.clientType || 'casino',
            type = options.type,
            gamewindow = options.gamewindow,
            params = options.params || {},
            real = 'offline';

        // Override Global provider
        provider = options.provider;

        // Override clientType
        if (iapiConf['clientType'] !== undefined && iapiConf['clientType'] !== clientType) {
            // Override iapiConf clientType
            overrideConf('clientType', clientType);

            // Create new client URL for new clientType
            iapiSetClientUrl(clientType, iapiConf['clientUrl_' + clientType]);
        }

        for (var attr in options) {
            if (attr.indexOf('params', 0) === -1) {
                var key = attr.replace('params-', '');
                params[key] = options[attr];
            }
        }

        // make sure we put something on the real mode
        if (typeof options.real !== 'undefined') {
            switch (parseInt(options.real)) {
                case 0:
                    real = 'free';
                    break;
                case 1:
                    real = 'real';
                    break;
            }
        }

        iapiSetCallout('GetLoggedInPlayer', function (response) {
            // Analyze response
            isPasAlive = verifyCookie(response);

            // Session was changed on either PT or iCore
            var sessionChanged = isSessionChanged(response.username.toUpperCase());

            if (!isSessionAlive && 'real' === real) {
                // Real mode but on prelogin state
                $this.triggerEvent($this.events.preLogin, provider);
            } else if ((!isPasAlive && 'real' === real) || sessionChanged) {
                return doLoginAndGameLaunch(gameId, type, params, clientType, real, options, gamewindow, language);
            } else {
                // Active session
                return doLaunch(gameId, type, params, clientType, real, options, gamewindow);
            }
        });

        // Trigger the session check
        doCheckSession();
    };

    /**
     * Invoked when a player is logout
     *
     * @param array options
     *
     * @return boolean
     */
    this.logout = function () {
        // Trigger override prior to launch
        setiApiConfOverride();
        doLogout();
    };

    function doLoginAndGameLaunch(gameId, type, params, clientType, real, options, gamewindow, language) {
        // Set the callback for the PAS login
        iapiSetCallout('Login', onLogin(function () {
            return doLaunch(gameId, type, params, clientType, real, options, gamewindow);
        }, handleError, settings.player.username));

        Console.log('Playtech PAS Provider: Authenticate');
        iapiLoginUsernameExternalToken(settings.player.username, settings.player.token, 1, language);
    }

    /**
     * Callback on login process
     */
    function onLogin(resolve, reject, username) {
        return function (response) {
            Console.log('Playtech PAS Provider: onLogin');
            Console.log(response);

            Logger.log('AUTH.PAS', {
                status_code: 'OK',
                request: {
                    username: username,
                },
                response: response,
                others: 'PAS onLogin authentication',
            });

            if (0 === response.errorCode) {
                // Bind the user that triggered the PAS login
                storeSession();
                if (response.sessionValidationData !== undefined &&
                    response.sessionValidationData.SessionValidationByTCVersionData !== undefined
                ) {
                    // Change the ValidateLoginSession callback to handle the TC validation
                    iapiSetCallout('ValidateLoginSession', onTCVersionValidation(resolve, reject));
                    // Auto validate the TC version
                    iapiValidateTCVersion(response.sessionValidationData.SessionValidationByTCVersionData[0].termVersionReference, 1, 1);
                } else {
                    // Continue the login proces
                    return resolve();
                }
            } else {
                return reject(response.errorCode);
            }
        };
    }

    /**
     * Handle the TCVersion response during login
     */
    function onTCVersionValidation(resolve, reject) {
        return function (response) {
            Console.log('Playtech PAS Provider: onTCVersionValidation');
            Console.log(response);
            if (0 === response.errorCode) {
                return resolve();
            } else {
                return reject(response.errorCode);
            }
        };
    }

    /**
     * Logout handler
     */
    function onLogout(resolve) {
        return function (response) {
            Logger.log('AUTH.PAS', {
                status_code: 'OK',
                response: response,
                others: 'PAS onLogout authentication',
            });

            Console.log('Playtech PAS Provider: onLogout');
            Console.log(response);
            if (typeof resolve === 'function') {
                return resolve();
            }

            return true;
        };
    }

    function doLaunch(gameId, type, params, clientType, real, options, gamewindow) {
        try {
            // Trigger game launch
            // Freeplay and real mode
            params = setClientParams(params);

            if (type === 'html5') {
                params['ngm'] = 1;
            }

            iapiSetClientParams(clientType, drew.serialize(params));

            // If gamewindow an existing iframe, it will load the game inside it
            var launchResponse = iapiLaunchClient(clientType, gameId, real, gamewindow);
            Console.log('Playtech PAS Provider: Attempting to launch game `' + gameId + '`');
            Console.log(drew.serialize(params));
            Console.log(launchResponse);

            Logger.log('GL', {
                status_code: 'OK',
                request: options,
                response: launchResponse,
                others: 'PAS launch game',
            });
        } catch (e) {
            Logger.log('GL', {
                status_code: 'NOT OK',
                request: options,
                response: e,
                others: 'PAS launch game error',
            });
        }
    }

    /**
     * Logs out the PAS session
     */
    function doLogout() {
        iapiSetCallout('GetLoggedInPlayer', function (response) {
            // Set logout callback
            iapiSetCallout('Logout', onLogout);
            if (verifyCookie(response)) {
                Console.log('Playtech PAS Provider: doLogout');
                // If there's an existing cookie only then we trigger the logout
                // So that we don't blindly call and bombard the logout endpoint
                // 1 = logout to all platform
                // 1 = realmode
                iapiLogout(1, 1);
            }
        });

        // Trigger the session check
        doCheckSession();
    }

    /**
     * Checks the PAS session, checks if the player is logged-in in PT or not
     */
    function doCheckSession() {
        Console.log('Playtech PAS Provider: doCheckSession');
        iapiGetLoggedInPlayer(1);
    }

    function handleError(errorCode) {
        var map = settings.error_handling.mapping || null,
            message = 'Oops! Something went wrong, please try again later.',
            options = {
                button: settings.error_handling.button || false,
                headerTitle: settings.error_handling.headerTitle || false
            };
        if (map && map[errorCode]) {
            message = map[errorCode].message;
            options.headerTitle = map[errorCode].header || options.headerTitle;
        } else if (map['all']) {
            message = map['all'].message;
            options.headerTitle = map['all'].header || options.headerTitle;
        }

        $this.triggerEvent($this.events.serviceError, provider, {
            errorCode: errorCode,
            message: message,
            options: options
        });
    }

    /**
     * Check the getLoggedInPlayer response
     */
    function verifyCookie(res) {
        Console.log('Playtech PAS Provider: verifyCookie', res);
        if (res.errorCode === 0 &&
            (typeof res.username !== 'undefined' && res.username.length > 0)
        ) {
            return true;
        }
        return false;
    }

    function isSessionChanged(username) {
        var csession = window.btoa(username + '@' + settings.player.token),
            osession = drew.getCookie(sessionKey);
        return csession !== osession;
    }

    function storeSession() {
        var session = window.btoa(settings.player.username + '@' + settings.player.token);
        drew.setCookie(sessionKey, session);
    }

    /**
     * Define user parameters
     */
    function setClientParams(parameters) {
        // remap language
        var language = getLanguageMap(siteLang),
            defaults = {
                language: language,
                advertiser: 'ptt',
                fixedsize: 1
            },
            params = parameters || {};

        // Set defaults
        for (var name in defaults) {
            if (params[name] === undefined) {
                params[name] = defaults[name];
            }
        }

        return params;
    }

    /**
     * Gets the language mapping
     */
    function getLanguageMap(lang) {
        var map = settings.language_map;
        return typeof map[lang] !== 'undefined' ? map[lang] : lang;
    }

    /**
     * Override the default iapiConf settings from Playtech
     */
    function setiApiConfOverride() {
        if (iapiConf !== undefined) {
            for (var k in iapiConf) {
                if (typeof app.settings.pas.iapiconf_override !== 'undefined' &&
                    typeof app.settings.pas.iapiconf_override[k] !== 'undefined' &&
                    app.settings.pas.iapiconf_override[k] !== undefined
                ) {
                    iapiConf[k] = app.settings.pas.iapiconf_override[k];
                }
            }
            for (var n in app.settings.pas.iapiconf_override) {
                if (typeof app.settings.pas.iapiconf_override !== 'undefined') {
                    iapiConf[n] = app.settings.pas.iapiconf_override[n];
                }
            }
        }
    }

    function overrideConf(key, value) {
        if (iapiConf !== undefined && iapiConf[key] !== undefined) {
            iapiConf[key] = value;
            return true;
        }

        return false;
    }

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
}

// inheritance
PASLauncher.prototype = GameLaunch.prototype;

export default PASLauncher;
