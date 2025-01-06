<?php

namespace App\Extensions\Form\Webform\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

/**
 * Class dedicated for the email field specfications
 */
class Email extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal email field to Symfony
     */
    public function type()
    {
        return EmailType::class;
    }

    /**
     * Specify custom options that you need for email field
     */
    public function options($fields, $configurations)
    {
        $options = [];

        $options['required'] = false;

        $options['hidden'] = $fields['#visibility'];
        $options['label'] = $fields['#title'];

        // set class attributes
        if (isset($fields['#attributes']) && !empty($fields['#attributes'])) {
            $options['attr']['class'] = $fields['#attributes']['class'];
        }

        // set placeholder
        if (isset($fields['#placeholder']) && !empty($fields['#placeholder'])) {
            $options['attr']['placeholder'] = $fields['#placeholder'];
        }

        // fetch the default values
        if (!empty($fields['#default_value'])) {
            $options['data'] = $fields['#default_value'];
        }

        return $options;
    }
}
