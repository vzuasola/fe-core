import * as utility from "Base/utility";

/**
 * Helper class for modals
 */
export default function Modal(options) {
    // Default options
    var defaults = {
        beforeOpen: null,
        beforeClose: null,
        afterOpen: null,
        afterClose: null
    };

    // extend options
    options = options || {};
    for (var name in defaults) {
        if (options[name] === undefined) {
            options[name] = defaults[name];
        }
    }

    /**
     * Shows a modal on demand
     */
    this.show = function (id) {
        // before open callback
        if (typeof options.beforeOpen === 'function') {
            options.beforeOpen();
        }

        var element = document.getElementById(id);
        utility.addClass(element, "modal-active");
        utility.invoke(document, 'show.util.modal');

        // after open callback
        if (typeof options.afterOpen === 'function') {
            options.afterOpen();
        }
    };

    /**
     *
     */
    this.hide = function (id) {
        // before close callback
        if (typeof options.beforeClose === 'function') {
            options.beforeClose();
        }

        var element = document.getElementById(id);
        utility.removeClass(element, "modal-active");
        document.body.style.overflow = "inherit";
        utility.invoke(document, 'hide.util.modal');

        // after close callback
        if (typeof options.afterClose === 'function') {
            options.afterClose();
        }
    };
}
