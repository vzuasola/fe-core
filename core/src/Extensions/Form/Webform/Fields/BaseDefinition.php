<?php

namespace App\Extensions\Form\Webform\Fields;

/**
 *
 */
class BaseDefinition
{
    /**
     * Specify custom validations
     */
    public function validators($fields, $configurations)
    {
        $validators = $configurations['validations'] ?? [];
        $validators = array_keys($validators);

        $validations = [];

        foreach ($validators as $validation) {
            $key = "#$validation";

            if (isset($fields[$key]) && $fields[$key]) {
                // add the message to the validation
                if (!empty($fields["{$key}_error"])) {
                    $message = $fields["{$key}_error"];
                } else {
                    $message = "The $validation validation was not met";
                }
                $validations["callback_$validation"]['message'] = $message;

                // check if arguments are enabled
                // also see ServerValidationTrait.php
                if (isset($fields["{$key}_value"])) {
                    $validations["callback_$validation"]['arguments'] = [
                        $fields["{$key}_value"]
                    ];
                }
            }
        }

        return $validations;
    }

    /**
     * Alter the definitions
     *
     * Add required asterisk on the label
     */
    public function alterDefinitions($name, $key, &$definitions)
    {
        if (isset($definitions[$name][$key]['validators']['rules']['callback_required'])) {
            $label = $definitions[$name][$key]['options']['label'];

            $definitions[$name][$key]['options']['label'] = $label . " <span class='required-field'>*</span>";
        }
    }
}
