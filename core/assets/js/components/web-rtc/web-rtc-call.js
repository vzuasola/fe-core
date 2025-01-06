import * as utility from 'Base/utility';
import PopupWindow from 'Base/utils/popup';
import xhr from "BaseVendor/reqwest";
import Webrtc from './web-rtc';

function WebrtcCall(config) {
    'use strict';

    var webrtcClass = null,
        windowTitle = 'webrtcWindow',
        windowObject = null,
        webrtcLink = '',
        urlQryStr = false,
        dataSrc = false,
        options = {
            apiUrl: config.url_post,
            bearerToken: config.bearer_token,
            validity: config.validity_time,
            nonce: config.jwt_token || false,
            timeout: config.url_post_timout || 5000,
            onSuccess: function (token) {
                // Add the token to the base url
                updatePopupWindow(utility.addQueryParam(config.base_url, 's', token));
            },
            onFail: function (error) {
                // Use the default webrtc base url
                updatePopupWindow(config.base_url);
            }
        },
        callUsUrl;

    function updatePopupWindow(url) {
        try {
            var updatedUrl = url;

            if (windowObject.location.href !== 'about:blank' &&
                url === webrtcLink &&
                !windowObject.closed
            ) {
                windowObject.focus();
            } else {
                if (urlQryStr) {
                    var checkUrl = url.indexOf('?');

                    if (checkUrl !== -1) {
                        updatedUrl = url + '&' + urlQryStr;
                    } else {
                        updatedUrl = url + '?' + urlQryStr;
                    }
                }

                if (dataSrc) {
                    updatedUrl = updatedUrl.replace('mc-desktop', dataSrc);
                }

                webrtcLink = url;
                windowObject.location.replace(updatedUrl);
            }
        } catch (e) {
            if (windowObject) {
                windowObject.focus();
            }
        }
    }

    /**
    * Event listener for the webrtc link
    * @param  object event
    * @return void/boolean
    */
    function getWebrtcToken(event) {
        var evt = event || window.event,
            $target = evt.target || evt.srcElement;

        // Get parent Anchor if target is inside of anchor
        if ($target.tagName !== 'A' && ($target.parentNode !== null && $target.parentNode.tagName === 'A')) {
            $target = $target.parentNode;
        }

        // Check if the link should be changed to webrtc link
        if ($target.href !== undefined && ($target.href.indexOf('www.cs-livechat.com/call') !== -1)) {
            evt.preventDefault();
            callUsUrl = $target.href;

            urlQryStr = $target.getAttribute('data-parameters') || false;
            dataSrc = $target.getAttribute('data-src') || false;

            var target = utility.getParameterByName('target', $target.href);

            if (target === '_self') {
                // Same tab
                windowObject = window;
            } else if (target === '_blank') {
                // New tab
                windowObject = window.open('', '_blank');
            } else {
                // Popup
                // We use a different data attribute for the popup,
                // since popup-window.js is already using the target=window
                var title = utility.getParameterByName('title', $target.href);
                title = title || ($target.getAttribute('data-popup-title') || windowTitle);

                var prop = popUpProperties($target);
                try {
                    if (windowObject &&
                        !windowObject.closed &&
                        windowObject.location.href !== 'about:blank'
                    ) {
                        windowObject.focus();
                    } else {
                        windowObject = PopupWindow('', title, prop);
                    }
                } catch (e) {
                    if (windowObject) {
                        windowObject.focus();
                    }
                }
            }

            getJWT(function (response) {
                webrtcClass.getCallToken($target);
                return false;
            });
        }
    }

    function getJWT(callback) {
        xhr({
            url: utility.url('ajax/avaya/jwt'),
            type: 'json',
            method: 'get',
            contentType: 'text/plain',
            crossOrigin: true,
            timeout: options.timeout,
        }).then(function (response) {
            webrtcClass.clearStorageData();
            webrtcClass.setToken(response.jwt);
            webrtcClass.setOnSuccess(function (token) {
                updatePopupWindow(utility.addQueryParam(callUsUrl, "s", token));
            });
            webrtcClass.setOnFail(function (error) {
                updatePopupWindow(callUsUrl);
            });
            callback(response);
        }).fail(function (err, msg) {
            // do nothing
        });
    }

    /**
     * Method to prepare the popup properties
     *
     * @param  object $target Element to check
     * @return object
     */
    function popUpProperties($target) {
        var defaults = {
                'width': 360,
                'height': 720,
                'scrollbars': 1,
                'scrollable': 1,
                'resizable': 1
            },
            properties = {};

        // Check the properties and get all possible values
        for (var i in defaults) {
            var property = utility.getParameterByName(i, $target.href) || ($target.getAttribute('data-popup-' + i) || defaults[i]);
            properties[i] = property;
        }

        return properties;
    }

    /**
    * Initialize everything
    */
    this.init = function () {
        webrtcClass = new Webrtc(options);
        var eventType = utility.eventType();
        utility.addEventListener(document, eventType, getWebrtcToken);
    };

    return this;
}

/**
 * This will implement the webrtc.js that is a standard component
 * Presentation layer
 *
 * @return void
 */
utility.ready(function () {

    // If live chat config are available
    if (app.settings.liveChatConfig === undefined) {
        return;
    }

    if (app.settings.liveChatConfig.avaya !== undefined) {
        var webrtcChat = new WebrtcCall(app.settings.liveChatConfig.avaya);
        webrtcChat.init();
    }
});
