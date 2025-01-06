/**
 * Extension for hiding the langauge switcher base from geoIp
 */
export default function geoIpLanguageSwitcher() {
    "use strict";

    var $langSwitcherWrapper = document.querySelector(".language-switcher"),
        geoIpMap = 'id',
        geoIp = document.getElementsByTagName('body')[0].getAttribute('data-geoip');

    if ($langSwitcherWrapper && geoIp && geoIpMap.indexOf(geoIp.toLowerCase()) !== -1) {
        $langSwitcherWrapper.style.display = 'none';
    }
}
