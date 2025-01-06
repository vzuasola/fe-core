import * as utility from "Base/utility";
import detectIE from "Base/browser-detect";

var slides = document.querySelectorAll('.Slider-list .Slider-item'),
    dotsWrapper = document.querySelector('.Slider-pagination'),
    dots = document.querySelectorAll('.Slider-pagination .Slider-dot'),
    next = document.querySelector('.Slider-buttonNext'),
    previous = document.querySelector('.Slider-buttonPrevious'),
    currentSlide = 0;

// Next Slide
function nextSlide() {
    goToSlide(currentSlide + 1);
}

// Previous Slide
function previousSlide() {
    goToSlide(currentSlide - 1);
}

// Go to nth slide
// Add relevant classes for current and previous slides
function goToSlide(n) {
    utility.forEach(dots, function (elem, i) {
        utility.removeClass(elem, 'active');
    });
    utility.forEach(slides, function (elem, i) {
        elem.className = 'Slider-item';
    });
    currentSlide = (n + slides.length) % slides.length;
    utility.addClass(dots[currentSlide], 'active');
    slides[currentSlide].className = 'Slider-item showing Slider-item--showNext';
    // Fix out of bounds for previous slide index when current slide is 0
    var prevIndex = ((currentSlide) === 0) ? (slides.length - 1) : (currentSlide - 1);
    slides[prevIndex].className = 'Slider-item Slider-item--hidePrevious';
}

// Pauses slideshow when triggered
function pauseSlideshow() {
    clearInterval(slideInterval);
}

// Plays slideshow when triggered
function playSlideshow() {
    slideInterval = setInterval(nextSlide, 4000);
}

// Triggered when clicked on pager dot
function pagerClick() {
    for (var j = 0; j < dots.length; j++) {
        var dot = dots[j];

        dot.setAttribute('data-index', j);
        dot.onclick = function () {
            var dataIndex = parseInt(this.getAttribute('data-index'));
            pauseSlideshow();
            goToSlide(dataIndex);
            playSlideshow();
        };
    }
}

function ieCheck() {
    if (detectIE() === 8) {
        utility.forEach(slides, function (elem) {
            var elemBlurb = elem.querySelector('.Slider-blurb');

            if (utility.hasClass(elemBlurb, "right") || utility.hasClass(elemBlurb, "left")) {
                var elemBlurbContentHeight = elemBlurb.clientHeight;
                elemBlurb.style.marginTop = -(elemBlurbContentHeight / 2) + "px";
            }
        });

        next.style.marginTop = -(next.clientHeight / 2) + "px";
        previous.style.marginTop = -(previous.clientHeight / 2) + "px";
    }
}

// Check if main slider exists
var mainSlider = document.querySelector('.main-slider');
if (mainSlider) {
    // Check for slider blurb content alignment
    ieCheck();
    // Init the main slider. We set the first dot and slide to be shown onload.
    dots[currentSlide].className = 'Slider-dot active';
    slides[currentSlide].className = 'Slider-item showing Slider-item--showNext';
    // Check if carousel or banner
    // If not a carousel hide the controls and pager
    if (slides.length <= 1) {
        dotsWrapper.style.display = 'none';
        next.style.display = 'none';
        previous.style.display = 'none';
    } else {
        slides[slides.length - 1].className = 'Slider-item Slider-item--hidePrevious';
        var slideInterval = setInterval(nextSlide, 4000);
        next.onclick = function (e) {
            pauseSlideshow();
            nextSlide();
            playSlideshow();
            utility.preventDefault(e);
        };
        previous.onclick = function (e) {
            pauseSlideshow();
            previousSlide();
            playSlideshow();
            utility.preventDefault(e);
        };
        pagerClick();
    }
}
