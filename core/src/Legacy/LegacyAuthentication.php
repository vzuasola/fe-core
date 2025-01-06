<?php

namespace App\Legacy;

/**
 * Class that handles legacy authentication
 */
class LegacyAuthentication
{
    /**
     * The SSL token used for hashing the configuration
     */
    const SSL_TOKEN = '2f99a4c96706451e500d774cd46d48fe';

    /**
     * Player Session
     *
     * @var object
     */
    private $playerSession;

    /**
     * Session Handler
     *
     * @var object
     */
    private $session;

    /**
     * Public constructor
     */
    public function __construct($playerSession, $session)
    {
        $this->playerSession = $playerSession;
        $this->session = $session;
    }

    /**
     * Gets the legacy token
     *
     * @return string
     */
    public function getAuthenticationToken()
    {
        if ($this->playerSession->isLogin()) {
            return $this->session->get('legacy_credentials');
        }
    }
}
