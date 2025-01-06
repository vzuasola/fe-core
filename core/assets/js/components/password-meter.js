/**
 * Decprecated
 *
 * DO NOT USE THIS
 */
import * as utility from "Base/utility";

/**
 * Password Meter
 *
 * @param string el - should be input element selector
 * @param int minLenght - should be password field minlenght
 * @param array labels - should be password meter strenght labels
 * @param string wrapper - should be password meter wrapper div selector
 * @param boolean isValid - should be tru or false if password passed other validation
 * @param string textWrapper - should be password meter strenght label wrapper div selector
 */
export default function passwordMeter(el, labels, wrapper, textWrapper, isValid) {
    var _this = this;

    _this.el = null;
    _this.averageRegex = /(?=.*[A-Z])(?=.*[a-z])|(?=.*[A-Z])(?=.*[0-9])|(?=.*[a-z])(?=.*[0-9])/g;
    _this.strongRegex = /(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])/g;

    this.passwordMeterContruct = function () {
        var strenght = _this.passwordStrengthTest();

        _this.passwordMeterRender(strenght);
    };

    this.passwordStrengthTest = function () {
        var password = _this.el.value;

        if (!_this.isValid) {
            return 'hidden';
        }

        if (_this.strongRegex.test(password)) {
            return 'strong';
        }

        if (_this.averageRegex.test(password)) {
            return 'average';
        }

        return 'weak';
    };

    this.passwordMeterRender = function (strength) {
        var strengthText = document.querySelector(_this.textWrapper);

        if (strength !== "hidden") {
            utility.removeClass(_this.wrapper, "password-meter-hidden");
            utility.removeClass(_this.wrapper, "password-meter-weak");
            utility.removeClass(_this.wrapper, "password-meter-average");
            utility.removeClass(_this.wrapper, "password-meter-strong");
            utility.addClass(_this.wrapper, "password-meter-" + strength);
            strengthText.innerHTML = _this.labels[strength];
        }

        if (strength === "hidden") {
            utility.removeClass(_this.wrapper, "password-meter-weak");
            utility.removeClass(_this.wrapper, "password-meter-average");
            utility.removeClass(_this.wrapper, "password-meter-strong");
            utility.addClass(_this.wrapper, "password-meter-hidden");
            strengthText.innerHTML = "";
        }
    };

    this.init = function (el, labels, wrapper, textWrapper, isValid) {
        _this.el = document.querySelector(el);
        _this.wrapper = document.querySelector(wrapper);
        _this.labels = labels;
        _this.isValid = isValid;
        _this.textWrapper = textWrapper;
        _this.passwordMeterContruct();
    };

    this.init(el, labels, wrapper, textWrapper, isValid);
}
