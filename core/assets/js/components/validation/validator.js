import ValidatorExtension from "Base/validation/validator-extension";
import Rules from "Base/validation/rules";
import ErrorHandler from "Base/validation/error-handler";

var FormValidator = require("BaseVendor/validate");

/**
 * The dynamic validator object
 * Allows you to activate webform validation rules
 *
 * Available options
 *     object rules An object containing the clientside rules (see rules.js)
 *     closure error(errors, event) A closure to invoke when a client side error occurs
 */
export default function Validator(options) {
    "use strict";

    var $this = this,

        // dependencies
        $validator;

    /**
     *
     */
    function setOptions() {
        // Default options
        var defaults = {
            rules: Rules,
            error: ErrorHandler,
        };

        // extend options
        options = options || {};

        for (var name in defaults) {
            if (options[name] === undefined) {
                options[name] = defaults[name];
            }
        }
    }

    /**
     *
     */
    this.init = function () {
        setOptions();

        if (app &&
            app.settings &&
            app.settings.formValidations
        ) {
            for (var form in app.settings.formValidations) {
                var ruleset = getRules(form);

                if (ruleset) {
                    $validator = new FormValidator(
                        form,
                        ruleset,
                        errorCallback
                    );

                    // extend the validator class, the extension acts like a
                    // reference call, modifying the passed object
                    new ValidatorExtension($validator);

                    var validationRules = options.rules;

                    $this.registerValidators(ruleset, validationRules);
                }
            }
        }
    };

    /**
     *
     */
    function errorCallback(errors, event) {
        try {
            var handler = options.error;
            return new handler(errors, event);
        } catch (e) {
            console.log(e);
        }
    }

    /**
     * Register the defined rules on the validators
     */
    this.registerValidators = function (ruleset, rules) {
        for (var rule in rules) {
            $validator.registerCallback(rule, rules[rule].callback);
            $validator.setMessage(rule, rules[rule].message);
        }

        for (var i = 0; i < ruleset.length; i++) {
            for (var set in ruleset[i].messages) {
                $validator.setMessage(ruleset[i].name + '.' + set.replace(/^callback_/, ''), ruleset[i].messages[set]);
            }
        }
    };

    /**
     *
     */
    function getRules(form) {
        var rules = [];

        if (app.settings.formValidations[form]) {
            for (var field in app.settings.formValidations[form]) {

                var definition = {
                    name: form + '[' + field + ']',
                    formClass: formClass,
                    messages: { },
                    args: { },
                };

                var ruleset = [];

                for (var rule in app.settings.formValidations[form][field].rules) {

                    definition.messages[rule] = app.settings.formValidations[form][field].rules[rule].message;

                    // append arguments on the rule value
                    if (typeof app.settings.formValidations[form][field].rules[rule].arguments !== 'undefined') {
                        var args = app.settings.formValidations[form][field].rules[rule].arguments;

                        definition.args[rule] = args;
                    }

                    ruleset.push(rule);
                }

                definition.rules = ruleset.join('|');

                var formClass = app.settings.formValidations[form][field]['class'];

                switch (formClass) {
                    case 'App\\Extensions\\Form\\ConfigurableForm\\Fields\\Checkboxes':
                        definition.name = definition.name + '[]';
                        break;
                }

                rules.push(definition);
            }
        }

        return rules;
    }
}

