/**
 * Tag classes for featured promotions
 */
module.exports = function (index, length, options) {

    var set = Math.ceil((index + 1) / 3);

    if ((index % 3) < 3) {
        return options.fn(this) + " " + "row-" + set;
    } else {
        return options.inverse(this);
    }
};
