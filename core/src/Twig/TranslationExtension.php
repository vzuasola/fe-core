<?php

/**
 * Translation extension ported from Symfony
 *
 * https://github.com/symfony/twig-bridge/blob/3.2/Extension/TranslationExtension.php
 */

namespace App\Twig;

/**
 * Provides integration of the Translation component with Twig.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TranslationExtension extends \Twig_Extension
{
    private $translator;
    private $translationNodeVisitor;

    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('trans', array($this, 'trans')),
            new \Twig_SimpleFilter('transchoice', array($this, 'transchoice')),
        );
    }

    public function trans($message, array $arguments = array(), $domain = null, $locale = null)
    {
        // Pass through return as is
        return $message;
    }

    public function transchoice($message, $count, array $arguments = array(), $domain = null, $locale = null)
    {
        return $message;
    }
}
