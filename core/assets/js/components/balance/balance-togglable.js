import * as utility from "Base/utility";
import Storage from "Base/utils/session-storage";
import CheckboxStyler from "Base/checkbox-styler";
import detectIE from "Base/browser-detect";
import Balance from "Base/balance/balance";
import BalanceTooltip from "BaseTemplate/handlebars/balance-tooltip.handlebars";
import BalanceRefresh from "BaseTemplate/handlebars/balance-refresh.handlebars";

/**
 * Balance view
 */
export default function BalanceTogglable() {
    "use strict";

    var $this = this,
        accountSection = document.querySelector('.account-section'),
        checkbox = document.getElementById('toggable-checkbox'),
        username = utility.getCookie('username'),

        totalBalanceHolder = document.getElementById("player-balance"),
        productBalanceHolder = document.getElementById("product-balance"),

        loaderImage = app.settings.balanceLoader,
        refreshControl = document.getElementById("balance-control"),
        breakDownWrapper = document.getElementById("balance-tooltip"),
        productBalanceWrapper = document.getElementById("product-balance"),

        storage = new Storage(),
        balanceClass = null,
        isBalanceActivated = false;

    /**
     *
     */
    this.init = function () {
        activateToggle();

        if (checkbox.checked) {
            this.activateBalance();
        }
    };

    /**
     *
     */
    this.activateBalance = function () {
        // Instantiate the balance library
        balanceClass = new Balance({
            preFetch: onPrefetch,
            onSuccess: onSuccess,
            onFail: onFail,
            postFetch: onPostfetch
        });

        isBalanceActivated = true;

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
     *
     */
    this.doToggle = function () {
        // since this is an onchange event, state should be reversed
        if (!this.checked) {
            utility.addClass(accountSection, 'hide-balance');
            setState('false');
            this.checked = false;
        } else {
            utility.removeClass(accountSection, 'hide-balance');
            setState('true');
            this.checked = true;

            if (!isBalanceActivated) {
                $this.activateBalance();
            }
        }
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

            var totalLabel = response.label['total_balance_label'];

            // append product balance
            if (response.product && response.product.length > 0) {
                for (var j = 0; j < response.product.length; j++) {
                    if (response.product[j].wallet !== null) {
                        var span = document.createElement("span");

                        if (response.product[j].label) {
                            var productBalance = app.settings.balanceErrorProduct;
                            if (response.product[j].total) {
                                productBalance = utility.replaceStringTokens({
                                    "{total}": response.product[j].total,
                                    "{currency}": response.currency,
                                }, response.format);
                            }

                            span.innerText = response.product[j].label + ': ' + productBalance;
                        }

                        utility.append(productBalanceWrapper, span);
                    }
                }
                utility.addClass(accountSection, "has-product-balance-" + response.product.length);
            } else {
                utility.addClass(accountSection, "no-product-balance");
            }

            // append total balance

            var totalBalance = utility.replaceStringTokens({
                "{total}": response.total,
                "{currency}": response.currency,
            }, response.format);

            // autofill labels

            if (totalLabel) {
                totalBalance = totalLabel + ': ' + '<span class="balance-amount">' + totalBalance + '</span>';
            }

            totalBalanceHolder.innerHTML = totalBalance;

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
        productBalanceHolder.innerHTML = '';
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
        utility.empty(productBalanceWrapper);
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

    /**
     * Toggle helpers
     *
     */

    /**
     *
     */
    function activateToggle() {
        var accountSection = document.querySelector('.account-section'),
            styledCheckbox = new CheckboxStyler(checkbox, "balance-checkbox");

        initState();

        styledCheckbox.checker(checkbox);

        if (checkbox.checked) {
            utility.removeClass(accountSection, 'hide-balance');
        } else {
            utility.addClass(accountSection, 'hide-balance');
        }

        if (detectIE() === 8) {
            try {
                utility.addEventListener(checkbox, 'click', $this.doToggle);
            } catch (e) {
                console.log(e);
            }
        } else {
            // Toogle balance on change
            utility.addEventListener(checkbox, 'change', $this.doToggle);
        }
    }

    /**
     *
     */
    function initState() {
        var state = getState();

        if (state) {
            if (state === 'true') {
                checkbox.checked = true;
            } else if (state === 'false') {
                checkbox.checked = false;
            } else {
                // capture if local storage is invalid
                checkbox.checked = true;
                setState('true');
            }
        }

        // populate local storage value based on checkbox state
        if (!state) {
            if (checkbox.checked) {
                setState('true');
            } else {
                setState('false');
            }
        }
    }

    /**
     *
     */
    function getState() {
        return storage.get('showBalance:' + username);
    }

    /**
     * Setter functions
     *
     */
    function setState(value) {
        return storage.set('showBalance:' + username, value);
    }

    return this;
}
