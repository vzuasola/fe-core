<?php

namespace App;

/**
 * Handles parameter configuration handling
 */
class Parameters implements \ArrayAccess
{
    private $parameters;

    /**
     *
     */
    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Sets a parameter or an object.
     */
    public function offsetSet($id, $value)
    {
        $this->parameters[$id] = $value;
    }

    /**
     * Gets a parameter or an object.
     */
    public function offsetGet($id)
    {
        $value = null;

        if (isset($this->parameters[$id])) {
            $param = $this->parameters[$id];

            // check if this is an environment variable
            if (preg_match('/%env\((.*)\)%/', $param, $matches) === 1) {
                $key = $matches[1];

                if (isset($_SERVER[$key])) {
                    $value = $_SERVER[$key];
                } else {
                    $default = "env($key)";
                    $value = $this->parameters[$default] ?? null;
                }
            } else {
                // else just use the actual parameter
                $value = $param;
            }
        }

        return $value;
    }

    /**
     * Checks if a parameter or an object is set.
     */
    public function offsetExists($id)
    {
        return isset($this->parameters[$id]);
    }

    /**
     * Unsets a parameter or an object.
     */
    public function offsetUnset($id)
    {
        unset($this->parameters[$id]);
    }
}
