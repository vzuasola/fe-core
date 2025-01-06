import * as utility from "Base/utility";
import ModalUtil from "Base/utils/modal";

export default function UnsupportedCountry() {
    "use strict";

    var modalUtil = new ModalUtil();

    this.ucl = function (options) {
        var countries = options.countries || null,
            country = document.getElementsByTagName('body')[0].getAttribute('data-geoip'),
            isSupported = true;

        if (country && countries) {
            utility.forEach(countries, function (c, index) {
                if (c.toLowerCase() === country.toLowerCase()) {
                    isSupported = false;
                    return;
                }
            });
        }

        if (isSupported && typeof options.supported === 'function') {
            options.supported.apply(null);
        } else if (!isSupported) {
            modalUtil.show('game-rcl-lightbox');
        }
    };

    return this;
}
