/**
 * Flexible product menu
 */
import * as utility from "Base/utility";
import detectIE from "Base/browser-detect";
var priorityNav = require("BaseVendor/priority-nav");

if (detectIE() !== 8) {
    // Adjust width on load
    adjustMenuWidth();

    // Auto adjjust width on resize
    utility.addEventListener(window, 'resize', adjustMenuWidth);

    // Detect touch support
    var eventIn = (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch) ? "touchend" : "mouseenter",
        eventOut = (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch) ? "touchend" : "mousemove";

    // Initialize priotity nav plugin
    priorityNav.init({
        mainNavWrapper: ".navbar nav",
        navDropdownLabel: app.settings.mainMenuConfig ? app.settings.mainMenuConfig.more : 'More',
        dropdownEventIn: eventIn,
        dropdownEventOut: eventOut,
        breakPoint: 0
    });
}

/**
 * auto adjust product menu container width
 */
function adjustMenuWidth() {
    var menuContainer = document.querySelector('.navbar .container > .pull-left'),
        secondaryMenuWidth = document.querySelector('.main-menu.pull-right').clientWidth,
        container = document.querySelector('.footer .grid') || document.querySelector('.footer .row'),
        containerWidth = container.clientWidth;

    menuContainer.style.width = (containerWidth - secondaryMenuWidth - 56) + 'px';
}
