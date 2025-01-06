/**
 * Execute a popup window
 *
 * @param string url The url of the page to popup
 * @param array options
 */
export default function PopupWindow(url, title, options) {
    var defaults = {
        'toolbar': 'no',
        'location': 'no',
        'directories': 'no',
        'status': 'no',
        'menubar': 'no',
        'scrollbars': 'yes',
        'resizable': 'yes',
        'copyhistory': 'no',
    };

    options = options || {};

    for (var name in defaults) {
        if (options[name] === undefined) {
            options[name] = defaults[name];
        }
    }

    var template = [];

    for (var option in options) {
        if (options[option] !== undefined && options[option] !== '') {
            template.push(option + '=' + options[option]);
        }
    }

    var popup = window.open(url, title, template);

    if (window.focus) {
        try {
            popup.focus();
        } catch (e) {
            // do nothing
        }
    }

    return popup;
}
