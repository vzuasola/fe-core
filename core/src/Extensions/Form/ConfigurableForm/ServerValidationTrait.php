<?php

namespace App\Extensions\Form\ConfigurableForm;

/**
 * Handles the complexity of the dynamic server side validation
 */
trait ServerValidationTrait
{
    /**
     * Pre submit hook for validating data
     */
    public function preSubmit($form, $options)
    {
        $id = $this->getFormId();
        $settings = $this->form->getDataById($id);

        $validators = $options['configurations']['validations'] ?? [];

        try {
            $submission = $form->getData();
            $constraints = $this->validate($settings, $submission, $validators);
        } catch (\Exception $e) {
            $this->onValidateException($form, $constraints, $e);
        }

        if (!empty($constraints)) {
            $this->onValidateError($form, $constraints);
        }
    }

    /**
     * Validate the entire submission data
     */
    private function validate($settings, $submission, $validators)
    {
        $errors = [];
        $fields = $settings['fields'];

        foreach ($submission as $key => $value) {
            foreach ($validators as $index => $validator) {
                if (isset($fields[$key]['field_validations'][$index]['enable']) &&
                    $fields[$key]['field_validations'][$index]['enable']
                ) {
                    $error = $this->doValidate($fields[$key], $key, $value, $index, $validator);

                    if ($error) {
                        $errors[$key][] = $error;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validate a specific field
     */
    private function doValidate($field, $key, $data, $method, $class)
    {
        $arguments = [];

        if (isset($field['field_validations'][$method]['parameters'])) {
            $arguments = $field['field_validations'][$method]['parameters'];
        }

        $options = [$data, $arguments, $field];
        $result = $this->executeValidator($class, $options);

        if (empty($result)) {
            $message = "The $method validation has failed for field $key";
            return $message;
        }
    }

    /**
     * Executes the validator base on class definition
     */
    private function executeValidator($definition, $options)
    {
        list($class, $method) = explode(':', $definition);

        $instance = $this->getInstance($class);

        return $instance->$method(...$options);
    }

    /**
     * Gets a cached instance of a validator
     */
    private function getInstance($class)
    {
        if (!isset($this->validators[$class])) {
            $instance = new $class();
            $this->validatorClasses[$class] = $instance;
        } else {
            $instance = $this->validators[$class];
        }

        return $instance;
    }
}
