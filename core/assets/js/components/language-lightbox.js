import * as utility from "Base/utility";

if (document.querySelector(".language-lightbox")) {
    var langDropdownList = document.querySelector(".language-lightbox").querySelectorAll("li");

    for (var i = 0; i < langDropdownList.length; i++) {
        // Click Even to get the current selected Langauge and
        // redirect the page to respective language content.
        langDropdownList[i].onclick = function (e) {
            utility.preventDefault(e);

            var selectedLang = this.getAttribute("data-lang");
            var currentLang = app.settings.lang;
            // Replacing the current language to the language selected by user from the Langauge Selector.
            var hostname = window.location.hostname;
            var regexp = new RegExp(hostname + "\/" + currentLang + "(\/?.*)$", "i");
            var redirectionUrl = window.location.href.replace(regexp, hostname + "/" + selectedLang + "$1");

            window.location.href = redirectionUrl;
        };
    }
}
