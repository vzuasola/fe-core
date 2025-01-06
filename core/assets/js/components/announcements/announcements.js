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

    var params = {},
        lang = app.settings.lang,
        announcementMainModal = new modal(),
        storage = new Storage(),
        lastIdKey = 'announcement.lastId.' + lang,
        dataKey = 'announcement.data.' + lang,
        $doc = document.body,
        announcementsCounter = document.querySelector('.notification-counter'),
        modalId = 'announcementLightbox',
        $modalEl = document.getElementById(modalId),
        $modalScrollable = '#' + modalId + ' .modal-body',
        newData = [],
        unreadData = [],
        scrollObj = null,
        timerId = null;

    /**
     * Set options
     */
    var setOptions = function () {
        // Default options
        params.defaults = {
            path: utility.url('/ajax/v2/announcements'),
            errorMessage: 'Error retrieving.',
            maxHeight: 500
        };

        // extend options
        params.options = options || {};

        for (var name in params.defaults) {
            if (params.options[name] === undefined) {
                params.options[name] = params.defaults[name];
            }
        }
    };

    /**
     * Click handler when badge is clicked
     */
    var onBadgeClick = function () {
        utility.addClass($modalEl, "modal-active");
        $doc.style.overflow = 'hidden';
        announcementsCounter.style.display = 'none';
        setData();
        updateLightbox(newData);
        setBadge(0);
    };

    /**
     * Gets the announcements data
     */
    var getAnnouncements = function (callback) {
        reqwest({
            url: params.options.path,
            data: {
                nocache: new Date().getTime()
            },
            method: 'get',
            complete: function (response) {
                callback(response);
            }
        }).fail(function () {
            announcementsCounter.innerText = params.defaults.errorMessage;
        });
        return;
    };

    /**
     * Refresh announcements on background
     */
    var autoRefresh = function (timer) {
        if (null === timerId && null !== timer) {
            timerId = setTimeout(function () {
                if (!utility.hasClass($modalEl, 'modal-active')) {
                    init();
                    timerId = null;
                }
            }, (timer * 1000));
        }
    };

    /**
     * Set data to the localstorage
     */
    var setData = function () {
        var lastId = null,
            sData = [];

        if (newData.length > 0) {
            lastId = newData[0].nid;
        } else {
            lastId = "";
        }
        for (var i = 0; i < newData.length; i++) {
            // store only the NIDs
            sData.push({ nid: newData[i].nid });
        }
        storage.set(lastIdKey, lastId);
        storage.set(dataKey, JSON.stringify(sData));
    };

    /**
     * Get the data from localstorage
     */
    var getData = function () {
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
    var getUnread = function () {
        var tempData = [];
        var existingData = getData().data ? getData().data : [];

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
    var setBadge = function (count) {
        announcementsCounter.innerText = count;

        if (count > 0 && !utility.hasClass($modalEl, 'modal-active')) {
            announcementsCounter.style.display = 'inline-block';
        } else {
            announcementsCounter.style.display = 'none';
        }
    };

    /**
     * Update the lightbox content
     */
    var updateLightbox = function (data) {
        if (utility.hasClass($modalEl, 'modal-active')) {
            return;
        }

        var mBody = $modalEl.querySelector('.modal-body'),
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

        modalHeightRefresh();
    };

    var modalHeightRefresh = function () {
        setTimeout(function () {
            var mBody = $modalEl.querySelector('.modal-body'),
                containerHeight = document.querySelector('.announcement--container').clientHeight;

            if (containerHeight > params.options.maxHeight) {
                mBody.style.height = params.options.maxHeight + 'px';

                if (!scrollObj) {
                    scrollObj = new scrollbot($modalScrollable);
                }
            } else {
                mBody.style.height = 'auto';
            }

            if (scrollObj) {
                scrollObj.refresh();
                scrollObj.setScroll(0, 100);
            }

            if (detectIE() === 8) {
                announcementMainModal.centerModalContent($modalEl);
            }
        }, 1);
    };

    /**
     * Attach window onload handler
     */
    utility.addEventListener(window, 'load', function () {
        setOptions();
        init('init');
        bindEvents();
    });

    /**
     * Main Init function
     */
    var init = function (param) {
        getAnnouncements(function (response) {
            newData = response.data || [];
            getUnread();
            var unreadCount = unreadData.length;
            setBadge(unreadCount);
            updateLightbox(newData);
            autoRefresh(response.timer);

            if (unreadCount && typeof param !== 'undefined' && param === 'init') {
                document.querySelector('.icon-notification').click();
            }
        });
    };

    /**
     * Attach Events
     */
    var bindEvents = function () {
        // Event: Clicks
        utility.addEventListener($doc, 'click', function (e) {
            var modalOverlay = $modalEl.querySelector('.modal-overlay'),
                negativeClass = $modalEl.querySelector('.modal-close'),
                notificationIcon = document.querySelector('.icon-notification'),
                notificationCounter = document.querySelector('.notification-counter');

            e = e || window.event;
            var target = e.target || e.srcElement;

            if (negativeClass === target || modalOverlay === target) {
                init();
            } else if (notificationCounter === target || notificationIcon === target) {
                onBadgeClick();
            }
        });

        // Event: ESC
        utility.addEventListener($doc, 'keydown', function (evt) {
            evt = evt || window.event;

            if (evt.keyCode === 27) {
                init();
            }
        });
    };
}
