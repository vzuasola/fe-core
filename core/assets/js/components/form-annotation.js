import * as utility from "Base/utility";

utility.forEach(document.querySelectorAll('[data-annotation]'), function (elem) {

    // Add annotation on focus
    utility.addEventListener(elem, 'focus', function () {
        if (utility.eventType() === 'touchend') {
            var annotationElem = document.querySelector('.form-annotation');
            if (annotationElem !== null) {
                annotationElem.parentNode.removeChild(annotationElem);
            }
        }

        if (this.hasAttribute('data-annotation') &&
            (!this.hasAttribute('data-annotation-weak') ||
            !this.hasAttribute('data-annotation-average'))
        ) {
            var span = document.createElement('span');
            span.className = 'form-annotation';
            span.innerHTML = this.getAttribute('data-annotation');

            // Insert to DOM
            if (this.hasAttribute('data-parent-annotation')) {
                var elemParent = this.getAttribute('data-parent-annotation');
                elemParent = document.querySelector(elemParent);

                if (elemParent !== null) {
                    span.className = 'form-annotation transfer-form-annotation';
                    elemParent.parentNode.insertBefore(span, elemParent.nextSibling);
                }
            } else {
                this.parentNode.insertBefore(span, this.nextSibling);
            }
        }
    });

    // Remove annotation on Blur
    utility.addEventListener(elem, 'blur', function () {
        if (this.hasAttribute('data-parent-annotation')) {
            var elemParent = this.getAttribute('data-parent-annotation');
            elemParent = document.querySelector(elemParent);

            if (elemParent !== null) {
                var childElem = document.querySelector('.form-annotation');
                if (childElem !== null) {
                    elemParent.parentNode.removeChild(childElem);
                } else {
                    try {
                        this.parentNode.removeChild(document.querySelector('.form-annotation'));
                    } catch (e) { }
                }
            }
        } else {
            try {
                this.parentNode.removeChild(document.querySelector('.form-annotation'));
            } catch (e) { }
        }
    });
});
