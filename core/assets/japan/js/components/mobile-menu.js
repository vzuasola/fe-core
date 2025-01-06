import * as utility from "Base/utility";
import Dropdown from "Japan/dropdown";

// Global vars
var html = document.documentElement,
    mobileButton = html.querySelector('.menu-mobile-button'),
    mobileMenu = html.querySelector('.menu-accordion');

var eventType = (app.settings.login_config.platform === 'mobile') ? "touchend" : "click";

// Event
function bindEvents() {
    utility.addEventListener(document, eventType, function (e) {
        var target = utility.getTarget(e),
            menuOverlay = html.querySelector('.menu-mobile-overlay'),
            menuHeader = html.querySelector('.menu-header img'),
            menuArrow = html.querySelector('.menu-header .fa-chevron-right'),
            closeButton = html.querySelector('.menu-mobile-header span'),
            parent = utility.findParent(target, ".menu-mobile-button");
        if (target === mobileButton || utility.hasClass(parent, "menu-mobile-button")) {
            openMenu();
        } else if (target === menuOverlay || target === closeButton || target === menuHeader || target === menuArrow) {
            closeMenu();
        }
    });
}


utility.ready(function () {
    menuList(mobileMenu.querySelector('.main-menu'), true);
    menuList(document.querySelector('.menu-tab .main-menu'), false);
    avayaBlocking();
    bindEvents();
});

function avayaBlocking() {
    var cashierLinks = document.querySelectorAll('.cashier-link');
    utility.forEach(cashierLinks, function (link, key) {
        if ( app.settings.isBlocked === 'true' ) {
            link.href = utility.addQueryParam(link.href, 'uav', 'blocked');
        }
    });
}

function openMenu() {
    utility.addClass(html, 'menu-open');
    createOverlay();
}

function closeMenu() {
    utility.removeClass(html, 'menu-open');
}

function createOverlay() {
    if (!html.querySelector('.menu-mobile-overlay')) {
        var overlay = document.createElement("div");
        utility.addClass(overlay, "menu-mobile-overlay");
        mobileMenu.parentNode.insertBefore(overlay, mobileMenu);
    }
}

function toggleIcon(trigger, dropdown) {
    var icon = trigger.querySelector(".fa");

    if (!trigger.classList.contains("active")) {
        // icon.classList.replace("fa-chevron-down", "fa-chevron-right");
        icon.classList.add('fa-chevron-right');
        icon.classList.remove('fa-chevron-down');
    } else {
        // icon.classList.replace("fa-chevron-right", "fa-chevron-down");
        icon.classList.add('fa-chevron-down');
        icon.classList.remove('fa-chevron-right');
    }
}

function menuList(elem, mobile) {
    if (checkPlatform()) {
        var x, i, oppositePlatform = 'desktop';
        x = elem.childNodes;
        for (i = 0; i < x.length; i++) {
            if (utility.hasClass(x[i], 'mobile')) {
                utility.removeClass(x[i], 'hidden');
            }

            if (utility.hasClass(x[i], 'has-submenu')) {
                menuList(x[i].querySelector('.sub-menu-wrapper .sub-menu'), false);
            }
        }

        for (i = 0; i < x.length; i++) {
            if (utility.hasClass(x[i], oppositePlatform)) {
                x[i].remove();
            }
        }
    }

    if (mobile) {
        new Dropdown({
            selector: ".menu-accordion .has-submenu > .main-menu-link",
            // hideDropdownOnClickOutside: false,
            onOpen: toggleIcon,
            onClose: toggleIcon
        });
    }
}

/**
 * fix for iOS 13 ipad safari specific issue
 */
function detectUA() {

    var isTouchDevice = 'ontouchstart' in document.documentElement;
    var userAgent = window.navigator.userAgent.toLowerCase();

    return isTouchDevice && userAgent.includes('macintosh');
}

function checkPlatform() {
    if (detectUA()) {
        return "mobile";
    }
}
