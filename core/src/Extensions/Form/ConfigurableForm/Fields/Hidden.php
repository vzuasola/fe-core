<?php

namespace App\Extensions\Form\ConfigurableForm\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class dedicated for the hidden field specfications
 */
class Hidden extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal text field to Symfony
     */
    public function type()
    {
        return HiddenType::class;
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
