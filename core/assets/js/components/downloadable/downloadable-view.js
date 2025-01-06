import * as utility from "Base/utility";
import Downloadable from "Base/downloadable/downloadable";

/**
 * This will implement the downloadable.js that is part of the header
 * Presentation logic
 * @return void
 */
utility.addEventListener(window, 'load', function () {
    var files = app.settings.downloadableFiles;
    if (files !== undefined && files.length > 0) {
        var download = new Downloadable({ files: files });
        download.multiple();
    }
});
