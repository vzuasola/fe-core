<?php

namespace App\Plugins\Widget;

/**
 * Defines a menu widget class
 */
interface MenuWidgetInterface
{
    /**
     * Defines the data to be passed to the twig template
     *
     * @param array $data The data passed by Drupal
     *
     * @return array
     */
    public function alterData($data);

    /**
     * Defines the template path
     *
     * @return string
     */
    public function getTemplate();
}
