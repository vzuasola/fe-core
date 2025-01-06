import PopupWindow from "Base/utils/popup";
import * as utility from "Base/utility";

/**
 * Add option for popup windows by appending attribute `data-popup` to true
 */
(function () {
    "use strict";

    utility.addEventListener(document, 'click', function (event) {
        var evt = event || window.event;
        var target = evt.target || evt.srcElement;

        // Get parent Anchor if target is inside of anchor
        if (target.tagName !== 'A') {
            var parent = utility.findParent(target, 'a');
            if (parent) {
                target = parent;
            }
        }

        if (target.getAttribute('data-popup') === 'true' || target.getAttribute('target') === 'window') {
            utility.preventDefault(event);
            if (target.href) {
                var title = utility.getParameterByName('title', target.href),
                    width = utility.getParameterByName('width', target.href),
                    height = utility.getParameterByName('height', target.href),
                    top = utility.getParameterByName('top', target.href),
                    left = utility.getParameterByName('left', target.href);

                title = title || (target.getAttribute('data-popup-title') || 'windowName');
                width = width || (target.getAttribute('data-popup-width') || 800);
                height = height || (target.getAttribute('data-popup-height') || 600);
                top = top || (target.getAttribute('data-popup-top') || 0);
                left = left || (target.getAttribute('data-popup-left') || 0);

                var position = target.getAttribute('data-popup-position');

                switch (position) {
                    case "center":
                        var pos = getCenter(width, height);
                        top = pos.top;
                        left = pos.left;
                        break;
                }

                PopupWindow(target.href, title, {width: width, height: height, top: top, left: left});
            }
        }
    });

    /**
     * Gets the center
     */
    function getCenter(w, h) {
        var left = (screen.width / 2) - (w / 2);
        var top = (screen.height / 2) - (h / 2);

        return {top: top, left: left};
    }
})();
