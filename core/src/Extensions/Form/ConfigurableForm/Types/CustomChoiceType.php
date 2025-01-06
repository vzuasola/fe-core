<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\Form\ConfigurableForm\Types;

use Symfony\Component\Form\AbstractType;

class CustomChoiceType extends AbstractType
{


    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return '\Symfony\Component\Form\Extension\Core\Type\ChoiceType';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'custom_choice';
    }
}
