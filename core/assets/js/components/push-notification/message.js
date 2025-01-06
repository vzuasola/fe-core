import * as utility from "Base/utility";
import reqwest from "BaseVendor/reqwest";

import pushnxRender from "Base/push-notification/renderer"; // template
import pushnxModal from "Base/push-notification/modal"; // modal
import pushnxDismiss from "Base/push-notification/dismiss"; // dismiss
import pushnxAlert from "Base/push-notification/alert"; // alert notif
import pushnxActionHook from "Base/push-notification/action-hook"; // action hook


function pushnxMessage(option) {
    "use strict";

    var self = this;
    var defaultSrc = 'icore';

    var opt = option || {};

    var pushnx_global = opt.global || {};
    var pushnx_debug = opt.console || {};
    var pushnx_support = opt.support || {};
    var pushnx_socket = opt.socket || {};
    var push_notif_eb = opt.eb || {};
    var push_notif_server_address = opt.server || {};
    var pushnx_render = new pushnxRender({
        template: opt.template,
        icon: opt.icontemplate
    }); // templates

    var islogin = opt.islogin || false;
    var isActive = false;
    var useModal = (opt.modal.enable === undefined) ? true : opt.modal.enable;
    var control = (opt.modal.control === undefined) ? true : opt.modal.control;
    var modalHeight = (opt.modal.height === undefined) ? false : opt.modal.height;

    // action hook
    var pushnx_action_hook = new pushnxActionHook({
        global: pushnx_global,
        debug: pushnx_debug
    });

    // modal
    var pushnx_modal = new pushnxModal({
        isactive: isActive,
        scrollbot: opt.scrollbot,
        control: control,
        height: modalHeight,
        debug: pushnx_debug
    });

    // dismiss
    if (opt.dismiss) {
        var pushnx_dismiss = new pushnxDismiss({
            content: pushnx_global.dismiss.content,
            yes: pushnx_global.dismiss.yes,
            no: pushnx_global.dismiss.no,
            render: pushnx_render,
            global: pushnx_global,
            support: pushnx_support,
            socket: pushnx_socket,
            message: self,
            debug: pushnx_debug
        });
    }

    var pushnx_alert = new pushnxAlert({
        global: pushnx_global,
        debug: pushnx_debug
    });

    pushnx_debug.console(opt, 'Fetched Config', opt, 'warn');

    // date format
    var date_format = pushnx_global.settings.dateformat.format;
    var offset = pushnx_global.settings.dateformat.offset;

    // variable storage
    var storedMessage = [];
    var incomingMessages = [];
    var newMessage = [];
    var removedMessages = [];
    var dismissableMessages = [];
    var hookAction = [];

    var validSession = pushnx_support.validSession(islogin) || false;

    var $container = document.getElementById('pushnxMessages') || false;

    /**
     * acknowledge message
     */
    this.acknowledge = function (evt) {
        // Cross browser event
        evt = evt || window.event;
        // get srcElement if target is falsy (IE8)
        var target = evt.target || evt.srcElement;

        if (typeof target.getAttribute('data-msg-id') !== 'undefined' && target.getAttribute('data-msg-id') !== null) {
            var ctaButton = false;
            var overRide = false;
            var actionId = target.getAttribute('data-id');
            var msgId = target.getAttribute('data-msg-id');
            var container = document.getElementById('actions' + msgId);
            var expiry = container.getAttribute('data-expiry');
            var action = target.getAttribute('data-action');
            var src = target.getAttribute('data-msg-src') || defaultSrc;
            var ctaride = target.getAttribute('data-action-override') || false;
            var autoAcknowledge = target.getAttribute('data-auto-acknowledge');

            // click on cta button, auto-acknowledge value is null
            if (autoAcknowledge === null) {
                ctaButton = true; // cta button set autoAcknowledge to true, this will allow to proceed on sendReply
            }

            // action button override
            var over = document.querySelector('[data-id-override="' + actionId + '"]');

            if ((ctaride && over) || (ctaButton && over)) {
                overRide = true; // allow cta button to use the override data-parameters
            }

            pushnx_debug.console('Message Id: ' + msgId + ', Action Id: ' + actionId + ', Retry Count config: ' + pushnx_global.settings.retryCount + ', Message Expiration: ' + expiry, 'Message Acknowledged Action');

            var errorMsg = pushnx_render.expiredMessage({
                isexpired: pushnx_support.isExpired(expiry, msgId),
                messageClass: pushnx_global.css.expiredMessage,
                message: pushnx_global.translations.expired_message
            });

            if (errorMsg) {
                container.innerHTML = errorMsg;
                pushnx_debug.console(errorMsg, 'Message Acknowledged Action', 'Error Message Display on Action container.');
            }

            var messageId = msgId.replace(src, '');

            var parameters = {
                "secureToken": pushnx_global.settings.token,
                "clientIP": pushnx_global.settings.clientIP,
                "playerId": parseInt(pushnx_global.settings.playerId),
                "id": parseInt(messageId),
                "actionId": actionId,
                "src": src
            };

            self.updateMessageStatus();

            removedMessages.push(parseInt(msgId)); // set clicked message id and convert to number
            pushnx_debug.console(removedMessages, 'Removed Messages', 'Remove message id: ' + msgId);

            self.sendReply(parameters, pushnx_global.settings.retryCount, action, ctaride, overRide, ctaButton, target);
        }
    };

    this.sendReply = function (parameters, retryCount, action, ctaride, overRide, ctaButton, target) {
        pushnx_debug.console(validSession, 'is Valid Session for reply ' + validSession, 'ctaButton: ' + ctaButton);
        if (validSession) {
            isActive = true;

            reqwest({
                url: pushnx_global.settings.replyUri,
                method: 'post',
                data: JSON.stringify(parameters),
                type : 'json',
                crossOrigin: pushnx_socket.hasWebsocket(),
                contentType: 'text/plain',
                complete: function (response) {
                    if (response.status === 200 || response.status === 424) {
                        var actionParam = {
                            'id': parameters.src + parameters.id,
                            'actionId': parameters.actionId,
                            'action': action,
                            'ctaride': ctaride,
                            'overRide': overRide,
                            'ctaButton': ctaButton,
                            'status': response.status,
                            'target': target,
                        };

                        pushnx_action_hook.doActionHook(actionParam, function () {
                            self.removeMessage(parameters.id, parameters.src);
                            pushnx_debug.notify('Response: ' + response.status + ', Removed message id: ' + parameters.id);
                        });
                        return;
                    }

                    if (retryCount) {
                        setTimeout(function () {
                            self.sendReply(parameters, retryCount - 1, action, ctaride, overRide, ctaButton, target);
                        }, pushnx_global.settings.delayCount);
                    } else {
                        self.closeMessage(true);
                    }
                }
            });
        }
    };

    /**
     * see push-notification line 241
     * use on manual binding of cta buttons
     */
    this.action = function () {
        if (islogin) {
            utility.addEventListener(document.body, "click", this.acknowledge);
        }
    };

    this.unbindAction = function () {
        utility.removeEventListener(document.body, "click", this.acknowledge);
    };

    /**
     * process messages filters and validation
     */
    this.generateMessage = function (message) {
        var formattedMsg = '';
        var msgTemp = '';
        var objLen = message.length;

        if (objLen) {
            for (var gmIndex = 0; gmIndex < objLen; gmIndex++) {
                var ExpiryDate = 0;
                var publishDate = '6666';
                var isexpired = false;
                var msg = message[gmIndex];
                var msgContent = {};

                msgContent.messageId = msg.Id;
                msgContent.actions = msg.Actions;
                msgContent.content = msg.Contents;

                if (msg.hasOwnProperty('ExpiryDate')) {
                    ExpiryDate = msg.ExpiryDate;
                    isexpired = pushnx_support.isExpired(ExpiryDate, msg.Id);
                }

                msgContent.expiryDate = msg.ExpiryDate;

                // render debug date expiration label
                msgContent.expirationLabel = pushnx_render.expirationDate({
                    displayExpiryDate: pushnx_global.settings.displayExpiryDate,
                    label: pushnx_global.label.expiration_date,
                    formattedDate: pushnx_support.formatDateTime(ExpiryDate, date_format, offset),
                    debugClass: (!isexpired) ? pushnx_global.css.dateActive : pushnx_global.css.dateExpired,
                    Phase1Date: (!pushnx_support.isDateInMilli(ExpiryDate, msg.Id)) ? ' (Phase1 date format) ' : '',
                });
                pushnx_debug.console(
                    msgContent.expirationLabel,
                    'Prepare Expiration Date Template',
                    'Expiration Date Template for message id ' + msgContent.messageId
                );

                if (msg.hasOwnProperty('Contents') && msg.hasOwnProperty('Actions')) {
                    if (isexpired && utility.inArray(msg.Id, removedMessages)) {
                        pushnx_debug.console(removedMessages, 'Filter Removed Messages', 'is expired and msg id: ' + msg.Id + ' is existing on remove ids');
                        msgContent.actions = pushnx_render.expiredMessage({
                            isexpired: pushnx_support.isExpired(ExpiryDate, msg.Id),
                            messageClass: pushnx_global.css.expiredMessage,
                            message: pushnx_global.translations.expired_message
                        });
                    } else {
                        pushnx_debug.console(removedMessages, 'Filter Removed Messages', 'is not expired and message id ' + msg.Id + ' is not existing on remove ids');
                        // generate action
                        msgContent.actions = pushnx_render.messageAction({
                            id: msg.Id,
                            actions: msg.Actions,
                            cta: pushnx_global.cta,
                            actionClass: pushnx_global.css.actionClass,
                            actionId: pushnx_global.css.actionId,
                            src: msg.Src || defaultSrc
                        });
                    }
                    pushnx_debug.console(
                        msgContent.actions,
                        'Prepare Expired Message Template',
                        'Expired message Template for message id ' + msgContent.messageId
                    );

                    if (msg.hasOwnProperty('DateTriggered')) {
                        publishDate = msg.DateTriggered;
                    } else if (msg.hasOwnProperty('receivedDate')) {
                        publishDate = msg.receivedDate;
                    }

                    var param = msg.Parameters || {};
                    var productTypeId = param.ProductTypeId || '0';
                    var msgIcon = self.iconMessage(productTypeId);

                    if (msgIcon) {
                        msgContent.genericIcon = (msgIcon === 'generic') ? true : null;
                        msgContent.icon = pushnx_render.messageIcon({
                            icon: msgIcon
                        });
                    }

                    msgContent.publishDate = pushnx_support.formatDateTime(publishDate, date_format, offset);
                    msgContent.title = pushnx_render.messageTitle({
                        title: self.messageTitle(productTypeId)
                    });
                    pushnx_debug.console(
                        msgContent.title,
                        'Prepare Message Title Template',
                        'Message title Template for message id ' + msgContent.messageId
                    );

                    msgTemp = pushnx_render.pushMessage(msgContent); // render message template
                    pushnx_debug.console(
                        msgTemp,
                        'Prepare Message Template',
                        'Message Template for message id ' + msgContent.messageId
                    );

                    hookAction.push(msgContent.messageId); // msgId to bind on action

                    // generate message
                    formattedMsg += [
                        msgTemp
                    ].join("\n");
                }
            }
        }

        return self.constructMessage(formattedMsg);
    };

    this.constructMessage = function (messages) {
        if (messages) {
            return {existing: false, content: messages};
        }

        return {existing: true, content: messages};
    };

    this.sendMessages = function (messages) {
        var fetchMessage = '';
        var incomingMsg = '';

        self.containerMessage();

        self.messageReady(false);

        pushnx_debug.console(isActive, 'Active Browser?');
        pushnx_debug.console(storedMessage, 'storedMessage is empty?', 'storedMessage.length: ' + storedMessage.length);

        var filteredMsg = self.removeExpiredMessage(messages);

        newMessage = self.getMessages(filteredMsg);

        if (isActive && storedMessage.length > 0) {
            incomingMsg = newMessage; // will return an array of new messages

            pushnx_debug.console(incomingMsg, 'New Messages', 'new message received from server.');
        } else {
            incomingMsg = filteredMsg;
            storedMessage = filteredMsg;

            pushnx_debug.console(storedMessage, 'Update storedMessage', 'Replace storedMessage value.');
        }

        if (typeof storedMessage === 'object') {
            if (!utility.isEmptyObject(storedMessage) && !utility.isEmptyObject(incomingMsg)) {
                pushnx_debug.console(incomingMsg, 'Generate Message', 'Generate Message for Player Id: ' + pushnx_global.settings.playerId);

                pushnx_alert.updateMessageAlert(storedMessage.length); // trigger "pushnx.count.message"
                fetchMessage = self.generateMessage(incomingMsg);

                pushnx_debug.console(fetchMessage, 'Generate HTML Message', 'Generate Message for Player Id: ' + pushnx_global.settings.playerId);

                self.openMessage(fetchMessage);

                pushnx_alert.newMessageAlert(newMessage.length);

                isActive = false;
            } else if (utility.isEmptyObject(storedMessage)) {
                pushnx_debug.console('closeMessage has been triggered.', 'Empty Message');
                pushnx_debug.notify('Empty message: ' + storedMessage.length);
                pushnx_alert.updateMessageAlert(false);
                self.closeMessage(true);
            }
        } else {
            pushnx_debug.console(storedMessage, 'Message(s)', 'Invalid Format.');
            pushnx_debug.notify('Empty message: ' + storedMessage.length);
        }
    };

    this.getMessages = function (messages) {
        incomingMessages = [];

        pushnx_debug.console(messages, 'Incoming Messages');

        if (utility.isEmptyObject(messages)) {
            pushnx_debug.console(messages, 'Incoming Message(s)', 'is empty');
        }

        var newMsg = messages;
        var objLen = newMsg.length;

        for (var msgIndex = 0; msgIndex < objLen; msgIndex++) {
            var findStored = self.findStoredMessage(newMsg[msgIndex].Id);

            if (findStored === 'empty') {
                pushnx_debug.console(newMsg[msgIndex], 'Incoming Message', 'Message Id: ' + newMsg[msgIndex].Id);
                incomingMessages = self.addToMessages(newMsg[msgIndex]);
            } else {
                pushnx_debug.console('Message Id: ' + newMsg[msgIndex].Id + ' is already existing.', 'Existing Message');
            }
        }

        pushnx_debug.console(incomingMessages, 'Incoming Messages');

        return incomingMessages;
    };

    this.addToMessages = function (addMessage) {
        var tmpAddMsg = [];

        if (pushnx_global.settings.displayAllMessage) {
            pushnx_debug.console(pushnx_global.settings.displayAllMessage, 'Filter Expired Messages');

            if (typeof addMessage.ExpiryDate !== 'undefined') {
                pushnx_debug.console(addMessage.ExpiryDate, 'Message ExpiryDate', 'Message Id: ' + addMessage.Id + ' expiry date is defined');
                // validate expiration date
                var foundMsg = self.findStoredMessage(addMessage.Id);
                var isexpired = pushnx_support.isExpired(addMessage.ExpiryDate, addMessage.Id);

                if (!isexpired && foundMsg === 'empty') {
                    storedMessage.push(addMessage);
                    tmpAddMsg.push(addMessage);
                    pushnx_debug.console(addMessage, 'Add Message', 'Message Id: ' + addMessage.Id + ' has been added.');
                }
            } else {
                pushnx_debug.console(addMessage.ExpiryDate, 'Message ExpiryDate', 'Message Id: ' + addMessage.Id + ' expiry date is undefined');
                storedMessage.push(addMessage);
                tmpAddMsg.push(addMessage);
                pushnx_debug.console(addMessage, 'Add Message', 'Message Id: ' + addMessage.Id + ' has been added.');
            }
        } else {
            storedMessage.push(addMessage);
            tmpAddMsg.push(addMessage);
            pushnx_debug.console(addMessage, 'Add Message', 'Message Id: ' + addMessage.Id + ' has been added.');
        }

        self.setDismissableMessage(addMessage);

        self.updateMessageStatus();

        return tmpAddMsg;
    };

    this.removeMessage = function (msgId, src) {
        var selectorId = src + msgId;
        var expiryDelayCount = pushnx_global.settings.expiryDelayCount || 1000;
        var actionElement = document.getElementById('actions' + selectorId);
        var msgElement = document.getElementById('message' + selectorId);

        if (actionElement
            && msgElement
            && pushnx_support.isExpired(actionElement.getAttribute('data-expiry'), selectorId)) {
            setTimeout(function () {
                self.removeStoredMessage(selectorId);

                if (document.getElementById('message' + selectorId)) {
                    pushnx_debug.console('Message Id: ' + selectorId + ' will be remove in ' + expiryDelayCount + 'ms.', 'Remove Expired Message');

                    document.getElementById('message' + selectorId).remove();
                    self.closeMessage(false);

                    if (useModal) {
                        pushnx_modal.modalHeightRefresh();
                    }
                }
            }, expiryDelayCount);
        } else if (msgElement) {
            self.removeStoredMessage(selectorId);

            msgElement.remove();

            pushnx_debug.console('Message Id: ' + selectorId + ' will be remove immediately', 'Removed Message');

            self.closeMessage(false);
        }
    };

    this.removeStoredMessage = function (msgId) {
        if (msgId < 0) {
            pushnx_debug.console(msgId, 'Remove Message', 'Message Id: ' + msgId + ' is empty');
            return;
        }

        var msgKey = self.findStoredMessage(msgId);

        if (msgKey !== 'empty') {
            if (storedMessage.splice(msgKey, 1)) {
                pushnx_debug.console('Removed Message Id: ' + msgId + ' on list', 'Removed Message');
            }
        }

        self.removeDismissableMessage(msgId);

        self.updateMessageStatus();

        pushnx_debug.console(storedMessage, 'Updated Message', 'post remove message');

        isActive = false;

        pushnx_alert.updateMessageAlert(storedMessage.length);

        return storedMessage;
    };

    this.removeExpiredMessage = function (rmExpiredMsg) {
        var messages = [];

        if (rmExpiredMsg) {
            var objLen = rmExpiredMsg.length;

            if (pushnx_global.settings.displayAllMessage) {
                pushnx_debug.console(rmExpiredMsg, 'Start to Remove Expired Message', 'Messages has ' + rmExpiredMsg.length + ' items.');
                pushnx_debug.console(pushnx_global.settings.displayAllMessage, 'Removing Expired Message', 'Checking config... Filter Expired Messages?');

                for (var expIndex = 0; expIndex < objLen; expIndex++) {
                    var msg = rmExpiredMsg[expIndex];

                    if (msg && msg.ExpiryDate) {
                        // ExpiryDate is existing (Message format version 2 and 3)
                        // validate expiration
                        var isexpired = pushnx_support.isExpired(msg.ExpiryDate, msg.Id);

                        if (!isexpired) { // not expired
                            messages.push(msg);
                        } else if (isexpired && !isActive) {
                            self.removeStoredMessage(msg.Id);
                        }
                    } else {
                        // Message format version 1 handler
                        messages.push(msg);
                    }
                }
            } else {
                return rmExpiredMsg;
            }
        }

        return messages;
    };

    this.updateMessageStatus = function () {
        if (pushnx_global.settings.displayExpiryDate) {
            var objLen = storedMessage.length;

            for (var statusIndex = 0; statusIndex < objLen; statusIndex++) {
                var isexpired = pushnx_support.isExpired(storedMessage[statusIndex].ExpiryDate, storedMessage[statusIndex].Id);

                pushnx_debug.console('Message Id: ' + storedMessage[statusIndex].Id + ' is expired: ' + isexpired, 'Update Messages Status');

                if (isexpired) {
                    var span = document.querySelector('#message' + storedMessage[statusIndex].Id + ' .time span');
                    utility.removeClass(span, 'debug-active');
                    utility.addClass(span, 'debug-expired');

                    pushnx_debug.console('Message Id: ' + storedMessage[statusIndex].Id + ' is expired.', 'Update Message Status');
                }
            }
        }
    };

    this.extractMessage = function (messages) {
        var result = false;
        var parsedJson = JSON.parse(messages);
        var extractedJson = false;

        if (parsedJson.hasOwnProperty('body')) {
            pushnx_debug.console(parsedJson, 'Extracted Messages', 'Validate Message Format. Message Format is messages.body.body');
            extractedJson = parsedJson.body;
        } else if (Object.keys(parsedJson).length >= 0) {
            pushnx_debug.console(parsedJson, 'Extracted Messages', 'Validate Message Format. Message Format is messages.body');
            extractedJson = parsedJson;
        } else {
            pushnx_debug.console(parsedJson, 'Extracted Messages', 'Invalid Message Format from Server', 'error');
        }

        if (extractedJson) {
            result = this.touchMessage(extractedJson);
        }

        return result;
    };

    this.touchMessage = function (messages) {
        var tMsg = [];
        var tLen = messages.length;

        if (!utility.isEmptyObject(messages)) {
            for (var tIndex = 0; tIndex < tLen; tIndex++) {
                var tmMsg = messages[tIndex];
                var src = messages[tIndex].Src || defaultSrc;

                tmMsg.Id = src + tmMsg.Id;

                tMsg.push(tmMsg);
            }
        }

        return tMsg;
    };

    this.findMessageByProductTypeId = function (messages) {
        var filterMsg = [];
        var objLen = messages.length;

        if (!utility.isEmptyObject(messages)) {
            for (var filterIndex = 0; filterIndex < objLen; filterIndex++) {
                var msg = messages[filterIndex];
                var msgParam = msg.Parameters;
                var productTypeId = msgParam.ProductTypeId || '0';

                pushnx_debug.console(productTypeId, 'Product Type Id is undefined?', 'Product Type Id');
                pushnx_debug.console('Searching for: ' + pushnx_global.settings.productTypeId + ', Search Index: ' + filterIndex + ', Search Product Type Id: ' + productTypeId, 'Product Type Id found?');
                if (!msgParam.hasOwnProperty('ProductTypeId') || utility.inArray(productTypeId, pushnx_global.settings.productTypeId)) {
                    pushnx_debug.console(productTypeId, 'Search Message by Product Type Id result', 'Product Type Id is ' + productTypeId + ', Result found at index ' + filterIndex);
                    pushnx_debug.console(pushnx_global.settings.disableBonusAward, 'Disable Bonus Awarded', 'Bonus Awarded status: ' + pushnx_global.settings.disableBonusAward);

                    if (!pushnx_global.settings.disableBonusAward || (pushnx_global.settings.disableBonusAward && msg.TransactionalMessageName !== 'BonusIsAwarded')) {
                        filterMsg.push(msg);
                    }
                } else {
                    pushnx_debug.console(msg.Id, 'Search Message by Product Type Id result', 'Product Type Id not found');
                }
            }
        } else {
            pushnx_debug.console(messages, 'Searching Messages by Product Type Id', 'messages is empty');
        }

        return filterMsg;
    };

    this.findStoredMessage = function (msgId) {
        var objLen = storedMessage.length;

        pushnx_debug.console(storedMessage, 'Start Searching Message on storedMessage', 'Message Id: ' + msgId);

        if (!utility.isEmptyObject(storedMessage)) {
            for (var fsmIndex = 0; fsmIndex < objLen; fsmIndex++) {
                if (storedMessage[fsmIndex].Id === msgId) {
                    pushnx_debug.console(storedMessage[fsmIndex].Id, 'Search Message result', 'Message Id found at index ' + fsmIndex);
                    return fsmIndex;
                }
            }
        } else {
            pushnx_debug.console(storedMessage, 'Searching Messages by ID', 'messages is empty', 'warn');
        }

        return 'empty';
    };

    this.violatileMessage = function (msgId) {
        if (push_notif_eb) {
            var json = {"playerId": pushnx_global.settings.playerId, "violatileMsgId": msgId};
            var headers = {"content-type":"application/json"};

            push_notif_eb.publish(push_notif_server_address, json, headers);

            pushnx_debug.console(json, 'Violatile Message', 'Published to ' + push_notif_server_address);
        }
    };

    this.productMessage = function (messages) {
        var filteredMsg = self.findMessageByProductTypeId(messages);
        return filteredMsg;
    };

    this.containerMessage = function () {
        // render push-notification body template
        if (!document.getElementById('push-notification') && $container) {
            var content = [
                pushnx_render.pushTemplate({
                    title: (pushnx_global.translations.title) ? pushnx_global.translations.title : pushnx_global.label.modal_title,
                    messages: pushnx_global.translations.empty,
                    dismiss: (pushnx_global.dismiss && pushnx_global.dismiss.button_label) ? pushnx_global.dismiss.button_label : false
                })
            ].join("\n");

            $container.innerHTML = content;

            pushnx_debug.console(content, 'Render Body Template', 'body template is not existing looking for id: push-notification on ' + $container);
        }
    };

    this.openMessage = function (messages) {
        if (messages) {
            var $msgWrapper = document.querySelector('.messages .scrollbot-inner-parent') || document.querySelector('.messages');

            if (useModal) {
                pushnx_debug.console(opt.modal, 'Render in Modal', 'Modal enabled: ' + opt.modal.enable);
                pushnx_modal.modalOpen(messages); // open modal
            } else if ($container) {
                pushnx_debug.console(opt.modal, 'Render in non-modal', 'Modal enabled: ' + opt.modal.enable);
                $msgWrapper.innerHTML = messages.content; // non-modal
            }

            if ($msgWrapper) {
                this.messageReady(true);
            }

            pushnx_action_hook.contentHook(hookAction);
        }

        if (opt.dismiss) {
            pushnx_dismiss.enable(); // enable pushnx dismiss
        }
    };

    this.closeMessage = function (force) {
        if (useModal) {
            pushnx_modal.modalClose(force);
        } else {
            self.emptyMessage();
        }

        self.containerMessage();

        pushnx_alert.updateMessageAlert(storedMessage.length);
    };

    this.emptyMessage = function () {
        var $messages = document.querySelector('.messages') || false;

        if (utility.isEmptyObject(storedMessage) && $messages) {
            $messages.innerHTML = pushnx_global.translations.empty || 'There are no new notifications.';
        }
    };

    this.iconMessage = function (productTypeId) {
        if (pushnx_global.icon) {
            var ind = pushnx_global.settings.productDetails[productTypeId].icon;
            return ind || 'generic';
        }

        return null;
    };

    this.messageTitle = function (productTypeId) {
        if (pushnx_global.settings.productDetails[productTypeId].label) {
            return pushnx_global.settings.productDetails[productTypeId].label;
        }

        return '';
    };

    /**
     * trigger pushnx.message status
     */
    this.messageReady = function (status) {
        utility.triggerEvent(document, 'pushnx.message', {
            ready: status
        });
    };

    /**
     * dismiss messages
     */
    this.setDismissableMessage = function (message) {
        var params = message.Parameters || {};
        var index = params.ProductTypeId || '0';
        var msgid = message.Id;

        if (!pushnx_global.settings.productDetails[index].allowtodismiss) {
            dismissableMessages.push(msgid);
        }
    };

    this.getDismissableMessage = function () {
        return dismissableMessages;
    };

    this.removeDismissableMessage = function (msgId) {
        var msgKey = self.findDismissableMessage(msgId);

        if (msgKey !== 'empty') {
            if (dismissableMessages.splice(msgKey, 1)) {
                pushnx_debug.console('Removed Dismissable Message Id: ' + msgId + ' on list', 'Removed Message');
            }
        }
    };

    this.findDismissableMessage = function (msgId) {
        var objLen = dismissableMessages.length;

        pushnx_debug.console(dismissableMessages, 'Start Searching Message on dismissableMessages');

        if (!utility.isEmptyObject(dismissableMessages)) {
            for (var fsmIndex = 0; fsmIndex < objLen; fsmIndex++) {
                if (dismissableMessages[fsmIndex].Id === msgId) {
                    pushnx_debug.console(dismissableMessages[fsmIndex].Id, 'Search Message result', 'Message Id found at index ' + fsmIndex);
                    return fsmIndex;
                }
            }
        } else {
            pushnx_debug.console(dismissableMessages, 'Searching Messages by ID', 'messages is empty', 'warn');
        }

        return 'empty';
    };
}

export default pushnxMessage;
