<?php

namespace App\Extensions\Form\Webform\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class dedicated for the select field specfications
 */
class Select extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal select field to Symfony
     */
    public function type()
    {
        return ChoiceType::class;
    }

    /**
     * Specify custom options that you need for select field
     */
    public function options($fields, $configurations)
    {
        $options = [];

        $options['required'] = false;

        $options['hidden'] = $fields['#visibility'];
        $options['label'] = $fields['#title'];

        // We need to flip the array value since Symfony expect that key will be the name
        // and value will be the option value
        $options['choices'] = array_flip($fields['#options']);

        // check if we need to show an empty option
        if (isset($fields['#empty_option'])) {
            $options['placeholder'] = $fields['#empty_option'];
        } else {
            $options['placeholder'] = false;
        }

        // fetch the default values
        if (!empty($fields['#default_value'])) {
            $options['data'] = $fields['#default_value'];
        }

        return $options;
    }
}
