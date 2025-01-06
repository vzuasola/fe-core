import * as utility from "Base/utility";

/**
 * @deprecated To be removed
 */
export default function () {
    "use strict";

    function PopupCenter(url, title, w, h) {
        var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : screen.left,
            dualScreenTop = window.screenTop !== undefined ? window.screenTop : screen.top,

            width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width,
            height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height,

            left = ((width / 2) - (w / 2)) + dualScreenLeft,
            top = ((height / 2) - (h / 2)) + dualScreenTop,

            newWindow = window.open(url, title, 'scrollbars=1,toolbar=0,menubar=0,location=0,resizable=1,status=1, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        if (window.focus) {
            newWindow.focus();
        }
    }

    utility.forEachElement('a[data-popup]', function (el, i) {
        utility.addEventListener(el, 'click', function (e) {
            utility.preventDefault(e);

            var url = this.href,
                url_width = utility.getParameterByName('width', url),
                url_height = utility.getParameterByName('height', url),
                target_width, target_height;

            target_width = url_width || 820;
            target_height = url_height || 700;

            PopupCenter(url, 'cashier', target_width, target_height);
        });
    });
}
