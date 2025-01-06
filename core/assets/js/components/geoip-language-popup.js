import * as drew from "Base/utility";
import ModalHelper from "Base/utils/modal";
import xhr from "BaseVendor/reqwest";

function geoIpLanguagePopup() {
    var settings = app.settings.geoIpLanguagePopup,
        modalId = 'geoIpLanguagePopup',
        geoip = document.body.getAttribute("data-geoip");

    var $popup = document.getElementById(modalId),
        $modalHelper = new ModalHelper();

    if ($popup && (settings && settings.show)) {
        $popup.querySelector('.modal-body').innerHTML = settings.message.value;
        $modalHelper.show(modalId);
    }

    /**
     * Handles when player closes the popup
     */
    function onClose() {
        xhr({
            url: drew.url('/ajax/userpref/geoip/language'),
            method: 'get',
            data: {
                langcode: null,
                geoip: geoip
            }
        });
    }

    /**
     * Handles when player selects a pref langauge
     */
    function onSelectLanguage(langcode) {
        xhr({
            url: drew.url('/ajax/userpref/geoip/language'),
            method: 'get',
            data: {
                langcode: langcode,
                geoip: geoip
            }
        }).always(function () {
            var redirectUrl = location.href.replace(app.settings.lang, langcode);
            location.href = redirectUrl;
        });
    }

    /**
     * Attach click event on the body
     */
    drew.addEventListener(document.body, 'click', function (event) {
        var $modal = document.getElementById(modalId),
            overlay = $modal.querySelector('.modal-overlay'),
            closeBtn = $modal.querySelector('.modal-close'),
            langBtns = $modal.querySelectorAll('.btn');

        event = event || window.event;
        var target = event.target || event.srcElement;

        if (closeBtn === target || overlay === target) {
            event.preventDefault();
            onClose();
        } else if (drew.inArray(target, langBtns)) {
            event.preventDefault();
            onSelectLanguage(target.getAttribute('data-lang-code'));
        }
    });

    /**
     * Attach keydown event on the body
     */
    drew.addEventListener(document.body, 'keydown', function (event) {
        event = event || window.event;

        if (event.keyCode === 27) {
            onClose();
        }
    });
}

/**
 * Instantiate the geoip popup
 */
drew.ready( function () {
    if (app.settings.login) {
        geoIpLanguagePopup();
    }
});
