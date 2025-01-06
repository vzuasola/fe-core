<?php

namespace Tests\Plugins\Section\Mock;

use App\Plugins\Section\SectionInterface;

class MockFooter implements SectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSection(array $options)
    {
        return [
            'about' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, 
                sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
                Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris 
                nisi ut aliquip ex ea commodo consequat',
        ];
    }
}
