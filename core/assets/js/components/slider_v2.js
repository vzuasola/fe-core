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

        showSlider();

        utility.forEach($slides, function (elem) {
            elem.className = $this.options.childClassSelector + " slides-item";
        });

        $slides[$this.options.currentSlide].className = $this.options.childClassSelector + " slides-item showing slides-item--showNext";

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
        var pagerHtml = "<div class='slider-pager'></div>";
        $selector.insertAdjacentHTML("beforeend", pagerHtml);
        var $pagerContainer = $selector.querySelector(".slider-pager");

        if (app.settings.v2.banner_v2_enable === 0) {
            var previousHtml = "<span class='" + $this.options.selector.substring(1) + " slider-button btn-prev'>Previous</span>";
            var nextHtml = "<span class='" + $this.options.selector.substring(1) + " slider-button btn-next'>Next</span>";
            var controllerHtml = "<div class='slider-controls'>" + previousHtml + nextHtml + "</div>";

            $selector.insertAdjacentHTML('beforeend', controllerHtml);
            $previous = $selector.querySelector("." + $this.options.selector.substring(1) + ".btn-prev");
            $next = $selector.querySelector("." + $this.options.selector.substring(1) + ".btn-next");
            utility.addClass($pagerContainer, 'slider-v1');
        } else {
            utility.addClass($pagerContainer, 'slider-v2');
        }
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

    // Display slider when JS is ready
    function showSlider() {
        var placeholder = $selector.querySelector(".slider-placeholder-image"),
            slidesContainer = $selector.querySelector(".banner-slides");

        // Hide placeholder image
        if (placeholder) {
            placeholder.style.display = "none";
        }

        // Show slider container
        slidesContainer.style.display = "block";

        // Used for transition
        setTimeout(function () {
            slidesContainer.style.opacity = "1";
        }, 10);
    }
}
