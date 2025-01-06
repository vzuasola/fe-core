import * as utility from "Base/utility";

/**
 * Sitemap
 */
export default function sitemap() {
    "use strict";

    var doc = document.body,
        siteMap = document.querySelectorAll('.sitemap');

    if (siteMap.length > 0) {

        utility.forEach(siteMap, function (elem) {

            var parentUL = elem.querySelectorAll('.sitemap > ul'),
                parentLI = elem.querySelectorAll('.sitemap > ul > li'),
                iconClass = elem.querySelectorAll('.icon');

            utility.forEach(iconClass, function (item) {
                utility.addClass(item, "icon-document");
            });

            utility.forEach(parentUL, function (item) {
                var childUL = item.querySelectorAll('ul');

                utility.addClass(item, "parent_ul");

                utility.forEach(childUL, function (item) {
                    utility.addClass(item, "child_ul");
                    item.style.display = 'block';
                    utility.addClass(item, 'active');
                    utility.addClass(item.parentNode.querySelector('.icon'), "icon-folder");
                    utility.addClass(item.parentNode.querySelector('.icon'), "open");
                });
            });

            utility.forEach(parentLI, function (item) {
                var childLI = item.querySelectorAll('li');

                utility.addClass(item, "parent_li");
                utility.addClass(item, "leaf");
                utility.addClass(item.querySelector('.icon'), "icon-document");

                utility.forEach(childLI, function (item) {
                    utility.addClass(item.parentNode.parentNode, "expanded");
                    utility.removeClass(item.parentNode.parentNode, "leaf");
                    utility.removeClass(item.parentNode.parentNode.querySelector('.icon'), "icon-document");
                    utility.addClass(item, "child_li");
                });
            });

        });

        // Add click event to body once because quick edits & ajax calls might reset the HTML.
        utility.addEventListener(doc, 'click', function (e) {
            e = e || window.event;
            var target = e.target || e.srcElement;

            if (utility.hasClass(target, "icon-folder")) {
                var newTarget = utility.nextElementSibling(target);

                var clickedElem;

                if (newTarget.nodeName === 'SPAN') {
                    clickedElem = newTarget.parentNode;
                } else if (newTarget.nodeName === 'A') {
                    clickedElem = newTarget.parentNode;
                }

                if (clickedElem && clickedElem.querySelector('ul').length !== 0) {
                    var activeUL = clickedElem.querySelector('ul');

                    if (activeUL.style.display === "none") {
                        activeUL.style.display = "block";
                        utility.addClass(activeUL, "active");
                        utility.addClass(target, "open");
                        utility.removeClass(target, "close");
                    } else if (utility.hasClass(activeUL, "active")) {
                        activeUL.style.display = "none";
                        utility.removeClass(activeUL, "active");
                        utility.addClass(target, "close");
                        utility.removeClass(target, "open");
                    } else {
                        activeUL.style.display = "none";
                        utility.removeClass(activeUL, "active");
                        utility.addClass(target, "close");
                        utility.removeClass(target, "open");
                    }
                }
            }
        });
    }
}
