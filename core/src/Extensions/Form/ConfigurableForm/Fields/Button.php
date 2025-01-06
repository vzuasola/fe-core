<?php

namespace App\Extensions\Form\ConfigurableForm\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

/**
 * Class dedicated for the submit field specfications
 */
class Button extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal choice field to Symfony
     */
    public function type()
    {
        return ButtonType::class;
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
