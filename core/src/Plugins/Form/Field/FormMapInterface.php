<?php

namespace App\Plugins\Form\Field;

/**
 *
 */
interface FormMapInterface
{
    /**
     * The Symfony form type class mapping
     *
     * @return string
     */
    public function type();

    /**
     * The Symfony form options
     *
     * @param array $fields         The data fetched from the service that defines the field
     * @param array $configurations The defined form configuration on the forms.yml
     *
     * @return array
     */
    public function options($fields, $configurations);

    /**
     * The validation definition of each field
     *
     * @param array $fields         The data fetched from the service that defines the field
     * @param array $configurations The defined form configuration on the forms.yml
     *
     * @return array
     */
    public function validators($fields, $configurations);

    /**
     * Alters the field definition based on existing data
     *
     * NOTE: THIS IS A HIDDEN METHOD, DO NOT ENABLE THIS ON THE INTERFACE
     * Classes can declare this method if they want to alter something
     *
     * @param array $name The current form name
     * @param array $key The current field key
     * @param array $definitions The form definition to alter
     * @param array $configurations The defined form configuration on the forms.yml
     *
     * @return array
     */
    // public function alterDefinitions($name, $key, &$definitions, $configurations)
}
