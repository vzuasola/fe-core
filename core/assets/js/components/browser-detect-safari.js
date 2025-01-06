/**
 * Browser Detect Safari
 * returns boolean
 */

export default function detectSafari() {
    var ua = navigator.userAgent;

    if (ua.indexOf('Safari') !== -1 && ua.indexOf('Chrome') === -1) {
        return true;
    }

    // other browser
    return false;
}
