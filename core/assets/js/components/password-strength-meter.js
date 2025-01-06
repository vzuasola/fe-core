import * as utility from "Base/utility";

/**
 * Password Meter - A visual assessment of password strengths and weaknesses
 *
 * Available Options:
 *      selector: Field element selector
 *      strengths: Array of labels
 *      wrapperSelector: Password meter wrapper selector
 *      textWrapperSelector: Password meter strenght label wrapper selector
 *      event: Event method
 *
 * Usage:
 *      new PasswordStrengthMeter({
 *           selector: '#FieldSelector'
 *      });
 *
 */



export default function PasswordStrengthMeter(options) {
    "use strict";

    var $this = this;

    var strengths = {
        weak: 'Weak',
        average: 'Average',
        strong: 'Strong',
        label: 'Password strength'
    };

    // Default options
    var defaults = {
        selector: "#RegistrationForm_password",
        strength: strengths,
        wrapperSelector: '.password_meter_wrapper',
        textWrapperSelector: '.password-meter-message span',
        event: 'blur',
        isValid : 0
    };

    // extend options
    $this.options = options || {};
    for (var name in defaults) {
        if ($this.options[name] === undefined) {
            $this.options[name] = defaults[name];
        }
    }

    if (document.querySelector(this.options.selector)) {
        this.init();
    }
}

PasswordStrengthMeter.prototype.init = function () {
    var $this = this;

    this.passwordField = document.querySelector(this.options.selector);
    this.passwordContainer = utility.findParent(this.passwordField, '.form-item');

    $this.generateMarkup();

    utility.addEventListener($this.passwordField, $this.options.event, function (event) {
        $this.options.isValid = 1;
        $this.passwordMeterContruct();
    });

    if ($this.options.event === 'blur') {
        utility.addEventListener($this.passwordField, 'focus', function (event) {
            $this.options.isValid = 0;
            $this.passwordMeterContruct();
        });
    }

    utility.listen($this.passwordField, "focus", function (event, src) {
        $this.options.isValid = 1;
        $this.formAnnotationRender();
    });

    utility.listen($this.passwordField, "keydown", function (event, src) {
        $this.options.isValid = 1;
        $this.formAnnotationRender();
    });

    utility.listen($this.passwordField, "blur", function (event, src) {
        $this.hideFormAnnotationMeter();
    });
};

PasswordStrengthMeter.prototype.passwordMeterContruct = function () {
    var strength = this.passwordStrengthTest();
    this.passwordMeterRender(strength);
};

PasswordStrengthMeter.prototype.passwordStrengthTest = function () {
    var $this = this,
        password = $this.passwordField.value,
        averageRegex = /(?=.*[A-Z])(?=.*[a-z])|(?=.*[A-Z])(?=.*[0-9])|(?=.*[a-z])(?=.*[0-9])/g,
        strongRegex = /(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])/g;

    if (!($this.options.isValid) ||
        (utility.hasClass($this.passwordContainer, 'has-error')) ||
        ($this.passwordField.value === '')) {
        return 'hidden';
    }

    if (strongRegex.test(password)) {
        return 'strong';
    }

    if (averageRegex.test(password)) {
        return 'average';
    }

    return 'weak';
};

PasswordStrengthMeter.prototype.passwordMeterRender = function (strength) {
    var strengthText = document.querySelector(this.options.textWrapperSelector),
        wrapper = document.querySelector(this.options.wrapperSelector),
        passwordMeterWrapper = document.querySelector('.password-meter');

    utility.removeClass(wrapper, "password-meter-hidden");
    utility.removeClass(wrapper, "password-meter-weak");
    utility.removeClass(wrapper, "password-meter-average");
    utility.removeClass(wrapper, "password-meter-strong");

    if (strength !== "hidden") {
        utility.removeClass(passwordMeterWrapper, 'hidden');
        wrapper.style.display = 'block';
        utility.addClass(wrapper, "password-meter-" + strength);
        strengthText.innerHTML = this.options.strength[strength];
    }

    if (strength === "hidden") {
        utility.addClass(passwordMeterWrapper, 'hidden');
        wrapper.style.display = 'none';
        utility.addClass(wrapper, "password-meter-hidden");
        strengthText.innerHTML = "";
    }
};

PasswordStrengthMeter.prototype.generateMarkup = function () {
    var formItem = document.createElement('div');
    utility.addClass(formItem, 'form-item');
    utility.addClass(formItem, 'hidden');
    utility.addClass(formItem, 'password-meter');

    var markupHtml = '<label class="form-label"></label><div class="form-field"><div class="password_meter_wrapper password-meter-hidden" style="display:none"><div><div class="password-meter-message">';
    markupHtml += this.options.strength['label'];
    markupHtml += ': <span></span></div></div><div><div class="password-meter-bar-bg"><div class="password-meter-bar"></div></div></div></div></div>';

    formItem.innerHTML = markupHtml;

    this.passwordContainer.parentNode.insertBefore(formItem, this.passwordContainer.nextSibling);
};

PasswordStrengthMeter.prototype.formAnnotationRender = function () {
    if (this.passwordField.hasAttribute("data-annotation-weak") || this.passwordField.hasAttribute("data-annotation-average")) {
        var strength = this.passwordStrengthTest();

        if (strength === "hidden" || strength === "weak" || strength === "average") {
            this.showFormAnnotationMeter(strength);
        }

        if (strength === "strong") {
            this.hideFormAnnotationMeter();
        }
    }
};

PasswordStrengthMeter.prototype.showFormAnnotationMeter = function (strength) {
    var annotationElem = createAnnotation.call(this, strength),
        formField = utility.findParent(this.passwordField, '.form-field');

    this.hideFormAnnotationMeter();

    formField.insertBefore(annotationElem, this.passwordField.nextSibling);
};

PasswordStrengthMeter.prototype.hideFormAnnotationMeter = function () {
    var annotationElem = this.passwordContainer.querySelector(".form-annotation-meter");

    if (annotationElem) {
        annotationElem.remove();
    }
};

function createAnnotation(strength) {
    var span = document.createElement("span"),
        annotationData;

    utility.addClass(span, "form-annotation-meter");

    switch (strength) {
        case "hidden":
            annotationData = this.passwordField.getAttribute("data-annotation");
            break;
        case "weak":
            annotationData = this.passwordField.getAttribute("data-annotation-weak");
            break;
        case "average":
            annotationData = this.passwordField.getAttribute("data-annotation-average");
            break;
    }

    span.innerHTML = annotationData;

    return span;
}
