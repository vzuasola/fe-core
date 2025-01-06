/**
 * Generic Modal
 * Author: Yunyce de Jesus
 */
import * as utility from "Base/utility";
import scrollbot from "BaseVendor/scrollbot";

export default function modalGeneric(options) {
    "use strict";

    var _this = this,
        modal = '',
        modalId = 'genericLightbox',
        modalTrigger = 'modal-generic-trigger',
        modalScrollClass = 'generic-modal--container',
        bodySelector = document.querySelector("body"),
        modalHeader = '#' + modalId + ' .modal-header',
        modalScrollable = '#' + modalId + ' .modal-body',
        modalScrollbot = "<div class='" + modalScrollClass + " scrollbot'></div>";

    /**
     * Create Generic overlay element
     */
    var genericOverlay = function () {
        var modalWrapper = document.createElement("div"),
            modalOverlay = "<div class='modal-overlay'></div>",
            modalHeaderContent = "<div class='modal-header'></div>",
            modalCloseBtn = "<span class='modal-close modal-close-button'></span>";

        modalWrapper.className = "modal modal-generic";
        modalWrapper.id = modalId;
        modalWrapper.innerHTML = modalOverlay +
            "<div class='modal-content'>" +
            modalHeaderContent +
            "<div class='modal-body'>" +
            modalScrollbot +
            "</div>" +
            modalCloseBtn +
            "</div>";
        bodySelector.insertAfter(modalWrapper, bodySelector.querySelector("#announcementLightbox"));
    };

    /**
     * Generic Event Listener
     */
    var genericListener = function () {
        var modalOverlay = modal.querySelector('.modal-overlay'),
            modalClose = modal.querySelector('.modal-close');

        utility.addEventListener(document, 'click', function (evt) {
            var target = utility.getTarget(evt);
            if (utility.hasClass(target, modalTrigger)
                || utility.hasClass(target.parentNode, modalTrigger)) {
                utility.preventDefault(evt);
                openModal(target);
            }

            if (target === modalOverlay || target === modalClose) {
                utility.preventDefault(evt);
                closeModal();
            }
        });

        // Close modal on clicking Escape key
        utility.addEventListener(document, 'keydown', function (evt) {
            // Cross browser event
            evt = evt || window.event;

            if (evt.keyCode === 27) {
                closeModal();
            }
        });
    };

    /**
     * Open Generic Modal function
     */
    var openModal = function (target) {
        var modalHeaderDiv = document.querySelector(modalHeader),
            modalBodyScroll = document.querySelector(modalScrollable + " .scrollbot");

        if (target.getAttribute('data-modal-id') && modal) {
            var modalContent = document.querySelector(target.getAttribute('data-modal-id')),
                headerContent = modalContent.querySelector('.generic-modal-content-header').innerHTML,
                modalContentValue = modalContent.querySelector('.generic-modal-content-wrapper').innerHTML;

            // Attach header and content to modal
            modalHeaderDiv.innerHTML = headerContent;
            modalBodyScroll.innerHTML = modalContentValue;

            utility.addClass(modal, 'modal-active');
            bodySelector.style.overflow = "hidden";
            modalHeightRefresh();
        }
    };

    /**
     * Close Generic Modal function
     */
    var closeModal = function (target) {
        if (modal) {
            utility.removeClass(modal, 'modal-active');

            var modalBodyScroll = document.querySelector(modalScrollable);

            setTimeout(function () {
                modal.querySelectorAll('.' + modalScrollClass).remove();
                bodySelector.style.overflow = "inherit";
                modalBodyScroll.removeAttribute("style");
                modalBodyScroll.innerHTML = modalScrollbot;
            }, 500);
        }
    };

    /**
     * Modal Height Function
     * Know when to execute scrollbot
     */
    var modalHeightRefresh = function () {
        var modalBodyScroll = document.querySelector(modalScrollable);

        // Condition to know when height exceed maxHeight
        if (modalBodyScroll.clientHeight > 485) {
            modalBodyScroll.style.height = 485 + 'px';

            setTimeout(function () {
                new scrollbot(modalScrollable);
            }, 1);
        }
    };

    /**
     * Attach window onload handler
     */
    utility.addEventListener(window, 'load', function () {
        _this.init();
    });

    /**
     * Initialize Generic Modal
     */
    this.init = function () {
        genericOverlay();
        modal = document.querySelector('.modal-generic');
        genericListener();
    };
}
