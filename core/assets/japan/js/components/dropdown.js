import * as utility from "Base/utility";

/*
 * Custom dropdown script using pure javascript (Supports IE8 and above)
 *
 * *** Sample Markup ***
 * <a href="#" class="dropdown-trigger">Open dropdown</a>
 * <ul>
 *      <li>...</li>
 *      <li>...</li>
 * </ul>
 *
 * Note: The content of the dropdown trigger is the immediate next element sibling (can be <ul>, <div>, etc.)
 */
export default function Dropdown(options) {
    "use strict";

    var $this = this;

    // Default options
    var defaults = {
        selector: ".dropdown-trigger", // selector to trigger dropdowns
        transitionSpeed: 2, // dropdown transition speed in seconds
        hideDropdownOnClick: false, // Close dropdown content on click
        hideDropdownOnMouseOut: false, // Close dropdown content on mouseout
        // hideDropdownOnClickOutside: false, // Close dropdown on click outside trigger/dropdown
        onOpen: null,
        onClose: null
    };

    var html = document.documentElement;

    // extend options
    $this.options = options || {};
    for (var name in defaults) {
        if ($this.options[name] === undefined) {
            $this.options[name] = defaults[name];
        }
    }

    $this.dropdownButtons = document.querySelectorAll($this.options.selector);
    $this.dropdownContents = [];
    $this.dropdownContentHeights = [];

    $this.initializeDropdownContents();
    utility.listen(window, "resize", function () {
        if (window.innerWidth >= 1025) {
            utility.removeClass(html, 'menu-open');
        }
        $this.dropdownContentHeights = [];
        $this.initializeDropdownContents();
    });
    $this.eventTriggered();
}

/**
 * Function to initialize and get initial height of dropdown content
 */
Dropdown.prototype.initializeDropdownContents = function () {
    var $this = this;

    utility.forEach($this.dropdownButtons, function (buttonItem) {

        // Check all selectors to see if it has immediate next element sibling and add to array
        if (buttonItem.nextElementSibling || utility.nextElementSibling(buttonItem)) {
            var dropdownContent = buttonItem.nextElementSibling || utility.nextElementSibling(buttonItem),
                dropdownContentHeight = dropdownContent.children[0].offsetHeight;

            // Cached all initial dropdown contents and heights
            $this.dropdownContents.push(dropdownContent);
            $this.dropdownContentHeights.push(dropdownContentHeight);

            if (dropdownContent.style.height !== "0px" ) {
                dropdownContent.style.height = dropdownContentHeight + "px";
            } else {
                dropdownContent.style.height = 0;
            }
            dropdownContent.style.overflow = "hidden";
            dropdownContent.style.transition = "height ." + $this.options.transitionSpeed + "s";
        }

    });
};

/**
 * Events function to activate active dropdown
 */
Dropdown.prototype.eventTriggered = function () {
    var $this = this;

    var eventType = (app.settings.login_config.platform === 'mobile') ? "touchend" : "click";

    utility.addEventListener(document, eventType, function (e) {

        e = e || window.event;
        var target = e.target || e.srcElement;

        // Set active dropdown on clicked of dropdown button/link
        $this.setActive(target);

        // PreventDefault when target is the dropdown button/link
        utility.forEach($this.dropdownButtons, function (triggerElem) {
            if (target === triggerElem || target.parentNode === triggerElem) {
                utility.preventDefault(e);
            }
        });
    });

    // Add option to Hide dropdown content on mouseout
    if ($this.options.hideDropdownOnMouseOut) {
        utility.addEventListener(document, 'mouseout', function (e) {

            e = e || window.event;
            var target = e.target || e.srcElement;

            utility.forEach($this.dropdownContents, function (contentItem) {
                if (target === contentItem) {
                    $this.closeDropdown(utility.previousElementSibling(contentItem), contentItem);
                }
            });
        });
    }
};

/**
 * Set Active dropdown function
 */
Dropdown.prototype.setActive = function (target) {
    var $this = this;

    if (typeof target === 'string') {
        target = document.querySelectorAll(target);
    }

    utility.forEachElement($this.options.selector, function (buttonElem, index) {
        var dropdownItem = $this.dropdownContents[index],
            dropdownHeight = $this.dropdownContentHeights[index];

        // Check all Clicked dropdown button/link to see if has immediate next element sibling
        if (buttonElem.nextElementSibling || utility.nextElementSibling(buttonElem)) {

            // check if target is the dropdown trigger or any element inside the dropdown trigger
            if (target === buttonElem || target.parentNode === buttonElem) {

                // Toggle dropdown when clicking dropdown button/link
                if (dropdownItem.style.height === "0px") {
                    $this.openDropdown(buttonElem, dropdownItem, dropdownHeight);
                } else {
                    $this.closeDropdown(buttonElem, dropdownItem);
                }
            } else if (dropdownItem.style.height !== "0px" && (target === dropdownItem || target.parentNode === dropdownItem || target.parentNode.parentNode === dropdownItem || target.parentNode.parentNode.parentNode === dropdownItem) && !$this.options.hideDropdownOnClick) {
                $this.openDropdown(buttonElem, dropdownItem, dropdownHeight);
            } else if (dropdownHeight !== "0px") {
            // } else if (dropdownHeight !== "0px" && $this.options.hideDropdownOnClickOutside) {
                $this.closeDropdown(buttonElem, dropdownItem);
            }
        }
    });
};

Dropdown.prototype.openDropdown = function (dropdownButton, dropdownContent, dropdownContentHeight) {
    dropdownContent.style.height = dropdownContentHeight + "px";
    utility.addClass(dropdownButton, "active");
    utility.addClass(dropdownContent, "active");

    // trigger onOpen callback
    if (typeof this.options.onOpen === 'function') {
        this.options.onOpen(dropdownButton, dropdownContent);
    }
};

Dropdown.prototype.closeDropdown = function (dropdownButton, dropdownContent) {
    dropdownContent.style.height = 0;
    utility.removeClass(dropdownButton, "active");
    utility.removeClass(dropdownContent, "active");

    // trigger onClose callback
    if (typeof this.options.onClose === 'function') {
        this.options.onClose(dropdownButton, dropdownContent);
    }
};
