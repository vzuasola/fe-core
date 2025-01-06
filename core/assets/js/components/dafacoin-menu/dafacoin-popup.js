import * as utility from "Base/utility";
import * as popupTemplate from "BaseTemplate/handlebars/dcoin-popup.handlebars";
import Modal from "Base/utils/modal";
import Xlider from "Base/dcoin-xlider";

/**
 * Dafacoin Popup
 * @constructor
 */

function DcoinPopup(options) {

    /**
     * Constructor
     * @param options
     */

    // Private properties, please do not modify directly!
    this.options = null;
    this.elements = null;
    this.dcoinPopupStatusDone = "shown";

    // Set default options
    const _default = {
        selectors: {
            popupWrapper: "#dcoin-popup",
            popupContentWrapper: "#dcoin-popup-content",
        },
        product: "",
        productUrlPrefix: "",
        language: "en",
        apiUrl: "/ajax/content-sliders",
    };

    // Set options
    this.options = Object.assign({}, _default, typeof options !== 'object' ? {} : options);
}

/**
 * Initialize Guided Tour Popup
 */
DcoinPopup.prototype.init = function () {
    if (utility.getCookie("dcoin.popup.status") !== this.dcoinPopupStatusDone) {
        this.getPopupContent();
    }
};

DcoinPopup.prototype.clearPopupStatus =  function () {
    utility.removeCookie("dcoin.popup.status");
};

/**
 * Fetch popup contents
 */
DcoinPopup.prototype.getPopupContent = function () {
    var _this = this;
    var wrapper = document.querySelector(_this.options.selectors.popupContentWrapper);
    var urlParams = new URLSearchParams({ "product": _this.options.product });

    if (wrapper) {
        var fullUrl = '';
        fullUrl += this.options.language ? '/' + _this.options.language : '/en';
        fullUrl += (this.options.productUrlPrefix ? '/' + _this.options.productUrlPrefix :  '' );
        fullUrl += _this.options.apiUrl;
        fullUrl += (Array.from(urlParams).length > 0 ? '?' + urlParams.toString() : '');

        fetch(fullUrl)
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                _this.generatePopupMarkup(response);
                var modal = new Modal();
                modal.show('dcoin-popup-content');
            });
    }
};

/**
 * Generates html markup for the popup contents based from response
 * @param {*} response
 */
DcoinPopup.prototype.generatePopupMarkup = function (response) {
    var dcoinPopup = document.querySelector(this.options.selectors.popupWrapper);

    if (typeof response !== "undefined") {
        var template = popupTemplate({
            popupTitle: response.data.popupTitle,
            slides: response.data.slides
        });
        dcoinPopup.innerHTML = template;
        utility.setCookie("dcoin.popup.status", this.dcoinPopupStatusDone);
        if (response.data.slides.length > 1) {
            this.activateSlider(response);
        }
    }
};

/**
 * Add close button at the end of slide
 */
DcoinPopup.prototype.addClosebtn = function (response) {
    const button = document.createElement("button");
    button.classList.add("modal-close");
    button.classList.add("dcoin-popup-last");
    button.innerText = response.data.closeBtnLabel || "Close";
    document.querySelector(".slider-controls").appendChild(button);
};

/**
 * Activate Slider
 */
DcoinPopup.prototype.activateSlider = function (response) {
    var _this = this;
    var slider = document.querySelector("#dcoin-popup-slider");
    if (slider && slider.querySelectorAll(".dcoin-popup-content").length > 0) {
        var sliderObj_1 = new Xlider({
            selector: "#dcoin-popup-slider",
            loop: false,
            duration: 300,
            controls: false,
            draggable: false,
            onInit: function () {
                setTimeout(function () {
                    sliderObj_1.addIndicators();
                    sliderObj_1.updateIndicators();
                    _this.addClosebtn(response);

                    window.addEventListener('resize', () => {
                        _this.addClosebtn(response);

                        if (sliderObj_1.currentSlide === 0 || sliderObj_1.currentSlide !== sliderObj_1.innerElements.length - 1) {
                            utility.addClass(document.querySelector(".dcoin-popup-last"), "hidden");
                        }

                    });
                }, 10);
            },
            onChange: function (slide, $this) {
                sliderObj_1.updateIndicators();
                if (sliderObj_1.currentSlide === 0) {
                    utility.addClass(document.querySelector(".btn-prev"), "dcoin-popup-first-page");
                } else {
                    utility.removeClass(document.querySelector(".btn-prev"), "dcoin-popup-first-page");
                }

                if (sliderObj_1.currentSlide === sliderObj_1.innerElements.length - 1) {
                    utility.addClass(document.querySelector(".btn-next"), "hidden");
                    utility.removeClass(document.querySelector(".dcoin-popup-last"), "hidden");
                } else {
                    utility.removeClass(document.querySelector(".btn-next"), "hidden");
                    utility.addClass(document.querySelector(".dcoin-popup-last"), "hidden");
                }
            },
        });
        setTimeout(() => {
            if (sliderObj_1.currentSlide === 0) {
                utility.addClass(document.querySelector(".btn-prev"), "dcoin-popup-first-page");
                utility.addClass(document.querySelector(".dcoin-popup-last"), "hidden");
            }
        }, 10);
    }
};
export default DcoinPopup;
