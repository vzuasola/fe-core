<?php

namespace App\Profiler;

/**
 *
 */
class Profiler
{
    private $start = [];
    private $messages = [];

    /**
     * Public constructor
     */
    public function __construct($session, $settings)
    {
        $this->session = $session;
        $this->settings = $settings;
    }

    /**
     * Start the profiling
     */
    public function start($name = 'DEBUG')
    {
        $this->start[$name] = microtime(true);
    }

    /**
     * Stop the profiling
     */
    public function stop($name = 'DEBUG')
    {
        if (isset($this->start[$name])) {
            $time = microtime(true) - $this->start[$name];

            return round($time * 1000, 4);
        }
    }

    /**
     * Check if profiler should be enabled
     *
     * @return boolean
     */
    public function isProfileable()
    {
        return $this->settings['debug'] ?? 0;
    }

    /**
     * Gets the total render time
     *
     * @return float
     */
    public function getRenderTime()
    {
        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

        return round($time * 1000, 4);
    }

    /**
     * Gets all messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Sets a debug message
     *
     * @param string $message The message content
     * @param string $title The title of this message
     */
    public function setMessage($message, $title = 0, $trace = 0)
    {
        // no profiling when profiler is disabled
        if (!$this->isProfileable()) {
            return;
        }

        $stack = [
            'message' => $message,
        ];

        if ($trace) {
            $stack['trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        if (!isset($this->messages[$title])) {
            $this->messages[$title] = [];
        }

        if (!in_array($stack, $this->messages[$title], true)) {
            $this->messages[$title][] = $stack;
        }
    }

    /**
     *
     */
    public function debug($message, $title = 0, $trace = 0)
    {
        if (function_exists('d')) {
            $this->setMessage(@d($message), $title, $trace);
        }
    }

    /**
     *
     */
    public function debugSession($title = 0, $trace = 0)
    {
        if (function_exists('d')) {
            $this->setMessage(@d($this->session->all()), $title, $trace);
        }
    }
}
