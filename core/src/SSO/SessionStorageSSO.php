<?php

namespace App\SSO;

use App\Utils\Host;

class SessionStorageSSO implements SSOInterface
{
    /**
     * Player session object
     *
     * @var object
     */
    private $playerSession;

    /**
     * Config fetcher
     *
     * @var object
     */
    private $configs;

    /**
     * Router request object
     *
     * @var object
     */
    private $request;

    /**
     * Encryption object
     *
     * @var object
     */
    private $encryption;

    /**
     * Logger object
     *
     * @var object
     */
    private $logger;

    /**
     * Public constructor
     */
    public function __construct($playerSession, $configs, $request, $encryption, $logger)
    {
        $this->playerSession = $playerSession;
        $this->configs = $configs;
        $this->request = $request;
        $this->encryption = $encryption;
        $this->logger = $logger;
    }

    /**
     * @{inheritdoc}
     */
    public function getServerUri()
    {
        $config = $this->configs->getGeneralConfigById('single_signon');

        return $config['single_signon_server'] ?? null;
    }

    /**
     * @{inheritdoc}
     */
    public function isDomainAllowed($domain)
    {
        if ($domain) {
            $config = $this->configs->getGeneralConfigById('single_signon');
            $whitelists = $config['supported_domains'] ?? [];

            foreach (explode(PHP_EOL, $whitelists) as $value) {
                $value = trim($value);

                if (fnmatch($value, $domain)) {
                    return true;
                }
            }
        }
    }

    /**
     * @{inheritdoc}
     */
    public function getServerIdentifier()
    {
        if (session_status() !== PHP_SESSION_NONE) {
            $id = session_id();

            return $this->encryptToken($id);
        }
    }

    /**
     * @{inheritdoc}
     */
    public function isClientAuthenticated($id)
    {
        $currentId = session_id();
        $id = $this->decryptToken($id);

        return $id === $currentId;
    }

    /**
     * @{inheritdoc}
     */
    public function setClientIdentifier($id)
    {
        try {
            $id = $this->decryptToken($id);

            if ($id) {
                $SESSION = $_SESSION;

                session_destroy();
                session_id($id);
                session_start();

                // merges old session with new session
                $_SESSION = array_replace_recursive($_SESSION, $SESSION);

                return true;
            }
        } catch (\Exception $e) {
            // do nothing, just return null
        }
    }

    /**
     *
     */
    private function encryptToken($id)
    {
        $issuer = Host::getHostnameFromUri($this->request->getUri()->getBaseUrl());

        // JWT options
        $options = [
            'issuer' => trim($issuer, '/'),
            'audience' => 'webcomposer',
            'expire_time' => time() + (60 * 5), // 5 minutes
        ];

        return $this->encryption->encrypt($id, $options);
    }

    /**
     *
     */
    private function decryptToken($id)
    {
        $server = $this->getServerUri();
        $server = Host::getHostnameFromUri($server);

        $options = [
            'issuer' => trim($server, '/'),
            'audience' => 'webcomposer',
        ];

        $decrypt = $this->encryption->decrypt($id, $options);

        if (empty($decrypt)) {
            $this->logger->warning('sso_invalid_token', [
                'message' => 'An invalid token was attempted to used as SSO authentication',
                'token' => $id,
            ]);
        }

        return $decrypt;
    }
}
