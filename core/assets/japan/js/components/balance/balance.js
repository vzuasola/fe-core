import xhr from "BaseVendor/reqwest";
import * as utility from "Base/utility";
import Console from "Base/debug/console";
import mobileBalance from "Base/balance/balance-mobile";

/**
 * Balance handler class
 */
export default function Balance(options) {
    "use strict";

    var $this = this,
        apiUrl = utility.url('/api/detailed-total-balance'),
        ignore = [];

    function construct() {
        var defaults = {
            preFetch: false,
            postFetch: false,
            onSuccess: false,
            onFail: false,
        };

        options = options || {};

        for (var name in defaults) {
            if (options[name] === undefined) {
                options[name] = defaults[name];
            }
        }
    }

    construct();

    /**
     * Actual request for fetching the balance
     * @return Void
     */
    function fetchBalance() {
        // Fetch the balance from server
        xhr({
            url: apiUrl,
            type: 'json',
            withCredentials: true,
            method: 'get',
            data: {
                nocache: new Date().getTime(),
                ignore: ignore,
                product: getProduct()
            }
        }).then(function (response) {
            // total balance for mobile/tablet
            mobileBalance(response.total);

            Console.log("Fetching success success");
            triggerCallback('onSuccess', [response]);
        }).fail(function (err, msg) {
            Console.log('Fetch balance failed');
            triggerCallback('onFail', [err, msg]);
        }).always(function (response) {
            Console.log('Fetch balance completed');
            triggerCallback('postFetch', [response]);
        });
    }

    /**
     * Call listeners to be used for applying into the template
     *
     * @param  string e Event
     * @param  Array args Arguments
     * @return void
     */
    function triggerCallback(e, args) {
        if (typeof options[e] === 'function') {
            options[e].apply($this, args);
        }
    }

    function getProduct() {
        if (window.location.href.indexOf("/sports") > -1) {
            return "sports";
        }
        return "casino";
    }

    /**
     * This will ignore a specific balanceId
     *
     * @param int/string balanceId balanceId for to be ignored
     */
    this.addIgnore = function (balanceId) {
        balanceId = parseInt(balanceId);

        ignore.push(balanceId);

        Console.log(balanceId + " was added to the ignore list");
    };

    /**
     * Remove the ignored balanceId
     *
     * @param int/string balanceId balance to be re-added
     * @return void
     */
    this.removeIgnore = function (balanceId) {
        balanceId = parseInt(balanceId);

        if (Array.prototype.indexOf) {
            // Check if indexOf for Array is supported
            ignore.splice(ignore.indexOf(balanceId), 1);
        } else {
            // Loop thru all ignore items
            for (var i = 0; i < ignore.length; i++) {
                if (balanceId === ignore[i]) {
                    ignore.splice(i, 1);
                    break;
                }
            }
        }

        Console.log(balanceId + " was removed in the ignore list");
    };

    /**
     * Trigger the balance fetch
     *
     * @return void
     */
    this.getBalance = function () {
        triggerCallback('preFetch', []);
        fetchBalance();
    };

    return this;
}
