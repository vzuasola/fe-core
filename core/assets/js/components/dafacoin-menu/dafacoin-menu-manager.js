import * as utility from "Base/utility";
import DafacoinMenu from "Base/dafacoin-menu/dafacoin-menu";

/**
 * This will implement the Dafacoin Menu that is part of the header
 * Presentation logic
 *
 * @return void
 */
utility.ready(function () {
    if (typeof app.settings.login === "undefined") {
        return;
    }
    var customOptions = {};
    if (app.settings.lang) {
        customOptions.language = app.settings.lang;
    }
    if (app.settings.product) {
        customOptions.product = app.settings.product;
    }
    // Create new class instance
    var dafacoinMenu = new DafacoinMenu(customOptions);

    // Initialize
    dafacoinMenu.init();
});
