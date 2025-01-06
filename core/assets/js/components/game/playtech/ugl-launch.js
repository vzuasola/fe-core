import GameLaunch from "Base/game/game-launch";
import ModalUtil from "Base/utils/modal";
import * as utility from "Base/utility";
import xhr from "BaseVendor/reqwest";
import Console from "Base/debug/console";
import * as gameLaunchUtil from  "./../game-launch-util";

/**
 * Playtech UGL specific game launching
 */
function UGLLauncher() {
    var pas = app.settings.pas,
        language = app.settings.lang || '',
        currencyUserDetails = app.settings.userDetails ? app.settings.userDetails.currency : '' || '',
        playerIdUserDetails = app.settings.userDetails ? app.settings.userDetails.playerId : '' || '',
        player = pas.player,
        productsPlayerGame = ['games', 'live-dealer'],
        modalId = 'unsupported-currency-ugl',
        modalUtil = new ModalUtil();

    /**
     * A custom init method that will be called on document ready
     */
    this.init = function () {
        if (app.settings.product === 'live-dealer' && getErrorCodeFromUrl()) {
            getUglErrorsMap();
        } else {
            listeniFrame();
        }
    };

    /**
     * Launch a game
     *
     * @param array options
     *
     * @return boolean
     */
    this.launch = function (options) {
        var configs = {
            uglconfig: options.uglconfig,
            gameCodeName: options.code,
            extGameId: options.extGameId,
            tableAlias: options.tablename || '',
            currency: options.currency || currencyUserDetails,
            playerId: options.playerId || playerIdUserDetails,
            username: player.username.toUpperCase(),
            externalToken: player.token,
        };

        var currencies = getCurrencyMap(configs.uglconfig.ugl_currency, configs.currency);
        var languageSupport = getLanguageMap(configs.uglconfig.ugl_languages, language);

        if (!currencies) {
            unsupportedCurrencyUGL();
        }

        if (productsPlayerGame.indexOf(options.providerProduct) !== -1) {
            configs.gameCodeName = options.gamecode;
            requestPlayerGameApi(options, configs, languageSupport);
        } else {
            createUglUrl(options, configs, languageSupport);
        }
    };

    /**
     * Create UGL url
     */
    function createUglUrl(options, configs, languageSupport) {
        try {
            var data = {},
                queryString = [];
            var url = configs.uglconfig.ugl_url;
            var parameters = getParametersMap(configs.uglconfig.ugl_parameters);

            var search = ['{username}', '{gameCodeName}', '{language}', '{externalToken}', '{playerId}'];
            var replacements = [
                configs.username,
                configs.gameCodeName,
                languageSupport,
                configs.externalToken,
                configs.playerId
            ];

            Object.keys(parameters).forEach(function (key) {
                if (key === 'tableAlias') {
                    if (configs.tableAlias.trim() !== '') {
                        queryString.push(key + '=' + configs.tableAlias);
                    }
                } else {
                    var param = parameters[key];
                    queryString.push(key + '=' + param);
                }
            });

            url = url + '?' + queryString.join('&');

            search.forEach(function (placeholder, index) {
                var regex = new RegExp(placeholder, 'g');
                if (url.includes(placeholder)) {
                    url = url.replace(regex, replacements[index]);
                }
            });

            data.gameurl = url;
            Console.log("UGL::Url - success");
            options.onSuccess.apply(null, [data]);
        } catch (e) {
            Console.log("UGL::Url - failed");
            options.onFail.apply(null, ['']);
        }
    }

    /**
     * Send request to iCore for PlayerGame API
     */
    function requestPlayerGameApi(options, configs, languageSupport) {
        var data = {
            languageCode: languageSupport,
            playMode: options.playmode,
            extGameId: options.extGameId,
        };
        var codePath = '';
        // Override onFail method for Playtech
        options.onFail = gameLaunchUtil.showErrorMessage;
        if (options.gameCodeName) {
            codePath = '/' + options.gameCodeName;
        }

        var endpoint = 'ajax/game/url/playtech' + codePath;

        xhr({
            url: utility.url(endpoint),
            method: 'get',
            type: 'json',
            data: data
        }).then(function (response) {
            if (response.errors === undefined) {
                Console.log("UGL PlayerGame::launch - Feth game success");
                configs.username = response.gameUrlParams.Username.toUpperCase();
                configs.playerId = response.gameUrlParams.PlayerId;
                createUglUrl(options, configs, languageSupport);
            } else {
                Console.log("UGL PlayerGame::launch - Fetch game failed");
                if (typeof options.onFail === 'function') {
                    options.onFail.apply(null, [options.providerProduct, response]);
                }
            }
        }).fail(function (err, msg) {
            Console.log("UGL PlayerGame::launch - Fetch game failed", err, msg);
            options.onFail.apply(null, [err, msg]);
        });
    }

    /**
     * Gets the parameters mapping
     */
    function getParametersMap(parameters) {
        const keyValueObject = {};
        const parametersArray = parameters.trim().split('\r\n');

        parametersArray.forEach(function (line) {
            const keyValueArray = line.split('|');
            const key = keyValueArray[0];
            const value = keyValueArray[1];
            keyValueObject[key] = value;
        });

        return keyValueObject;
    }

    /**
     * Gets the currency mapping
     */
    function getCurrencyMap(currencies, currency) {
        const currenciesArray = currencies.trim().split('\r\n');

        return !!currenciesArray.includes(currency);
    }

    /**
     * Gets the language mapping
     */
    function getLanguageMap(languages, lang) {
        const keyValueObject = {};
        const lines = languages.trim().split('\r\n');

        lines.forEach(function (line) {
            const keyValueArray = line.split('|');
            const key = keyValueArray[0];
            const value = keyValueArray[1];
            keyValueObject[key] = value;
        });

        return typeof keyValueObject[lang] !== 'undefined' ? keyValueObject[lang] : lang;
    }

    /**
     * Open unsupported Currency Lightbox for UGL
     */
    function unsupportedCurrencyUGL() {
        modalUtil.show(modalId);

        utility.addEventListener(document, 'click', function (e) {

            e = e || window.event;
            var target = e.target || e.srcElement;

            if (utility.hasClass(target, 'currency-ugl-close')) {
                window.history.back();
            }

            if (utility.hasClass(target, 'modal-overlay')) {
                var parent = target.parentNode;

                if (utility.hasClass(parent, 'currency-ugl-modal')) {
                    window.history.back();
                }
            }
        });

        return false;
    }

    /**
     * Check Iframe exist in page
     */
    function listeniFrame() {
        const errorCodeValue = getErrorCodeFromUrl();

        if (window.self !== window.top) {
            const iframe = window.frameElement;

            if (iframe && iframe.id === 'gameframe') {
                const parentDocument = iframe.ownerDocument;

                if (errorCodeValue) {
                    lightbox(parentDocument);
                }
            }
        } else if (app.settings.product === 'live-dealer') {

            if (errorCodeValue) {
                lightbox(document);
            }
        }
    }

    /**
     * Check if URL contains errorCode parameter
     */
    function getErrorCodeFromUrl() {
        var errorCodeValue = '';
        const currentUrl = window.location.href;

        if (currentUrl.includes('errorCode=')) {
            errorCodeValue = currentUrl.split('errorCode=')[1] || '6';
        }

        return errorCodeValue;
    }

    /**
     * Listen iFrame
     */
    function lightbox(parentDocument) {

        var modal = parentDocument.getElementById('ugl-errors');

        if (modal) {
            modal.classList.add('modal-active');
            // Trigger the show.util.modal event
            parentDocument.dispatchEvent(new CustomEvent('show.util.modal'));

            utility.addEventListener(modal, 'click', function (event) {
                event = event || window.event;
                const target = event.target || event.srcElement;
                utility.preventDefault(event);

                if (utility.hasClass(target, 'modal-overlay') || utility.hasClass(target, 'modal-close')) {
                    if (app.settings.product === 'live-dealer') {
                        window.parent.close();
                    } else {
                        window.parent.location.href = window.location.origin + window.location.pathname;
                    }
                }
            });
        }
    }

    /**
     * get UGL error messages from cms for live-dealer
     */
    function getUglErrorsMap() {
        xhr({
            url: utility.url('/api/components/ugl-errors'),
            type: "json",
            method: "post",
            data: {
                product: 'live-dealer',
            },
        }).then(function (response) {
            var bodyScope = document;

            if (window.self !== window.top) {
                var iframe = window.frameElement;
                bodyScope = iframe.ownerDocument;
            }

            var modal = bodyScope.getElementById('ugl-errors');
            var modalContent = modal.querySelector('.modal-content');
            var modalHeader = modalContent.querySelector('.modal-header');
            var modalBody = modalContent.querySelector('.modal-body');

            // Check if modalHeader is empty before appending text
            if (!modalHeader.hasChildNodes()) {
                var headerText = document.createTextNode(response.ugl_header);
                modalHeader.appendChild(headerText);
            }

            // Check if modalBody is empty before appending text
            if (!modalBody.hasChildNodes()) {
                var bodyText = document.createTextNode(response.ugl_message);
                modalBody.appendChild(bodyText);
            }

            if (modalHeader.hasChildNodes() && modalBody.hasChildNodes()) {
                listeniFrame();
            }
        }).fail(function (err, msg) {
            Console.log("UGL:Error Messages - Fetch failed", err, msg);
        });
    }
}

// inheritance
UGLLauncher.prototype = GameLaunch.prototype;

export default UGLLauncher;
