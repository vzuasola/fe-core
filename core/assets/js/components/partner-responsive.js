import * as utility from "Base/utility";

partnerResponsive();

utility.addEventListener(window, 'resize', partnerResponsive);

function partnerResponsive() {
    var partner = document.querySelector('.partners-logo'),
        tabletContainer = document.querySelector('.partner-tablet-container'),
        desktopContainer = document.querySelector('.partner-desktop-container'),
        mobileContainer = document.querySelector('.partner-mobile-container'),
        viewport = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

    if (partner) {
        if (viewport >= 768 && viewport < 1024) {
            tabletContainer.appendChild(partner);
        } else if (viewport >= 1024) {
            desktopContainer.appendChild(partner);
        } else {
            mobileContainer.appendChild(partner);
        }
    }
}
