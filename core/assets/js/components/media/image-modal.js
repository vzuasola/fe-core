// Image Modal
// Author: Yunyce de Jesus
import * as utility from "Base/utility";
import scrollbot from "BaseVendor/scrollbot";

export default function imageModal(options) {
    "use strict";

    var _this = this,
        modal = '',
        modalId = 'imageLightbox',
        bodySelector = document.querySelector("body"),
        modalDefault = '#' + modalId + ' .modal-body.default',
        modalScrollable = '#' + modalId + ' .modal-body.scroll',
        modalScrollbot = "<div class='image-lightbox--container scrollbot'></div>";

    /**
     * Attach window onload handler
     */
    utility.addEventListener(window, 'load', function () {
        _this.init();
    });

    /**
     * Initialize Image Modal
     */
    this.init = function () {
        _this.imageOverlay();
        modal = document.querySelector('.image--lightbox');
        _this.imageListener();
    };

    /**
     * Create image overlay element
     */
    this.imageOverlay = function () {
        var modalWrapper = document.createElement("div"),
            modalOverlay = "<div class='modal-overlay'></div>",
            modalCloseBtn = "<span class='modal-close modal-close-button'></span>";

        modalWrapper.className = "modal image--lightbox";
        modalWrapper.id = modalId;
        modalWrapper.innerHTML = modalOverlay +
            "<div class='modal-content'><div class='modal-body scroll hidden'>" +
            modalScrollbot +
            "</div><div class='modal-body default'></div>" +
            modalCloseBtn +
            "</div>";
        bodySelector.insertAfter(modalWrapper, bodySelector.querySelector("#announcementLightbox"));
    };

    /**
     * Image Event Listener
     */
    this.imageListener = function () {
        var modalOverlay = modal.querySelector('.modal-overlay'),
            modalClose = modal.querySelector('.modal-close');

        utility.addEventListener(document, 'click', function (evt) {
            var target = utility.getTarget(evt);

            if (utility.hasClass(target, 'modal-image-trigger')
                || utility.hasClass(target.parentNode, 'modal-image-trigger')) {
                utility.preventDefault(evt);
                _this.openModal(target);
            }

            if (target === modalOverlay || target === modalClose) {
                utility.preventDefault(evt);
                _this.closeModal();
            }
        });

        // Close modal on clicking Escape key
        utility.addEventListener(document, 'keydown', function (evt) {
            // Cross browser event
            evt = evt || window.event;

            if (evt.keyCode === 27) {
                _this.closeModal();
            }
        });
    };

    /**
     * Open Image Modal function
     */
    this.openModal = function (target) {
        var modalBodyDefault = document.querySelector(modalDefault),
            modalBodyScroll = document.querySelector(modalScrollable + " .scrollbot"),
            src = "";

        if (target.getAttribute('src') && modal) {
            src = target.getAttribute('src');

            // Added image element inside modal-body
            modalBodyDefault.innerHTML = "<img src=" + src + ">";
            modalBodyScroll.innerHTML = "<img src=" + src + ">";

            utility.addClass(modal, 'modal-active');
            bodySelector.style.overflow = "hidden";
            _this.modalHeightRefresh();
        }
    };

    /**
     * Close Image Modal function
     */
    this.closeModal = function (target) {
        if (modal) {
            utility.removeClass(modal, 'modal-active');

            var modalEl = document.getElementById(modalId),
                modalBodyScroll = document.querySelector(modalScrollable),
                modalContent = document.querySelector('#' + modalId + ' .modal-content');

            setTimeout(function () {
                utility.addClass(modalEl.querySelector(modalScrollable), 'hidden');
                utility.removeClass(modalEl.querySelector(modalDefault), 'hidden');
                modal.querySelectorAll('.modal-body img').remove();
                bodySelector.style.overflow = "inherit";
                document.querySelector(modalDefault).style.height = 'auto';
                utility.removeClass(modalContent, 'pl-10');
                modalBodyScroll.removeAttribute("style");
                modalBodyScroll.innerHTML = modalScrollbot;
            }, 500);
        }
    };

    /**
     * Modal Height Function
     * Know when to execute scrollbot
     */
    this.modalHeightRefresh = function () {
        var modalContent = document.querySelector('#' + modalId + ' .modal-content'),
            modalBodyScroll = document.querySelector(modalScrollable),
            modalBodyDefault = document.querySelector(modalDefault),
            img = document.querySelector(modalDefault + ' img'),
            maxHeight = modal.offsetHeight * 0.85;

        // Condition to know when height exceed maxHeight
        if (img.clientHeight > maxHeight) {
            utility.addClass(modalBodyDefault, 'hidden');
            utility.removeClass(modalBodyScroll, 'hidden');
            modalBodyScroll.style.height = maxHeight + 'px';

            setTimeout(function () {
                new scrollbot(modalScrollable);

                var modalParent = modalBodyScroll.querySelector('.scrollbot-inner-parent');
                modalParent.style.paddingRight = '17px';
                utility.addClass(modalContent, 'pl-10');
            }, 1);
        } else {
            modalBodyDefault.style.height = 'auto';
            utility.removeClass(modalBodyDefault, 'hidden');
        }
    };
}
