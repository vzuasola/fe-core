<?php

namespace App\Fetcher\AsyncIntegration;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use App\Fetcher\Integration\Exception\AccountLockedException;
use App\Fetcher\Integration\Exception\AccountSuspendedException;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Fetches session related data
 */
class SessionFetcher extends AbstractIntegration
{
    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     * @param object $logger
     */
    public function __construct($session, $playerSession, Client $client, $host, $logger, $product)
    {
        parent::__construct($session, $playerSession, $client, $host, $logger, $product);

        $this->client = $client;
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
    public function login($username, $password)
    {
        try {
            $response = $this->request('POST', "$this->host/user/login/", [
                'json' => ['username' => $username, 'password' => $password]
            ]);
        } catch (GuzzleException $e) {
            $contents = $e->getResponse()->getBody()->getContents();
            $error = json_decode($contents, true);

            // check if the exception thrown by logic layer actually means
            // anything
            if (isset($error['responseCode'])) {
                $code = $error['responseCode'];

                switch ($code) {
                    case 'INT008':
                        throw new AccountLockedException('Account is locked', 0, $e);

                    case 'INT009':
                        throw new AccountSuspendedException('Account is suspended', 0, $e);
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
    public function isLogin()
    {
        $cookieJar = $this->getCookieJar();

        $callback = function ($data, $options, $response) {
            if (!empty($data)) {
                return true;
            }

            return false;
        };

        $options = [
            'cookies' => $cookieJar
        ];

        return $this->createRequest($this->client, 'GET', "$this->host/user/login/validate/", $options, $callback);
    }

    /**
     * Logs out the current active player session
     *
     * @return array
     */
    public function logout()
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/logout/", [
                'cookies' => $cookieJar
            ]);
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
     * @return Definition
     */
    public function getAuthToken()
    {
        $cookieJar = $this->getCookieJar();

        $callback = function ($data, $options, $response) {
            if (!empty($data)) {
                $data = json_decode($data, true);

                return $data['body'];
            }
        };

        $options = [
            'cookies' => $cookieJar
        ];

        return $this->createRequest($this->client, 'GET', "$this->host/user/auth/token/", $options, $callback);
    }

    /**
     * Authenticates a user using an authentication token
     *
     * @param string $token
     *
     * @return Definition
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
     * Check if username and password is valid
     *
     * @param string $username
     * @param string $password
     *
     * @return boolean
     */
    public function validate($username, $password)
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
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return true;
    }
}
