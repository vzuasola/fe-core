import * as utility from "Base/utility";

export default function Loader(target, overlay) {
    this.target = target;
    this.loader = this.target.querySelector('.loader') || this.createLoader();
    this.overlay = overlay;
}

Loader.prototype.createLoader = function () {
    var loader = document.createElement('div');
    var container = document.createElement('div');
    var ray = '';

    for (var i = 0; i < 8; i++) {
        ray += '<div class="ray" id="ray-' + i + '"></div>';
    }

    container.innerHTML = ray;

    utility.addClass(container, 'loader-container');
    utility.addClass(loader, 'loader');

    loader.appendChild(container);

    return loader;
};

Loader.prototype.show = function () {
    utility.removeClass(this.loader, 'hidden');

    var loaderOverlay = null;
    // set loader as overlay within component
    if (this.overlay) {
        utility.removeClass(this.target.querySelector('.loader-overlay'), 'hidden');
        if (this.target.querySelector('.loader-overlay') === null) {
            loaderOverlay = document.createElement('div');
            utility.addClass(loaderOverlay, "loader-overlay");
        } else {
            loaderOverlay = this.target.querySelector('.loader-overlay');
        }
        loaderOverlay.appendChild(this.loader);
    }

    if (loaderOverlay !== null) {
        this.target.appendChild(loaderOverlay);
    } else {
        this.target.appendChild(this.loader);
    }
};

Loader.prototype.hide = function () {
    if (this.loader) {
        utility.addClass(this.loader, 'hidden');
        utility.addClass(this.target.querySelector('.loader-overlay'), 'hidden');
    }
};
