import * as utility from "Base/utility";
import reqwest from "BaseVendor/reqwest";
import modal from "Base/modal";
import detectIE from "Base/browser-detect";
import scrollbot from "BaseVendor/scrollbot";
import Storage from "Base/utils/storage";

/**
 * Lists out the result of the Annoucements.
 */
export default function Announcements(options) {
    "use strict";

    var _this = this,
        lang = app.settings.lang,
        announcementMainModal = new modal(),
        storage = new Storage(),
        lastIdKey = 'announcement.lastId.' + lang,
        dataKey = 'announcement.data.' + lang,
        doc = document.body,
        announcementsCounter = document.querySelector('.notification-counter'),
        modalId = 'announcementLightbox',
        modalEl = document.getElementById(modalId),
        modalScrollable = '#' + modalId + ' .modal-body',
        newData = [],
        unreadData = [],
        scrollObj = null;

    /**
     * Set options
     */
    this.setOptions = function () {
        // Default options
        _this.defaults = {
            path: utility.url('/ajax/announcements'),
            errorMessage: 'Error retrieving.',
            autoRefreshTime: 180000,
            maxHeight: 500
        };

        // extend options
        _this.options = options || {};

        for (var name in _this.defaults) {
            if (_this.options[name] === undefined) {
                _this.options[name] = _this.defaults[name];
            }
        }
    };

    /**
     * Click handler when badge is clicked
     */
    this.onBadgeClick = function () {
        utility.addClass(modalEl, "modal-active");
        doc.style.overflow = 'hidden';
        announcementsCounter.style.display = 'none';
        _this.setData();
        _this.updateLightbox(newData);
        _this.setBadge(0);
    };

    /**
     * Gets the announcements data
     */
    this.getAnnouncements = function (callback) {
        reqwest({
            url: _this.options.path,
            data: {
                nocache: new Date().getTime()
            },
            method: 'get',
            complete: function (response) {
                callback(response.data);
            }
        }).fail(function () {
            announcementsCounter.innerText = _this.defaults.errorMessage;
        });
        return;
    };

    /**
     * Refresh announcements on background
     */
    this.autoRefresh = function () {
        setInterval(function () {
            if (!utility.hasClass(modalEl, 'modal-active')) {
                _this.init();
            }
        }, _this.options.autoRefreshTime);
    };

    /**
     * Set data to the localstorage
     */
    this.setData = function () {
        var lastId = null;

        if (newData.length > 0) {
            lastId = newData[0].nid;
        } else {
            lastId = "";
        }

        storage.set(lastIdKey, lastId);
        storage.set(dataKey, JSON.stringify(newData));
    };

    /**
     * Get the data from localstorage
     */
    this.getData = function () {
        var data = [];

        if (storage.get(dataKey)) {
            data = JSON.parse(storage.get(dataKey));
        }

        return {
            lastId: storage.get(lastIdKey),
            data: data
        };
    };

    /**
     * Get the unread announcements
     */
    this.getUnread = function () {
        var tempData = [];
        var existingData = this.getData().data ? this.getData().data : [];

        if (newData) {
            for (var i = 0; i < newData.length; i++) {
                var isExisting = false;

                for (var j = 0; j < existingData.length; j++) {
                    if (newData[i].nid === existingData[j].nid) {
                        isExisting = true;
                    }
                }

                if (!isExisting) {
                    tempData.push(newData[i]);
                }
            }
        }

        unreadData = tempData;
    };

    /**
     * Set the badge count
     */
    this.setBadge = function (count) {
        if (announcementsCounter) {
            announcementsCounter.innerText = count;

            if (count > 0 && !utility.hasClass(modalEl, 'modal-active')) {
                announcementsCounter.style.display = 'inline-block';
            } else {
                announcementsCounter.style.display = 'none';
            }
        }
    };

    /**
     * Update the lightbox content
     */
    this.updateLightbox = function (data) {
        if (utility.hasClass(modalEl, 'modal-active')) {
            return;
        }

        var mBody = modalEl.querySelector('.modal-body'),
            defaultMessage = mBody.querySelector('.announcement--default-message'),
            announcementContainer = mBody.querySelector('.announcement--container');

        if (data && data.length > 0) {
            utility.removeClass(announcementContainer, 'hidden');
            utility.addClass(defaultMessage, 'hidden');

            var html = "";

            utility.forEach(data, function (item) {
                html += "<div class='announcement--list announcement--id-" + item.nid + "'>";
                html += item.text;
                html += "</div>";
            });

            announcementContainer.innerHTML = html;
        } else {
            utility.removeClass(defaultMessage, 'hidden');
            utility.addClass(announcementContainer, 'hidden');
        }

        _this.modalHeightRefresh();
    };

    this.modalHeightRefresh = function () {
        setTimeout(function () {
            var mBody = modalEl.querySelector('.modal-body'),
                containerHeight = document.querySelector('.announcement--container').clientHeight;

            if (containerHeight > _this.options.maxHeight) {
                mBody.style.height = _this.options.maxHeight + 'px';

                if (!scrollObj) {
                    scrollObj = new scrollbot(modalScrollable);
                }
            } else {
                mBody.style.height = 'auto';
            }

            if (scrollObj) {
                scrollObj.refresh();
                scrollObj.setScroll(0, 100);
            }

            if (detectIE() === 8) {
                announcementMainModal.centerModalContent(modalEl);
            }
        }, 1);
    };

    /**
     * Attach window onload handler
     */
    utility.addEventListener(window, 'load', function () {
        _this.setOptions();
        _this.init('init');
        _this.bindEvents();
        _this.autoRefresh();
    });

    /**
     * Main Init function
     */
    this.init = function (param) {
        _this.getAnnouncements(function (response) {
            newData = response;
            _this.getUnread();
            var unreadCount = unreadData.length;
            _this.setBadge(unreadCount);
            _this.updateLightbox(newData);

            if (unreadCount && typeof param !== 'undefined' && param === 'init') {
                document.querySelector('.icon-notification').click();
            }
        });
    };

    /**
     * Attach Events
     */
    this.bindEvents = function () {
        // Event: Clicks
        utility.addEventListener(doc, 'click', function (e) {
            var modalOverlay = modalEl.querySelector('.modal-overlay'),
                negativeClass = modalEl.querySelector('.modal-close'),
                notificationIcon = document.querySelector('.icon-notification'),
                notificationCounter = document.querySelector('.notification-counter');

            e = e || window.event;
            var target = e.target || e.srcElement;

            if (negativeClass === target || modalOverlay === target) {
                _this.init();
            } else if (notificationCounter === target || notificationIcon === target) {
                _this.onBadgeClick();
            }
        });

        // Event: ESC
        utility.addEventListener(doc, 'keydown', function (evt) {
            evt = evt || window.event;

            if (evt.keyCode === 27) {
                _this.init();
            }
        });
    };
}
