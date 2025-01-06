import * as utility from "Base/utility";
// import PopupWindow from "Base/utils/popup";

function pushnxActionHook(options) {
    "use strict";

    var opt = options || {};
    var pushnx_debug = opt.debug || {};
    var pushnx_global = opt.global || {};
    var dontBindClick = ['avaya'];
    var toAutoAcknowledge = 'a[href="#"]';
    var self = this;

    /**
     * Check content if hook exists
     */
    this.contentHook = function (ids) {
        if (ids.length) {
            for (var z = 0; z < ids.length; z++) {
                // set auto-acknowledge
                self.setAutoAcknowledge(ids[z]);
                // bind content link to cta request
                self.bindLinkToAcknowledge(ids[z]);
                // bind content action
                self.bindContentHook(ids[z]);
            }
        }
    };

    this.bindContentHook = function (id) {
        var contentAction = document.querySelectorAll('#message' + id + ' .content [data-action]');

        for (var i = 0; i < contentAction.length; i++) {
            var element = contentAction[i];
            var isAutoAcknowledge = element.getAttribute('data-auto-acknowledge');

            if (element && !isAutoAcknowledge) {
                element.setAttribute('data-hook-id', id);

                self.customDataAttribute(id, element, 'data-custom-attribute');

                var action = element.getAttribute('data-action');

                var setaction = self.extractAction(action);
                pushnx_debug.console(setaction, 'Set action', setaction);

                if (setaction.target && !utility.inArray(setaction.target, dontBindClick)) {
                    utility.addEventListener(document.body, 'click', self.processContent);
                    pushnx_debug.console(element, 'Bind content action Hook', id);
                } else {
                    self.addDataParameters(id, element);
                }
            }
        }
    };

    this.setAutoAcknowledge = function (id) {
        var links = document.querySelectorAll('#message' + id + ' .content ' + toAutoAcknowledge);

        for (var i = 0; i < links.length; i++) {
            if (links[i]) {
                var auto = links[i].getAttribute('data-auto-acknowledge');

                if (!auto) {
                    links[i].setAttribute('data-auto-acknowledge', true);
                }
            }
        }
    };

    this.bindLinkToAcknowledge = function (id) {
        var atrigger = document.querySelectorAll('#message' + id + ' .content [data-auto-acknowledge="true"]');

        if (atrigger) {
            for (var x = 0; x < atrigger.length; x++) {
                var el = atrigger[x];

                if (el) {
                    var target = document.querySelector('#message' + id + ' [data-default-acknowledge="true"]');

                    // get data attribute of default cta button
                    var actionId = target.getAttribute('data-id');
                    var msgId = target.getAttribute('data-msg-id');
                    var src = target.getAttribute('data-msg-src');

                    // set data attribute of cta to link
                    el.setAttribute('data-id', actionId);
                    el.setAttribute('data-msg-id', msgId);
                    el.setAttribute('data-msg-src', src);

                    var actionOverride = el.getAttribute('data-action-override');

                    if (actionOverride === null) {
                        el.setAttribute('data-action-override', false);
                    }

                    pushnx_debug.console(el, 'Set data id', actionId);
                    pushnx_debug.console(el, 'Set data msg-id', msgId);
                    pushnx_debug.console(el, 'Set data msg-src', src);
                    pushnx_debug.console(el, 'Set default data action-override', false);
                }
            }
        }
    };

    this.addDataParameters = function (msgId, element) {
        var dataId = element.getAttribute('data-id');
        var customParam = self.overrideCTA(dataId, msgId, 'data-parameters');

        if (customParam) {
            element.setAttribute('data-parameters', customParam);
            pushnx_debug.console(element, 'Set data parameters', customParam);
        }
    };

    /**
     * Set custom data attribtue
     */
    this.customDataAttribute = function (msgId, element, dataAttr) {
        var dataId = element.getAttribute('data-id');

        var customParam = self.overrideCTA(dataId, msgId, dataAttr);

        if (customParam) {
            var attr = customParam.split('&');

            for (var a = 0; a < attr.length; a++) {
                if (attr[a].indexOf('=')) {
                    var custom = attr[a].split('=');

                    if (custom[0] !== undefined && custom[1] !== undefined) {
                        element.setAttribute(custom[0], custom[1]);
                        pushnx_debug.console(element, 'Set data attribute', custom[0] + ' ' + custom[1]);
                    }
                }
            }
        }
    };

    /**
     * Process content links upon click
     */
    this.processContent = function (evt) {
        // Cross browser event
        evt = evt || window.event;
        // get srcElement if target is falsy (IE8)
        var target = evt.target || evt.srcElement;

        var isCTA = utility.hasClass(target, 'action');
        pushnx_debug.console(target, 'is CTA button and hasClass .action', isCTA);

        if (!isCTA) {
            var dataId = target.getAttribute('data-id');
            var id = target.getAttribute('data-hook-id');
            var action = target.getAttribute('data-action');
            var params = target.getAttribute('data-parameters');

            pushnx_debug.console(target, 'dataId: ' + dataId + ' id: ' + id + ' action: ' + action + ' params: ' + params);

            var setaction = self.extractAction(action);

            self.actionHook(id, setaction, params, target);
        }
    };

    /**
     * Do CTA hook when response receive from reply service
     */
    this.doActionHook = function (param, callback) {
        var setaction = self.extractAction(param.action);
        var doOverride = (param.ctaride && param.ctaride === 'false') ? false : true;
        var params = '';

        pushnx_debug.console(param.ctaride, 'Lets do override CTA ' + doOverride, doOverride);

        if (doOverride) {
            params = self.overrideCTA(param.actionId, param.id, 'data-parameters');
        } else {
            params = param.target.getAttribute('data-parameters');
        }

        // can proceed to actionHook if button with 200 response OR not a button
        if ((param.ctaButton && param.status === 200) || (!param.ctaButton)) {
            // proceed with action
            pushnx_debug.console(setaction, 'is target exists and on list to bind', dontBindClick);

            if (setaction.target && !utility.inArray(setaction.target, dontBindClick)) {
                self.actionHook(param.id, setaction, params);
            }
        }

        callback();
    };

    /**
     * Process CTA Hook
     */
    this.actionHook = function (msgId, action, params, targetElement) {
        var targetEl = targetElement || false;
        pushnx_debug.console(action, 'Action Hook', 'Message id: ' + msgId);

        if (action) {
            var key = action.key;
            var target = action.target;

            switch (key) {
                case 'copy':
                    pushnx_debug.console(key, 'Action Hook', 'Message id: ' + msgId + ', target: ' + target);
                    self.doCopy(msgId, target);
                    self.showTooltip(msgId, target, targetEl);
                    break;
                case 'redirect':
                    pushnx_debug.console(key, 'Action Hook', 'Message id: ' + msgId + ', target: ' + target);
                    self.doRedirect(msgId, target, params, '_self');
                    break;
                case 'popup':
                    pushnx_debug.console(key, 'Action Hook', 'Message id: ' + msgId + ', target: ' + target);
                    self.doPopup(msgId, target, params);
                    break;
                case 'newtab':
                    pushnx_debug.console(key, 'Action Hook', 'Message id: ' + msgId + ', target: ' + target);
                    self.doRedirect(msgId, target, params, '_blank');
                    break;
            }
        }
    };

    /**
     * showCopyMessage
     */
    this.showTooltip = function (msgId, targetElementId, appendParentElement) {
        var globalNotif = false;

        if (pushnx_global.lightboxNotif) {
            globalNotif = document.querySelector(pushnx_global.lightboxNotif);
        }

        var lightboxMsg = globalNotif || appendParentElement;

        if (!lightboxMsg) {
            return;
        }

        var copyMsg = pushnx_global.translations.copy_to_clipboard || 'Copied to Clipboard';

        var span = document.createElement('span');
        span.className = 'tooltip';
        span.id = 'tooltip' + msgId + targetElementId;

        var copyMessage = document.createTextNode(copyMsg);
        span.appendChild(copyMessage);

        if (document.querySelector('#tooltip' + msgId + targetElementId)) {
            utility.removeClass(document.querySelector('#tooltip' + msgId + targetElementId), 'hidden');
        } else {
            lightboxMsg.appendChild(span);
        }

        setTimeout( function () {
            if (document.querySelector('#tooltip' + msgId + targetElementId)) {
                utility.addClass(document.querySelector('#tooltip' + msgId + targetElementId), 'hidden');
            }
        }, 3000);
    };

    /**
     * extract action and key action::key
     */
    this.extractAction = function (action) {
        if (action && action.indexOf('::') !== -1) {
            var arr = action.split('::');
            pushnx_debug.console(arr, 'Action Hook data');

            if (arr[0] !== undefined && arr[1] !== undefined) {
                return {key: arr[0], target:arr[1]};
            }
        }

        return {key: false, target: false};
    };

    /**
     * mapped domain key on config
     */
    this.mappedKey = function (key) {
        pushnx_debug.console(key, 'Domain Mapped ' + pushnx_global.domains[key]);
        return pushnx_global.domains[key] || key;
    };

    /**
     * CTA button hook
     */
    this.doCopy = function (id, target) {
        var select = document.querySelector('#message' + id + ' #' + target);
        pushnx_debug.console(select, 'Action Hook target ' + target);

        if (select) {
            var input = document.createElement("input");
            input.type = "text";
            input.style.position = 'absolute';
            input.style.left = '-10000px';
            input.style.right = '-10000px';
            input.id = 'copy' + id;
            input.value = select.innerHTML;

            document.querySelector("#message" + id).appendChild(input);

            var toselect = document.getElementById('copy' + id);
            pushnx_debug.console(toselect, 'Action Hook copy target ' + toselect);
            toselect.select();

            if (document.execCommand('copy')) {
                toselect.remove();
            }

            return document.execCommand('copy');
        }

        return false;
    };

    this.doPopup = function (id, target, params) {
        var urltarget = self.mappedKey(target);
        var buildUrl = self.buildUrl(urltarget, params);

        pushnx_debug.console(buildUrl, 'action hook do Popup ');

        var url_width = utility.getParameterByName('width', urltarget),
            url_height = utility.getParameterByName('height', urltarget),
            w, h;

        w = url_width || 820;
        h = url_height || 700;

        var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : screen.left,
            dualScreenTop = window.screenTop !== undefined ? window.screenTop : screen.top,

            width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width,
            height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height,

            left = ((width / 2) - (w / 2)) + dualScreenLeft,
            top = ((height / 2) - (h / 2)) + dualScreenTop,
            newWindow = window.open(buildUrl, '', 'scrollbars=1,toolbar=0,menubar=0,location=0,resizable=1,status=1, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        if (window.focus()) {
            newWindow.focus();
        }
    };

    this.doRedirect = function (id, target, params, type) {
        var urltarget = self.mappedKey(target);
        var buildUrl = self.buildUrl(urltarget, params);
        pushnx_debug.console(buildUrl, 'action hook do Redirect ');
        var win = window.open(buildUrl, type);
        win.focus();
    };

    /**
     * Construct URL
     */
    this.buildUrl = function (url, param) {
        var newparam = '';

        if (param) {
            newparam = param;
        }

        if (url.indexOf('?') !== -1 && newparam !== '') {
            return url + '&' + newparam;
        } else if (url.indexOf('?') === -1 && newparam !== '') {
            return url + '?' + newparam;
        }

        return url;
    };

    /**
     * Check override element
     */
    this.overrideCTA = function (actionId, msgId, dataParam) {
        var rideParams = false;
        var override = document.querySelector('#message' + msgId + ' .content [data-id-override="' + actionId + '"]');

        if (override) {
            rideParams = override.getAttribute(dataParam);
        }

        pushnx_debug.console(override, 'override params ' + rideParams);

        return rideParams;
    };
}

export default pushnxActionHook;
