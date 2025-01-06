import * as utility from "Base/utility";

/**
 * Insert balance for mobile/tablet
 */
export default function mobileBalance(balance) {
    var mobileHolder = document.querySelector(".mobile-balance");

    if (mobileHolder) {
        var link = mobileHolder.querySelector("a"),
            loader = mobileHolder.querySelector("img");

        mobileHolder.querySelector(".mobile-balance-amount").innerHTML = balance;

        utility.removeClass(link, "hidden");
        utility.addClass(loader, "hidden");
        setTimeout(function () {
            document.querySelector('.total-balance-container').innerHTML = document.querySelector('#player-balance').innerHTML;
        }, 50);
    }
}
