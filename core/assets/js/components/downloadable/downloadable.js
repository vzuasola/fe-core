import Platform from "Base/utils/platform";
/**
 * Downloadable class
 */
export default function Downloadable(options) {
    "use strict";

    var timerId = 0,
        platform = '';

    /**
     * Prepare options
     */
    function construct() {
        var defaults = {
            files: [],
        };
        options = options || {};
        for (var name in defaults) {
            if (options[name] === undefined) {
                options[name] = defaults[name];
            }
        }
        platform = new Platform();
    }
    construct();

    /**
     * Trigger browser download
     * NOTE: Behavior of download will be on a per browser basis and browser setting
     */
    function triggerDownloadEvent(file) {
        if (typeof file === 'object' && typeof file.platforms.join === 'function') {
            if (file.platforms.join(' ').indexOf(platform.getPlatform()) !== -1) {
                file = file.file;
            } else {
                return;
            }
        }

        // create link
        var a = document.createElement('a');
        a.href = file;
        a.target = '_parent';

        // Add a to the doc for click to work.
        (document.body || document.documentElement).appendChild(a);
        if (a.click) {
            // The click method is supported by most browsers.
            a.click();
        }

        // Delete the temporary link.
        a.parentNode.removeChild(a);
    }

    function triggerMultipleDownload(idx) {
        idx = idx || 0;

        if (idx >= options.files.length) {
            // Clear any timout
            window.clearTimeout(timerId);
            return;
        }
        var file = options.files[idx] || "#";
        triggerDownloadEvent(file);

        // Download the next file with a small timeout. The timeout is necessary
        // for IE, which will otherwise only download the first file.
        timerId = window.setTimeout(function () {
            triggerMultipleDownload(idx + 1);
        }, 1000);
    }

    /**
     * Trigger single download event
     * @param  string file File path
     * @return Void
     */
    this.single = function (file) {
        // Add some slight delay
        window.setTimeout(function () {
            triggerDownloadEvent(file);
        }, 1000);
    };

    /**
     * Process multiple virus-like downloads
     * @param  int idx Index of the the file path to start the download
     * @return Void
     */
    this.multiple = function (idx) {
        idx = idx || 0;
        // Add some slight delay
        window.setTimeout(function () {
            triggerMultipleDownload(idx);
        }, 1000);
    };

    return this;
}
