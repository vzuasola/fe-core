<?php

namespace App\Extensions\Form\ConfigurableForm\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Class dedicated for the checkbox field specfications
 */
class Checkbox extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal choice field to Symfony
     */
    public function type()
    {
        return CheckboxType::class;
    }

    /**
     * @{inheritdoc}
     */
    public function options($fields, $configurations)
    {
        return parent::options($fields, $configurations);
    }

    /**
     * Alter the definitions
     */
    public function alterDefinitions($name, $key, &$definitions)
    {
    }
}
