/**
 * The Url generator component
 */
export default (function () {
    var Router = {};

    /**
     * Prepend the language and product to the url
     *
     * @param string path The url relative path to generate
     *
     * @return string
     */
    Router.url = function (path, option) {
        var lang;
        var product;

        var opt = option || {};
        path = path.trim('/');

        // check if it is external
        if (path.indexOf('http://') === 0) {
            return path;
        }

        if (path.indexOf('https://') === 0) {
            return path;
        }

        // remove trailing first slash
        if (path.charAt(0) === '/') {
            path = path.slice(1);
        }

        if (path.indexOf('internal:/') === 0) {
            path = path.slice('internal:/'.length);
        }

        if (opt.lang) {
            lang = opt.lang;
        } else if (typeof app !== 'undefined'
            && typeof app.settings !== 'undefined'
            && typeof app.settings.lang !== 'undefined') {
            lang = app.settings.lang;
        } else {
            lang = 'en';
        }

        if (typeof app !== 'undefined'
            && typeof app.settings !== 'undefined'
            && typeof app.settings.product !== 'undefined') {
            product = app.settings.product;
        } else {
            product = opt.product || false;
        }

        var url = '/' + lang;

        if (product) {
            url += '/' + product;
        }

        url += '/' + path;

        if (Router.isApiCall()) {
            url = Router.getBaseUrl() + url;
        }

        return url;
    };

    /**
     * Converts path to absolute
     *
     * @param string
     *
     * @return string
     */
    Router.toAbsolute = function (path) {
        var link = document.createElement("a");

        link.href = path;

        return link.href;
    };

    /**
     * Check if a url is external or not
     *
     * @param string path The url relative path to generate
     *
     * @return boolean
     */
    Router.isExternal = function (url) {
        var pattern = /https?:\/\/((?:[\w\d-]+\.)+[\w\d]{2,})/i;
        var match = pattern.exec(url);

        if (match) {
            return pattern.exec(location.href)[1] !== match[1];
        }
    };

    /**
     * Asset generation function
     *
     * @param string path The asset relative path to generate
     *
     * @return string
     */
    Router.asset = function (path) {
        var prefixed = app.settings.prefixed,
            basePath = "/",
            lang = app.settings.lang || app.settings.defaultLang,
            product = app.settings.defaultProduct;

        // Remove the the / on first
        if (path.indexOf('/') === 0) {
            path = path.substr(1);
        }

        // Construct the base path for the asset URL
        if (prefixed) {
            basePath = ["/", lang, "/", product, "/"].join('');
        }

        // Check if it's the api call
        if (Router.isApiCall()) {
            basePath = Router.getBaseUrl() + basePath;
        }

        basePath += path;

        return basePath;
    };

    /**
     * Function to get specific sting's value from URL query parameter
     *
     * @param string name The parameter name to fetch
     * @param string url The url string
     *
     * @return string
     */
    Router.getParameterByName = function (name, url) {
        if (!url) {
            url = window.location.href;
        }

        name = name.replace(/[\[\]]/g, "\\$&");

        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);

        if (!results) {
            return null;
        }

        if (!results[2]) {
            return '';
        }

        return decodeURIComponent(results[2].replace(/\+/g, " "));
    };

    /**
     * Get the list of parameters from a string
     *
     * @param string url The url string
     *
     * @return object
     */
    Router.getParameters = function (url) {
        var params = {};
        var parser = document.createElement('a');

        parser.href = url;

        var query = parser.search.substring(1);
        var vars = query.split('&');

        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            params[pair[0]] = decodeURIComponent(pair[1]);
        }

        return params;
    };

    /**
     * Get the text after the hash of the url
     *
     * @param string url
     *
     * @return string
     */
    Router.getHash = function (url) {
        var hashPos = url.lastIndexOf('#');
        return url.substring(hashPos + 1);
    };

    /**
     * Function to add specific query parameter to the URL query string
     *
     * @param string url current url to be appended
     * @param string param parameter key
     * @param string value paramter value
     */
    Router.addQueryParam = function (url, param, value) {
        var a = document.createElement('a'),
            regex = /(?:\?|&amp;|&)+([^=]+)(?:=([^&]*))*/g,
            match = null,
            str = [];
        a.href = url;
        param = encodeURIComponent(param);
        while ((match = regex.exec(a.search)) !== null) {
            if (param !== match[1]) {
                str.push(match[1] + (match[2] ? "=" + match[2] : ""));
            }
        }
        str.push(param + (value ? "=" + encodeURIComponent(value) : ""));
        a.search = str.join("&");
        return a.href;
    };

    /**
     * Function to remove specific query parameter to the URL query string
     *
     * @param string url current url to be modified
     * @param string param parameter key
     */
    Router.removeQueryParam = function (url, param) {
        var urlparts = url.split('?');

        if (urlparts.length >= 2) {

            var prefix = encodeURIComponent(param) + '=';
            var pars = urlparts[1].split(/[&;]/g);

            for (var i = pars.length; i-- > 0;) {
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                    pars.splice(i, 1);
                }
            }

            url = urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');

            return url;
        } else {
            return url;
        }
    };

    Router.removeHash = function (url) {
        return url.split('#')[0];
    };

    Router.addHash = function (url, hash) {
        return url.split('#')[0] + '#' + hash;
    };

    /**
     * Get Base url
     */
    Router.getBaseUrl = function () {
        return document.body.getAttribute("data-base-url");
    };

    /**
     * Check if API call
     */
    Router.isApiCall = function () {
        if (document.body.getAttribute('data-render-api')) {
            return true;
        }
        return false;
    };


    return Router;
})();
