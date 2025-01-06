<?php

namespace App\Async;

use GuzzleHttp\Client;

use App\Async\Processor\PromiseResolutionTrait;
use App\Async\Processor\PromiseInspectionTrait;

/**
 * An array async definition object
 *
 * A definition is a resource that is deferred, data will be fetch after
 * processing it
 */
class ArrayDefinition
{
    /**
     *
     */
    private $callback = [];

    /**
     *
     */
    private $options = [];

    /**
     * Public constructor
     */
    public function __construct($data, $options = [], $callback = null)
    {
        $this->data = $data;
        $this->options = $options;
        $this->callback[] = $callback;
    }

    /**
     *
     */
    public function getData()
    {
        return $this->data;
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
        return $this->data;
    }
}
