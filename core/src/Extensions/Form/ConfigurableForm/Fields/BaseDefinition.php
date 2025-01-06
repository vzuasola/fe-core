<?php

namespace App\Extensions\Form\ConfigurableForm\Fields;

/**
 *
 */
class BaseDefinition
{
    private $fieldsLabelOverride = [
        'Symfony\Component\Form\Extension\Core\Type\TextType',
        'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        'Symfony\Component\Form\Extension\Core\Type\PasswordType',
        'Symfony\Component\Form\Extension\Core\Type\TextAreaType',
    ];

    /**
     * @{inheritdoc}
     */
    public function options($fields, $configurations)
    {
        $options = [];
        $settings = $fields['field_settings'];

        $options['required'] = false;

        return array_replace_recursive($options, $settings);
    }

    /**
     * @{inheritdoc}
     */
    public function validators($fields, $configurations)
    {
        $result = [];

        $validators = $configurations['validations'] ?? [];
        $validators = array_keys($validators);

        foreach ($validators as $validation) {
            $settings = $fields['field_validations'][$validation] ?? [];

            if (isset($settings['enable']) && $settings['enable']) {
                // add the message to the validation
                if (!empty($settings['error_message'])) {
                    $message = $settings['error_message'];
                } else {
                    $message = "The $validation validation was not met";
                }

                $result["callback_$validation"]['message'] = $message;

                $description = $settings['rule_description'] ?? '';
                $weight = $settings['weight'] ?? "0";
                $result["callback_$validation"]['description'] = $description;
                $result["callback_$validation"]['weight'] = $weight;

                // check if arguments are enabled
                // also see ServerValidationTrait.php
                if (!empty($settings['parameters'])) {
                    $result["callback_$validation"]['arguments'] = array_values($settings['parameters']);
                }
            }
        }

        return $result;
    }

    /**
     * Alter the definitions
     *
     * Add required asterisk on the label
     */
    public function alterDefinitions($name, $key, &$definitions)
    {
        if (isset($definitions[$name][$key]['options']['label']) &&
            in_array($definitions[$name][$key]['type'], $this->fieldsLabelOverride) &&
            !isset($definitions[$name][$key]['validators']['rules']['callback_required'])) {
            $label = $definitions[$name][$key]['options']['label'];

            $definitions[$name][$key]['options']['label'] = "<span class='form-label-text'>$label</span>";
        }

        if (isset($definitions[$name][$key]['validators']['rules']['callback_required']) &&
            isset($definitions[$name][$key]['options']['label'])
        ) {
            $label = $definitions[$name][$key]['options']['label'];

            $definitions[$name][$key]['options']['label'] = "<span class='form-label-text'>$label</span>" .
                "<span class='required-field'>*</span>";
        }
    }
}
