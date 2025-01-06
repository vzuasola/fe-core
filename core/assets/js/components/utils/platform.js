/**
 * Detect the browser platform
 *
 * Disclaimer: This one is using the window.navigator object which is spoofable for most browsers/systems.
 */
export default function Platform() {
    var platform = window.navigator.platform || '',
        subString = {
            'windows': /Win/,
            'mac': /Mac/,
            'ios': /iPhone|iPad|iPod/,
            'android': /Android/,
            'linux': /Linux/
        };

    var getPlatform = function () {
        if (platform) {
            for (var k in subString) {
                if (subString.hasOwnProperty(k) && subString[k].test(platform)) {
                    return k;
                }
            }
        }

        return false;
    };

    return {
        getPlatform: function () {
            return getPlatform();
        }
    };
}
