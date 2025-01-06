/**
 * Manage session timeouts
 */
export default function SessionManager() {

    SessionManager.prototype = {
        /**
         * Statically reset session counter
         */
        reset: function () {
            // Trigger a click event to reset the session
            document.getElementsByTagName('head')[0].click();
        }
    };
}
