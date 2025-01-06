<?php

namespace App\Session;

/**
 * Custom Session Handler for managing session variables
 */
class SessionHandler implements SessionInterface
{
    use FlashMessagesTrait;

    const FLASH_PREFIX = '_FLASH_';

    private $session = [];
    private $isSessionStarted = false;
    private $domain;
    private $logger;
    private $lazy_session = false;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('settings')['product'],
            $container->get('settings')['session_handler']['cookie_domain'],
            $container->get('logger'),
            $container->get('settings')['session']['lazy']
        );
    }

    /**
     * Public constructor.
     */
    public function __construct($product, $domain, $logger, $lazy_session)
    {
        $this->product = $product;
        $this->domain = $domain;
        $this->logger = $logger;
        $this->lazy_session = $lazy_session;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $this->checkSessionState();

        return $this->session;
    }

    /**
     * {@inheritdoc}
     */
    public function get($index)
    {
        $this->checkSessionState();

        return $this->session[$index] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($index, $value)
    {
        $this->startSession(false);

        $_SESSION[$index] = $value;
        $this->session[$index] = $value;

        session_write_close();

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($index)
    {
        $this->startSession(false);

        unset($_SESSION[$index]);
        unset($this->session[$index]);

        session_write_close();
    }

    /**
     * {@inheritdoc}
     */
    public function destroy()
    {
        session_unset();
        session_destroy();
        session_write_close();
    }

    /**
     *
     */
    private function checkSessionState()
    {
        if (!$this->isSessionStarted) {
            $this->startSession();

            $this->isSessionStarted = true;
            $this->session = $_SESSION;

            session_write_close();
        }
    }

    /**
     *
     */
    private function startSession($read_and_close = true)
    {
        $options = [];

        // If lazy_session is true,
        // we start the session and close it immediately
        // using the read_and_close parameter.
        // Only set and delete operations will write to the session store
        // in this case redis
        if ($this->lazy_session) {
            $options['read_and_close'] = $read_and_close;
        }

        if ($this->domain && is_string($this->domain)) {
            $options['cookie_domain'] = $this->domain;
        } else {
            $this->logger->error('invalid_session_domain', [
                'domain' => $this->domain,
                'http_host' => $_SERVER['HTTP_HOST'] ?? '',
            ]);
        }

        @session_start($options);
    }
}
