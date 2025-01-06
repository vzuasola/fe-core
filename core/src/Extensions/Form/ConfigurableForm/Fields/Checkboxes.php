<?php

namespace App\Extensions\Form\ConfigurableForm\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class dedicated for the checkboxes field specfications
 */
class Checkboxes extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal choice field to Symfony
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

        unset($options['required']);

        $options['expanded'] = true;
        $options['multiple'] = true;

        return $options;
    }
}
