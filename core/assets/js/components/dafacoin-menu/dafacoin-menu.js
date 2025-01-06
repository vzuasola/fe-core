import * as utility from "Base/utility";
import BalanceTooltip from "BaseTemplate/handlebars/dcoin-balance-tooltip.handlebars";

/**
 * Dafacoin Menu
 * @constructor
 */

function DafacoinMenu(options) {

    /**
     * Constructor
     * @param options
     */

    // Private properties, please do not modify directly!
    this.options = null;
    this.elements = null;
    this.dafacoinMenuStatus = "closed";
    this.balanceToggleStatus = "loading";
    this.walletToggleStatus = {};
    this.walletChanges = {};

    // Set default options
    const _default = {
        selectors: {
            header: 'header',
            balanceMenuBtn: '.balance-menu-btn',
            balanceMenuDiv: '#balance-menu-div',
            balanceArrowHead: '.cashier-arrowhead',
            balanceRefreshIcon: '.balance-refresh-icon',
            balanceMobileContainer: '.total-balance-container',
            balanceMenuOverlay: '#dafacoin-overlay',
            saveButton: '#balance-save-btn',
            closeButton: '#balance-close-btn',
            popupYesButton: '#popup-yes-btn',
            popupNoButton: '#popup-no-btn',
            popupOverlay: '#dafacoin-warning-overlay',
            warningPopup: '#dafacoin-warning',
            balanceMenuShade: '#balance-menu-shade',
            savedCloseButton: '#dafacoin-saved-close-btn',
            cashierMenu: '.cashier-menu',
            cashierBalance: '#cashier-balance',
            cashierMenuLoader: '.cashier-menu-loader',
            cashierBalanceAnchorMobile: '.cashier-anchor-mobile',
            cashierBalanceAnchorDesktop: '.cashier-anchor-desktop',
            cashierBalanceAccountAmount: '.cashier-account-balance-amount',
            cashierBalanceAccountCurrency: '.cashier-account-balance-currency',
            cashierBalanceAccountFormatted: '.cashier-account-balance-formatted',
            toggleCheckbox: '.token_toggle input.checkbox',
            toggleAllAnchor: '.cashier-menu-toggle-all-container .btn-holder',
            savedOverlay: '#dafacoin-saved-overlay',
            setPrioritySuccessPopupMessage: '.dafacoin-priority-set-success',
            setPriorityFailurePopupMessage: '.dafacoin-priority-set-failure',
        },
        attachments: {},
        anchors: {
            anchorBalanceMenuX: true,
            anchorBalanceMenuY: true,
        },
        balanceUrl: '/ajax/detailed-total-balance',
        balancePriorityUrl: '/ajax/set-wallet-priority',
        product: '',
        language: 'en',
    };
    if (typeof app !== "undefined") {
        // If app.settings is not available in the product importing dcoin you need to pass the `dafacoin_menu` value as
        // the value of the `attachments` parameter during initialization
        _default.attachments = app.settings.dafacoin_menu;
    }

    // Set options
    this.options = this.replaceRecursively(_default, options);
}

/**
 * Initialize method
 */
DafacoinMenu.prototype.init = function () {
    // Set main header element
    this.element = document.querySelector(this.options.selectors.header);
    // Check dafacoin menu elements exists, otherwise do not proceed with initialization
    if (!this.element.querySelector(this.options.selectors.balanceMenuDiv)) {
        return;
    }
    // Initialize balance menu position
    this.anchorBalanceMenu();
    // Bind events
    this.bindEvents();
    // Refresh account balance
    this.refreshBalance();
};

DafacoinMenu.prototype.replaceRecursively = function (target, sources) {
    // Create a deep copy of the object
    var output = Object.assign({}, target);
    // Iterate over its keys, which can be an array, numeric indexes or object key/string pair
    for (const key of Object.keys(sources)) {
        // Determine the type of data we have for this given key/value pair
        if (sources[key] && typeof sources[key] === 'object' && !Array.isArray(sources[key])) {
            // If key/value is an object, then we should process this object recursively until we find final key/pair values such as strings or ints
            Object.assign(output, { [key]: this.replaceRecursively(output[key], sources[key]) });
        } else {
            // If key/value datatype is other than an object we assume its a final value and we replace it.
            Object.assign(output, { [key]: sources[key] });
        }
    }

    return output;
};

DafacoinMenu.prototype.bindEvents = function () {
    const self = this;

    // Set elements
    const balanceMenuBtn = this.element.querySelector(this.options.selectors.balanceMenuBtn);
    const balanceMenuDiv = this.element.querySelector(this.options.selectors.balanceMenuDiv);
    const balanceRefreshIcon = this.element.querySelector(this.options.selectors.balanceRefreshIcon);
    const saveButton = this.element.querySelector(this.options.selectors.saveButton);
    const closeButton = this.element.querySelector(this.options.selectors.closeButton);
    const popupYesButton = this.element.querySelector(this.options.selectors.popupYesButton);
    const popupNoButton = this.element.querySelector(this.options.selectors.popupNoButton);
    const balanceMenuOverlay = this.element.querySelector(this.options.selectors.balanceMenuOverlay);
    const popupOverlay = this.element.querySelector(this.options.selectors.popupOverlay);
    const savedCloseButton = this.element.querySelector(this.options.selectors.savedCloseButton);
    const toggleAllButton = this.element.querySelector(this.options.selectors.toggleAllAnchor);

    // Bind listeners
    const resizer = function () {
        self.anchorBalanceMenu();
    };

    window.addEventListener('resize', resizer, true);

    this.applyToAllElements(this.options.selectors.balanceMenuBtn, function (element) {
        element.addEventListener('click', function (event) {
            event.stopPropagation();
            self.toggleDafacoinMenu();
        });
    });

    saveButton && saveButton.addEventListener("click", function () {
        // Set button loader
        self.toggleButtonLoader(saveButton);
        // Call API
        self.setWalletPriorities()
            .then(function (response) {
                self.openSavedPopup((response && response.status === 'ok'));
            })
            .finally(function () {
                // Remove is loading from save button
                self.toggleButtonLoader(saveButton, false);
                // Close popup & menu
                self.closeDafacoinPopup();
                self.closeDafacoinMenu();
            });
    });

    closeButton && closeButton.addEventListener("click", function (event) {
        if (self.haveWalletsChanged()) {
            self.openDafacoinPopup();
        } else {
            self.closeDafacoinMenu();
        }
    });

    balanceMenuDiv && balanceMenuDiv.addEventListener("click", function (event) {
        event.stopPropagation();
    });

    popupOverlay && popupOverlay.addEventListener("click", function (event) {
        event.stopPropagation();
    });

    popupYesButton && popupYesButton.addEventListener("click", function (event) {
        event.stopPropagation();
        self.closeDafacoinPopup();
        self.closeDafacoinMenu();

        self.resetWalletCheckboxes();
        self.updateSaveButton(false);
    });

    popupNoButton && popupNoButton.addEventListener("click", function (event) {
        event.stopPropagation();
        self.closeDafacoinPopup();
    });

    balanceMenuOverlay && balanceMenuOverlay.addEventListener("click", function (event) {
        event.stopPropagation();
        self.closeDafacoinMenu();
    });

    document.addEventListener("click", function (event) {
        if (self.dafacoinMenuStatus === "closed") {
            return;
        }
        event = event || window.event;
        const target = event.target || event.srcElement;

        const clickedOutsideMenu = !balanceMenuDiv.contains(target) && !balanceMenuBtn.contains(target);
        if (clickedOutsideMenu) {
            event.stopPropagation();

            self.closeDafacoinMenu();
        }
    });

    savedCloseButton && savedCloseButton.addEventListener("click", function () {
        self.closeSavedPopup();
    });

    balanceRefreshIcon && balanceRefreshIcon.addEventListener("click", function () {
        // Set loading status
        self.toggleBalance('loading');
        // Debounce the api call
        clearTimeout(self.timer);
        self.timer = setTimeout(function () {
            self.refreshBalance();
        }, 1500);

    });

    toggleAllButton && toggleAllButton.addEventListener('click', function (evt) {
        var individualToggles = self.element.querySelectorAll('.token_toggle');

        // The original status of the "Toggle All" button.
        var dcStatus = Array.from(evt.target.classList).includes('active');

        Array.from(individualToggles)
            .filter(function (toggle) {
                var checkbox = toggle.querySelector('input.checkbox');
                return checkbox.checked === dcStatus;
            }).map(function (toggle) {
                self.toggleWallet(toggle);
            });
    });

};

DafacoinMenu.prototype.applyToAllElements = function (selector, callback) {
    document.querySelectorAll(selector).forEach(function (elem) {
        callback(elem);
    });
};

DafacoinMenu.prototype.updateSaveButton = function (changesExist) {
    const saveButton = this.element.querySelector("#balance-save-btn");
    if (changesExist) {
        saveButton.disabled = false;
    } else {
        saveButton.disabled = true;
    }
};

DafacoinMenu.prototype.toggleDafacoinMenu = function () {
    if (this.dafacoinMenuStatus === "closed") {
        this.openDafacoinMenu();
    } else {
        this.closeDafacoinMenu();
    }
};

DafacoinMenu.prototype.toggleButtonLoader = function (element, setLoading = true, disable = true) {
    // Check if have a loader image
    const loaderImage = this.element.querySelector(this.options.selectors.cashierMenuLoader + ' > img');
    if (!loaderImage) {
        return;
    }
    var wasSetLoading = element.hasAttribute('data-is-loading-label');
    if (setLoading === true && wasSetLoading === false) {
        // Set current element state and build loader image
        element.setAttribute('data-is-loading-label', element.innerHTML);
        element.innerHTML = '<img src="' + loaderImage.getAttribute('src') + '" style="height: 1em;" />';
        // Check if we also need to disable it
        if (disable) {
            element.setAttribute('data-is-loading-disabled', element.getAttribute('disabled'));
            element.disabled = true;
        }
    }
    if (setLoading === false && wasSetLoading === true) {
        // Get element initial state
        element.innerHTML = element.getAttribute('data-is-loading-label');
        element.removeAttribute('data-is-loading-label');
        // Check if we also need to un disable it
        if (element.hasAttribute('data-is-loading-disabled')) {
            element.disabled = element.getAttribute('data-is-loading-disabled');
            element.removeAttribute('data-is-loading-disabled');
        }
    }
};

DafacoinMenu.prototype.closeDafacoinMenu = function () {
    const balanceMenuDiv = this.element.querySelector(this.options.selectors.balanceMenuDiv);
    utility.addClass(balanceMenuDiv, "hidden");

    this.applyToAllElements(this.options.selectors.balanceArrowHead, function (element) {
        element.innerHTML = "&#8964;";
        utility.removeClass(element, "up-arrowhead");
        utility.addClass(element, "down-arrowhead");
    });
    const dafacoinOverlay = this.element.querySelector(this.options.selectors.balanceMenuOverlay);
    utility.addClass(dafacoinOverlay, "hidden");

    this.dafacoinMenuStatus = "closed";
};

DafacoinMenu.prototype.openDafacoinMenu = function () {
    const balanceMenuDiv = this.element.querySelector(this.options.selectors.balanceMenuDiv);
    utility.removeClass(balanceMenuDiv, "hidden");

    this.applyToAllElements(this.options.selectors.balanceArrowHead, function (element) {
        element.innerHTML = "&#8963;";
        utility.removeClass(element, "down-arrowhead");
        utility.addClass(element, "up-arrowhead");
    });

    const dafacoinOverlay = this.element.querySelector(this.options.selectors.balanceMenuOverlay);
    utility.removeClass(dafacoinOverlay, "hidden");

    this.dafacoinMenuStatus = "open";
};

DafacoinMenu.prototype.closeDafacoinPopup = function () {
    const dafacoinWarning = this.element.querySelector(this.options.selectors.warningPopup);
    const dafacoinWarningOverlay = this.element.querySelector(this.options.selectors.popupOverlay);
    const balanceMenuShade = this.element.querySelector(this.options.selectors.balanceMenuShade);

    utility.addClass(dafacoinWarning, "hidden");
    utility.addClass(dafacoinWarningOverlay, "hidden");
    utility.removeClass(balanceMenuShade, "div-shade");
};

DafacoinMenu.prototype.openDafacoinPopup = function () {
    const dafacoinOverlay = this.element.querySelector(this.options.selectors.warningPopup);
    const dafacoinWarningOverlay = this.element.querySelector(this.options.selectors.popupOverlay);
    const balanceMenuShade = this.element.querySelector(this.options.selectors.balanceMenuShade);

    utility.removeClass(dafacoinOverlay, "hidden");
    utility.removeClass(dafacoinWarningOverlay, "hidden");
    utility.addClass(balanceMenuShade, "div-shade");
};

DafacoinMenu.prototype.openSavedPopup = function (success) {
    success = typeof success !== 'undefined' ? success : true;
    const dafacoinSavedPopup = this.element.querySelector(this.options.selectors.savedOverlay);
    utility.removeClass(dafacoinSavedPopup, "hidden");
    var showClass = success ? this.options.selectors.setPrioritySuccessPopupMessage : this.options.selectors.setPriorityFailurePopupMessage;
    dafacoinSavedPopup.querySelector(showClass).classList.remove('hidden');


    setTimeout(this.closeSavedPopup.bind(this), this.options.attachments.notificationPopupDisplayTime * 1000);
};

DafacoinMenu.prototype.closeSavedPopup = function () {
    const dafacoinSavedPopup = this.element.querySelector(this.options.selectors.savedOverlay);
    utility.addClass(dafacoinSavedPopup, "hidden");
    this.applyToAllElements('.dafacoin-saved-text > *', function (element) {
        element.classList.add('hidden');
    });
};

DafacoinMenu.prototype.anchorBalanceMenu = function () {
    const dcoinmenu = document.querySelector(this.options.selectors.cashierBalance);
    var selector = this.options.selectors.cashierBalanceAnchorDesktop;
    var selectorMobile = this.options.selectors.cashierBalanceAnchorMobile;
    // Build offset
    var offset = 8;
    // Apply logic for responsive sites
    var anchorMobileElement = document.querySelector(selectorMobile);
    if (this.getUserDevice() === 'mobile' && anchorMobileElement) {
        // For registration where both anchor exists check first that anchor is visible
        if(anchorMobileElement.offsetParent !== null) {
            selector = selectorMobile;
        } else {
            // For registration on tablet device using desktop view, adjust anchor position
            offset = 36;
        }
    }
    // Get element
    const anchor = document.querySelector(selector);
    if (anchor === null) {
        return;
    }
    const coord = anchor.getBoundingClientRect();
    // Seems that getBoundingClientRect() returns different values on chrome, we need to compensate for this so
    // we subtract the scrollbar width from the calculation if the scrollbar is visible
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    // Apply position
    if (this.options.anchors.anchorBalanceMenuX) {
        dcoinmenu.style.right = Math.round(window.innerWidth - coord.x - offset - (this.documentHasVerticalScrollbar() && scrollbarWidth > 0 ? scrollbarWidth : 0)) + 'px';
    }
    if (this.options.anchors.anchorBalanceMenuY) {
        dcoinmenu.style.top = Math.round(coord.y + 10) + 'px';
    }
};

DafacoinMenu.prototype.getUserDevice = function () {
    if (window.innerWidth <= 1024) {
        return 'mobile';
    }
    return 'desktop';
};

DafacoinMenu.prototype.compileApiUrl = function (destination) {
    var self = this;
    var url = '';
    url += self.options.language ? '/' + self.options.language : '/en';
    url += (self.options.product ? '/' + self.options.product : '');
    url += destination;
    return url;
};

DafacoinMenu.prototype.setWalletPriorities = function () {
    var self = this;
    var fullUrl = self.compileApiUrl(self.options.balancePriorityUrl);

    // If per-wallet-toggles are disabled OR toggleAll button is active, then toggle everything.
    const shouldToggleAllWallets = this.shouldToggleAllWallets();

    // Send data to API
    return fetch(fullUrl, {
        method: 'POST',
        body: JSON.stringify({
            priorities: self.walletChanges,
            toggleAll: shouldToggleAllWallets
        }),
        headers: {
            'Content-type': 'application/json; charset=UTF-8',
        }
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (response) {
            // Reset wallter checkboxes
            self.resetWalletCheckboxes();
            return response;
        })
        .catch(function (error) {
            // Report to console
            console.warn(error);
        })
        .finally(function () {
            // Always refresh balance, regardless
            self.refreshBalance();
        });
};

DafacoinMenu.prototype.refreshBalance = function () {
    var self = this;

    this.innerBalance = document.querySelector("#inner-total-balance");
    this.innerBalanceRows = document.querySelector(".cashier-menu-wallet-rows");
    const enablePerWalletToggles = this.options.attachments.enablePerWalletToggles;
    var excludedWallets = this.options.attachments.balanceExclusion ? this.options.attachments.balanceExclusion.map(function (walletId) {
        return ['ignore[]', walletId];
    }) : [];
    // Show loading balance
    this.toggleBalance('loading');

    // Build url for balance
    var urlParams = new URLSearchParams(excludedWallets);
    var fullUrl = self.compileApiUrl(this.options.balanceUrl);
    fullUrl += (Array.from(urlParams).length > 0 ? '?' + urlParams.toString() : '');

    // Fetch balance from API
    fetch(fullUrl)
        .then(function (response) {
            return response.json();
        })
        .then(function (response) {
            if (typeof response.total === "undefined") {
                return Promise.reject(response);
            }

            // Build formatted total string
            var replacements = {
                "{total}": response.total,
                "{currency}": response.currency,
            };
            var formattedBalance = response.format;
            Object.entries(replacements).forEach(function (element) {
                formattedBalance = formattedBalance.replace(element[0], element[1]);
            });

            let productBalance = response.total;
            const product = document.body.getAttribute("data-product");
            if (response.productMap && response.productMap.hasOwnProperty(product) && response.productMap[product] !== 0) {
                const productWallet = response.productMap[product];
                const lookup = response.breakdown.find(breakdownItem => productWallet === breakdownItem.wallet);
                if (lookup !== undefined) {
                    productBalance = lookup.total;
                }
            }

            // Apply total values to desktop and mobile elements
            self.applyToAllElements(self.options.selectors.cashierBalanceAccountAmount, function (element) {
                element.innerHTML = productBalance;
            });

            self.applyToAllElements(self.options.selectors.cashierBalanceAccountCurrency, function (element) {
                element.innerHTML = response.currency;
            });

            self.applyToAllElements(self.options.selectors.cashierBalanceAccountFormatted, function (element) {
                element.innerHTML = formattedBalance;
            });

            // Build toggles
            self.innerBalance.innerHTML = response.total;
            var filteredBreakDown = response.breakdown
                .filter(function (el) {
                    return !['block', 'uc', 'ignore'].includes(el.visibility);
                })
                .map(function (el) {
                    if (el.total === null || el.total.trim() === "") {
                        el.total = 'N/A';
                    }
                    if (el.totalToken === null || el.totalToken.trim() === "") {
                        el.totalToken = 'N/A';
                    }
                    self.walletToggleStatus[el.wallet] = el.tokenFlag;
                    return el;
                });

            var tokenLabel = response.tokenLabel;
            self.innerBalanceRows.innerHTML = BalanceTooltip({
                currency: response.rawCurrency,
                breakdown: filteredBreakDown,
                tokenLabel: tokenLabel,
                fetchErrors: self.options.attachments.fetchErrors,
                labels: {
                    'dc': self.options.attachments.labels.walletRowsHeaderDC,
                    'switch': self.options.attachments.labels.walletRowsHeaderSwitch
                }
            });

            var allBtns = self.innerBalanceRows.querySelectorAll('.token_toggle');
            if (enablePerWalletToggles) {
                allBtns.forEach(function (btn) {
                    btn.addEventListener('click', function (evt) {
                        self.toggleWallet(evt.target);
                    });
                    btn.style.cursor = 'pointer';
                });
            }

            self.setToggleAllBtn();

        })
        .catch(function (error) {
            // Report to console
            console.warn(error);
        })
        .finally(function () {
            // Show balance anyway
            self.toggleBalance('display');
        });
};

DafacoinMenu.prototype.shouldToggleAllWallets = function () {
    var checkboxes = this.element.querySelectorAll(this.options.selectors.toggleCheckbox);

    var numberOfFiatToggles = Array.from(checkboxes)
        .filter(function (checkbox) {
            return checkbox.checked !== true;
        }).length;

    return ((numberOfFiatToggles === 0) || (numberOfFiatToggles === checkboxes.length));
};

DafacoinMenu.prototype.toggleWallet = function (switchElement) {
    this.tokenToggle(switchElement);
    this.updateSaveButton(this.haveWalletsChanged());
    this.setToggleAllBtn();
};

DafacoinMenu.prototype.toggleBalance = function (status) {
    status = typeof status === 'undefined' ? {} : status;
    if ((this.balanceToggleStatus === 'loading' && status === null) || status === 'display') {
        this.applyToAllElements(this.options.selectors.cashierMenu, function (element) {
            utility.removeClass(element, "hidden");
        });
        this.applyToAllElements(this.options.selectors.cashierMenuLoader, function (element) {
            utility.addClass(element, "hidden");
        });
        this.balanceToggleStatus = 'display';
    }
    if ((this.balanceToggleStatus === 'display' && status === null) || status === 'loading') {
        this.applyToAllElements(this.options.selectors.cashierMenu, function (element) {
            utility.addClass(element, "hidden");
        });
        this.applyToAllElements(this.options.selectors.cashierMenuLoader, function (element) {
            utility.removeClass(element, "hidden");
        });
        this.balanceToggleStatus = 'loading';
    }
    // Re-anchor balance
    this.anchorBalanceMenu();
};

DafacoinMenu.prototype.formatBalance = function (balance) {
    return balance.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

DafacoinMenu.prototype.tokenToggle = function (btn) {

    var fiatLabel = btn.querySelector('.fiat');
    var dcLabel = btn.querySelector('.dafacoin');
    var checkbox = btn.querySelector('input.checkbox');
    fiatLabel.classList.toggle('active');
    dcLabel.classList.toggle('active');
    var dcActive = dcLabel.classList.contains('active');
    checkbox.checked = dcActive;

    if (this.walletToggleStatus[checkbox.dataset.walletId] === dcActive) {
        delete this.walletChanges[checkbox.dataset.walletId];
    } else {
        var currentAddition = {};
        currentAddition[checkbox.dataset.walletId] = dcActive;
        Object.assign(this.walletChanges, currentAddition);
    }

};

DafacoinMenu.prototype.haveWalletsChanged = function () {
    return Object.keys(this.walletChanges).length > 0;
};

DafacoinMenu.prototype.setToggleAllBtn = function () {
    var checkboxes = this.element.querySelectorAll(this.options.selectors.toggleCheckbox);
    const toggleAllButton = this.element.querySelector(this.options.selectors.toggleAllAnchor);

    var fiatCheckboxes = Array.from(checkboxes)
        .filter(function (checkbox) {
            return checkbox.checked !== true;
        });

    if (fiatCheckboxes.length > 0) {
        toggleAllButton.classList.remove('active');
    } else {
        toggleAllButton.classList.add('active');
    }
};

DafacoinMenu.prototype.resetWalletCheckboxes = function () {
    var self = this;
    var changes = self.walletChanges;
    Object.entries(changes).forEach(function (change) {
        var toggleButton = document.querySelector('input[data-wallet-id="' + change[0] + '"]').parentElement;
        self.toggleWallet(toggleButton);
    });
    self.walletChanges = {};
    self.updateSaveButton(false);
    self.setToggleAllBtn();
};

DafacoinMenu.prototype.documentHasVerticalScrollbar = function () {
    try {
        // Checks for old browsers in case there is no DOCTYPE declaration
        var root = document.compatMode === 'BackCompat' ? document.body : document.documentElement;
        return root.scrollHeight > root.clientHeight;
    } catch(e) {
        // Do nothing
    }
    return false;
}
export default DafacoinMenu;
