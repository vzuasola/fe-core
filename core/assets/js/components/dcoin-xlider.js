import Siema from "BaseVendor/siema";

"use strict";

export default class Xlider extends Siema {
    constructor(options) {
        super(options);

        // Xlider options
        const extendedOptions = Xlider.mergeSettings(options);

        // Merge Siema and Xlider options
        this.config = Object.assign(extendedOptions, this.config);

        this.initXlider();
    }

    initXlider() {
        // Show slider once initialized (Slider height is 0 initially from CSS)
        // this prevent unstyled component while loading
        this.selector.style.height = "auto";

        // Add class to slides container
        this.selector.firstElementChild.classList.add("banner-slides");

        // controls
        this.createControls();

        window.addEventListener('resize', () => {
            this.createControls();
            this.addIndicators();
            this.config.onChange.call(this, this.innerElements[this.currentSlide], this);
        });
    }

    /**
     * Additional config/options
     */
    static mergeSettings(options) {
        const settings = {
            controls: true
        };

        const userSttings = options;
        for (const attrname in userSttings) {
            settings[attrname] = userSttings[attrname];
        }

        return settings;
    }

    /**
     * Create element with classname
     */
    static createElem(tagName, className) {
        const element = document.createElement(tagName);
        element.classList.add(className || "");

        return element;
    }

    /**
     * Override from Siema
     */
    buildSliderFrameItem(elm) {
        const elementContainer = document.createElement('div');
        // Add class for each slider items
        elementContainer.classList.add("xlider-item");
        elementContainer.style.cssFloat = this.config.rtl ? 'right' : 'left';
        elementContainer.style.float = this.config.rtl ? 'right' : 'left';
        elementContainer.style.width = `${this.config.loop ? 100 / (this.innerElements.length + (this.perPage * 2)) : 100 / (this.innerElements.length)}%`;
        elementContainer.appendChild(elm);
        return elementContainer;
    }

    /**
     * Generate prev/next button
     */
    createControls() {
        if (this.innerElements.length <= this.perPage) {
            return;
        }
        const iconPrev = "<svg width='36' height='36' viewBox='0 0 36 36' fill='none' xmlns='http://www.w3.org/2000/svg'><circle cx='18' cy='18' r='18' transform='matrix(-1 0 0 1 36 0)' fill='#5E5E5E' fill-opacity='0.2'/><path d='M23.7954 10.0483L24.0001 9.81605L19.0818 9.81605L15.541 13.8599C13.5925 16.086 12.0001 17.9478 12.0001 17.9979C12.0001 18.0479 13.5925 19.9117 15.541 22.1358L19.0818 26.1797L23.994 26.1797L23.7627 25.9114C23.6297 25.7593 22.0312 23.9255 20.2014 21.8375C18.3716 19.7495 16.8734 18.0199 16.8734 17.9979C16.8734 17.9758 18.3859 16.2302 20.2362 14.1222L23.7954 10.0483Z' fill='black'/></svg>";
        const iconNext = "<svg width='36' height='36' viewBox='0 0 36 36' fill='none' xmlns='http://www.w3.org/2000/svg'><circle cx='18' cy='18' r='18' transform='matrix(-1 0 0 1 36 0)' fill='#ffe000'/><path d='M23.7954 10.0483L24.0001 9.81605L19.0818 9.81605L15.541 13.8599C13.5925 16.086 12.0001 17.9478 12.0001 17.9979C12.0001 18.0479 13.5925 19.9117 15.541 22.1358L19.0818 26.1797L23.994 26.1797L23.7627 25.9114C23.6297 25.7593 22.0312 23.9255 20.2014 21.8375C18.3716 19.7495 16.8734 18.0199 16.8734 17.9979C16.8734 17.9758 18.3859 16.2302 20.2362 14.1222L23.7954 10.0483Z' fill='black'/></svg>";
        const prevElem = Xlider.createElem("span", "btn-prev");
        const nextElem = Xlider.createElem("span", "btn-next");
        const controlElem = Xlider.createElem("div", "slider-controls");

        prevElem.innerHTML = iconPrev;
        nextElem.innerHTML = iconNext;
        controlElem.appendChild(prevElem);
        controlElem.appendChild(nextElem);

        this.selector.appendChild(controlElem);



        prevElem.addEventListener('click', () => this.prev());
        nextElem.addEventListener('click', () => this.next());
    }

    prev(howManySlides = 1, callback) {
        // early return when there is nothing to slide
        if (this.innerElements.length <= this.perPage) {
            return;
        }

        const beforeChange = this.currentSlide;

        if (this.config.loop) {
            const isNewIndexClone = this.currentSlide - howManySlides < 0;
            if (isNewIndexClone) {
                this.disableTransition();

                const mirrorSlideIndex = this.currentSlide + this.innerElements.length;
                const mirrorSlideIndexOffset = this.perPage;
                const moveTo = mirrorSlideIndex + mirrorSlideIndexOffset;
                const offset = (this.config.rtl ? 1 : -1) * moveTo * (this.selectorWidth / this.perPage);
                const dragDistance = this.config.draggable ? this.drag.endX - this.drag.startX : 0;

                this.sliderFrame.style[this.transformProperty] = `translate3d(${offset + dragDistance}px, 0, 0)`;
                this.currentSlide = mirrorSlideIndex - howManySlides;
            } else {
                this.currentSlide = this.currentSlide - howManySlides;
            }
        } else {
            this.currentSlide = Math.max(this.currentSlide - howManySlides, 0);
        }

        if (beforeChange !== this.currentSlide) {
            this.slideToCurrent(this.config.loop);
            this.config.onChange.call(this, this.innerElements[this.currentSlide], this);
            if (callback) {
                callback.call(this);
            }
        }
    }

    next(howManySlides = 1, callback) {
        // early return when there is nothing to slide
        if (this.innerElements.length <= this.perPage) {
            return;
        }

        const beforeChange = this.currentSlide;

        if (this.config.loop) {
            const isNewIndexClone = this.currentSlide + howManySlides > this.innerElements.length - this.perPage;
            if (isNewIndexClone) {
                this.disableTransition();

                const mirrorSlideIndex = this.currentSlide - this.innerElements.length;
                const mirrorSlideIndexOffset = this.perPage;
                const moveTo = mirrorSlideIndex + mirrorSlideIndexOffset;
                const offset = (this.config.rtl ? 1 : -1) * moveTo * (this.selectorWidth / this.perPage);
                const dragDistance = this.config.draggable ? this.drag.endX - this.drag.startX : 0;

                this.sliderFrame.style[this.transformProperty] = `translate3d(${offset + dragDistance}px, 0, 0)`;
                this.currentSlide = mirrorSlideIndex + howManySlides;
            } else {
                this.currentSlide = this.currentSlide + howManySlides;
            }
        } else {
            this.currentSlide = Math.min(this.currentSlide + howManySlides, this.innerElements.length - this.perPage);
        }
        if (beforeChange !== this.currentSlide) {
            this.slideToCurrent(this.config.loop);
            this.config.onChange.call(this, this.innerElements[this.currentSlide], this);
            if (callback) {
                callback.call(this);
            }
        }
    }

    addIndicators() {
        // create a contnier for all indicators
        // add a class 'indicators' for styling reason
        this.indicators = document.createElement('div');
        this.indicators.classList.add('indicators');
        // loop through slides to create a number of indicators
        for (let i = 0; i < this.innerElements.length; i++) {
            // create a indicator
            const indicator = document.createElement('button');

            // add a class to indicator
            indicator.classList.add('indicators-item');

            // append indicator to a container for all of them
            this.indicators.appendChild(indicator);
        }

        // add the container full of indicators after selector
        this.selector.parentNode.insertBefore(this.indicators, this.selector.nextSibling);
        this.selector.appendChild(this.indicators);
    }

    updateIndicators() {
        // loop through all indicators
        for (let i = 0; i < this.indicators.querySelectorAll('button').length; i++) {
            // if current indicator matches currentSlide prop, add a class to it, remove otherwise
            const addOrRemove = this.currentSlide === i ? 'add' : 'remove';
            this.indicators.querySelectorAll('button')[i].classList[addOrRemove]('indicators-item-active');
        }
    }
}
