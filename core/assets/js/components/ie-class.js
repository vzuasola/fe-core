import * as utility from "Base/utility";
import detectIE from "Base/browser-detect";

/**
 * Add class in <html> tag for IEs
 */
if (detectIE()) {
    utility.addClass(document.getElementsByTagName('html')[0], "ie-" + detectIE());
}
