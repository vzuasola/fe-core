<?php

namespace App\Plugins\Form\Builder;

/**
 *
 */
class Factory
{
    /**
     * Public constructor
     */
    public function __construct($scripts, $configuration)
    {
        $this->scripts = $scripts;
        $this->configurations = $configuration->getConfiguration('forms');
    }

    /**
     *
     */
    public function createBuilder($type)
    {
        $mapping = $this->configurations['types'][$type]['fields'];
        $validation = $this->configurations['types'][$type]['validation'];

        return new FormBuilder($mapping, $validation, $this->scripts, $this->configurations);
    }
}
