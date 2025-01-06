import * as utility from "Base/utility";
import EqualHeight from "Base/equal-height";

// Global vars
var html = document.documentElement,
    mobileIcon = html.querySelector('.mobile-menu-icon'),
    mobileMenu = html.querySelector('.mobile-menu');

// Detect touch support
var eventType = (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch) ? "touchend" : "click";

// Event
utility.addEventListener(document, eventType, function (e) {
    var target = utility.getTarget(e),
        menuOverlay = html.querySelector('.mobile-menu-overlay'),
        closeButtonPath = html.querySelector('.mobile-menu-close-button');

    if (target === mobileIcon || target.parentNode === mobileIcon) {
        openMenu();
    } else if (target === menuOverlay || target === closeButtonPath) {
        closeMenu();
    }
});

function openMenu() {
    utility.addClass(html, 'menu-open');
    createOverlay();
    equalizeProductHeight();
    equalizeQuicklinksHeight();
}

function closeMenu() {
    utility.removeClass(html, 'menu-open');
}

function createOverlay() {
    if (!html.querySelector('.mobile-menu-overlay')) {
        var overlay = document.createElement("div");
        utility.addClass(overlay, "mobile-menu-overlay");
        mobileMenu.parentNode.insertBefore(overlay, mobileMenu);
    }
}

function equalizeProductHeight() {
    const equalProduct = new EqualHeight(".navicon-thumbnail-product a");
    equalProduct.init();
}

function equalizeQuicklinksHeight() {
    const equalQuicklinks = new EqualHeight(".navicon-thumbnail-quicklinks a");
    equalQuicklinks.init();
}

