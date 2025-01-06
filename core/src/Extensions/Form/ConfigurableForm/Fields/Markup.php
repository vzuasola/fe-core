<?php

namespace App\Extensions\Form\ConfigurableForm\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use App\Symfony\Form\Type\MarkupType;

/**
 * Class dedicated for the select field specfications
 */
class Markup extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal text field to Symfony
     */
    public function type()
    {
        return MarkupType::class;
    }

    /**
     * @{inheritdoc}
     */
    public function options($fields, $configurations)
    {
        $options = parent::options($fields, $configurations);

        unset($options['required']);

        return $options;
    }
}
