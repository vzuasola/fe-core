<?php

namespace App\Plugins\ComponentWidget;

/**
 * Defines a class for script inclusion
 */
interface ComponentIncludesInterface
{
    /**
     * Define the scripts to be included
     *
     * @return array
     */
    public function getIncludes();
}
