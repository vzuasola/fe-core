import * as utility from "Base/utility";

export default function Loader(target, overlay, opacity) {
    this.target = target;
    this.overlay = overlay || false;
    this.opacity = opacity;
    this.loader = this.target.querySelector('.loader') || this.createLoader();
}

Loader.prototype.createLoader = function () {
    var loader = document.createElement('div');
    var container = document.createElement('div');
    var ray = '';

    for (var i = 0; i < 10; i++) {
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

    if (this.opacity) {
        this.loader.style.background = "rgba(0, 0, 0, " + this.opacity.toString() + ")";
    }

    // set loader as overlay within component
    if (this.overlay) {
        utility.addClass(this.target, "loader-overlay");
    }

    this.target.appendChild(this.loader);
};

Loader.prototype.hide = function () {
    if (this.loader) {
        utility.addClass(this.loader, 'hidden');
    }
};
