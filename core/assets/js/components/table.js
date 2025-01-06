import * as utility from "Base/utility";

export default (function () {
    var Table = {};

    /**
     * Add odd and even class to the given list of elements
     *
     * @param string els
     *
     * @return string
     */
    Table.addOddEvenClass = function (els) {
        document.querySelectorAll(els);

        if (els.length !== 0) {
            utility.forEachElement(els, function (el, i) {
                utility.removeClass(el, 'odd');
                utility.removeClass(el, 'even');
                if ((i % 2) === 0) {
                    utility.addClass(el, 'even');
                } else {
                    utility.addClass(el, 'odd');
                }
            });
        }

        return els;
    };

    return Table;
})();
