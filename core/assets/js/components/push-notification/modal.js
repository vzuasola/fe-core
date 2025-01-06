import * as utility from "Base/utility";
import modal from "Base/modal";
import scrollbot from "BaseVendor/scrollbot";
import detectIE from "Base/browser-detect";

function pushnxModal(option) {
    "use strict";

    var self = this;

    var opt = option || {};

    var isActive = opt.isactive || false;
    var defaultScroll = opt.scrollbot;
    var modalControl = opt.control;

    var pushnx_debug = opt.debug || {};

    var bodyTag = document.body;
    var $pnxMessages = document.getElementById('pushnxMessages');
    var $modalId = document.getElementById('pushnxLightbox');

    var scrollObj = null;
    var storedMessage = [];
    var msgsHeight = opt.height || 450;

    var modalPushnx = new modal({
        closeOverlayClick: false,
        escapeClose: false,
        id : 'pushnxLightbox'
    });

    this.modalOpen = function (messages) {
        var $msgWrapper = document.querySelector('.messages');

        if (!messages.existing && $msgWrapper) {
            // Add messages content
            $msgWrapper.innerHTML = messages.content;
            // Open Modal
            if (modalControl) {
                utility.addClass($modalId, "modal-active");
                bodyTag.style.overflow = "hidden";
            }

            if (defaultScroll) {
                // Srollbar
                scrollObj = new scrollbot('.messages');
            }
        } else {
            // Get messages wrapper
            var msgWrapper = document.querySelector('.messages .scrollbot-inner-parent') || document.querySelector('.messages');

            if ($modalId && msgWrapper) {
                // If not active, remove all messages and update with new ones
                if (!isActive) {
                    msgWrapper.innerHTML = messages.content;
                } else {
                // Else if active, append the new messages
                    msgWrapper.innerHTML += messages.content;
                }
            }
        }

        self.modalHeightRefresh();
    };

    this.modalClose = function (force) {
        if (force || !document.querySelector('#push-notification .message')) {
            // Empty pushnxMessages
            storedMessage = [];
            $pnxMessages.innerHTML = '';
            pushnx_debug.console(storedMessage, 'Modal Close', 'Close Modal and clear message variable.');
            // Close modal
            utility.removeClass($modalId, "modal-active");

            bodyTag.style.overflow = "inherit";
        }

        self.modalHeightRefresh();
    };

    this.modalHeightRefresh = function () {
        setTimeout(function () {
            // Get height of messages
            var msgSelector = document.querySelectorAll('.message-pnx');
            var msgsSelector = document.querySelector('.messages-pnx');
            var msgHeighTotal = 0;

            if (!msgsSelector) {
                return;
            }

            for (var msgsIndex = 0; msgsIndex < msgSelector.length; msgsIndex++) {
                msgHeighTotal  += msgSelector[msgsIndex].offsetHeight;
            }

            // Set height
            // if total height is less than 'msgHeight',
            // set height to auto to remove scrollbar
            if (msgHeighTotal < msgsHeight) {
                msgsSelector.style.height = 'auto';
            } else {
                msgsSelector.style.height = msgsHeight + 'px';
            }
            // Refresh scroll
            if (scrollObj !== null) {
                scrollObj.refresh();
            }
            // Center Modal
            if (detectIE() === 8) {
                modalPushnx.centerModalContent(document.getElementById('pushnxLightbox'));
            }
        }, 1);

    };
}

export default pushnxModal;
