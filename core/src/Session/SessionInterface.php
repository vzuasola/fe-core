<?php

namespace App\Session;

/**
 * Defines how the application should consume the session
 */
interface SessionInterface
{
    /**
     * Gets all the active session
     */
    public function all();

    /**
     * Gets a specific session index
     *
     * @param string $index
     */
    public function get($index);

    /**
     * Sets a specific session value
     *
     * @param string $index
     * @param mixed  $value
     */
    public function set($index, $value);

    /**
     * Deletes a specific session index
     *
     * @param string $index
     */
    public function delete($index);

    /**
     * Gets a flash session value
     * A flash session will get destroyed after being fetched
     *
     * @param string $index
     *
     * @return array
     */
    public function getFlash($index);

    /**
     * Checks if a flash session value exist
     *
     * @param string $index
     *
     * @return boolean
     */
    public function hasFlash($index);

    /**
     * Checks if there are flash messages that are not yet consumed
     *
     * @return boolean
     */
    public function hasFlashes();

    /**
     * Checks if this request has a flash message action done
     *
     * @return boolean
     */
    public function requestHasFlashes();

    /**
     * Sets a specific session value
     *
     * @param string $index
     * @param mixed  $value
     */
    public function setFlash($index, $value);

    /**
     * Destory the PHP session immediately
     */
    public function destroy();
}
