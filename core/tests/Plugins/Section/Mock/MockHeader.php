<?php

namespace Tests\Plugins\Section\Mock;

use App\Plugins\Section\SectionInterface;

class MockHeader implements SectionInterface
{
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getSection(array $options)
    {
        $data = [
            'name' => 'leandrew',
            'age' => 35,
        ];

        if (isset($options['extra'])) {
            $data['extra'] = $options['extra'];
        }

        return $data;
    }
}
