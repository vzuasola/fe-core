# Creating a Configurable Form Instance

To create a configurable form, make sure that on Drupal, the `Webcomposer Form`
module is all set and there is a form ready for rendering.

> See this [guide](https://gitlab.ph.esl-asia.com/CMS/drupal-data/blob/working/docs/webcomposer-form-plugin.md) for Drupal

# Defining a Configurable Form Instance

Define a form instance, but this time you need to extend `App\Extensions\Form\ConfigurableForm\FormBase`

> All forms should be defined under the `App\Product\Form` namespace for site specific
> and `App\Form` for core

A sample form is like this:

```php
namespace App\MyProduct\Form;

use App\Plugins\Form\FormInterface;
use App\Extensions\Form\ConfigurableForm\FormBase;

class MyForm extends FormBase implements FormInterface
{
    /**
     * @{inheritdoc}
     */
    public function getFormId()
    {
        return 'my_form';
    }

    /**
     * @{inheritdoc}
     */
    public function alterFormDefinition($definition, $data)
    {
        return $definition;
    }

    /**
     * @{inheritdoc}
     */
    public function submit($form, $options)
    {
        ddd($form->getData());
    }
}
```

The method `getFormId` needs to return the form ID you defined on Drupal.

The special method `alterFormDefinition` allows you to alter the form in any way you want.
* `$definition` Contains the current form definition while
* `$data` Contains the data returned from the Webcomposer Form Drupal service

The method `submit` defines what will happen after a form is submitted.

# How to Render a Form from a Controller

Just call the form manager against your form class 

```php
namespace App\MyProduct\Controller;

use App\BaseController;
use App\MyProduct\Form\MyForm;

class MyController extends BaseController
{
    /**
     *
     */
    public function form($request, $response)
    {
        $data['form'] = $this->get('form_manager')->getForm(MyForm::class)->createView();

        return $this->view->render($response, '@site/pages/form.html.twig', $data);
    }
}
```

Your twig can look like this

```twig
<div class="form">
    {% form_theme form '@base/form/standard.html.twig' %}

    {% set formAttribute = {
        'class': 'pure-form form-vertical form-optin text-14',
    } %}

    {{ form_start(form, {
        'attr': formAttribute,
    }) }}
    {{ form_end(form) }}
</div>
```

# Advance Dependency Injection for Configurable Forms

If you have additional dependencies, you need to consider the `FormBase`

```php
namespace App\MyProduct\Form;

use App\Plugins\Form\FormInterface;
use App\Extensions\Form\ConfigurableForm\FormBase;

class MyForm extends FormBase implements FormInterface
{
    /**
     * Some dependency
     * 
     * @var object
     */
    private $someDependency;

    /**
     * Container dependency injection
     */
    public function setContainer($container)
    {
        parent::setContainer($container);

        $this->someDependency = $container->get('some_dependency');
    }

    /**
     * @{inheritdoc}
     */
    public function getFormId()
    {
        return 'myform';
    }

    /**
     * @{inheritdoc}
     */
    public function alterFormDefinition($definition, $data)
    {
        return $definition;
    }

    /**
     * @{inheritdoc}
     */
    public function submit($form, $options)
    {
        ddd($form->getData());
    }
}
```

> To add validations, refer to [Form Validations](form-validations.md) documentation

# Controlling How Server Side Validations Work

You can override some methods on the base class the change the server validation
behavior.

The form base has these 2 methods that controls how server side validation behaves.

```php
abstract class FormBase implements FormInterface
{
    /**
     * Defines how a validation error will be handled
     *
     * @param object $form The current form object
     * @param array $constraints The list of all validation constraints
     */
    public function onValidateError($form, $constraints)
    {
        foreach ($constraints as $field => $errors) {
            foreach ($errors as $error) {
                $form->addError(new FormError($error));
            }
        }

        throw new ValidationException("Failed server side validation for form $id");
    }

    /**
     * Defines how a validation exception will be handled
     *
     * @param object $form The current form object
     * @param array $constraints The list of all validation constraints
     * @param Exception $e
     */
    public function onValidateException($form, $constraints, $e)
    {
        $message = "Form server side validation has failed";

        $form->addError(new FormError($message));

        throw $e;
    }
}
```

Simply redeclare the methods to your class as follows

```php
namespace App\MyProduct\Form;

use App\Plugins\Form\FormInterface;
use App\Extensions\Form\ConfigurableForm\FormBase;

class MyForm extends FormBase implements FormInterface
{
    /**
     * @{inheritdoc}
     */
    public function onValidateError($form, $constraints)
    {
        // do anything here
    }

    /**
     * @{inheritdoc}
     */
    public function onValidateException($form, $constraints, $e)
    {
        // do anything here
    }
}
```

> If you want to override the entire server validation processm you can redeclare
> the method `preSubmit`, see `App\Extensions\Form\ConfigurableForm\ServerValidationTrait` 
> for more information

# Code Examples

Code sample for live working form examples

### FE Form Plugin

```php
<?php

namespace App\Demo\Form;

use App\Plugins\Form\FormInterface;
use App\Extensions\Form\ConfigurableForm\FormBase;

class RegistrationForm extends FormBase implements FormInterface
{
    /**
     * Container dependency injection
     */
    public function setContainer($container)
    {
        parent::setContainer($container);

        $this->session = $container->get('session');
    }

    /**
     * @{inheritdoc}
     */
    public function getFormId()
    {
        return 'registration';
    }

    /**
     * @{inheritdoc}
     */
    public function alterFormDefinition($definition, $data)
    {
        // Gender Field
        // Radios

        $choice = $definition['gender']['options']['choices'];

        unset($definition['gender']['options']['choices']);

        $choices = $this->pipeToChoices($choice);

        $definition['gender']['options']['choices'] = $choices;
        $definition['gender']['options']['data'] = reset($choices);

        // Foods Field
        // Checkboxes

        $choice = $definition['foods']['options']['choices'];

        unset($definition['foods']['options']['choices']);

        $choices = $this->pipeToChoices($choice);

        $definition['foods']['options']['choices'] = $choices;

        // Countries Field
        // Select type

        $choice = $definition['countries']['options']['choices'];

        unset($definition['countries']['options']['choices']);

        $choices = $this->pipeToChoices($choice);

        $definition['countries']['options']['choices'] = $choices;

        return $definition;
    }

    /**
     * @{inheritdoc}
     */
    public function submit($form, $options)
    {
        $this->session->setFlash('form.success.data', $form->getData());

        $form->clear = true;
    }
}
```

### Drupal Webcomposer Form Plugin

```php
namespace Drupal\webcomposer_form_sample\Plugin\Webcomposer\Form;

use Drupal\webcomposer_form_manager\WebcomposerFormBase;
use Drupal\webcomposer_form_manager\WebcomposerFormInterface;

/**
 * RegistrationForm
 *
 * @WebcomposerForm(
 *   id = "registration",
 *   name = "Registration Form",
 * )
 */
class RegistrationForm extends WebcomposerFormBase implements WebcomposerFormInterface {
  /**
   *
   */
  public function getSettings() {
    return [
      'header' => [
        '#title' => 'Form header',
        '#type' => 'textarea',
        '#description' => 'Header text for this form',
      ],
    ];
  }

  /**
   *
   */
  public function getFields() {
    return [
      'username' => [
        'name' => 'Username',
        'type' => 'textfield',
        'settings' => [
          'label' => [
            '#title' => 'Label',
            '#type' => 'textfield',
            '#description' => 'Label for this field',
            '#default_value' => 'Username',
          ],
        ],
      ],

      'password' => [
        'name' => 'Password',
        'type' => 'password',
        'settings' => [
          'label' => [
            '#title' => 'Label',
            '#type' => 'textfield',
            '#description' => 'Label for this field',
            '#default_value' => 'Password',
          ],
        ],
      ],

      'email' => [
        'name' => 'Email',
        'type' => 'textfield',
        'settings' => [
          'label' => [
            '#title' => 'Label',
            '#type' => 'textfield',
            '#description' => 'Label for this field',
            '#default_value' => 'Email',
          ],
        ],
      ],

      'comments' => [
        'name' => 'Comment',
        'type' => 'textarea',
        'settings' => [
          'label' => [
            '#title' => 'Label',
            '#type' => 'textfield',
            '#description' => 'Label for this field',
            '#default_value' => 'Comments',
          ],
        ],
      ],

      'gender' => [
        'name' => 'Gender',
        'type' => 'radios',
        'settings' => [
          'label' => [
            '#title' => 'Label',
            '#type' => 'textfield',
            '#description' => 'The label for this field',
            '#default_value' => 'Gender',
          ],
          'choices' => [
            '#title' => 'Gender Choices',
            '#type' => 'textarea',
            '#description' => 'Provide a pipe separated key value pair. <br> <small>Example key|My Value</small>',
            '#default_value' => implode(PHP_EOL, ['male|Male', 'female|Female']),
          ],
        ],
      ],

      'foods' => [
        'name' => 'Foods',
        'type' => 'checkboxes',
        'settings' => [
          'label' => [
            '#title' => 'Label',
            '#type' => 'textfield',
            '#description' => 'The label for this field',
            '#default_value' => 'Foods',
          ],
          'choices' => [
            '#title' => 'Food Choices',
            '#type' => 'textarea',
            '#description' => 'Provide a pipe separated key value pair. <br> <small>Example key|My Value</small>',
            '#default_value' => implode(PHP_EOL, ['pizza|Pizza', 'donut|Donut']),
          ],
        ],
      ],

      'countries' => [
        'name' => 'Countries',
        'type' => 'select',
        'settings' => [
          'label' => [
            '#title' => 'Label',
            '#type' => 'textfield',
            '#description' => 'The label for this field',
            '#default_value' => 'Country',
          ],
          'placeholder' => [
            '#title' => 'Choose a country',
            '#type' => 'textfield',
            '#description' => 'Placeholder value for this textfield',
            '#default_value' => 'Select your country...',
          ],
          'choices' => [
            '#title' => 'Country Choices',
            '#type' => 'textarea',
            '#description' => 'Provide a pipe separated key value pair. <br> <small>Example key|My Value</small>',
            '#default_value' => implode(PHP_EOL, ['ch|China', 'us|United States']),
          ],
        ],
      ],

      'age' => [
        'name' => 'Age',
        'type' => 'checkbox',
        'settings' => [
          'label' => [
            '#title' => 'Label',
            '#type' => 'textfield',
            '#description' => 'The label for this checkbox',
            '#default_value' => 'I am sure that I am above 18 years old',
          ],
        ],
      ],

      'submit' => [
        'name' => 'Submit',
        'type' => 'submit',
        'settings' => [
          'label' => [
            '#title' => 'Submit Label',
            '#type' => 'textfield',
            '#description' => 'Label for the submit button',
            '#default_value' => 'Submit',
          ],
        ],
      ],
    ];
  }
}
```
