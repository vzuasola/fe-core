import * as utility from "Base/utility";

export default function registration() {
    utility.ready(function () {

        var cashierLink = document.querySelector('.deposit-now'),
            playLink = document.querySelector('.skip-button');

        if (cashierLink) {
            utility.addEventListener(cashierLink, 'click', function () {

                if (playLink) {
                    playLink.click();
                }
            });
        }
    });
}

registration();
