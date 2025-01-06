import * as utility from "Base/utility";

import Balance from "Base/balance/balance";
import BalanceTooltip from "BaseTemplate/handlebars/balance-tooltip.handlebars";
import BalanceRefresh from "BaseTemplate/handlebars/balance-refresh.handlebars";

/**
 * Balance view
 */
export default function BalanceView() {
    "use strict";

    var totalBalanceHolder = document.getElementById("player-balance"),
        loaderImage = app.settings.balanceLoader,
        refreshControl = document.getElementById("balance-control"),
        breakDownWrapper = document.getElementById("balance-tooltip"),
        balanceClass = null;

    /**
     *
     */
    this.init = function () {
        // Instantiate the balance library
        balanceClass = new Balance({
            preFetch: onPrefetch,
            onSuccess: onSuccess,
            onFail: onFail,
            postFetch: onPostfetch
        });

        utility.addEventListener(document.querySelector(".account-balance"), "click", refreshBalance);

        // add balance exclusion list
        if (typeof app.settings.balanceExclusion !== 'undefined' && app.settings.balanceExclusion) {
            for (var i = 0; i < app.settings.balanceExclusion.length; i++) {
                balanceClass.addIgnore(app.settings.balanceExclusion[i]);
            }
        }

        balanceClass.getBalance();
    };

    /**
     * Callbacks
     *
     */

    /**
     *
     */
    function onPrefetch() {
        clearBalances();
        utility.append(refreshControl, createLoader());
    }

    /**
     *
     */
    function onSuccess(response) {
        clearBalances();

        if (typeof response.breakdown !== "undefined" &&
            typeof response.total !== "undefined" &&
            response.total !== null
        ) {
            var replacements = {
                "{total}": response.total,
                "{currency}": response.currency,
            };

            var totalBalance = utility.replaceStringTokens(replacements, response.format);
            utility.append(totalBalanceHolder, document.createTextNode(totalBalance));

            for (var i in response.breakdown) {
                if (response.breakdown[i].total === null) {

                    // For blocked (territory) and unsupported currency
                    if (response.breakdown[i].visibility === "block" || response.breakdown[i].visibility === "uc") {
                        delete response.breakdown[i];
                        continue;
                    }

                    // Ignored balance (This is for the delayed balance control)
                    if (response.breakdown[i].visibility === "ignore") {
                        response.breakdown[i].total = BalanceRefresh({id: response.breakdown[i].wallet});
                        continue;
                    }

                    // Failed fetch
                    response.breakdown[i].total = app.settings.balanceErrorProduct;
                }
            }

            breakDownWrapper.innerHTML = BalanceTooltip(response);
            return;
        }

        // No results
        utility.append(totalBalanceHolder, document.createTextNode(app.settings.balanceError));
    }

    /**
     *
     */
    function onFail() {
        clearBalances();
        utility.append(totalBalanceHolder, document.createTextNode(app.settings.balanceError));
    }

    /**
     *
     */
    function onPostfetch() {
        utility.append(refreshControl, createRefresh());
    }

    /**
     * Helper methods
     *
     */

    /**
     *
     */
    function refreshBalance(event) {
        var evt = event || window.event,
            $target = evt.target || evt.srcElement;

        if (utility.hasClass($target, 'refresh-balance')) {
            var product = $target.getAttribute('product-id');

            evt.preventDefault();
            balanceClass.removeIgnore(product);
            balanceClass.getBalance();

            return false;
        }
    }

    /**
     *
     */
    function clearBalances() {
        utility.empty(refreshControl);
        utility.empty(totalBalanceHolder);
        utility.empty(breakDownWrapper);
    }

    /**
     *
     */
    function createLoader() {
        var loader = document.createElement("img");
        loader.src = loaderImage;
        utility.addClass(loader, "refresh-loading");

        return loader;
    }

    /**
     *
     */
    function createRefresh() {
        var refresh = document.createElement("SPAN");
        utility.addClass(refresh, "refresh-balance");
        utility.addClass(refresh, "refresh-icon");
        utility.addClass(refresh, "inline-block");
        refresh.setAttribute("style", "display: inline-block");

        return refresh;
    }

    return this;
}
