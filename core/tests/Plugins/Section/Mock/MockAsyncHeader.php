<?php

namespace Tests\Plugins\Section\Mock;

use App\Plugins\Section\AsyncSectionInterface;
use App\Async\ArrayDefinition;

class MockAsyncHeader implements AsyncSectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'leandrew' => new ArrayDefinition([
                'name' => 'leandrew',
                'age' => 35,
            ]),
            'alex' => new ArrayDefinition([
                'name' => 'alex',
                'age' => 20,
            ]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $result[$key] = $value;

            if (isset($options['extra'])) {
                $result[$key]['extra'] = $options['extra'];
            }
        }

        return $result;
    }
}
