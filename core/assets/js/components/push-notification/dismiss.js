import * as utility from "Base/utility";
import reqwest from "BaseVendor/reqwest";
import modal from "Base/modal";

function pushnxDismiss(options) {
    "use strict";

    var self = this;

    var $modalId = document.getElementById('pushnxDismissLightbox');
    var $pnxMessages = document.querySelector('#pushnxDismissLightbox .modal-body');

    var opt = options || {};
    var message = opt.content || false;
    var yes = opt.yes || false;
    var no = opt.no || false;

    var pushnx_global = opt.global || {};
    var pushnx_message = opt.message || {};
    var pushnx_socket = opt.socket || {};
    var pushnx_render = opt.render || {};

    var defaultSrc = 'icore';
    var isAllowToDismiss = ['icore'];

    new modal({
        closeOverlayClick: false,
        escapeClose: false,
        id : 'pushnxDismissLightbox'
    });

    this.enable = function () {
        var dismissAll = document.querySelector('.pushnx-lightbox-dismiss-all');
        utility.addEventListener(dismissAll, 'click', self.dismissModal);
    };

    this.dismissModal = function () {
        var dismissMessage = pushnx_render.dismissAll({
            message: message.value,
            yes: yes,
            no: no
        });

        // Add messages content
        $pnxMessages.innerHTML = dismissMessage;

        // Open modal
        utility.addClass($modalId, "modal-active");
        document.body.style.overflow = 'hidden';

        var dismissYes = document.getElementById('dismiss-yes');
        var dismissNo = document.getElementById('dismiss-no');

        if (dismissYes) {
            utility.addEventListener(dismissYes, 'click', self.dismissMessages);
        }

        if (dismissNo) {
            utility.addEventListener(dismissNo, 'click', self.closeDismissModal);
        }
    };

    this.closeDismissModal = function () {
        // Close modal
        utility.removeClass($modalId, "modal-active");
        document.body.style.overflow = "inherit";
    };

    this.dismissMessages = function () {
        var msgIds = pushnx_message.getDismissableMessage();
        var extracted = self.extractDismissIds(msgIds);

        self.sendReply(extracted);
        self.closeDismissModal();
    };

    this.extractDismissIds = function (msgIds) {
        var tmp = [];

        for (var a = 0; a < msgIds.length; a++) {
            var src = self.isAllowToDismiss(msgIds[a]) || defaultSrc;

            if (src) {
                var msgId = msgIds[a].replace(src, '');
                tmp.push(parseInt(msgId));
            }
        }

        return tmp;
    };

    this.sendReply = function (msgIds) {
        var parameters = {
            "secureToken": pushnx_global.settings.token,
            "clientIP": pushnx_global.settings.clientIP,
            "playerId": pushnx_global.settings.playerId,
            "id": '-1',
            "actionId": '-1',
            "msgs": msgIds,
        };

        reqwest({
            url: pushnx_global.settings.replyUri,
            method: 'post',
            data: JSON.stringify(parameters),
            type : 'json',
            crossOrigin: pushnx_socket.hasWebsocket(),
            contentType: 'text/plain',
            complete: function (response) {
                if (response.status === 424 || response.status === 200) {
                    for (var index = 0; index < msgIds[index]; index++) {
                        var src = self.isAllowToDismiss(msgIds[index]) || defaultSrc;
                        pushnx_message.removeMessage(msgIds[index], src);
                    }

                    return;
                }

                if (pushnx_global.settings.retryCount) {
                    setTimeout(function () {
                        self.sendReply(parameters, pushnx_global.settings.retryCount - 1);
                    }, pushnx_global.settings.delayCount);
                }
            }
        });
    };

    this.isAllowToDismiss = function (msgId) {
        for (var i = 0; i < isAllowToDismiss.length; i++) {
            if (msgId.indexOf(isAllowToDismiss[i]) !== -1) {
                return isAllowToDismiss[i];
            }
        }

        return false;
    };
}

export default pushnxDismiss;
