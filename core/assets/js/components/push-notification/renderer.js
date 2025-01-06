import * as utility from "Base/utility";
// Template
import pushnxTemplate from "Base/push-notification/template";

function pushnxRender(option) {
    "use strict";

    var tpl = option || {};

    var template = new pushnxTemplate(tpl);

    this.dismissAll = function (option) {
        return template.dismissAllTemplate({
            message: option.message,
            yes: option.yes,
            no: option.no
        });
    };

    this.expirationDate = function (option) {
        if (option.displayExpiryDate) {
            return template.expirationDateTemplate({
                debugClass: option.debugClass,
                expirationLabel: option.label,
                formattedDate: option.formattedDate,
                Phase1Date: option.Phase1Date
            });
        }

        return '';
    };

    this.expiredMessage = function (option) {
        if (option.isexpired) {
            return template.expiredMessageTemplate({
                isexpired: option.isexpired,
                messageClass: option.messageClass,
                message: option.message
            });
        }

        return option.isexpired;
    };

    this.messageAction = function (option) {
        var allowToAuto = ['proceed'];
        var msgActions = '';
        var objLen = option.actions.length;

        for (var actionIndex = 0; actionIndex < objLen; actionIndex++) {
            var actionText = option.actions[actionIndex].Name;

            if (actionText !== undefined) {
                var action_key = actionText.toLowerCase();
                var actClass = 'action';
                var autoAcknowledge = false;

                if (utility.inArray(action_key, allowToAuto)) {
                    autoAcknowledge = true;
                }

                if (!(typeof option.cta.buttons[action_key] === 'undefined'
                    || option.cta.buttons[action_key] === null)) {

                    if (option.actionClass[action_key] !== undefined) {
                        actClass = option.actionClass[action_key];
                    }

                    var data = {
                        'class': actClass,
                        'id': option.actionId,
                        'messageId': option.id,
                        'actionId': option.actions[actionIndex].Id,
                        'actionKey': option.cta.buttons[action_key].label,
                        'action': option.cta.buttons[action_key].action,
                        'source': option.src,
                        'acknowledge': autoAcknowledge
                    };

                    msgActions += template.actionTemplate(data);
                }
            }
        }

        return msgActions;
    };

    this.messageTitle = function (option) {
        return template.titleTemplate({
            title: option.title,
            // icon: option.icon
        });
    };

    this.pushMessage = function (option) {
        return template.messageTemplate({
            messageId: option.messageId,
            icon: option.icon,
            genericIcon: option.genericIcon,
            title: option.title,
            content: option.content,
            expiryDate: option.expiryDate,
            actions: option.actions,
            publishDate: option.publishDate,
            expirationLabel: option.expirationLabel
        });
    };

    this.pushTemplate = function (option) {
        return template.bodyTemplate({
            title: option.title,
            messages: option.messages || {},
            dismiss: option.dismiss
        });
    };

    this.messageIcon = function (option) {
        return template.iconTemplate({
            icon: option.icon
        });
    };
}

export default pushnxRender;
