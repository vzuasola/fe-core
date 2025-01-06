import * as utility from "Base/utility";
import BalanceView from "Base/balance/balance-view";
import BalanceToggle from "Base/balance/balance-togglable";

/**
 * This will implement the balance.js that is part of the header
 * Presentation logic
 *
 * @return void
 */
utility.ready(function () {
    if (typeof app.settings.login === "undefined") {
        return;
    }

    var balanceView,
        accountSection = document.querySelector('.account-section');

    if (utility.hasClass(accountSection, 'toggable')) {
        balanceView = new BalanceToggle();
    } else {
        balanceView = new BalanceView();
    }

    balanceView.init();
});
