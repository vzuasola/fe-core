import * as utility from "Base/utility";
import xhr from "BaseVendor/reqwest";
import detectIE from "Base/browser-detect";

/**
 * Mega Menu
 *
 * @param Node target menu anchor tag
 * @param Object options
 */
export default function MegaMenu(target, uri, options) {
    "use strict";

    var wrapper = target.nextElementSibling || utility.nextElementSibling(target),
        container = wrapper.firstChild,
        errorMessage = wrapper.getAttribute('dropdown-menu-error-message'),
        fetched = false;

    /**
     * Constructor
     */
    function construct() {
        // Default options
        var defaults = {
            beforeShow: null,
            afterShow: null
        };

        // extend options
        options = options || {};

        for (var name in defaults) {
            if (options[name] === undefined) {
                options[name] = defaults[name];
            }
        }
    }

    construct();

    /**
     *
     */
    this.init = function () {
        attachEvents();
    };

    /**
     *
     */
    function attachEvents() {
        utility.addEventListener(target, 'mouseenter', function (e) {
            showMenu(target);
        });

        utility.addEventListener(target.parentNode, 'mouseleave', function (e) {
            hideMenu(target);
        });
    }

    /**
     * Show menu
     */
    function showMenu() {
        // Disable mega-menu inside priority-nav/dropdown
        if (!utility.findParent(target, ".nav__dropdown")) {
            // highlight current menu
            utility.addClass(target.parentNode, 'active');

            // Show mega menu
            wrapper.style.display = 'block';

            fetchData(uri + '?nocache=' + (new Date()).getTime(), target);
        }
    }

    /**
     * Hide menu
     */
    function hideMenu() {
        // Remove highlight current menu
        utility.removeClass(target.parentNode, 'active');

        // Hide mega menu
        wrapper.style.display = 'none';
    }

    /**
     * Fetch data
     */
    function fetchData() {
        // Check if request not loaded already
        if (!fetched) {
            container.innerHTML = "";

            // Loading icon
            showLoader(container);

            var request = xhr({
                url: uri,
                method: 'get',
            }).then(function (resp) {
                var header = request.request.getResponseHeader('X-Webcomposer-Dropdown');

                // make sure that it is a valid response
                if (header === 'true') {
                    fetched = true;

                    // beforeShow callback
                    if (typeof options.beforeShow === "function") {
                        options.beforeShow(container);
                    }

                    container.innerHTML = resp;

                    ie8HoverFix();

                    var attachments = container.querySelectorAll('.menu-dropdown-widget script');

                    if (attachments) {
                        utility.forEach(attachments, function (attachment) {
                            var script = attachment.innerHTML;
                            var result = eval(script);

                            if (result.default) {
                                result.default(attachment.parentElement);
                            }
                        });
                    }

                    // afterShow callback
                    if (typeof options.afterShow === "function") {
                        options.afterShow(container);
                    }
                } else {
                    showContainerError(container);
                }
            }).fail(function (err, msg) {
                showContainerError(container);
            });
        }
    }

    /**
     *
     */
    function showContainerError(elem) {
        elem.innerHTML = '<p class="text-center mt-20 mb-25"><span class="icon-error-fetching mr-5"></span>' + errorMessage + '</p>';
    }

    /**
     * Create loader
     */
    function createLoader(elem) {
        if (!elem.querySelector('.loading-animator')) {
            var loader = document.createElement('div'),
                ray = '';

            for (var i = 0; i < 10; i++) {
                ray += '<div class="loader" id="loader-' + i + '"></div>';
            }

            utility.addClass(loader, 'loading-animator');
            utility.addClass(loader, 'mt-60');
            utility.addClass(loader, 'mb-60');
            loader.id = 'loader';
            loader.innerHTML = ray;

            elem.appendChild(loader);

            return loader;
        }
    }

    /**
     * Show loader
     */
    function showLoader(elem) {
        var loader = elem.querySelector('.loading-animator') || createLoader(elem);

        utility.removeClass(loader, 'hidden');
    }

    /**
     * IE8 fixes
     */
    function ie8HoverFix() {
        if (detectIE() === 8) {
            var hoverContents = wrapper.querySelectorAll(".hover-content");

            utility.forEach(hoverContents, function (elem) {
                // Vertically center hover-content
                elem.style.marginTop = "-" + (elem.clientHeight / 2) + "px";
            });

            // Add class to wrapper
            utility.addClass(wrapper, "ie-8");
        }
    }
}
