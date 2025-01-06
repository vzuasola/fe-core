<?php

namespace App\Async;

use GuzzleHttp\Promise;

use App\Async\Processor\PromiseResolutionTrait;
use App\Async\Processor\PromiseInspectionTrait;
use App\Async\Processor\PromiseResponseCacheTrait;

/**
 * An async collection definition object
 *
 * A definition is a resource that is deferred, data will be fetch after
 * processing it
 */
class DefinitionCollection
{
    use PromiseResolutionTrait;
    use PromiseInspectionTrait;
    use PromiseResponseCacheTrait;

    /**
     *
     */
    private $callback = [];

    /**
     * Public constructor
     */
    public function __construct($definitions, $options, $callback = null)
    {
        $this->definitions = $definitions;
        $this->options = $options;
        $this->callback[] = $callback;
    }

    /**
     *
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     *
     */
    public function getDefinition($definition)
    {
        return $this->definitions[$definition] ?? null;
    }

    /**
     *
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     *
     */
    public function getCallbacks()
    {
        return $this->callback;
    }

    /**
     *
     */
    public function withCallback($callback)
    {
        $this->callback[] = $callback;

        return $this;
    }

    /**
     *
     */
    public function resolve()
    {
        $definitions = ['definition' => $this];

        $promises = $this->getPromisesByDefinitions($definitions);
        $result = $this->settlePromisesWithDefinition($promises, $definitions);

        return reset($result);
    }
}
