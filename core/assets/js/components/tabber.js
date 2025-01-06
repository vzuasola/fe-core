import * as utility from "Base/utility";

function Tabber(options) {
    var $this = this;

    // Default options
    var defaults = {
        selector: ".idtab a",
        contentSelector: ".tab-content",
        event: 'click',
        onUpdate: null,
    };

    // extend options
    $this.options = options || {};
    for (var name in defaults) {
        if ($this.options[name] === undefined) {
            $this.options[name] = defaults[name];
        }
    }

    $this.tabLinks = document.querySelectorAll($this.options.selector);
    $this.tabContents = document.querySelectorAll($this.options.contentSelector);
    $this.tabWithAdditionalContent = [];

    // Attach event to document
    utility.addEventListener(document, $this.options.event, function (event) {

        var target = utility.getTarget(event);
        var anchor = (target.tagName === "A") ? target : utility.findParent(target, "a");

        // Set active tab on clicked element
        $this.setActiveTab(anchor);

        // Prevent default only when tab links are clicked!
        utility.forEach($this.tabLinks, function (item) {
            if (anchor === item) {
                utility.preventDefault(event);
            }
        });
    });

    utility.ready(function () {
        $this.additionalTabContent();

        var tabWithActiveClass = [];

        utility.forEach($this.tabLinks, function (item) {

            // Set active Tab on selector with 'active' class
            if (utility.hasClass(item.parentNode, 'active')) {
                $this.setActiveTab(item);
            }

            // Add to 'tabWithActiveClass' for item with 'active' class
            if (utility.hasClass(item.parentNode, 'active')) {
                tabWithActiveClass.push(item);
            }
        });

        // Set active class to first tab element when no 'active' class is added
        if (tabWithActiveClass.length < 1) {
            $this.setActiveTab($this.tabLinks[0]);
        }
    });
}

// Set active tab
Tabber.prototype.setActiveTab = function (activeElem) {
    var $this = this;

    utility.forEachElement($this.options.selector, function (elem) {
        if (activeElem === elem) {
            var targetId = $this.getHash(elem.href),
                targetTabContent = document.getElementById(targetId),
                targetItemSiblings = utility.siblings(activeElem.parentNode);

            // Remove "active" class for target element siblings
            utility.forEach(targetItemSiblings, function (item) {
                utility.removeClass(item, 'active');
            });

            // Add "active" class for target/clicked element
            utility.addClass(activeElem.parentNode, 'active');

            // Hide tab contents
            utility.forEach($this.tabContents, function (item) {
                utility.addClass(item, 'hidden');
            });

            // show tab contents on active tab
            utility.removeClass(targetTabContent, "hidden");

            // If target has data-add-content
            $this.activeAdditionalContent(elem);

            // trigger callback
            if (typeof $this.options.onUpdate === 'function') {
                $this.options.onUpdate.apply(this, [targetTabContent]);
            }
        }
    });
};

Tabber.prototype.activeAdditionalContent = function (activeTab) {
    var $this = this;

    // Hide first all additional tab content
    utility.forEach($this.tabWithAdditionalContent, function (elem) {
        var additionalTabContent = $this.getAdditionalContentElem(elem);
        utility.addClass(additionalTabContent, "hidden");
    });

    var additionalContentElem = $this.getAdditionalContentElem(activeTab);

    // Show only on active tab
    if (additionalContentElem) {
        utility.removeClass(additionalContentElem, 'hidden');
    }
};

// Additional Tab Content base on html data attribute
Tabber.prototype.additionalTabContent = function () {
    var $this = this;

    // Get all additional tab content
    utility.forEachElement($this.options.selector, function (elem) {
        if (elem.getAttribute("data-add-content")) {
            $this.tabWithAdditionalContent.push(elem);
        }
    });

    utility.forEach($this.tabWithAdditionalContent, function (elem) {
        var additionalTabContent = $this.getAdditionalContentElem(elem);

        // Hide Tab content ID first
        utility.addClass(additionalTabContent, "hidden");

    });
};

// Default Open Tab
Tabber.prototype.defaultActiveTab = function (elem) {
    elem = document.querySelector(this.options.defaultTab);
    return elem;
};

// Get hash function
Tabber.prototype.getHash = function (url) {
    var hashPos = url.lastIndexOf('#');
    return url.substring(hashPos + 1);
};

// Get ID of Additional tab content
Tabber.prototype.getAdditionalContentId = function (elem) {
    return elem.getAttribute('data-add-content');
};

// Get Element of Additional tab content
Tabber.prototype.getAdditionalContentElem = function (elem) {
    return document.getElementById(this.getAdditionalContentId(elem));
};

export default Tabber;
