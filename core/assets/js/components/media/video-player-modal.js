import * as utility from "Base/utility";
import VideoPlayer from 'Base/media/video-player';

/**
 * Create VideoPlayer that will open/play in modal/lightbox
 */
export default function ModalVideoPlayer(target, options) {
    "use strict";

    var $this = this;

    // Inherit base VideoPlayer constructor
    VideoPlayer.call(this, target, options);

    // Modal elements specific to video tag inside modal/lightbox
    this.modal = utility.findParent(this.media, '.modal');
    this.modalTrigger = document.querySelector("a[href='#" + this.modal.id + "']");
    this.ieVideo = $this.modal.querySelector('object');

    // Add class to modal container for styling
    utility.addClass(this.modal, "modal-video-ligthbox");

    // Keydown listener
    utility.addEventListener(document, 'keydown', function (e) {
        // Cross browser event
        e = e || window.event;

        // Close player on clicking Escape key
        if (e.keyCode === 27) {
            $this.stopPlayer();
            if ($this.ieVideo && typeof $this.ieVideo.StopPlay !== "undefined") {
                $this.ieVideo.StopPlay();
            }
        }
    });

    // Click listener
    utility.addEventListener(document, 'click', function (evt) {
        var target = utility.getTarget(evt),
            modalOverlay = $this.modal.querySelector('.modal-overlay'),
            modalClose = $this.modal.querySelector('.modal-close'),
            drawer = document.querySelector('.drawer');

        if (target === $this.modalTrigger || target.parentNode === $this.modalTrigger) {

            // remove clipping for safari
            if (drawer) {
                drawer.style.overflow = "visible";
            }

            // play video when modal trigger is clicked
            $this.playPlayer();
            if ($this.ieVideo && typeof $this.ieVideo.Play !== "undefined") {
                $this.ieVideo.Play();
            }
        }

        if (target === modalOverlay || target === modalClose) {
            // show clipping for safari
            if (drawer) {
                drawer.style.overflow = "auto";
            }

            // stop video when close button or overlay is clicked
            $this.stopPlayer();
            if ($this.ieVideo && typeof $this.ieVideo.StopPlay !== "undefined") {
                $this.ieVideo.StopPlay();
            }
        }
    });

    utility.addEventListener(document, 'pnxMessagesByProduct', function (e) {
        var video = document.querySelector('.modal-active video'),
            ieVideo = document.querySelector('.modal-active object');

        if (e.customData.count) {
            if (video && typeof video.pause !== "undefined") {
                video.pause();
            }
            if (ieVideo && typeof ieVideo.StopPlay !== "undefined") {
                ieVideo.StopPlay();
            }
        } else if (!e.customData.count) {
            if (video && typeof video.play !== "undefined") {
                video.play();
            }
            if (ieVideo && typeof ieVideo.Play !== "undefined") {
                ieVideo.Play();
            }
        }
    });
}

// Inherit ModalVideoPlayer prototype from VideoPlayer prototype
inheritPrototype(ModalVideoPlayer, VideoPlayer);

// Function to inherit prototype
function inheritPrototype(childObject, parentObject) {
    var copyOfParent = Object.create(parentObject.prototype);
    copyOfParent.constructor = childObject;
    childObject.prototype = copyOfParent;
}
