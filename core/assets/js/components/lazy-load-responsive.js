import * as utility from "Base/utility";
var Blazy = require("BaseVendor/blazy");
var bLazy;
// Add scroll hack on Domload when revalidate/lazyLoad fails to fire
utility.ready(function () {
    setTimeout(function () {
        // Fix for ie based browsers as per 'https://developer.mozilla.org/it/docs/Web/API/Window/scrollX#Notes'
        var doc = document.body,
            docElem = document.documentElement,
            scrollX = (window.pageXOffset !== undefined) ? window.scrollX || window.pageXOffset : (docElem || doc.parentNode || doc).scrollLeft,
            scrollY = (window.pageYOffset !== undefined) ? window.scrollY || window.pageYOffset : (docElem || doc.parentNode || doc).scrollTop;

        window.scrollTo(scrollX, scrollY + 1);
        window.scrollTo(scrollX, scrollY - 1);
    }, 500);
});

var mobileMenu = document.querySelector('.btn-lazy-load');

if (mobileMenu) {
    mobileMenu.addEventListener('click', function () {
        setTimeout(function () {
            bLazy.revalidate();
        }, 100);
    }, true);
}

window.addEventListener('resize', function () {
    initBlazy();
}, true);

function initBlazy() {
    bLazy = new Blazy({
        successClass: "lazy-loaded",
        selector: '.lazy-load'
    });
}

setTimeout(function () {
    bLazy.revalidate();
}, 2000);

initBlazy();

export default bLazy;
