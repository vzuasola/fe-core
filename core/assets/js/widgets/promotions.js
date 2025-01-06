import xhr from "BaseVendor/reqwest";
import template from "BaseTemplate/handlebars/widgets/menu/promotion.handlebars";

export default function PromotionsWidget(container) {
    var item = container.querySelector('section');
    var errorMessage = item.getAttribute('data-promotion-error-message');

    /**
     *
     */
    this.init = function () {
        var uri = item.getAttribute('data-promotion-endoint');

        if (uri) {
            uri = uri + '?nocache=' + (new Date()).getTime();

            xhr({
                url: uri,
                type: 'json',
                method: 'get',
                crossOrigin: true,
            }).then(function (response) {
                item.style.display = 'block';

                response.promotions = response.promotions.slice(0, 2);

                item.querySelector('.menu-widget-promotion-container').innerHTML = template(response);
            }).fail(function (error, message) {
                doFail();
            });
        } else {
            doFail();
        }
    };

    /**
     *
     */
    function doFail() {
        var wrapper = item.querySelector('.menu-widget-promotion-container');

        wrapper.innerHTML = '<p class="text-center mt-20 mb-25">' + errorMessage + '</p>';
    }

    this.init();
}
