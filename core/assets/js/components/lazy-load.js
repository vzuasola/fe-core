import * as utility from "Base/utility";
var Blazy = require("BaseVendor/blazy");

var bLazy = new Blazy({
    successClass: "lazy-loaded",
    selector: '.lazy-load'
});

// Add scroll hack on Domload when revalidate/lazyLoad fails to fire
utility.ready(function () {
    bLazy.revalidate();
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

export default bLazy;
