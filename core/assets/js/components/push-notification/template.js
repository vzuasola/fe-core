// Default template
import tplBody from "BaseTemplate/handlebars/pushnx/body.handlebars";
import tplAction from "BaseTemplate/handlebars/pushnx/action.handlebars";
import tplMessage from "BaseTemplate/handlebars/pushnx/message.handlebars";
import tplExpirationDate from "BaseTemplate/handlebars/pushnx/expiration.date.handlebars";
import tplExpiredMessage from "BaseTemplate/handlebars/pushnx/expired.message.handlebars";
import tplDismissMessage from "BaseTemplate/handlebars/pushnx/dismiss.message.handlebars";
import tplTitleMessage from "BaseTemplate/handlebars/pushnx/title.message.handlebars";

function pushnxTemplate(tpl) {
    "use strict";

    // default desktop template
    var template = {
        body: tpl.template.body || false,
        action: tpl.template.action || false,
        message: tpl.template.message || false,
        title: tpl.template.title || false,
        expirationDate: tpl.template.expirationDate || false,
        expiredMessage: tpl.template.expiredMessage || false,
        dismissAllMessage: tpl.template.dismissAllMessage || false,
        icon: tpl.icon || false,
    };

    this.bodyTemplate = function (data) {
        return (!template.body) ? tplBody(data) : template.body(data);
    };

    this.actionTemplate = function (data) {
        return (!template.action) ? tplAction(data) : template.action(data);
    };

    this.messageTemplate = function (data) {
        return (!template.message) ? tplMessage(data) : template.message(data);
    };

    this.titleTemplate = function (data) {
        return (!template.title) ? tplTitleMessage(data) : template.title(data);
    };

    this.expirationDateTemplate = function (data) {
        return (!template.expirationDate) ? tplExpirationDate(data) : template.expirationDate(data);
    };

    this.expiredMessageTemplate = function (data) {
        return (!template.expiredMessage) ? tplExpiredMessage(data) : template.expiredMessage(data);
    };

    this.dismissAllTemplate = function (data) {
        return (!template.dismissAllMessage) ? tplDismissMessage(data) : template.dismissAllMessage(data);
    };

    this.iconTemplate = function (data) {
        var prod = data.icon;
        if (template.icon && template.icon[prod]) {
            return template.icon[prod](data);
        }
    };
}

export default pushnxTemplate;
