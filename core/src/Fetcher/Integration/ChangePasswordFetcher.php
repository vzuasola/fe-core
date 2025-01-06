<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 * Change password fetcher class
 */
class ChangePasswordFetcher extends AbstractIntegration
{
    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

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

        $this->host = $host;
    }

    /**
     * Change player password
     * Player initiates the change password request
     *
     * @param $livePassword
     * @param $newPassword
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function changePlayerPassword($livePassword, $newPassword)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/change/password/", [
                'cookies' => $cookieJar,
                'json' => [
                    'livePassword' => $livePassword,
                    'newPassword' => $newPassword
                ]
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Reset Password
     * Player initiates the reset password request
     *
     * @param $token
     * @param $password
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function setResetPassword($token, $password)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/change/forgotten/password/", [
                'cookies' => $cookieJar,
                'json' => [
                    'token' => $token,
                    'password' => $password
                ]
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
