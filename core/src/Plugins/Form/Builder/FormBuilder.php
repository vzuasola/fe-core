<?php

namespace App\Plugins\Form\Builder;

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormBuilderInterface;

use App\Plugins\Form\Field\FormMapInterface;
use App\Plugins\Form\Field\FormMapException;

/**
 * Available options
 *
 * string 'type_index' Tells the form builder where to look for the field type
 */
class FormBuilder
{
    /**
     * Public constructor
     */
    public function __construct($mapping, $validations, $scripts, $configurations)
    {
        $this->mapping = $mapping;
        $this->validations = $validations;

        $this->scripts = $scripts;
        $this->configurations = $configurations;
    }

    /**
     *
     */
    public function createForm($name, array $fields, FormBuilderInterface $form = null, $options = [])
    {
        // if no form object is passed, create the form object from scratch
        if (!isset($form)) {
            $formFactory = Forms::createFormFactory();
            $form = $formFactory->createNamedBuilder($name);
        }

        // change name to the actual form name
        $name = $form->getName();

        $definition = $this->getFormDefinition($name, $fields, $options);

        return $this->createFormByDefinition($definition, $form);
    }

    /**
     *
     */
    public function getFormDefinition($name, array $fields, $options = [])
    {
        $definition = [];

        foreach ($fields as $key => $field) {
            $type = $options['type_index'] ?? $field['#type'] ?? $field['type'] ?? null;
            $map = $this->mapping[$type] ?? null;

            if ($type && $map) {
                $fieldInstance = new $map;

                if ($fieldInstance instanceof FormMapInterface == false) {
                    throw new FormMapException("Object $map is not an instance of FormMapInterface");
                }

                // build option
                if (method_exists($fieldInstance, 'build')) {
                    $fieldInstance->build($this, $name, $key, $fields, $options);
                }

                $fieldOptions = $fieldInstance->options($field, $this->configurations);

                // skip rendering of hidden fields
                if (isset($fieldOptions['hidden']) && ! $fieldOptions['hidden']) {
                    continue;
                }

                // we remove the hidden option since Symfony form does not support this
                unset($fieldOptions['hidden']);

                // append the passed option if any
                if (!empty($options[$key])) {
                    $fieldOptions = array_replace($fieldOptions, $options[$key]);
                }

                // aggregate the validation definition from each field
                $validator = $fieldInstance->validators($field, $this->configurations);

                $definition[$name][$key] = [
                    'type' => $fieldInstance->type($field, $this->configurations),
                    'map' => $map,
                    'options' => $fieldOptions,
                    'validators' => [
                        'class' => $map,
                        'rules' => $validator,
                    ],
                ];

                // invoke the hidden alter method
                if (method_exists($fieldInstance, 'alterDefinitions')) {
                    $fieldInstance->alterDefinitions($name, $key, $definition, $this->configurations);
                }
            }
        }

        return $definition;
    }

    /**
     * Construct the form using Symfony form factory builder
     */
    public function createFormByDefinition($definition, FormBuilderInterface $form)
    {
        $validators = [];

        foreach ($definition as $name => $fields) {
            foreach ($fields as $key => $value) {
                $defaultOptions = $form
                    ->getFormFactory()
                    ->createNamedBuilder($key, $value['type'])
                    ->getOptions();

                $options = array_intersect_key($value['options'], $defaultOptions);
                // insert data on default options if isset
                if (isset($value['options']['data'])) {
                    $options['data'] = $value['options']['data'];
                }

                $form->add($key, $value['type'], $options);

                if (!empty($value['validators']['rules'])) {
                    $validators[$name][$key] = $value['validators'];
                } else {
                    // we add a default validation for field with no validation
                    // so that we can control all field on the clientside
                    // error handler
                    // @hack
                    if (isset($value['map']) &&
                        in_array($value['map'], $this->validations)
                    ) {
                        $validators[$name][$key] = $value['validators'];
                        $validators[$name][$key]['rules'] = [
                            'callback_defaults' => 'This field is being validated'
                        ];
                    }
                }
            }
        }

        $this->attachValidations($validators);

        return $form;
    }

    /**
     *
     */
    private function attachValidations($validators)
    {
        $definition['formValidations'] = $validators;

        $this->scripts->attach($definition, true);
    }

    /**
     *
     */
    public function getValidationConfigurations($definition)
    {
        $validators = [];

        foreach ($definition as $name => $fields) {
            foreach ($fields as $key => $value) {
                if (!empty($value['validators']['rules'])) {
                    $validators[$name][$key] = $value['validators'];
                } else {
                    // we add a default validation for field with no validation
                    // so that we can control all field on the clientside
                    // error handler
                    // @hack
                    if (isset($value['map']) &&
                        in_array($value['map'], $this->validations)
                    ) {
                        $validators[$name][$key] = $value['validators'];
                        $validators[$name][$key]['rules'] = [
                            'callback_defaults' => 'This field is being validated'
                        ];
                    }
                }
            }
        }

        return $validators;
    }
}
