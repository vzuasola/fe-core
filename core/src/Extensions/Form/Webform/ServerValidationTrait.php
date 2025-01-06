<?php

namespace App\Extensions\Form\Webform;

use App\Extensions\Form\Webform\Exception\ValidationException;
use Symfony\Component\Form\FormError;

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
        $id = $options['formId'];
        $settings = $this->form->getDataById($id);

        $validators = $options['configurations']['validations'] ?? [];

        try {
            $submission = $form->getData();
            $constraints = $this->validate($settings, $submission, $validators);
        } catch (\Exception $e) {
            $message = self::DEFAULT_FAILED_MESSAGE;
            $form->addError(new FormError($message));

            throw $e;
        }

        if (!empty($constraints)) {
            foreach ($constraints as $field => $errors) {
                foreach ($errors as $error) {
                    $form->addError(new FormError($error));
                }
            }

            throw new ValidationException("Failed server side validation for form $id");
        }
    }

    /**
     * Validate the entire submission data
     */
    private function validate($settings, $submission, $validators)
    {
        $errors = [];
        $fields = $settings['elements'];

        foreach ($submission as $key => $value) {
            foreach ($validators as $index => $validator) {
                $prepend = '#';
                $hash = "$prepend$index";

                if (isset($fields[$key][$hash]) && $fields[$key][$hash]) {
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

        // here comes the code for resolving the arguments
        // also see Drupal/Form/BaseDefinition.php
        if (isset($field["#{$method}_value"])) {
            $arguments[] = $field["#{$method}_value"];
        }

        $result = $this->executeValidator($class, $data, $arguments, $field);

        if (empty($result)) {
            if (isset($field["#{$method}_error"]) && !empty($field["#{$method}_error"])) {
                $message = $field["#{$method}_error"];
            } else {
                $message = "The $method validation has failed for field $key";
            }

            return $message;
        }
    }

    /**
     * Executes the validator base on class definition
     */
    private function executeValidator($definition, ...$data)
    {
        list($class, $method) = explode(':', $definition);

        $instance = $this->getInstance($class);

        return $instance->$method(...$data);
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
