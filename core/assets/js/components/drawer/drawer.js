import * as utility from "Base/utility";
import Loader from "Base/loader";
import xhr from "BaseVendor/reqwest";
import DrawerTemplate from "BaseTemplate/handlebars/drawer.handlebars";

"use strict";

export default function Drawer(trigger, options) {
    // Default options
    var defaults = {
        beforeOpen: null,
        afterOpen: null,
        beforeClose: null,
        afterClose: null,
        template: DrawerTemplate,
        alter: null,
        escapeClose: true, // close drawer on pressing escape key
    };

    // Extend options
    this.options = options || {};

    for (var name in defaults) {
        if (this.options[name] === undefined) {
            this.options[name] = defaults[name];
        }
    }

    this.trigger = trigger;
    this.drawer = document.querySelector(".drawer");
    this.id = this.trigger.getAttribute("data-drawer-id");
    this.loader = new Loader(this.drawer);

    // Initialize drawer
    this.init();
}

Drawer.prototype.fetched = {};

Drawer.prototype.init = function () {
    // Add overlay
    addOverlay(this.drawer);

    // bind events
    this.bindEvent();
};

Drawer.prototype.bindEvent = function () {
    var $this = this;

    utility.addEventListener(document, "click", function (e) {
        var target = utility.getTarget(e),
            overlay = utility.previousElementSibling($this.drawer);

        // Open drawer on click of trigger
        if (target === $this.trigger) {
            utility.preventDefault(e);
            $this.openDrawer();
        }

        // close drawer on click of close button / overlay
        if (target === $this.drawer.querySelector(".drawer-close-button") || target === overlay) {
            $this.closeDrawer();
        }
    });

    utility.addEventListener(document.body, 'keydown', function (e) {
        // Cross browser event
        e = e || window.event;

        // Close drawer on clicking Escape key
        if (e.keyCode === 27 && $this.options.escapeClose) {
            $this.closeDrawer();
        }
    });
};

Drawer.prototype.closeDrawer = function () {
    // Before close callback
    if (typeof this.options.beforeClose === "function") {
        this.options.beforeClose(this.trigger, this.drawer);
    }

    this.drawer.style.display = "block";
    this.drawer.style.marginRight = -this.drawer.offsetWidth + "px";
    utility.removeClass(document.body, "drawer-active");

    // After close callback
    if (typeof this.options.afterClose === "function") {
        this.options.afterClose(this.trigger, this.drawer);
    }
};

Drawer.prototype.openDrawer = function () {
    this.fetchData();
    this.drawer.style.marginRight = "0";
    utility.addClass(document.body, "drawer-active");
};

Drawer.prototype.fetchData = function () {
    var $this = this;

    $this.drawer.innerHTML = "";

    $this.loader.show();

    if (!this.fetched[this.id]) {
        xhr({
            url: utility.url('/api/drawer/' + this.id),
            type: 'json',
            data: {
                nocache: new Date().getTime()
            },
            method: 'get'
        })
            .then(function (resp) {
                $this.fetched[$this.id] = resp;

                populate();
            })
            .fail(function (err, msg) {
                $this.closeDrawer();
            })
            .always(function (resp) {
                $this.loader.hide();
            });
    } else {
        populate();
    }

    function populate() {
        // Before open callback
        if (typeof $this.options.beforeOpen === "function") {
            $this.options.beforeOpen($this.trigger, $this.drawer);
        }

        if (typeof $this.options.alter === "function") {
            $this.fetched[$this.id] = $this.options.alter($this.fetched[$this.id]);
        }

        $this.drawer.innerHTML = $this.options.template($this.fetched[$this.id]);
        addCloseButton($this.drawer);

        // After open callback
        if (typeof $this.options.afterOpen === "function") {
            $this.options.afterOpen($this.trigger, $this.drawer);
        }
    }
};

// Add close button
function addCloseButton(drawer) {
    var button = createElem("span", "drawer-close-button");

    // append/insert thumbnails element
    drawer.insertBefore(button, drawer.firstChild);
}

// Create overlay
function addOverlay(drawer) {
    if (!document.querySelector(".drawer-overlay")) {
        var overlay = createElem("div", "drawer-overlay");

        drawer.parentNode.insertBefore(overlay, drawer);
    }
}

// Create element with classname
function createElem(tagName, className) {
    var element = document.createElement(tagName);
    utility.addClass(element, className || "");

    return element;
}
