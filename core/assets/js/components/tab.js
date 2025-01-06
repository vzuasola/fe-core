import * as utility from "Base/utility";

/**
 * Tab
 *
 * @param array options AN array of options
 *
 * Available options:
 *     selector: A node of objects to be treated as the tabs
 *     defaultTab: A node of object to be treated as the default tab, should be
 *         an element of the `selector` option
 *     onClick: A closure that is invoked when a tab is clicked, the target element
 *         is passed as an argument
 */
export default function Tab(options) {
    var $this = this,
        $active;

    /**
     *
     */
    function setOptions() {
        // Default options
        $this.defaults = {
            selector: false,
            defaultTab: false,
            // closure that triggers when the tab is clicked
            onClick: false,
        };

        // extend options
        $this.options = options || {};

        for (var name in $this.defaults) {
            if ($this.options[name] === undefined) {
                $this.options[name] = $this.defaults[name];
            }
        }
    }

    /**
     *
     */
    this.init = function () {
        setOptions();

        // Attach event to the <body> tag
        utility.addEventListener(document.body, 'click', $this.onClick);

        // Open first tab on document ready
        var defaults = $this.setActiveTab($this.options.defaultTab);

        if ($this.options.defaultTab && defaults) {
            return $this;
        } else {
            var elements = document.querySelectorAll($this.options.selector);
            $this.setActiveTab(elements[0]);
        }

        return $this;
    };

    /**
     *
     */
    this.onClick = function (event) {
        var evt = event || window.event;
        var target = evt.target || evt.srcElement;

        // Set active tab on clicked element
        $this.setActiveTab(target);

        // Prevent default only when tab links are clicked!
        utility.forEachElement($this.options.selector, function (item) {
            if (target === item) {
                if (typeof $this.options.onClick === 'function') {
                    $this.options.onClick(target);
                }

                utility.preventDefault(evt);
            }
        });

        return $active;
    };

    /**
     * Gets the active tab element
     *
     * @return node The node element
     */
    this.getActiveTab = function () {
        return $active;
    };

    /**
     *
     */
    this.setActiveTab = function (activeElem) {
        var result;

        if (typeof activeElem === 'string') {
            activeElem = document.querySelectorAll(activeElem)[0];
        }

        utility.forEachElement($this.options.selector, function (elem) {
            if (activeElem === elem) {
                var targetItemSiblings = utility.siblings(activeElem.parentNode);

                // Remove "active" class for target item siblings
                utility.forEach(targetItemSiblings, function (item) {
                    utility.removeClass(item, 'active');
                });

                // Set "active" class for clicked/targeted tab link and content
                utility.addClass(activeElem.parentNode, 'active');
                $active = activeElem;

                result = true;
            }
        });

        return result;
    };
}
