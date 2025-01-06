<?php

namespace App\Extensions\Form\ConfigurableForm\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class dedicated for the select field specfications
 */
class Radios extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal text field to Symfony
     */
    public function type()
    {
        return ChoiceType::class;
    }

    /**
     * @{inheritdoc}
     */
    public function options($fields, $configurations)
    {
        $options = parent::options($fields, $configurations);

        // This means it is a radio button
        $options['expanded'] = true;
        $options['multiple'] = false;

        $options['placeholder'] = false;

        return $options;
    }
}
