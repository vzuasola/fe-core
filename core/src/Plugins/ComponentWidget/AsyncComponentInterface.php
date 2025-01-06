<?php

namespace App\Plugins\ComponentWidget;

/**
 * Defines a component widget class
 */
interface AsyncComponentInterface
{
    /**
     * Fetches the specified section
     *
     * @return array definition
     */
    public function getDefinitions();
}
