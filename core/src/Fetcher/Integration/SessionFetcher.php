<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Fetcher\Integration\Exception\AccountLockedException;
use App\Fetcher\Integration\Exception\AccountSuspendedException;
use App\Fetcher\Integration\Exception\TokenQuotaExceededException;
use App\Fetcher\Integration\Exception\ServerDownException;

/**
 *
 */
class SessionFetcher extends AbstractIntegration
{
    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * The session object
     *
     * @var object
     */
    private $session;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     * @param object $logger
     */
    public function __construct($session, Client $client, $host, $logger, $product)
    {
        parent::__construct($session, $client, $host, $logger, $product);
        $this->session = $session;
        $this->host = $host;
    }

    /**
     * Logins a player using a username and a password
     *
     * @param string $username
     * @param string $password
     *
     * @return array
     */
    public function login($username, $password, $options = [])
    {
        try {
            $data['json'] = [
                'username' => $username,
                'password' => $password,
                'session'  => $options['session'] ?? true
            ];

            if (isset($options['header'])) {
                $data['headers'] = $options['header'];
            }

            $response = $this->request('POST', "$this->host/user/login/", $data);
        } catch (GuzzleException $e) {
            $error = json_decode($e->getResponse()->getBody()->getContents(), true);
            // check if the exception thrown by logic layer actually means
            // anything
            if (isset($error['responseCode'])) {
                switch ($error['responseCode']) {
                    case 'INT008':
                        throw new AccountLockedException('Account is locked', 0, $e);

                    case 'INT009':
                        throw new AccountSuspendedException('Account is suspended', 0, $e);

                    case 'INT051':
                        throw new TokenQuotaExceededException('Token Quota Exceeded', 0, $e);
                }
            }

            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Checks if a player is authenticated on logic layer
     *
     * @return array
     */
    public function isLogin($token = '', $playerId = '')
    {
        $data = [];

        if (!empty($token) && !empty($playerId)) {
            $data['query'] = [
                'secureToken' => $token,
                'playerId'    => $playerId
            ];
        } else {
            $cookieJar = $this->getCookieJar();
            $data['cookies'] = $cookieJar;
        }

        try {
            $response = $this->request('GET', "$this->host/user/login/validate/", $data);
        } catch (GuzzleException $e) {
            if ($e->getCode() == 401) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    /**
     * Logs out the current active player session
     *
     * @return array
     */
    public function logout($secureToken = '', $playerId = '')
    {
        $data = [];
        if (!empty($secureToken) && !empty($playerId)) {
            $data['json'] = [
                'secureToken' => $secureToken,
            ];
        } else {
            $cookieJar = $this->getCookieJar();
            $data['cookies'] = $cookieJar;
        }

        try {
            $response = $this->request('POST', "$this->host/user/logout/", $data);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Gets the current active authentication token
     *
     * @return array
     */
    public function getAuthToken()
    {
        $data = [];
        if (!empty($this->session->get('secureToken'))) {
            return ['token' => $this->session->get('secureToken')];
        }
        $cookieJar = $this->getCookieJar();
        $data['cookies'] = $cookieJar;

        try {
            $response = $this->request('GET', "$this->host/user/auth/token/", $data);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Authenticates a user using an authentication token
     *
     * @param string $token
     *
     * @return array
     */
    public function authenticateByToken($token)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/login/token/", [
                'cookies' => $cookieJar,
                'json' => ['token' => $token]
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Check Session Password
     *
     * @param $username
     * @param $password
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function validateSessionPassword($username, $password)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/validate/", [
                'cookies' => $cookieJar,
                'json' => [
                    'username' => $username,
                    'password' => $password
                ]
            ]);
        } catch (GuzzleException $e) {
            $response = $e->getResponse();
            if (isset($response->getHeaders()['Is-Mid']) && $response->getHeaders()['Is-Mid'][0] == "off") {
                throw new ServerDownException('MID is Down');
            }
            throw $e;
        }

        if (isset($response->getHeaders()['Is-Mid']) && $response->getHeaders()['Is-Mid'][0] == "off") {
            throw new ServerDownException('MID is Down');
        }


        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['responseCode'];
    }
}
