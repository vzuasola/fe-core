/**
 *
 */
module.exports = function (path) {
    path = path.trim('/');

    if (path.indexOf('internal:/') === 0) {
        path = path.slice('internal:/'.length);
    }

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

    var lang = app.settings.lang,
        product = app.settings.product,
        url = '/' + lang;

    if (product) {
        url += '/' + product;
    }

    url += '/' + path;

    return url;
};
