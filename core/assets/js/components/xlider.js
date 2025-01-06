import * as utility from "Base/utility";

/**
 * Xlider
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
 * @param Node parent parent element of the slider (this will target slider within parent element)
 */
export default function Xlider(options, parent) {
    "use strict";

    var $this = this,
        $pagerSelector,
        $previous,
        $next,
        slideInterval = null,
        $selector,
        $slides,
        $thumbContainer,
        $thumbOuter,
        $thumbItems,
        $thumbTotalWidth,
        $thumbHolderWidth;

    init();

    /**
     * Initiate Functions
     */
    function init() {
        setOptions();

        if (parent) {
            $selector = parent.querySelector($this.options.selector);
        } else {
            $selector = document.querySelector($this.options.selector);
        }

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

            // Generate thumbnails markup
            if ($this.options.thumbControls) {
                createThumbnails();
            }

            // Generate controls
            if ($this.options.controls) {
                initControls();
            }

            // Generate pager
            if ($this.options.pager) {
                initPager();
            }

            // Init the main slider. We set the first dot and slide to be shown onload.
            $slides[$slides.length - 1].className = $this.options.childClassSelector + " slides-item slides-item--hidePrevious";

            showHideControls($selector);
        }
    }

    function showHideControls(selector) {
        var drawer = utility.hasClass(selector, "drawer", true);

        if (!drawer && $this.options.thumbControls && $thumbOuter.clientWidth >= $thumbOuter.firstChild.clientWidth) {
            // Hide controls if outer holder can accomodate enough thumbnails items
            utility.addClass($previous, "hidden");
            utility.addClass($next, "hidden");
        } else if (drawer && $slides.length < 4) {
            // Hide controls if number of slides is less than 4
            utility.addClass($previous, "hidden");
            utility.addClass($next, "hidden");
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
            currentSlide: 0,
            thumbControls: false,
            thumbControlsInside: true // location for thumb controls (outside/inside slider container)
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
     * Create/generate thumbnails
     */
    function createThumbnails() {
        $thumbContainer = createElem("div", "slider-thumb");
        $thumbOuter = createElem("div", "slider-thumb-outer");

        var thumbInner = createElem("div", "slider-thumb-inner"),
            thumbItem,
            thumbImg,
            thumbSrc,
            fragment = document.createDocumentFragment();

        for (var i = 0; i < $slides.length; i++) {
            // Create thumbnail item
            thumbItem = createElem("span", "slider-thumb-item");
            thumbImg = createElem("img", "slider-thumb-object");
            thumbSrc = $slides[i].querySelector("[data-thumb]") ? $slides[i].querySelector("[data-thumb]").getAttribute("data-thumb") : $slides[i].querySelector("img").src;
            thumbImg.src = thumbSrc;
            thumbItem.setAttribute("data-index", i);
            thumbImg.alt = $slides[i].querySelector("[data-thumbnail]") ? $slides[i].querySelector("[data-thumbnail]").getAttribute("data-thumbnail") : '';

            thumbItem.appendChild(thumbImg);
            fragment.appendChild(thumbItem);

            onclickThumbnail(thumbImg, i);
        }

        // Append generated items to fragment
        thumbInner.appendChild(fragment);
        $thumbOuter.appendChild(thumbInner);
        $thumbContainer.appendChild($thumbOuter);

        if ($this.options.thumbControlsInside) {
            // append/insert thumbnails element
            $selector.appendChild($thumbContainer);

            // Get all slider thumbnail items
            $thumbItems = $selector.querySelectorAll('.slider-thumb-item');
        } else {
            // Get next element for slide element
            var nextSelector = $selector.nextElementSibling || utility.nextElementSibling($selector);

            // append/insert thumbnails element
            $selector.parentNode.insertBefore($thumbContainer, nextSelector);

            // Get all slider thumbnail items
            $thumbItems = $selector.parentNode.querySelectorAll('.slider-thumb-item');

            // Add class to slider and thumbnail container if the thumbnail element position is outside the slider element
            utility.addClass($selector, "has-thumb-outside");
            utility.addClass($thumbContainer, "slider-thumb-outside");
        }

        // set active estate on current slide
        utility.addClass($thumbItems[$this.options.currentSlide], "active");

        // Add class to container
        utility.addClass($selector, "has-thumb");

        setTimeout(function () {
            addThumbHolderWidth(thumbInner);
        }, 100);
    }

    function addThumbHolderWidth(holderElem) {
        $thumbTotalWidth = getTotalWidth($thumbItems);
        $thumbHolderWidth = holderElem.clientWidth;

        if ($thumbTotalWidth > $thumbHolderWidth) {
            holderElem.style.width = $thumbTotalWidth + "px";
        }
    }

    function getTotalWidth(nodes) {
        // convert nodelist to array
        var thumbItemsArr = Array.prototype.slice.call(nodes),
            thumbItemWidths,
            totalWidth;

        thumbItemWidths = thumbItemsArr.map(function (item) {
            return item.clientWidth;
        });

        totalWidth = thumbItemWidths.reduce(function (prev, current) {
            return prev + current;
        }, 0);

        return totalWidth;
    }

    /**
     * on click on thumbnail items
     */
    function onclickThumbnail(elem, index) {
        utility.addEventListener(elem, "click", function () {
            pauseSlideshow();
            goToSlide(index);
            playSlideshow();
        });
    }

    // Create element with classname
    function createElem(tagName, className) {
        var element = document.createElement(tagName);
        utility.addClass(element, className || "");

        return element;
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
        var previousHtml = "<span class='" + $this.options.selector.substring(1) + " slider-button btn-prev'>Previous</span>";
        var nextHtml = "<span class='" + $this.options.selector.substring(1) + " slider-button btn-next'>Next</span>";
        var controllerHtml = "<div class='slider-controls'>" + previousHtml + nextHtml + "</div>";

        if ($this.options.thumbControls && !$this.options.thumbControlsInside) {
            $selector.parentNode.querySelector(".slider-thumb").insertAdjacentHTML('beforeend', controllerHtml);
            $previous = $selector.parentNode.querySelector(".btn-prev");
            $next = $selector.parentNode.querySelector(".btn-next");
        } else {
            $selector.insertAdjacentHTML('beforeend', controllerHtml);
            $previous = $selector.querySelector("." + $this.options.selector.substring(1) + ".btn-prev");
            $next = $selector.querySelector("." + $this.options.selector.substring(1) + ".btn-next");
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

        $pagerSelector = $selector.querySelectorAll('.pager-item');
        utility.addClass($pagerSelector[$this.options.currentSlide], "active");
    }

    /**
     * Onclick function for control pager
     */
    function onclickPager(elem, index) {
        utility.addEventListener(elem[index], "click", function () {
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
        utility.forEach($slides, function (elem) {
            elem.className = $this.options.childClassSelector + " slides-item";
        });

        $this.options.currentSlide = (n + $slides.length) % $slides.length;
        $slides[$this.options.currentSlide].className = $this.options.childClassSelector + " slides-item showing slides-item--showNext";

        if ($this.options.pager) {
            setActiveControls($pagerSelector);
        }

        if ($this.options.thumbControls) {
            setActiveControls($thumbItems);
        }

        // Fix out of bounds for previous slide index when current slide is 0
        var prevIndex = (($this.options.currentSlide) === 0) ? ($slides.length - 1) : ($this.options.currentSlide - 1);
        $slides[prevIndex].className = $this.options.childClassSelector + " slides-item slides-item--hidePrevious";

        if ($this.options.thumbControls) {
            adjustThumbPosition(n);
        }
    }

    // if current item position is hidden a little bit
    // it will add negative margin equivalent to the width of single thumbnail width
    function adjustThumbPosition(curIndex) {

        // Get first index if curIndex is greater than the length of an array -1
        curIndex = (curIndex > $slides.length - 1) ? curIndex = 0 : curIndex;

        // Get Last index if curIndex is -1
        curIndex = (curIndex === -1) ? curIndex = $slides.length - 1 : curIndex;

        var curThumbPos = utility.getCoords($thumbItems[curIndex]),
            thumbHolder = $thumbOuter.firstChild,
            thumbHolderPos = utility.getCoords(thumbHolder.firstChild),
            thumbWidth = thumbHolder.firstChild.clientWidth,
            sliderThumbWidth = $thumbOuter.clientWidth;

        // Left position difference b/w current thumb and thumb holder
        var diff = (curThumbPos.left + thumbWidth) - thumbHolderPos.left,
            // Amount of left margin/ horizontal  space to negate
            slideLeft = diff - sliderThumbWidth;

        // show current thumb if it's hidden
        if (sliderThumbWidth <= diff) {
            thumbHolder.style.transform = "translateX(-" + slideLeft + "px)";
        }

        // Remove translateX if current active thumb is the first thumb
        if (curIndex === 0) {
            thumbHolder.style.transform = "translateX(0)";
        }
    }

    // Set active estate for controls
    function setActiveControls(elems) {
        utility.forEach(elems, function (elem) {
            utility.removeClass(elem, "active");
        });

        utility.addClass(elems[$this.options.currentSlide], "active");
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
