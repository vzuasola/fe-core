<?php

namespace App\Plugins\Section;

/**
 *
 */
interface SectionInterface
{
    /**
     * Fetches the specified section
     *
     * @param array $options Array of additional options
     */
    public function getSection(array $options);
}
