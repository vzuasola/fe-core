import * as utility from "Base/utility";
import Modal from "Base/utils/modal";
import Blinker from "Base/utils/blinker";

/**
 * Session confirmation lightbox
 */
export default function NotificationLightbox() {
    "use strict";

    var $id = 'modal-confirmation-timeout',
        $title = 'Session has timeout', // default value

        // dependencies
        $modal,
        $blinker;

    /**
     * Constructor
     */
    function construct() {
        if (app &&
            app.settings &&
            app.settings.loginConfig &&
            app.settings.loginConfig.notification_window_title
        ) {
            $title = app.settings.loginConfig.notification_window_title;
        }

        $modal = new Modal();
        $blinker = new Blinker($title);
    }

    construct();

    /**
     *
     */
    this.show = function () {
        var element = document.getElementById($id);

        // add on close event on the modal
        utility.addEventListener(element, 'modalclose', function (event) {
            var evt = event || window.event;
            var target = evt.target || evt.srcElement;

            if (target === element) {
                onClose();
            }
        });

        $modal.show($id);
        $blinker.start();
    };

    /**
     *
     */
    function onClose() {
        $blinker.stop();
    }
}
