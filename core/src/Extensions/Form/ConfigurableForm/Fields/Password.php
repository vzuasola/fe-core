<?php

namespace App\Extensions\Form\ConfigurableForm\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * Class dedicated for the password field specfications
 */
class Password extends BaseDefinition implements FormMapInterface
{
    /**
     * Return the class equivalent of Drupal text field to Symfony
     */
    public function type()
    {
        return PasswordType::class;
    }
}
