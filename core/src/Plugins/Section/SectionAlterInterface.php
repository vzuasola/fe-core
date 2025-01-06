<?php

namespace App\Plugins\Section;

/**
 *
 */
interface SectionAlterInterface
{
    /**
     * Fetches the specified section
     *
     * @param array $options Array of additional options
     */
    public function alterSection(&$data, array $options);
}
