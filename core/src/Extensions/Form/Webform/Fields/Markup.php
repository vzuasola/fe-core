<?php

namespace App\Extensions\Form\Webform\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use App\Symfony\Form\Type\MarkupType;

/**
 * Class dedicated for the markup field specfications
 */
class Markup extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal choice field to Symfony
     */
    public function type()
    {
        return MarkupType::class;
    }

    /**
     * Specify custom options that you need for choice field
     */
    public function options($fields, $configurations)
    {
        $options = [];

        $options['hidden'] = $fields['#visibility'];
        $options['markup'] = $fields['#text'];

        return $options;
    }
}
