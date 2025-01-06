import xhr from "BaseVendor/reqwest";
import * as utility from "Base/utility";
import Console from "Base/debug/console";

require('BaseVendor/easyXDM');

/**
 * Provides functionality to share the session across multiple domain by
 * retrieving the SSO server's unique session ID
 */
function SessionShare() {
    var cookie = 'ssotimestamp',
        enable = app.settings.sso_enable,
        endpoint = app.settings.sso_endpoint,
        domain = app.settings.sso_domain;

    /**
     *
     */
    this.init = function () {
        var server = isServer();

        if (enable && endpoint && !server) {
            validateSession();
        }
    };

    /**
     *
     */
    function isServer() {
        var hostname = window.location.hostname;

        return domain === hostname;
    }

    /**
     *
     */
    function validateSession() {
        new window.easyXDM.Socket({
            remote: endpoint + '/api/sso/xdm',
            onMessage: function (message, origin) {
                Console.log('SSO: Validating Session');

                var response = JSON.parse(message);

                if (typeof response.id !== 'undefined' && response.id) {
                    activateSession(response.id);
                }
            }
        });
    }

    function activateSession(id) {
        xhr({
            url: utility.url('/api/sso/validate'),
            type: 'json',
            method: 'post',
            data: {
                id: id
            },
        }).then(function (response) {
            switch (true) {
                case response.status === 100:
                    utility.removeCookie(cookie);
                    Console.log('SSO: Removing Cookie');
                    break;

                case response.status === 200:
                    Console.log('SSO: Cookie Good');
                    break;
            }
        });
    }
}

var sso = new SessionShare();
sso.init();
