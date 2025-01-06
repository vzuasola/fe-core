<?php

namespace App\Extensions\Form\ConfigurableForm\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use App\Extensions\Form\ConfigurableForm\Types\CustomChoiceType;

/**
 * Class dedicated for the select field specfications
 */
class CustomSelect extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal text field to Symfony
     */
    public function type()
    {
        return CustomChoiceType::class;
    }
}
