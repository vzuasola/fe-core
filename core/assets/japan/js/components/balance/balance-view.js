import * as utility from "Base/utility";

import Balance from "Japan/balance/balance";
import BalanceTooltip from "BaseTemplate/handlebars/balance-tooltip.handlebars";
import BalanceRefresh from "BaseTemplate/handlebars/balance-refresh.handlebars";

/**
 * Balance view
 */
export default function BalanceView() {
    "use strict";

    var accountDetailsHolder = document.getElementById("account-details"),
        totalBalanceHolder = document.getElementById("player-balance"),
        productBalanceHolder = document.getElementById("product-balance"),
        loader = document.getElementById("balance-loader"),
        refreshControl = document.getElementById("balance-control"),
        breakDownWrapper = document.getElementById("balance-tooltip"),
        productBalanceWrapper = document.getElementById("product-balance"),
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
        utility.addEventListener(document, "touchstart", openTooltip);
        utility.addEventListener(window, "resize", resizeTooltip);

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
        utility.removeClass(loader, "hidden");
    }

    /**
     *
     */
    function onSuccess(response) {
        clearBalances();
        utility.addClass(loader, "hidden");

        if (typeof response.breakdown !== "undefined" &&
            typeof response.total !== "undefined" &&
            response.total !== null
        ) {

            var totalLabel = response.label['total_balance_label'];

            // append product balance
            if (response.product && response.product.length > 0) {
                for (var j = 0; j < response.product.length; j++) {
                    if (response.product[j].wallet !== null) {
                        var productBalanceItem = null;

                        if (response.product[j].label) {
                            var productBalance = response.label['balance_error_text_product'];
                            if (response.product[j].total) {
                                productBalance = utility.replaceStringTokens({
                                    "{total}": response.product[j].total,
                                    "{currency}": response.currency,
                                }, response.format);
                            }

                            // span.innerText = response.product[j].label + ': ' + productBalance;
                            productBalanceItem = '<span class="label">' + response.product[j].label + ': </span>';
                            productBalanceItem += '<span class="balance-amount">' + productBalance + '</span>';
                        }
                        if (productBalanceItem) {
                            productBalanceWrapper.innerHTML += productBalanceItem;
                        }
                    }
                }
                // utility.addClass(accountSection, "has-product-balance-" + response.product.length);
            } else {
                // utility.addClass(accountSection, "no-product-balance");
            }

            // append total balance

            var totalBalance = utility.replaceStringTokens({
                    "{total}": response.total,
                    "{currency}": response.currency,
                }, response.format),
                tempTotalBalance = null;

            // autofill labels

            if (totalLabel) {
                tempTotalBalance = '<span class="label">' + totalLabel + ': </span>';
                tempTotalBalance += '<span class="balance-amount">' + totalBalance + '</span>';
            }

            totalBalanceHolder.innerHTML = tempTotalBalance;

            if (!response.enabled) {
                return;
            }

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
                    response.breakdown[i].total = response.label['balance_error_text_product'];
                }
            }

            breakDownWrapper.innerHTML = BalanceTooltip(response);
            resizeTooltip();
            return;
        }

        // No results
        utility.append(totalBalanceHolder, document.createTextNode(app.settings.balanceError));
        productBalanceHolder.innerHTML = '';
    }

    /**
     *
     */
    function onFail() {
        clearBalances();
        utility.addClass(loader, "hidden");
        utility.append(totalBalanceHolder, document.createTextNode(app.settings.balanceError));
    }

    /**
     *
     */
    function onPostfetch() {
        utility.removeClass(refreshControl, 'hidden');
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
        // utility.empty(refreshControl);
        utility.addClass(refreshControl, 'hidden');
        utility.empty(productBalanceHolder);
        utility.empty(totalBalanceHolder);
        utility.empty(breakDownWrapper);
    }

    function resizeTooltip() {
        var width = document.getElementById("header").offsetWidth,
            accountDetailsHolderWidth = accountDetailsHolder.offsetWidth,
            marginLeft = width - accountDetailsHolderWidth;

        if (checkPlatform() === "mobile") {
            breakDownWrapper.style.marginLeft = -marginLeft + "px";
        } else {
            breakDownWrapper.style.marginLeft = "";
            breakDownWrapper.style.display = "";
        }
    }

    function openTooltip(e) {
        var target = utility.getTarget(e),
            parent = utility.findParent(target, ".account");
        if (target === accountDetailsHolder || parent === accountDetailsHolder) {
            breakDownWrapper.style.display = 'block';
        } else {
            breakDownWrapper.style.display = 'none';
        }
    }

    /**
     * fix for iOS 13 ipad safari specific issue
     */
    function detectUA() {

        var isTouchDevice = 'ontouchstart' in document.documentElement;
        var userAgent = window.navigator.userAgent.toLowerCase();

        return isTouchDevice && userAgent.includes('macintosh');
    }

    function checkPlatform() {
        if (detectUA() || window.navigator.userAgent.indexOf('Mobile') !== -1) {
            return "mobile";
        }

        return "desktop";
    }

    return this;
}
