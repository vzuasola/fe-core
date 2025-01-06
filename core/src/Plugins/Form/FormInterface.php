<?php

namespace App\Plugins\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 *
 */
interface FormInterface
{
    /**
     * Fetches
     *
     * @param object $form    The form definition object
     * @param array  $options Array of additional options
     */
    public function getForm(FormBuilderInterface $form, $options);

    /**
     * Pre submit callback handler
     *
     * Mainly used for validation and data manipulation
     *
     * @param object $form The form definition object
     * @param array $options Array of additional options
     */
    // public function preSubmit($form, $options);

    /**
     * Submit callback handler
     *
     * @param object $form    The form definition object
     * @param array  $options Array of additional options
     */
    public function submit($form, $options);
}
