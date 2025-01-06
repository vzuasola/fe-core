import * as utility from "Base/utility";
import GameLaunch from "Base/game/game-launch";
import Console from "Base/debug/console";
import Logger from "Base/logger";
import Storage from "Base/utils/storage";

/**
 * Playtech specific game launching
 */
function PlaytechLauncher() {
    var $this = this,
        isSessionAlive = app.settings.login,
        isFuturama = app.settings.pas.futurama_switch || 0,
        timer = null,
        // milliseconds = (seconds * min) * number
        keepSessionTime = ((1000 * 60) * 15),
        sessionFlag = 'pas.session.flag',
        Store = new Storage();

    /**
     * A custom init method that will be called on document ready
     */
    this.init = function () {
        setiApiConfOverride();
        iapiSetCallout('Logout', onLogout);
        iapiSetCallout('KeepAlive', onKeepAlive);

        if (isSessionAlive) {
            // Persist session
            sessionPersist();
        } else if (Store.get(sessionFlag) !== null) {
            doLogout();
        }
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
        var real = 1,
            language = getLanguageMap(app.settings.lang);

        // PAS hijacks the login process
        // It will login via PAS first
        return new Promise(function (resolve, reject) {
            // If futurama toggle is on, then no need to do PAS login on login form
            if (isFuturama) {
                return resolve();
            }

            // Set the callback for the PAS login
            iapiSetCallout('Login', onLogin(resolve, username));

            // Before login, check if there are cookies on PTs end
            iapiSetCallout('GetLoggedInPlayer', function (response) {
                if (verifyCookie(response)) {
                    iapiSetCallout('Logout', function (response) {
                        Console.log('Playtech PAS Provider: Pre-login logout - complete');
                        Console.log(response);

                        Console.log('Playtech PAS Provider: Authenticate');
                        iapiLogin(username, password, real, language);
                    });

                    doLogout();
                } else {
                    Console.log('Playtech PAS Provider: Authenticate');
                    iapiLogin(username, password, real, language);
                }
            });

            // Trigger the session check
            doCheckSession();

            // after n seconds, nothing still happen, I'll let the other
            // hooks to proceed
            setTimeout(function () {
                reject();
            }, 10000);
        });
    };

    /**
     * Launch a game
     *
     * @param array options
     *
     * @return boolean
     */
    this.launch = function (options) {
        iapiSetCallout('GetLoggedInPlayer', function (response) {
            // dynamic values
            var provider = options.provider,
                gameId = options.code,
                product = options.product,
                type = options.type,
                gamewindow = options.gamewindow,
                params = options.params || {},
                real = 'offline';

            // allow params passed by attributes to be concatenated as an assoc
            // object
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

            if (!isSessionAlive && 'real' === real) {
                // Real mode but on prelogin state
                $this.triggerEvent($this.events.preLogin, provider);
            } else if (!verifyCookie(response) && 'real' === real) {
                // Real mode but without playtech session
                $this.triggerEvent($this.events.invalidSession, provider);
            } else {
                try {
                    // Trigger game launch
                    // Freeplay and real mode
                    params = setClientParams(params);

                    if (type === 'html5') {
                        params['ngm'] = 1;
                    }

                    iapiSetClientParams(product, utility.serialize(params));

                    // If gamewindow an existing iframe, it will load the game inside it
                    var launchResponse = iapiLaunchClient(product, gameId, real, gamewindow);
                    Console.log('Playtech PAS Provider: Attempting to launch game `' + gameId + '`');
                    Console.log(utility.serialize(params));
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
        doLogout();
    };

    /**
     * Callback on login process
     */
    function onLogin(resolve, username) {
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
                // Flag for detecting if the player is still logged-in on PAS
                Store.set(sessionFlag, '1');

                if (response.sessionValidationData !== undefined &&
                    response.sessionValidationData.SessionValidationByTCVersionData !== undefined
                ) {
                    // Change the ValidateLoginSession callback to handle the TC validation
                    iapiSetCallout('ValidateLoginSession', onTCVersionValidation(resolve, username));
                    // Auto validate the TC version
                    iapiValidateTCVersion(response.sessionValidationData.SessionValidationByTCVersionData[0].termVersionReference, 1, 1);
                } else {
                    // Continue the login proces
                    return resolve();
                }
            }
        };
    }

    /**
     * Handle the TCVersion response during login
     */
    function onTCVersionValidation(resolve, username) {
        return function (response) {
            Console.log('Playtech PAS Provider: onTCVersionValidation');
            Console.log(response);
            if (0 === response.errorCode) {
                return resolve();
            }
        };
    }

    /**
     * Logs out the PAS session
     */
    function doLogout() {
        iapiSetCallout('GetLoggedInPlayer', function (response) {
            // Remove the session flag to avoid recurring calls
            Store.remove(sessionFlag);
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
     * Logout handler
     */
    function onLogout(response) {
        Logger.log('AUTH.PAS', {
            status_code: 'OK',
            response: response,
            others: 'PAS onLogout authentication',
        });

        Console.log('Playtech PAS Provider: onLogout');
        Console.log(response);
        clearTimeout(timer);
    }

    /**
     * Call the keep alive iapi method, keepSessionTime is configured to 15mins
     * IMS default session timeout is configured to 30mins
     */
    function doKeepAlive() {
        iapiSetCallout('GetLoggedInPlayer', function (response) {
            if (verifyCookie(response)) {
                Console.log('Playtech PAS Provider: doKeepAlive');
                iapiKeepAlive(1, keepSessionTime);
            }
        });

        // Trigger the session check
        doCheckSession();
    }

    /**
     * Keeptimeout handler
     */
    function onKeepAlive(response) {
        Console.log('Playtech PAS Provider: onKeepAlive');
        Console.log(response);
        if (response.errorCode !== 0) {
            clearTimeout(timer);
        }
    }

    /**
     * Checks the PAS session, checks if the player is logged-in in PT or not
     */
    function doCheckSession() {
        Console.log('Playtech PAS Provider: doCheckSession');
        iapiGetLoggedInPlayer(1);
    }

    /**
     * Persist session on post-login
     */
    function sessionPersist() {
        if (!isFuturama) {
            doKeepAlive();
            if (timer === null) {
                timer = setTimeout(function () {
                    timer = null;
                    sessionPersist();
                }, keepSessionTime);
            }
        }
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

    /**
     * Define user parameters
     */
    function setClientParams(parameters) {
        // remap language
        var language = getLanguageMap(app.settings.lang),
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
        var map = app.settings.pas.language_map;

        return typeof map[lang] !== 'undefined' ? map[lang] : lang;
    }

    /**
     * Override the default iapiConf settings from Playtech
     */
    function setiApiConfOverride() {
        if (iapiConf) {
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
PlaytechLauncher.prototype = GameLaunch.prototype;

export default PlaytechLauncher;
