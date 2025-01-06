<?php

namespace App\Plugins\Form;

/**
 *
 */
interface FormValidatorInterface
{
    /**
     * Validates a given value
     *
     * @param mixed $value The value passed to this validator
     * @param array $options Additional option the controls this validator
     */
    public function validate($value, array $options);
}
