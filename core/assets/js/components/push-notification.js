/*
 * Push Notification
 * Style/SASS: sass/components/_push-notification.scss
 */

import * as utility from "Base/utility";
import reqwest from "BaseVendor/reqwest";

import pushnxDebug from "Base/push-notification/debug"; // debug
import pushnxHelper from "Base/push-notification/helper"; // helper
import pushnxMessage from "Base/push-notification/message"; // message
import pushnxSocket from "Base/push-notification/socket"; // socket

var EventBus = require("BaseVendor/vertx-eventbus");

function PushNotification(option) {
    "use strict";

    var opt = option || {};
    var enable = (opt.enable === undefined) ? true : opt.enable; // default value true - auto start pushnx
    var scrollbot = (opt.scrollbot === undefined) ? true : opt.scrollbot; // default value true
    var use_modal = (opt.modal === undefined) ? {enable: true} : opt.modal; // default value true - modal is used
    var tpl = opt.template || {}; // default template core pushnx template
    var dismiss = (opt.dismiss === undefined) ? false : opt.dismiss; // default value false - dismiss is off
    var counter = (opt.counter === undefined) ? false : opt.counter; // default value false - counter is off
    var notify = (opt.notify === undefined) ? false : opt.notify; // default value false - notify is off
    var action = (opt.action === undefined) ? true : opt.action; // default value is true - action is automatic enabled
    var buttons = (opt.buttons === undefined) ? 'action' : opt.buttons; // define button styles
    var icons = (opt.icons === undefined) ? false : opt.icons; // use icon
    var iconTpl = (opt.iconsvg === undefined) ? false : opt.iconsvg; // icon template
    var config = (opt.config === undefined) ? false : opt.config; // config default value request from ajax
    var lang = (opt.lang === undefined) ? false : opt.lang;
    var lightboxNotif = (opt.lightboxNotif === undefined) ? false : opt.lightboxNotif;

    var pushnxPath = 'ajax/pushnx/config';
    var pushnxAuth = {};

    // websocket
    var push_notif_eb = {};
    var push_notif_player_address = 'chat.to.client';
    var push_notif_server_address = 'chat.to.server';

    var login = false; // login default valule false

    if (typeof app !== 'undefined'
        && typeof app.settings !== 'undefined'
        && typeof app.settings.login !== 'undefined') {
        login = app.settings.login; // if app.settings.login is available
    }

    var isLogin = (opt.islogin === undefined) ? login : opt.islogin; // default value false - can be define on param
    var token = (opt.token === undefined) ? false : opt.token; // default value false - can be define on params

    var is404 = utility.hasClass(document.body, 'page-404');

    var pushnx_socket = new pushnxSocket(); // socket
    var pushnx_support = new pushnxHelper(); // helper
    var pushnx_debug = {};
    var pushnx_global = {};
    var pushnx_message = {};

    pushnx_global.icon = icons;

    pushnx_global.css = {
        dateExpired: 'debug-expired',
        dateActive: 'debug-active',
        actionClass: buttons,
        actionId: '',
        expiredMessage: 'expired'
    };

    pushnx_global.label = {
        modal_title: 'Notifications',
        expiration_date: 'Expiration Date: '
    };

    /**
     * Start Push Notification
     */
    this.enable = function () {
        if (lang) {
            opt.lang = lang;
        }

        pushnxAuth = pushnx_support.configUrl(pushnxPath, opt);

        if (pushnxAuth.token || token) {
            isLogin = true;
        }

        if (!is404 && isLogin) {
            if (config && config.enabled) {
                var eventBus, replyUri;

                var override = {};
                override = config;

                if (pushnx_socket.hasWebsocket()) {
                    eventBus = config.connection.socket.eventBus;
                    replyUri = config.connection.socket.replyUri;
                } else {
                    eventBus = config.connection.fallback.eventBus;
                    replyUri = config.connection.fallback.replyUri;
                }

                override.eventBus = eventBus;
                override.replyUri = replyUri;

                this.overrideDefault(override);
            } else if (!config) {
                this.useDefault();
            }
        }
    };

    /**
     * define config
     * @param configuration object
     */
    this.overrideDefault = function (settings) {
        if (settings.enabled) {
            this.bindService(settings);
        }
    };

    /**
     * fetch config via ajax
     */
    this.useDefault = function () {
        var self = this;

        var params = {
            ws: pushnx_socket.hasWebsocket(),
            path: encodeURIComponent('/'),
            t: Date.now()
        };

        if (pushnxAuth.token || token) {
            params.token = token;
        }

        reqwest({
            url: pushnxAuth.url,
            type: 'json',
            crossOrigin: pushnxAuth.xdomain,
            data: params,
            complete: function (res) {
                if (res.enabled) {
                    self.bindService(res);
                }
            }
        });
    };

    /**
     * start configurations and services
     * @param fetched config
     */
    this.bindService = function (settings) {
        pushnx_global.settings = settings;
        // translations contents
        pushnx_global.translations = pushnx_global.settings.texts;
        // excluded pages
        pushnx_global.excluded = pushnx_global.settings.excludedPages;
        // cta buttons
        pushnx_global.cta = pushnx_global.settings.cta;
        // mapped domains
        pushnx_global.domains = pushnx_global.settings.pushnx_domains;

        pushnx_global.lightboxNotif = lightboxNotif;

        // translations dismiss notifications
        if (dismiss) {
            pushnx_global.dismiss = pushnx_global.settings.dismiss;
        }

        // counter
        if (counter) {
            pushnx_global.counter = counter;
        }

        // new message indicator
        if (notify) {
            pushnx_global.notify = notify;
        }

        // helpers
        pushnx_debug = new pushnxDebug(pushnx_global.settings.logging, notify); // console logs
        pushnx_debug.console(settings, 'Settings');
        pushnx_support = new pushnxHelper({
            debug: pushnx_debug
        });

        if (pushnx_support.excludePage(pushnx_global.excluded)) {
            pushnx_debug.console(pushnx_global.excluded, 'Excluded Page');
            return;
        }

        // start socket
        push_notif_eb = new EventBus(pushnx_global.settings.eventBus, {
            vertxbus_ping_interval: 5000,
            transports: ["websocket", "xhr-polling", "iframe-xhr-polling"]
        });

        pushnx_socket = new pushnxSocket();

        // message
        pushnx_message = new pushnxMessage({
            global: pushnx_global,
            modal: use_modal,
            scrollbot: scrollbot,
            console: pushnx_debug,
            support: pushnx_support,
            socket: pushnx_socket,
            dismiss: dismiss,
            template: tpl,
            icontemplate: iconTpl,
            eb: push_notif_eb,
            server: push_notif_server_address,
            islogin:isLogin
        });

        // actions
        if (action) {
            pushnx_message.action();
        }

        // connect socket
        pushnx_socket.connect({
            console: pushnx_debug,
            global: pushnx_global,
            message: pushnx_message,
            eb: push_notif_eb,
            playerServer: push_notif_player_address,
            notifServer: push_notif_server_address
        });
    };

    /**
     * custom event to trigger "pushnx.close" close socket connection
     */
    this.bindCloseService = function () {
        utility.triggerEvent(document, 'pushnx.close', {
            close: true
        });
    };

    /**
     * custom event to trigger "pushnx.action" bind message cta button
     */
    this.bindAction = function () {
        if (pushnx_message.action !== undefined) {
            pushnx_message.action();
        }
    };

    /**
     * close modal [manual]
     */
    this.closeModal = function () {
        var $modalId = document.getElementById('pushnxLightbox');
        var $msgWrapper = document.querySelector('.messages');

        if ($modalId && $msgWrapper) {
            utility.addClass($modalId, "modal-close");
            utility.removeClass($modalId, "modal-active");
        }
    };

    /**
     * open modal [manual]
     */
    this.openModal = function () {
        var $modalId = document.getElementById('pushnxLightbox');
        var $msgWrapper = document.querySelector('.messages');

        if ($modalId && $msgWrapper) {
            utility.removeClass($modalId, "modal-close");
            utility.addClass($modalId, "modal-active");
        }
    };

    /**
     * listen to message status
     */
    this.listenToMessage = function () {
        utility.addEventListener(document, 'pushnx.message', this.readyMessage);
    };

    /**
     * broadcast message is ready
     */
    this.readyMessage = function (e) {
        utility.triggerEvent(document, 'pushnx.message.ready', {
            ready: e.customData.ready
        });
    };

    /**
     * unbind event listeners
     */
    this.unbindEvents = function () {
        utility.removeEventListener(document, 'pushnx.message', this.readyMessage);

        if (pushnx_message.unbindAction !== undefined) {
            pushnx_message.unbindAction();
        }

        if (pushnx_socket.unbindSocketClose !== undefined) {
            pushnx_socket.unbindSocketClose();
        }
    };

    /**
     * enable push notification
     * enable default value - true
     */
    if (enable) {
        this.enable();
    }

    // listeners
    if (isLogin) {
        this.listenToMessage();
    } else {
        this.unbindEvents();
    }
}

export default PushNotification;
