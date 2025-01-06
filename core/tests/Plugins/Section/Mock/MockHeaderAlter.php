<?php

namespace Tests\Plugins\Section\Mock;

use App\Plugins\Section\SectionAlterInterface;

class MockHeaderAlter implements SectionAlterInterface
{
    /**
     * {@inheritdoc}
     */
    public function alterSection(&$data, array $options)
    {
        $data['name'] = 'Leandrew Vicarpio';
    }
}
