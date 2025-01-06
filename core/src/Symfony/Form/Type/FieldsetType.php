<?php

namespace App\Symfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class FieldsetType extends AbstractType
{
    /**
     * @{inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'wrapper' => '', // can either be 'open' or 'close'
                'class' => '',
                'legend' => '',
            ]);
    }

    /**
     * @{inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['wrapper'] = $options['wrapper'];
        $view->vars['class'] = $options['class'];
        $view->vars['legend'] = $options['legend'];
    }

    /**
     * @{inheritdoc}
     */
    public function getName()
    {
        return 'fieldset';
    }
}
