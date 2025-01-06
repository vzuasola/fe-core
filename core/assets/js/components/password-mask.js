import * as utility from "Base/utility";
import detectIE from "Base/browser-detect";

/**
 * Password Mark/Unmask
 *
 * @param Node input password element
 */
export default function passwordMask(input) {
    // IE8 doesn't support type password, so no need for this feature in IE8
    if (input && utility.hasClass(input, "password-mask-enabled") && detectIE() !== 8) {
        var icon = createIcon(input);

        utility.addEventListener(icon, "click", changeType.bind(null, input));
    }
}

/**
 * Change input type
 */
function changeType(input) {
    var icon = utility.findSibling(input, '.password-mask-icon');

    if (input.type === "password") {
        input.setAttribute("type", "text");
    } else {
        input.setAttribute("type", "password");
    }

    utility.toggleClass(icon, "password-unmasked");
}

/**
 * Create mask/unmask icon
 */
function createIcon(input) {
    var icon = utility.findSibling(input, '.password-mask-icon');

    if (!icon) {
        icon = document.createElement("span");

        utility.addClass(icon, "password-mask-icon");

        input.parentNode.appendChild(icon);
    }

    return icon;
}
