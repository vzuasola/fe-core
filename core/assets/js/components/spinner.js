import * as utility from "Base/utility";

/**
 * Spinner
 *
 * @param Node target container of the spinner to insert to
 * @param Boolean overlay set spinner as overlay to its container
 * @param Boolean switchText switch content/text with spinner?
 * @param Boolean dark dark loader theme
 */
export default function Spinner(target, overlay, switchText, dark) {
    this.target = target;
    this.overlay = overlay || false;
    this.switchText = switchText;
    this.dark = dark,
    this.spinnerClass = "spinner";
    this.spinner = this.target.querySelector(this.spinnerClass) || createSpinner.call(this);
}

Spinner.prototype.show = function () {
    removeText.call(this);

    utility.removeClass(this.spinner, 'hidden');

    // set spinner as overlay within component
    if (this.overlay) {
        utility.addClass(this.target, "spinner-overlay");
    }

    this.target.appendChild(this.spinner);
};

Spinner.prototype.hide = function () {
    if (this.spinner) {
        showText.call(this);
        utility.addClass(this.spinner, 'hidden');
    }
};

function createSpinner() {
    var spinner = document.createElement('img');
    spinner.src = !this.dark ? utility.asset("images/balance-loader-white.gif") : utility.asset("images/loader.gif");
    utility.addClass(spinner, this.spinnerClass);

    return spinner;
}

function removeText() {
    if (this.switchText) {
        // cached original text
        this.origText = this.target.innerHTML;

        // cached width/height
        var cachedWidth = this.target.offsetWidth,
            cachedHeight = this.target.offsetHeight;

        this.target.innerHTML = "";
        this.target.style.width = cachedWidth + "px";
        this.target.style.height = cachedHeight + "px";
    }
}

function showText() {
    if (this.switchText) {
        this.target.innerHTML = this.origText;
    }
}
