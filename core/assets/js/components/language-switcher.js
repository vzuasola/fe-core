/*
 * Language swither
 * Style/SASS: sass/components/_language-switcher.scss
 */

import * as utility from "Base/utility";
import Dropdown from "Base/dropdown";

/**
 * Function to diplay the Language selectr dropdown and get the selected Language.
 *
 * @param  {string}  speed.
 */
export default function languageSwitcher() {
    "use strict";

    var langSwitcherWrapper = document.querySelector(".language-switcher");

    if (langSwitcherWrapper) {
        var langSwitcher = new Dropdown({
            selector: ".lang-btn",
            hideDropdownOnClick: true
        });

        // Show language switcher dropdown content
        langSwitcherWrapper.getElementsByTagName("ul")[0].style.opacity = "1";

        /**
         * Function to get the content for the selected Language from DropDown.
         */
        var langDropdownList = document.querySelector(".language-switcher").querySelectorAll("li");

        for (var i = 0; i < langDropdownList.length; i++) {
            // Click Even to get the current selected Langauge and
            // redirect the page to respective language content.
            langDropdownList[i].onclick = function (e) {
                utility.preventDefault(e);

                var selectedLang = this.getAttribute("data-lang");
                var currentLang = document.querySelector(langSwitcher.options.selector).getAttribute("data-current-lang");
                // Replacing the current language to the language selected by user from the Langauge Selector.
                var hostname = window.location.hostname;
                var regexp = new RegExp(hostname + "\/" + currentLang + "(\/?.*)$", "i");
                var redirectionUrl = window.location.href.replace(regexp, hostname + "/" + selectedLang + "$1");

                window.location.href = redirectionUrl;
            };
        }
    }
}
