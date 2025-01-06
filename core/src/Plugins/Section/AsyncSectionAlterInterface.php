<?php

namespace App\Plugins\Section;

/**
 *
 */
interface AsyncSectionAlterInterface
{
    /**
     * Fetches the specified section
     *
     * @param array $definitions Defines what data to fetch
     * @param array $options Array of additional options
     */
    public function alterSectionDefinition(&$definitions, array $options);

    /**
     * Fetches the specified section
     *
     * @param array $result
     * @param array $data The data from the definition
     * @param array $options Array of additional options
     */
    public function alterprocessDefinition(&$result, $data, array $options);
}
