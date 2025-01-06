/**
 * Tag classes for featured promotions
 */
module.exports = function () {
    if (this.field_product[0].field_product_id[0].value) {
        var productID = this.field_product[0].field_product_id[0].value;

        return "tag-" + productID;
    }
};
