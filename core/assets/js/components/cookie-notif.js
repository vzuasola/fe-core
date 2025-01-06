import * as utility from "Base/utility";
import Storage from "Base/utils/storage";

/**
 * Cookie Notification
 *
 * Style/SASS: core/assets/sass/components/_cookie-notif.scss
 */

var storage = new Storage(),
    isNotifDisabled = JSON.parse(storage.get("cookie-notif-disabled")),
    notif = document.querySelector('.cookie-notif'),
    closeButton = document.querySelector('.cookie-notif-close'),
    geoip = document.body.getAttribute("data-geoip"),
    cookieNotif = document.querySelector(".cookie-notif"),
    countryCode = cookieNotif ? cookieNotif.getAttribute("data-country-codes") : '',
    countryArray = countryCode.split(",");

// Check for EU geoip
if (geoip && countryArray.indexOf(geoip) > -1) {
    utility.removeClass(notif, "hidden");
    eventListeners();
}

// Check if close button is already clicked
if (!geoip || isNotifDisabled) {
    utility.addClass(notif, "hidden");
}

function eventListeners() {
    utility.addEventListener(window, "storage", function (e) {
        if (e.key === "cookie-notif-disabled") {
            if (e.newValue === "true") {
                utility.addClass(notif, "hidden");
            }
        }
    });

    utility.addEventListener(closeButton, "click", function () {
        storage.set("cookie-notif-disabled", true);
        utility.addClass(notif, "hidden");
    });
}
