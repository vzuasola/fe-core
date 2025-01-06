import Console from "Base/debug/console";
import ModalUtil from "Base/utils/modal";
import * as utility from "Base/utility";

/**
 * Get the mapped icore language from site language
 *
 * @param string provider The name of the provider
 */
export function getIcoreLanguageCode(provider) {
    var lang = app.settings.lang,
        map = app.settings.icore_games[provider].languages || {};
    Console.log('GameLaunchUtil:getIcoreLanguageCode:', provider, lang, map);
    return typeof map[lang] !== 'undefined' ? map[lang] : lang;
}

/**
 * Show Error Message Lightbox on failed game launch
 *
 * @param string providerProduct The product name
 * @param array response The error response from game launch
 */
export function showErrorMessage(providerProduct, response) {
    var $gameLoading = document.querySelector('.gamepage-loading') || null,
        $gameLoadingError = document.querySelector('.gamepage-loading-error') || null;

    utility.addClass($gameLoading, 'hidden');
    utility.removeClass($gameLoadingError, 'hidden');

    if (!response.errors) {
        if (providerProduct === 'live-dealer' || providerProduct === 'arcade') {
            setTimeout(function () {
                window.close();
            }, 5000);
        }

        return;
    }

    var element = document.getElementById("game-error-handling");
    element.querySelector(".modal-body").innerHTML = response.errors.errorMessage;
    element.querySelector("a.modal-close").innerHTML = response.errors.errorButton;
    element.querySelectorAll("#game-error-handling a.modal-close, #game-error-handling button.modal-close").forEach(function (element) {
        // Bind on click
        element.onclick = function (event) {
            utility.preventDefault(event);
            if (providerProduct === 'live-dealer' || providerProduct === 'arcade') {
                window.close();
            } else {
                window.location.href = utility.getAttributes(document.querySelector('#closeGame')).href;
            }
        };
    });
    if (providerProduct === 'live-dealer' || providerProduct === 'arcade') {
        element.querySelector('.modal-overlay').addEventListener('click', function (evt) {
            evt.stopPropagation();
        });
    }
    var modalUtil = new ModalUtil();
    modalUtil.show('game-error-handling');
}
