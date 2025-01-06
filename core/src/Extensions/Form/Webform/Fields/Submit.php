<?php

namespace App\Extensions\Form\Webform\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class dedicated for the submit field specfications
 */
class Submit extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal choice field to Symfony
     */
    public function type()
    {
        return SubmitType::class;
    }

    /**
     * Specify custom options that you need for choice field
     */
    public function options($fields, $configurations)
    {
        $options = [];

        $options['hidden'] = $fields['#visibility'];
        $options['label'] = $fields['#submit__label'];

        return $options;
    }
}
