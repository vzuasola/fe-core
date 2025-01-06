import * as utility from "Base/utility";

var DateFormat = require("BaseVendor/dateformat");

function pushnxHelper(options) {
    "use strict";
    var opt = options || {};

    var pushnx_debug = opt.debug || {};

    this.isExpired = function (date, msgId) {
        var self = this;

        if (!self.isDateInMilli(date, msgId)) {
            pushnx_debug.console(date, 'Is Expired', 'Message Id ' + msgId + ' is not yet expired.');
            return false;
        }

        var now = new Date();
        var utcDate = Date.UTC(
            now.getUTCFullYear(),
            now.getUTCMonth(),
            now.getUTCDate(),
            now.getUTCHours(),
            now.getUTCMinutes(),
            now.getUTCSeconds()
        );
        var expiryDate = new Date(date.match(/\d+/)[0] * 1).getTime();

        if (expiryDate && utcDate > expiryDate) {
            return true;
        }

        return false;
    };

    this.isDateInMilli = function (pushDate, msgId) {
        msgId = msgId || '';

        if (!pushDate) {
            pushnx_debug.console(pushDate, 'ExpiryDate Format', 'Invalid Date Format', 'warn');
            return false;
        }

        if (pushDate.indexOf('-') > -1) {
            pushnx_debug.console(
                pushDate,
                'ExpiryDate Format',
                'ExpiryDate format of Message Id ' + msgId + ' is based on Phase1'
            );
            return false;
        }

        return true;
    };

    this.formatDateTime = function (dateString, format, offset) {
        var self = this;

        if (!self.isDateInMilli(dateString)) {
            return '';
        }

        var date = dateString.match(/\d+/)[0] * 1;

        // Create Date object
        var d = new Date(date);
        // Add local timezone offset
        var utc = date + (d.getTimezoneOffset() * 60000);
        // Create new Date object using the offset
        var dateWithOffset = new Date(utc + (3600000 * offset));
        // Format the date

        var formattedDate = DateFormat(dateWithOffset, format);

        return formattedDate + ' (GMT' + offset + ')';
    };

    this.configUrl = function (path, option) {
        var self = this;
        var url = '', source = '';
        var param = {};
        var opt = option || {};
        var token = opt.token || false;

        // override option object via script data-pushnx attribute
        var override = self.scriptParam('pushnx');

        if (override.url) {
            opt.url = override.url;
        }

        if (override.lang) {
            opt.lang = override.lang;
        }

        if (override.product) {
            opt.product = override.product;
        }

        if (override.token) {
            token = override.token;
        }

        // set url based on option
        if (opt.url) {
            url = opt.url;
        }

        if (opt.lang) {
            param.lang = opt.lang;
        }

        if (opt.product) {
            param.product = opt.product;
        }

        if (token) {
            source += '?token=' + token;
        }

        return {
            xdomain: (url) ? true : false,
            url: url + utility.url(path, param) + source,
            token: token
        };
    };

    this.scriptParam = function (script) {
        var p = {};
        var sel = document.querySelector('script[data-pushnx]');

        if (sel) {
            var datapushnx = sel.getAttribute('data-pushnx');

            var pa = datapushnx.split("?").pop().split("&");

            for (var j = 0; j < pa.length; j++) {
                var kv = pa[j].split("=");
                p[kv[0]] = kv[1];
            }
        }

        return p;
    };

    this.validSession = function (isLogin) {
        var is404 = utility.hasClass(document.body, 'page-404');

        if (isLogin && !is404) {
            return true;
        }

        return false;
    };

    this.excludePage = function (validPath) {
        var current = window.location.href;

        if (validPath) {
            for (var i = 0; i < validPath.length; i++) {
                if (current.match(validPath[i])) {
                    return true;
                }
            }
        }

        return false;
    };

    this.setcookie = function (cname, cvalue, days) {
        var d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    };
}

export default pushnxHelper;
