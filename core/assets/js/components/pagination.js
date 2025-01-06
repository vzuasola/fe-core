import * as utility from "Base/utility";

/**
 * Add Button Previous and Next functionality for list.js pagination
 */
function Pagination(options) {
    var $this = this;

    // Default options
    var defaults = {
        next_selector: "#button-next", // selector for next button
        prev_selector: "#button-prev" // selector for next button
    };

    // extend options
    $this.options = options || {};
    for (var name in defaults) {
        if ($this.options[name] === undefined) {
            $this.options[name] = defaults[name];
        }
    }

    /**
     *
     */
    this.init = function () {
        var btnPrev = document.querySelector($this.options.prev_selector),
            btnNext = document.querySelector($this.options.next_selector);

        utility.addEventListener(btnPrev, 'click', function (e) {
            var currentActivePage = document.querySelector('.pagination .active'),
                liNavs = document.querySelectorAll('.pagination li');
            var nextPrevElem = $this.getNextPrevElemements(liNavs, currentActivePage);

            if (nextPrevElem[0] != null) {
                nextPrevElem[0].click();
            }
        });

        utility.addEventListener(btnNext, 'click', function (e) {
            var currentActivePage = document.querySelector('.pagination .active'),
                liNavs = document.querySelectorAll('.pagination li');
            var nextPrevElem = $this.getNextPrevElemements(liNavs, currentActivePage);

            if (nextPrevElem[1] != null) {
                nextPrevElem[1].click();
            }
        });
    };

    /**
     *
     */
    this.getNextPrevElemements = function (elements, selectionDiv) {
        var previous,
            next;

        for (var i = 0; i < elements.length; i++) {
            if (elements[i] === selectionDiv) {
                previous = elements[i - 1];
                next = elements[i + 1];
            }
        }

        return [previous, next];
    };
}

export default Pagination;
