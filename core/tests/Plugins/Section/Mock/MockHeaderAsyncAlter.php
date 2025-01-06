<?php

namespace Tests\Plugins\Section\Mock;

use App\Plugins\Section\AsyncSectionAlterInterface;

class MockHeaderAsyncAlter implements AsyncSectionAlterInterface
{
    /**
     * {@inheritdoc}
     */
    public function alterSectionDefinition(&$definitions, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function alterprocessDefinition(&$result, $data, array $options)
    {
        unset($result['alex']);
    }
}
