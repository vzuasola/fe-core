import * as utility from "Base/utility";
import Counter from "Base/utils/counter";
import Modal from "Base/utils/modal";

/**
 * Session timeout lightbox
 *
 * @param int timeout
 * @param array options
 */
export default function SessionLightbox(timeout, options) {
    "use strict";

    var $this = this,
        $id = 'modal-session-timeout',

        // dependencies
        $counter,
        $modal;

    /**
     * Constructor
     */
    function construct() {
        // Default options
        var defaults = {
            onTimeout: false,
            // confirm is when the player confirms to logout his session
            onConfirm: function (self) {
                console.log('Confirm');
                self.hide();
            },
            // cancel is when the lightbox is closed or cancelled
            onCancel: function (self) {
                console.log('Cancel');
                self.hide();
            },
        };

        // extend options
        options = options || {};

        for (var name in defaults) {
            if (options[name] === undefined) {
                options[name] = defaults[name];
            }
        }

        $counter = new Counter(timeout, {
            onCount: onCounterCount,
            onStop: onCounterStop,
        });

        $modal = new Modal();
    }

    construct();
    attachEvents();

    /**
     *
     */
    function attachEvents() {
        // add on close event on the modal
        var element = document.getElementById($id);

        // add an event when the modal closes, check what button was clicked
        utility.addEventListener(element, 'modalclose', onModalClose);

        // add an event when the modal
        if (element) {
            utility.addEventListener(element.querySelector('.modal-body'), 'click', onModalClick);
        }
    }

    /**
     * Reset or kill the session depending on the clicked button
     */
    function onModalClose(event) {
        var evt = event || window.event;
        var target = evt.target || evt.srcElement;

        var element = document.getElementById($id);

        if (target === element) {
            $counter.kill();

            if (utility.hasClass(evt.customData, 'confirm')) {
                if (typeof options.onConfirm === 'function') {
                    options.onConfirm($this);
                }

                return;
            }

            if (typeof options.onCancel === 'function') {
                options.onCancel($this);
            }
        }
    }

    /**
     * Reset session On click of the modal empty area
     */
    function onModalClick(event) {
        var evt = event || window.event;
        var target = evt.target || evt.srcElement;

        if (!utility.hasClass(target, 'modal-close')) {
            if (typeof options.onCancel === 'function') {
                $modal.hide($id);
                $counter.kill();

                utility.preventDefault(event);
                options.onCancel($this);
            }
        }
    }

    /**
     *
     */
    function onCounterCount(counter, seconds) {
        var time = timeout - seconds;
        document.getElementById($id).querySelector('.count').innerHTML = time;
    }

    /**
     *
     */
    function onCounterStop() {
        setTimeout(function () {
            $modal.hide($id);

            if (typeof options.onTimeout === 'function') {
                options.onTimeout($this);
            }
        }, 1000);
    }

    /**
     *
     */
    this.show = function () {
        try {
            document.getElementById($id).querySelector('.count').innerHTML = timeout;
        } catch (e) {
            // do nothing
        }

        $modal.show($id);
        $counter.start();
    };

    /**
     *
     */
    this.hide = function () {
        $modal.hide($id);
        $counter.kill();
    };
}
