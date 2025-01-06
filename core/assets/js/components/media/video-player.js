import * as utility from "Base/utility";

/**
 * Video Player
 *
 * @param Node target html5 video element/tag
 * @param Object options
 */
export default function VideoPlayer(target, options) {
    "use strict";

    // Default options
    var defaults = {
        autoPlay: false,
        playBox: false // Adds buttons
    };

    // extend options
    this.options = options || {};
    for (var name in defaults) {
        if (this.options[name] === undefined) {
            this.options[name] = defaults[name];
        }
    }

    // Get target param as video/media element/tag
    this.media = target,
    this.container = this.media.parentNode;

    // init
    this.init();
}

// Checker for HTML5 video support on browsers
VideoPlayer.prototype.supportsVideo = !!document.createElement('video').canPlayType;

// Initialize video
VideoPlayer.prototype.init = function () {
    if (this.supportsVideo) {

        var $this = this,
            doc = document;


        // Check video tags for standard and custom attribute (Prioritise video attributes for options)
        this.options.playBox = (this.media.hasAttribute('data-player-playbox')) ? true : this.options.playBox;
        this.options.autoPlay = (this.media.hasAttribute('autoplay')) ? true : this.options.autoPlay;
        this.options.customControl = (this.media.hasAttribute('controls')) ? false : true;

        // Custom controls
        this.customControls = {};
        this.customControls.container = this.media.nextElementSibling || utility.nextElementSibling(this.media);
        this.customControls.playPause = this.customControls.container.querySelector('.media-control-play-pause');
        this.customControls.play = this.customControls.container.querySelector('.media-control-play');
        this.customControls.pause = this.customControls.container.querySelector('.media-control-pause');
        this.customControls.stop = this.customControls.container.querySelector('.media-control-stop');
        this.customControls.progress = this.customControls.container.querySelector('.media-control-progress');
        this.customControls.mute = this.customControls.container.querySelector('.media-control-mute');
        this.customControls.volUp = this.customControls.container.querySelector('.media-control-volume-up');
        this.customControls.volDown = this.customControls.container.querySelector('.media-control-volume-down');
        this.customControls.fullscreen = this.customControls.container.querySelector('.media-control-fullscreen');

        // Add playbox button
        if (this.options.playBox && this.options.customControl) {
            this.createPlayBox();
        }

        // Playbox elements
        this.playBox = {
            container: $this.container.querySelector('.playbox'),
            button: $this.container.querySelector('.playbox-button')
        };

        // Show custom controls if enabled and show built-in controls if custom controls is disabled
        if (this.options.customControl) {
            utility.removeClass(this.customControls.container, 'hidden');
        } else {
            this.media.controls = true;
        }

        // Auto play video
        if (this.options.autoPlay) {
            $this.media.play();
        }

        // Add default play classes to playBox and playPause button if autoPlay is not set
        if (!this.options.autoPlay) {
            utility.addClass($this.playBox.button, 'play');
            utility.addClass($this.customControls.playPause, 'play');
        }

        // Addclass to mute element
        if ($this.media.muted) {
            changeButtonType($this.customControls.mute, 'mute', 'unmute');
        } else {
            changeButtonType($this.customControls.mute, 'unmute', 'mute');
        }

        // Add Fullscreen state to video tag on load
        this.setFullscreenData(false);

        // set Video container and video element equal to actual video width and height
        if ($this.media.videoWidth && $this.media.videoHeight) {
            addVideoDimension.call($this);
        }

        /**
         * Click event for custom controls
         */
        utility.addEventListener(doc, 'click', function (e) {
            var target = utility.getTarget(e);

            // Play/pause button
            if (target === $this.customControls.playPause) {
                $this.togglePlayer();
            }

            // Play button
            if (target === $this.customControls.play) {
                $this.media.play();
            }

            // Pause button
            if (target === $this.customControls.pause) {
                $this.media.pause();
            }

            // Stop button
            if (target === $this.customControls.stop) {
                $this.stopPlayer();
            }

            // Toggle mute button
            if (target === $this.customControls.mute) {
                if ($this.media.muted) {
                    $this.media.muted = false;
                    changeButtonType($this.customControls.mute, 'unmute', 'mute');
                } else {
                    $this.media.muted = true;
                    changeButtonType($this.customControls.mute, 'mute', 'unmute');
                }
            }

            // Increase volume
            if (target === $this.customControls.volUp) {
                alterVolume.call($this, 'up', $this.customControls.mute);
            }

            // Decrease volume
            if (target === $this.customControls.volDown) {
                alterVolume.call($this, 'down', $this.customControls.mute);
            }

            // Fullscreen
            if (target === $this.customControls.fullscreen) {
                fullscreenHandler.call($this);
            }

            // Playbox
            if (target === $this.playBox.button) {
                $this.togglePlayer();
            }
        });

        // Event when media is finished playing
        utility.addEventListener($this.media, 'ended', function (e) {
            // Stop player when it is ended and not set to loop
            if (!$this.media.hasAttribute('loop')) {
                $this.stopPlayer();
            }
        });

        // Add 'max' attribute to <progress> equal to video duration when metadata is loaded
        utility.addEventListener($this.media, 'loadedmetadata', function () {
            // set Video container and video element equal to actual video width and height
            addVideoDimension.call($this);
        });

        // Timeupdate for progress
        utility.addEventListener($this.media, 'timeupdate', function () {
            var size = parseInt($this.media.currentTime * $this.customControls.progress.clientWidth / $this.media.duration),
                progressIndicator = $this.customControls.progress.querySelector('.media-control-progress-indicator');

            progressIndicator.style.width = size + 'px';
        });

        // Progress skip ahead
        utility.addEventListener($this.customControls.progress, 'click', function (e) {
            var barWidth = $this.customControls.progress.clientWidth,
                progressIndicator = $this.customControls.progress.querySelector('.media-control-progress-indicator');

            var currentPosition = utility.getCoords($this.customControls.progress);
            var mouseX = e.pageX - currentPosition.left;
            var newtime = mouseX * $this.media.duration / barWidth;
            $this.media.currentTime = newtime;
            progressIndicator.style.width = mouseX + 'px';
        });

        // Listener when play method is triggered
        utility.addEventListener($this.media, 'play', function (e) {
            if ($this.customControls.playPause) {
                changeButtonType($this.customControls.playPause, 'pause', 'play');
            }

            if ($this.playBox.button) {
                changeButtonType($this.playBox.button, 'pause', 'play');
            }
        });

        // Listener when puase method is triggered
        utility.addEventListener($this.media, 'pause', function (e) {
            if ($this.customControls.playPause) {
                changeButtonType($this.customControls.playPause, 'play', 'pause');
            }

            if ($this.playBox.button) {
                changeButtonType($this.playBox.button, 'play', 'pause');
            }
        });

        // Add custom data attribute on Fullscreen change
        doc.addEventListener('fullscreenchange', function (e) {
            $this.setFullscreenData(!!(doc.fullScreen || doc.fullscreenElement));

            toggleControls.call($this);
        });

        doc.addEventListener('webkitfullscreenchange', function () {
            $this.setFullscreenData(!!doc.webkitIsFullScreen);

            toggleControls.call($this);
        });

        doc.addEventListener('mozfullscreenchange', function () {
            $this.setFullscreenData(!!doc.mozFullScreen);

            toggleControls.call($this);
        });

        doc.addEventListener('MSFullscreenChange', function () {
            $this.setFullscreenData(!!doc.msFullscreenElement);

            toggleControls.call($this);
        });
    }
};

VideoPlayer.prototype.playPlayer = function () {
    if (this.supportsVideo) {
        if (this.media.paused || this.media.ended) {
            this.media.play();
        }
    }
};

VideoPlayer.prototype.pausePlayer = function () {
    if (this.supportsVideo) {
        if (!this.media.paused) {
            this.media.pause();
        }
    }
};

VideoPlayer.prototype.stopPlayer = function () {
    if (this.supportsVideo) {
        var $this = this;

        this.media.pause();
        this.media.currentTime = 0;

        changeButtonType($this.customControls.playPause, 'play', 'pause');
    }
};

VideoPlayer.prototype.togglePlayer = function () {
    if (this.supportsVideo) {
        if (this.media.paused || this.media.ended) {
            this.media.play();
        } else {
            this.media.pause();
        }
    }
};

/**
 * Playbox
 */
VideoPlayer.prototype.createPlayBox = function () {
    var $this = this;

    var playBoxContainer = document.createElement('div'),
        playBoxButton = document.createElement('button');

    utility.addClass(playBoxContainer, 'playbox');
    utility.addClass(playBoxButton, 'playbox-button');

    playBoxContainer.appendChild(playBoxButton);

    utility.append($this.container, playBoxContainer);
};

// Check if video is in Fullscreen mode
VideoPlayer.prototype.isFullScreen = function () {
    return !!(document.fullScreen || document.webkitIsFullScreen || document.mozFullScreen || document.msFullscreenElement || document.fullscreenElement);
};

// Fullscreen state (custom attribute to video tags)
VideoPlayer.prototype.setFullscreenData = function (state) {
    this.media.setAttribute('data-fullscreen', !!state);
};

/**
 * Change play and pause button text and class
 */
function changeButtonType(btn, origClass, newClass) {
    btn.title = newClass;
    // btn.innerHTML = newClass;

    if (utility.hasClass(btn, newClass)) {
        utility.removeClass(btn, newClass);
    }
    utility.addClass(btn, origClass);
}

/**
 * Decrease/increase volume
 */
function alterVolume(direction, muteControl) {
    var currentVolume = Math.floor(this.media.volume * 10) / 10;

    if (this.media.muted) {
        this.media.muted = false;
        changeButtonType(muteControl, 'unmute', 'mute');
    }

    if (direction === 'up') {
        if (currentVolume < 1) {
            this.media.volume += 0.1;
        }
    } else if (direction === 'down') {
        if (currentVolume > 0) {
            this.media.volume -= 0.1;
        }
    }
}

/**
 * Fullscreen handler
 */
function fullscreenHandler() {
    if (this.isFullScreen()) {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
        this.setFullscreenData(false);
    } else {
        if (this.media.requestFullscreen) {
            this.media.requestFullscreen();
        } else if (this.media.mozRequestFullScreen) {
            this.media.mozRequestFullScreen();
        } else if (this.media.webkitRequestFullScreen) {
            this.media.webkitRequestFullScreen();
        } else if (this.media.msRequestFullscreen) {
            this.media.msRequestFullscreen();
        }
        this.setFullscreenData(true);
    }
}

/**
 * Function to set Video container and Video element equal to actual video width and height
 */
function addVideoDimension() {
    var width = this.media.videoWidth;
    var height = this.media.videoHeight;
    this.container.style.width = width + 'px';
    this.container.style.height = height + 'px';
    this.media.width = width;
    this.media.height = height;
}

/**
 * Show/hide built-in controls in fullsreen/normal mode
 */
function toggleControls() {
    if (this.options.customControl) {
        this.media.controls = !this.media.controls;
    }
}
