import * as utility from "Base/utility";

/**
 * Slider
 *
 * @param options must be array
 * Available options:
 *     selector: The parent wrapper of slider items
 *     innerSelector: Inner selector between the slider parent wrapper and slider items.
 *     childClassSelector: child slider class selector
 *     auto: for automatic start of transition
 *     controls: for adding of controls(prev and next) to slider
 *     pager: for adding of pager/ticker selector to slider
 *     speed: for adding custom transition speed to slider
 *     currentSlide: for custom start slide index
 */
export default function Slider(options) {
    "use strict";

    var $this = this,
        $pagerSelector,
        $previous,
        $next,
        slideInterval = null,
        $selector,
        $slides;

    init();

    /**
     * Initiate Functions
     */
    function init() {
        setOptions();

        $selector = document.querySelector($this.options.selector);
        $slides = $selector.querySelector($this.options.innerSelector).children;

        // Show slider once initialized (Slider is hidden initially from CSS)
        $selector.style.display = "block";

        utility.forEach($slides, function (elem) {
            elem.className = $this.options.childClassSelector + " slides-item";
        });

        if (typeof $slides[$this.options.currentSlide] !== 'undefined') {
            $slides[$this.options.currentSlide].className = $this.options.childClassSelector + " slides-item showing slides-item--showNext";
        }

        // Check if main slider exists
        if ($selector && ($slides.length > 1)) {
            if ($this.options.auto) {
                slideInterval = setInterval(nextSlide, $this.options.speed);
            }

            if ($this.options.controls) {
                initControls();
            }

            if ($this.options.pager) {
                initPager();
            }

            // Init the main slider. We set the first dot and slide to be shown onload.
            $slides[$slides.length - 1].className = $this.options.childClassSelector + " slides-item slides-item--hidePrevious";
        }
    }

    /**
     * Map Slider Options
     */
    function setOptions() {
        // Default options
        $this.defaults = {
            selector: ".banner",
            innerSelector: ".banner-slides",
            childClassSelector: "banner-slides-item",
            auto: true,
            controls: true,
            pager: true,
            speed: 4000,
            currentSlide: 0
        };

        // Extend options
        $this.options = options || {};

        for (var name in $this.defaults) {
            if ($this.options[name] === undefined) {
                $this.options[name] = $this.defaults[name];
            }
        }
    }

    /**
     * Intiate controller
     */
    function initControls() {
        createControls();
        onclickControls();
    }

    /**
     * Create slider controller
     */
    function createControls() {
        var previousHtml = "<span class='" + $this.options.selector.substring(1) + " slider-button btn-prev'><i class='fa fa-angle-left'></i></span>";
        var nextHtml = "<span class='" + $this.options.selector.substring(1) + " slider-button btn-next'><i class='fa fa-angle-right'></i></span>";
        var controllerHtml = "<div class='slider-controls'>" + previousHtml + nextHtml + "</div>";

        $selector.insertAdjacentHTML('beforeend', controllerHtml);
        $previous = $selector.querySelector("." + $this.options.selector.substring(1) + ".btn-prev");
        $next = $selector.querySelector("." + $this.options.selector.substring(1) + ".btn-next");
    }

    /**
     * Onclick function for control buttons
     */
    function onclickControls() {
        utility.addEventListener($next, "click", function (e) {
            pauseSlideshow();
            nextSlide();
            playSlideshow();
            utility.preventDefault(e);
        });
        utility.addEventListener($previous, "click", function (e) {
            pauseSlideshow();
            previousSlide();
            playSlideshow();
            utility.preventDefault(e);
        });

        // swipe
        document.addEventListener('touchstart', function (e) {
            var target = utility.getTarget(e);
            var parent = utility.findParent(target, ".banner-slides");

            if (utility.hasClass(parent, "banner-slides")) {
                xDown = getTouches(e)[0].clientX;
                yDown = getTouches(e)[0].clientY;
            }
        }, false);

        document.addEventListener('touchmove', function (e) {
            var target = utility.getTarget(e);
            var parent = utility.findParent(target, ".banner-slides");

            if (utility.hasClass(parent, "banner-slides")) {
                if ( !xDown || !yDown ) {
                    return;
                }

                var xUp = e.touches[0].clientX;
                var yUp = e.touches[0].clientY;

                var xDiff = xDown - xUp;
                var yDiff = yDown - yUp;

                if ( Math.abs( xDiff ) > Math.abs( yDiff ) ) { /* horizontal swipe */
                    if ( xDiff > 0 ) {
                        /* left swipe */
                        pauseSlideshow();
                        nextSlide();
                        playSlideshow();
                    } else {
                        /* right swipe */
                        pauseSlideshow();
                        previousSlide();
                        playSlideshow();
                    }
                }

                /* reset values */
                xDown = null;
                yDown = null;
            }
        }, false);

        var xDown = null;
        var yDown = null;

        function getTouches(evt) {
            return evt.touches;
        }
    }

    /**
     * Create and Initiate slider pager
     */
    function initPager() {
        var pagerHtml = "<div class='slider-pager'></div>";
        $selector.insertAdjacentHTML("beforeend", pagerHtml);
        var $pagerContainer = $selector.querySelector(".slider-pager");

        for (var j = 0; j < $slides.length; j++) {
            // Create pager button element
            pagerHtml = "<button class='pager-item' data-index='" + j + "'></button>";
            $pagerContainer.insertAdjacentHTML("beforeend", pagerHtml);

            var $pager = $selector.querySelectorAll(".pager-item");
            onclickPager($pager, j);
        }

        $pagerSelector = document.querySelectorAll($this.options.selector + " .pager-item");
        $pagerSelector[$this.options.currentSlide].className = "pager-item active";
    }

    /**
     * Onclick function for control pager
     */
    function onclickPager($pager, j) {
        utility.addEventListener($pager[j], "click", function () {
            var dataIndex = parseInt(this.getAttribute("data-index"));
            pauseSlideshow();
            goToSlide(dataIndex);
            playSlideshow();
        });
    }

    // Next Slide
    function nextSlide() {
        goToSlide($this.options.currentSlide + 1);
    }

    // Previous Slide
    function previousSlide() {
        goToSlide($this.options.currentSlide - 1);
    }

    // Go to nth slide
    // Add relevant classes for current and previous slides
    function goToSlide(n) {
        if ($this.options.pager) {
            utility.forEach($pagerSelector, function (elem) {
                utility.removeClass(elem, "active");
            });
        }

        utility.forEach($slides, function (elem) {
            elem.className = $this.options.childClassSelector + " slides-item";
        });

        $this.options.currentSlide = (n + $slides.length) % $slides.length;
        $slides[$this.options.currentSlide].className = $this.options.childClassSelector + " slides-item showing slides-item--showNext";

        if ($this.options.pager) {
            utility.addClass($pagerSelector[$this.options.currentSlide], "active");
        }
        // Fix out of bounds for previous slide index when current slide is 0
        var prevIndex = (($this.options.currentSlide) === 0) ? ($slides.length - 1) : ($this.options.currentSlide - 1);
        $slides[prevIndex].className = $this.options.childClassSelector + " slides-item slides-item--hidePrevious";
    }

    // Pauses slideshow when triggered
    function pauseSlideshow() {
        clearInterval(slideInterval);
    }

    // Plays slideshow when triggered
    function playSlideshow() {
        if ($this.options.auto) {
            slideInterval = setInterval(nextSlide, $this.options.speed);
        }
    }
}
