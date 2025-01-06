<?php

namespace App\Async\Processor;

use App\Async\Processor\PromiseResolutionTrait;
use App\Async\Processor\PromiseInspectionTrait;
use App\Async\Processor\PromiseResponseCacheTrait;

/**
 * Processor for asynchronous requests
 */
class Processor
{
    use PromiseResolutionTrait;
    use PromiseInspectionTrait;
    use PromiseResponseCacheTrait;

    /**
     * Convert definition to actual data
     *
     * @param array $definition A collection of definition array
     *
     * @return array
     */
    public function processDefinitions($definitions)
    {
        $promises = $this->getPromisesByDefinitions($definitions);

        return $this->settlePromisesWithDefinition($promises, $definitions);
    }
}
