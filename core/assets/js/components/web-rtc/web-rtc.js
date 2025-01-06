import xhr from "BaseVendor/reqwest";
import Storage from "Base/utils/storage";
import Console from "Base/debug/console";

/**
 * Webrtc integration class
 */
export default function Webrtc(options) {
    "use strict";

    var $this = this,
        $storage = new Storage(),
        flag = 0,
        webrtcStorage = 'webrtc.storage',
        storageData = {
            expires: 0,
            token: ''
        };

    function construct() {
        var defaults = {
            apiUrl: 'https://www.cs-livechat.com/s/jwt',
            bearerToken: '',
            validity: 1800,
            timeout: 5000,
            nonce: false,
            preFetch: false,
            postFetch: false,
            onSuccess: false,
            onFail: false
        };

        options = options || {};

        for (var name in defaults) {
            if (options[name] === undefined) {
                options[name] = defaults[name];
            }
        }

        // Clear any live chat call storage
        $storage.remove(webrtcStorage);
    }

    construct();

    /**
     * Actual request for fetching the token
     *
     * @return Void
     */
    function fetchToken() {
        // Fetch the token from webrtc api
        var xhrOptions = {
            url: options.apiUrl,
            type: 'json',
            method: 'post',
            contentType: 'text/plain',
            crossOrigin: true,
            timeout: options.timeout,
            headers: {},
            data: options.nonce
        };

        if (options.bearerToken) {
            // Set the Authorization header
            xhrOptions.headers.Authorization = options.bearerToken;
        }

        xhr(xhrOptions).then(function (response) {

            if (response.s === undefined) {
                Console.log('Fetch token connected success, but no token value');
                triggerCallback('onFail', ['empty token']);
                return;
            }

            Console.log("Fetching token successful. Token: " + response.s);
            triggerCallback('onSuccess', [response.s]);
            storeToken(response.s);

        }).fail(function (err, msg) {
            Console.log('Fetch token failed');
            triggerCallback('onFail', [err, msg]);
        }).always(function (response) {
            flag = 0;
            Console.log('Fetch token completed');
            triggerCallback('postFetch', [response]);
        });
    }

    /**
     * Store the fetched token and store it either in the current instance or in the browser
     *
     * @param  string token webrtc chat token
     * @return Void
     */
    function storeToken(token) {
        storageData = {
            expires: (options.validity * 1000),
            token: token
        };

        // Check if sessionStorage is useable
        // This will make the token availability global
        if (window.JSON !== undefined) {
            $storage.set(webrtcStorage, window.JSON.stringify(storageData));
        }
    }

    /**
     * Check if there is an existing data inside the data storage
     *
     * @return mixed token data or false
     */
    function checkStorage() {
        // Get the local instance storageData
        var data = storageData,
            time = new Date().getTime();

        // Check if there is an existing storage data in browser
        if (window.JSON !== undefined) {
            data = window.JSON.parse($storage.get(webrtcStorage)) || data;
        }

        if (time >= data.expires) {
            $storage.remove(webrtcStorage);
            return false;
        }

        return data.token;
    }

    /**
     * Call listeners to be used for applying into the template
     *
     * @param  string e Event
     * @param  Array args Arguments
     * @return void
     */
    function triggerCallback(e, args) {
        if (typeof options[e] === 'function') {
            options[e].apply($this, args);
        }
    }

    this.clearStorageData = function () {
        storageData = {
            expires: 0,
            token: ''
        };
    };

    this.setToken = function (token) {
        options.nonce = token;
    };

    this.setOnSuccess = function (success) {
        options.onSuccess = success;
    };

    this.setOnFail = function (fail) {
        options.onFail = fail;
    };

    /**
     * Trigger the fetch token
     *
     * @return void
     */
    this.getCallToken = function ($e) {
        $this.targetElement = $e;
        var token;

        // Block the event when user is spamming
        if (flag === 1) {
            Console.log("Still fetching token");
            return false;
        }

        // Nonce is only created during post-login
        if (options.nonce === false) {
            Console.log('Invalid/No nonce');
            $storage.remove(webrtcStorage);
            triggerCallback('onFail', ['invalid nonce']);
            return false;
        }

        triggerCallback('preFetch', []);

        // Token is still in storage
        if ((token = checkStorage()) !== false) {
            Console.log('Token not expired: ' + token);
            triggerCallback('onSuccess', [token]);
            flag = 0;
            return false;
        }

        // Fetch a new token
        flag = 1;
        fetchToken();
    };

    return this;
}
