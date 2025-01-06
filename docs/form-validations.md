# Form Validations

The Form API provides two pair of validation sets, one for clientside, and one for
server side. 

There are core provided validations but you can extend or modify
existing validations by overriding the core provided one with your site specific
validations.

# Creating new validation entry

All validation are defined on `config/forms.yml` under the validations array.

```yaml
validations:
  default: App\Extensions\Form\Validators\Validators:default
  required: App\Extensions\Form\Validators\Validators:required
  alphanumeric: App\Extensions\Form\Validators\Validators:alphanumeric
  no_symbols: App\Extensions\Form\Validators\Validators:noSymbols
  numeric: App\Extensions\Form\Validators\Validators:numeric
  numeric_symbols: App\Extensions\Form\Validators\Validators:numericSymbols
  email: App\Extensions\Form\Validators\Validators:email
  min_length: App\Extensions\Form\Validators\Validators:min
  max_length: App\Extensions\Form\Validators\Validators:max

```

Each has their own key value pair of classes. These classes can be explained on
the `Creating Server Side Validation entry` section.

Add your entry as is on your site specific `config\forms.yml`

```yaml
validations:
  my_validation: App\MyProduct\Extensions\Forms\Validators\MyValidator:validate
```

# Including the Clientside Validation Script

To make clientside validation work, you need to include the clientside validation
script.

Add this to your script.

```javascript
import Validator from "Base/validation/validator";

var validator = new Validator();
validator.init();
```

# Extending the Clientside Validation Script

After adding validation entries to `forms.yml`

Import the client side like this

```javascript
import Validator from "Base/validation/validator";
import MyRules from "Site/validator/rules";

var validator = new Validator();

validator.init({
    rules: MyRules
});
```

> See core file `/core/assets/js/components/validation/rules.js` for a concrete example.

# Creating a custom Clientside Validation error handler

By default the validator class has its own error handler and rules, if you don't
like the current error handling mechanism, or would like your own, you can
create a new one.

```javascript
import Validator from "Base/validation/validator";
import MyErrorHandler from "Site/validation/error-handler";

var validator = new Validator();

validator.init({
    error: MyErrorHandler
});
```

> See core file `core/core/assets/js/components/validation/error-handler.js` for a concrete example.

# Creating Server Side Validation entry

In `config/forms.yml`, add a new entry that corresponds to your new validation
entry. The format on the yml on the method should be `MyApp\MySrc\MyClass:myMethod` 
The method should return boolean. 

The method accept 3 arguments:

```php
public function myRule($value, $param, $field)
{
    return $value > 10;
}
```

> See `App\Extensions\Form\Validators\Validators` for more examples.
