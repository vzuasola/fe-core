import * as utility from "Base/utility";

import ModalVideoPlayer from 'Base/media/video-player-modal';
import Xlider from "Base/xlider";

import Drawer from "Base/drawer/drawer";

/**
 * Drawer manager
 *
 * @param Object drawerOptions options for drawer (see drawer.js for available options)
 * @param Object sliderOptions options for slider (see xlider.js for available options)
 */
export default function DrawerManager(drawerOptions, sliderOptions) {
    "use strict";

    utility.forEachElement(".drawer-trigger", function (trigger) {
        var drawerDefaults = {
                afterOpen: afterOpenHandler
            },
            name;

        for (name in drawerOptions) {
            if (drawerOptions[name] !== undefined) {
                drawerDefaults[name] = drawerOptions[name];
            }
        }

        new Drawer(trigger, drawerDefaults);
    });

    // Callback
    function afterOpenHandler(trigger, drawer) {
        activeSlider(trigger, drawer);
        activeVideoPlayer(trigger, drawer);
    }

    function activeSlider(trigger, drawer) {
        var sliderDefaults = {},
            name;

        for (name in sliderOptions) {
            if (sliderOptions[name] !== undefined) {
                sliderDefaults[name] = sliderOptions[name];
            }
        }

        // Activate slider
        var $banner = drawer.querySelector('.banner');

        if ($banner) {
            new Xlider(sliderDefaults, drawer);
        }
    }

    function activeVideoPlayer(trigger, drawer) {
        // Activate modal videos
        var videos = drawer.querySelectorAll('.modal video');

        if (videos) {
            utility.forEach(videos, function (videoItem) {
                new ModalVideoPlayer(videoItem);

                // Add video overlay for each modal video
                videoOverlay(videoItem);
            });
        }
    }

    /**
     * Create video overlay
     */
    function videoOverlay(videoTag) {
        var modal = utility.findParent(videoTag, ".modal");

        if (!modal.querySelector('.modal-overlay')) {
            var overlay = document.createElement("div");
            overlay.className = "modal-overlay";
            modal.insertBefore(overlay, modal.firstChild);

            if (utility.hasClass(modal, 'modal-active')) {
                document.body.style.overflow = "hidden";
            }
        }
    }
}
