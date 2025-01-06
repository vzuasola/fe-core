import * as utility from "Base/utility";

function UnsupportedCurrency() {
    var settings = app.settings.unsupportedCurrency;

    /**
     * Initiate Unsupported Currency Lightbox
     *
     * @param array options
     */
    this.ucl = function (options) {
        var gameCurrencies = options.currencies ? options.currencies : [];

        if (utility.inArray(app.settings.userDetails.currency, gameCurrencies)) {
            if (typeof options.supported === 'function') {
                options.supported.apply(null);
            }
        } else {
            prepareUcl(options);
            if (typeof options.unsupported === 'function') {
                options.unsupported.apply(null);
            }
        }
    };

    /**
     * Prepare Unsupported Currency Lightbox
     */
    function prepareUcl(options) {
        var $unsupportedCurrency = document.querySelector('.unsupported-currency-content'),
            $content = settings.message,
            provider = settings.providers[options.provider] ? settings.providers[options.provider] : '';

        if (options.subprovider) {
            // optional parameter to override default game provider label
            provider = options.subprovider || '';
        }

        $unsupportedCurrency.innerHTML = utility.replaceStringTokens({
            '{game_provider}': provider,
            '{game_name}': options.gameName
        }, $content);
    }
}

export default UnsupportedCurrency;
