<?php

namespace App\Handlers\Login;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use App\Legacy\LegacyEncryption;

/**
 * Handles setting of legacy credentials into the session
 */
class LegacyAuthentication
{
    /**
     * The SSL token used for hashing the configuration
     */
    const SSL_TOKEN = '2f99a4c96706451e500d774cd46d48fe';

    /**
     * Public constructor
     */
    public function __construct($container)
    {
        $this->session = $container->get('session');
    }

    /**
     *
     */
    public function __invoke($username, $password)
    {
        $credentials = LegacyEncryption::encrypt("$username|$password", self::SSL_TOKEN);

        $this->session->set('legacy_credentials', $credentials);
    }
}
