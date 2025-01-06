import * as utility from "Base/utility";
import xhr from "BaseVendor/reqwest";
import Slider from 'Base/slider_v2';
import sliderTemplate from "BaseTemplate/handlebars/banner/banner.handlebars";

/**
 * Fetch and renders all banner
 */
export default function Banner(options) {
    "use strict";

    var self = this,
        $bannerElements = {};

    var __construct = function () {
        setOptions();
        setElements();
        if (typeof self.options.onStart === 'function') {
            self.options.onStart.apply(null, [$bannerElements]);
        }
        fetchBanners();
    };

    /**
     * Set options
     */
    var setOptions = function () {
        // Default options
        self.defaults = {
            apiPath: utility.url('/ajax/slider'),
            apiParams: {},
            errorMessage: 'Error retrieving.',
            onStart: null,
            onComplete: null,
            onFail: null,
            template: sliderTemplate,
            wrapper: 'main-banner-section',
            customData: null,
            sliderOpts: null,
        };

        // extend options
        self.options = options || {};

        for (var name in self.defaults) {
            if (self.options[name] === undefined) {
                self.options[name] = self.defaults[name];
            }
        }
    };

    var setElements = function () {
        $bannerElements.$wrapper = document.getElementById(self.options.wrapper);
        $bannerElements.$loader = document.getElementById('banner-loader');
        $bannerElements.$banners = document.querySelector(self.options.wrapper + ' .banner-slides');
    };

    /**
     * Get all banner data
     */
    var fetchBanners = function () {
        var time = new Date();
        self.options.apiParams.nc = [
            time.getUTCFullYear(),
            time.getUTCMonth(),
            time.getUTCDate()
        ].join('');

        xhr({
            url: self.options.apiPath,
            data: self.options.apiParams,
        }).then(function (response) {
            renderBanners(response.data);
        }).fail(function (err, msg) {
            if (typeof self.options.onFail === 'function') {
                self.options.onFail.apply(null, [err, msg, $bannerElements]);
            }
        });
    };

    var renderBanners = function (data) {
        if (data.length > 0) {
            if (typeof self.options.preRender === 'function') {
                data = self.options.preRender.apply(this, [$bannerElements, data]);
            }

            // Do something if there are banners fetched
            var templateData = {
                banners: data
            };

            // Add new custom data to be added to the template
            if (self.options.customData !== null) {
                templateData.customData = self.options.customData;
            }

            $bannerElements.$wrapper.innerHTML = self.options.template(templateData);

            // Initialize the slider effect
            self.sliderObject = initSlider();
        } else {
            // Remove loader when there are is no data
            $bannerElements.$wrapper.innerHTML = '';
        }

        if (typeof self.options.onComplete === 'function') {
            self.options.onComplete.apply(null, [$bannerElements, data]);
        }
    };

    var initSlider = function () {
        if (self.options.sliderOpts !== null) {
            return new Slider(self.options.sliderOpts);
        }

        return null;
    };

    __construct();

    return this;
}
