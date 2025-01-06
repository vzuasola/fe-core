<?php

namespace App\Plugins\ComponentWidget;

/**
 * Defines a component widget class
 */
interface ComponentWidgetInterface
{
    /**
     * Defines the template path
     *
     * @return string
     */
    public function getTemplate();

    /**
     * Defines the data to be passed to the twig template
     *
     * @return array
     */
    public function getData();
}
