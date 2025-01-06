import * as utility from "Base/utility";
import MegaMenu from "Base/mega-menu/mega-menu";

utility.forEachElement("[data-drop-down-menu-url]", function (elem) {
    var uri = elem.getAttribute('data-drop-down-menu-url');

    if (uri) {
        var menu = new MegaMenu(elem, uri);
        menu.init();
    }
});
