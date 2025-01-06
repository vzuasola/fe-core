import * as utility from "Base/utility";

import template from "BaseTemplate/handlebars/floating-banner.handlebars";

import lightboxTemplate from "BaseTemplate/handlebars/floating-banner-lightbox.handlebars";
import xhr from "BaseVendor/reqwest";
import detectIE from "Base/browser-detect";
import lazyload from "Base/lazy-load";
import Modal from "Base/utils/modal";

/**
 *
 */
export default function FloatingBanner() {
    "use strict";

    var floatBannerPositionClose = "-290px",
        floatBannerPositionOpen = "0px";

    /**
     *
     */
    function init() {
        doRequest();
    }

    /**
     *
     */
    function doRequest() {

        var data = {
            path: encodeURIComponent(app.settings.path),
        };

        var queryParams = utility.getParameters(window.location.href);

        Object.keys(queryParams).map(function (key) {
            if (key !== "") {
                return data[key] = queryParams[key];
            }
        });

        xhr({
            url: utility.url('ajax/floating-banners'),
            type: 'json',
            data: data,
        }).then(function (response) {
            var data = {
                data: response,
                lang: app.settings.lang,
            };
            var modal = new Modal();
            var floatingBannerContainer = document.querySelector('.floating-banners-container'),
                atLeastOnedesktopItemrExists = false,
                v2IsEnabled = response.v2;
            utility.forEach(response.items, function (item) {
                if (item.field_platform[0].value === "desktop") {
                    if (Object.keys(item.field_banner_item).length > 0) {
                        atLeastOnedesktopItemrExists = true;
                        return;
                    }
                }
            });

            if (v2IsEnabled && atLeastOnedesktopItemrExists) {
                var livechat = document.querySelector('.main .livechat');
                if (livechat) {
                    livechat.classList.add('livechat-floting-banner');
                }
                var modalContainer = document.querySelector('#floating-banner-revamp');

                if (modalContainer) {
                    modalContainer.innerHTML = lightboxTemplate(data);
                    filterVisibility(modalContainer, '.floating-banner-item');
                    var title = document.querySelector('.floating-banner-lightbox-title');
                    if (title) {
                        var titles = title.querySelectorAll('span');
                        if (titles.length > 1) {
                            titles[0].append('&');
                        }
                    }
                    var downloads = document.querySelector('.floating-banner-lightbox-content-downloads');
                    var helpCenter = document.querySelector('.floating-banner-lightbox-content-help-center');
                    var bannerComponentTitle = document.querySelector('.floating-banners-title').querySelector('h1');
                    bannerComponentTitle.innerHTML = title.querySelector('h1').innerHTML;
                    if (downloads && helpCenter) {
                        addSeparator(downloads);
                    } else if (downloads) {
                        document.querySelector('.phone').remove();
                        document.querySelector('.slash').remove();
                    } else if (helpCenter) {
                        document.querySelector('.download').remove();
                        document.querySelector('.slash').remove();
                    }

                    document.querySelector('.floating-banners-container').style.display = 'block';
                }

                utility.addEventListener(floatingBannerContainer, 'click', function (event) {
                    event.preventDefault();
                    modal.show('floatingBannerLightbox');
                });
            } else {
                var wrapper = document.querySelector('.floating-banner-container');

                if (wrapper) {
                    wrapper.innerHTML = template(data);
                    filterVisibility(wrapper);
                    activateSlider();
                    utility.triggerEvent(document, "modal_init");
                }
            }

            if (floatingBannerContainer && !atLeastOnedesktopItemrExists) {
                document.querySelector('.floating-banners-container').remove();
            }

        });
    }

    /**
     *
     */
    function addSeparator(element) {
        var separator = document.createElement('div');
        separator.className = 'floating-banner-lightbox-content-separator';
        separator.innerHTML = '&nbsp';
        element.after(separator);
    }

    /**
     *
     */
    function filterVisibility(element, bannerElements) {
        var platform = document.body.getAttribute('data-device-view');
        var os = document.body.getAttribute('data-device-os');

        bannerElements = bannerElements || '.floating-banner';
        var banners = element.querySelectorAll(bannerElements);

        utility.forEach(banners, function (value) {
            var bannerPlatform = value.getAttribute('data-device-platform');
            var bannerOS = value.getAttribute('data-device-os');

            if (bannerPlatform && bannerPlatform !== platform) {
                value.style.display = 'none';
            }

            if (os) {
                var pattern = new RegExp(os, 'i');

                if (bannerOS && pattern.test(bannerOS)) {
                    value.style.display = 'none';
                }
            }
        });
    }

    /**
     *
     */
    function activateSlider() {
        // Need seperate 'setTimeout' functions as a fix for IE8
        setTimeout(function () {
            slideBanner('left');
        }, 50);

        setTimeout(function () {
            slideBanner('right');
        }, 50);
    }

    /**
     *
     */
    function slideBanner(position) {
        var banners = document.querySelectorAll('.floating-banner--' + position);

        utility.forEach(banners, function (floatBanner) {
            var floatBannerButton = floatBanner.querySelector(".floating-banner--title"),
                floatBannerDrawer = floatBanner.querySelector(".floating-banner--items"),
                floatBannerDrawerWrapper = floatBanner.querySelector(".floating-banner--title-wrapper");

            // Show banners
            floatBanner.style.opacity = "1";

            if (detectIE()) {
                utility.addClass(floatBannerButton, 'ie' + detectIE());
            }

            // Fix for whitespace while facing-right-stacked-downwards
            var items = "";

            if (utility.hasClass(floatBannerDrawerWrapper, 'floating-banner--align__right')) {
                var floatingBannerTitle = floatBannerButton.innerHTML;
                var titleString = floatingBannerTitle.split(/(\s+)/);

                titleString = utility.filter(titleString, function (e) {
                    return e.trim().length > 0;
                });

                floatingBannerTitle = String(titleString).replace(/,/g, ' ');
                utility.forEach(floatingBannerTitle, function (item) {
                    item += '<br>';
                    items += item;
                });

                floatBannerButton.innerHTML = items;
            }

            if (floatBannerDrawer) {
                // Hide floating banners initially
                floatBanner.style[position] = floatBannerPositionClose;
                utility.addClass(floatBannerDrawer, 'close');
                utility.addClass(floatBannerButton, 'close');
                utility.addClass(floatBanner, 'close');

                // Detect touch support
                if (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch) {
                    utility.addEventListener(floatBanner, "click", function () {
                        if (utility.hasClass(floatBanner, 'open')) {
                            floatBannerToggle(floatBannerDrawer, floatBannerButton, floatBanner, position, 'close');
                        } else {
                            floatBannerToggle(floatBannerDrawer, floatBannerButton, floatBanner, position, 'open');
                        }
                    });
                } else {
                    // Callback function on mouse hover events
                    utility.addEventListener(floatBanner, 'mouseover', function () {
                        floatBannerToggle(floatBannerDrawer, floatBannerButton, floatBanner, position, 'open');
                    });
                }

                // Callback function on mouse hover events
                utility.addEventListener(floatBanner, 'mouseout', function () {
                    floatBannerToggle(floatBannerDrawer, floatBannerButton, floatBanner, position, 'close');
                });
            }
        });
    }

    /**
     *
     */
    function floatBannerToggle(floatBannerDrawer, floatBannerButton, floatBanner, position, state) {
        floatBanner.style[position] = state === 'open' ? floatBannerPositionOpen : floatBannerPositionClose;
        utility.removeClass(floatBanner, state === 'open' ? 'close' : 'open');
        utility.addClass(floatBanner, state !== 'open' ? 'close' : 'open');
        utility.removeClass(floatBannerDrawer, state === 'open' ? 'close' : 'open');
        utility.addClass(floatBannerDrawer, state !== 'open' ? 'close' : 'open');
        utility.removeClass(floatBannerButton, state === 'open' ? 'close' : 'open');
        utility.addClass(floatBannerButton, state !== 'open' ? 'close' : 'open');

        setTimeout(function () {
            lazyload.revalidate();
        }, 100);
    }

    init();
}
