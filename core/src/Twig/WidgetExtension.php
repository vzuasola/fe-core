<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 *
 */
class WidgetExtension extends AbstractExtension
{
    /**
     * Public constructor.
     */
    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('widget', [$this, 'widget'], [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('modules', [$this, 'modules'], [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('module_scripts', [$this, 'includes'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     *
     */
    public function widget($id, $options = [])
    {
        return $this->manager->renderWidget($id, $options);
    }

    /**
     *
     */
    public function includes($options = [])
    {
        return $this->manager->renderModuleIncludes($options);
    }
}
