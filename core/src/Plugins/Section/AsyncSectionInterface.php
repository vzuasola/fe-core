<?php

namespace App\Plugins\Section;

/**
 *
 */
interface AsyncSectionInterface
{
    /**
     * Fetches the specified section
     *
     * @param array $options Array of additional options
     *
     * @return array definition
     */
    public function getSectionDefinition(array $options);

    /**
     * Fetches the specified section
     *
     * @param array $options Array of additional options
     *
     * @return array
     */
    public function processDefinition($data, array $options);
}
