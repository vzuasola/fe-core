<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Utils\Host;
use App\Fetcher\Integration\Exception\ServerDownException;

use App\Player\Player;

/**
 *
 */
class UserFetcher extends AbstractIntegration
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

    private $details;

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
     * Desc
     */
    public function getPlayerDetails($secureToken = '', $playerId = '')
    {
        if (!isset($this->details)) {
            $data = [];
            $secureToken = $this->session->get('secureToken');
            $playerId = $this->session->get('pid');
            if (!empty($secureToken) && !empty($playerId)) {
                $data['query'] = [
                    'secureToken' => $secureToken,
                    'playerId'    => $playerId,
                ];
            } else {
                $cookieJar = $this->getCookieJar();
                $data['cookies'] = $cookieJar;
            }

            try {
                $response = $this->request('GET', "$this->host/user/", $data);
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $data['body']['Is-Mid'] = $response->getHeader('Is-Mid')[0] ?? 'off';

            $this->details = $data['body'];
        }

        return $this->details;
    }

    /**
     * Override logging behavior
     */
    protected function logInfo($uri, $body)
    {
        $body = json_decode($body, true);

        foreach ($body['body'] as $key => $value) {
            if (!in_array($key, Player::KEYS)) {
                unset($body['body'][$key]);
            }
        }

        parent::logInfo($uri, $body);
    }

    /**
     * Update Player Profile
     *
     * @param $playerDetails
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function setPlayerDetails($playerDetails)
    {
        $secureToken = $this->session->get('secureToken');
        $secureToken = $this->session->get('pid');
        if (!empty($secureToken) && !empty($playerId)) {
            $options['json'] = [
                'playerDetails' => $playerDetails,
                'secureToken' => $secureToken,
                'playerId'    => $playerId,
            ];
        } else {
            $cookieJar = $this->getCookieJar();
            $options['cookies'] = $cookieJar;
            $options['json'] = [
                'playerDetails' => $playerDetails,
            ];
        }
        try {
            $response = $this->request('POST', "$this->host/user/update/player/profile/", $options);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['responseCode'];
    }

    /**
     * Get Player Bonus History
     */
    public function getPlayerBonusHistory()
    {
        $cookieJar = $this->getCookieJar();
        try {
            $response = $this->request('GET', "$this->host/user/bonus/history/", [
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
     * Forgot password
     * Player initiates the forgot password request
     *
     * @param $username
     * @param $email
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function setForgotPassword($username, $email, $domain = null, $portalId = 2)
    {
        $cookieJar = $this->getCookieJar();

        if (!isset($domain)) {
            $domain = "https://" . Host::getHostname();
        }

        try {
            $response = $this->request('POST', "$this->host/user/forgot/password/", [
                'cookies' => $cookieJar,
                'json' => [
                    'username' => $username,
                    'email' => $email,
                    'domain' => $domain,
                    'portalId' => $portalId,
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

        return $data['body'];
    }

    /**
     * Forgot Username
     * Player initiates the forgot username request
     *
     * @param $email
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function setForgotUsername($email, $options = [])
    {
        $cookieJar = $this->getCookieJar();

        $params['email'] = $email;

        if (isset($options['portalId'])) {
            $params['portalId'] = $options['portalId'];
        }

        try {
            $response = $this->request('POST', "$this->host/user/forgot/username/", [
                'cookies' => $cookieJar,
                'json' => $params
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

        return $data['body'];
    }

    /**
     * Register Player
     *
     * @param $playerDetails
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function registerPlayer($playerDetails)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/register/player/", [
                'cookies' => $cookieJar,
                'json' => [
                    'playerDetails' => $playerDetails
                ]
            ]);
        } catch (GuzzleException $e) {
            $response = $e->getResponse();
            if (isset($response->getHeaders()['Is-Mid']) && $response->getHeaders()['Is-Mid'][0] == "off") {
                throw new ServerDownException('MID is Down');
            }

            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Validate Player
     *
     * @param $playerDetails
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function validatePlayerData($playerDetails)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/register/validate/", [
                'cookies' => $cookieJar,
                'json' => [
                    'playerDetails' => $playerDetails
                ]
            ]);
        } catch (GuzzleException $e) {
            $response = $e->getResponse();
            if (isset($response->getHeaders()['Is-Mid']) && $response->getHeaders()['Is-Mid'][0] == "off") {
                throw new ServerDownException('MID is Down');
            }

            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
    /**
     * Redeem Coupon
     * Player initiates the redeem coupon request
     *
     * @param $couponCode
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function setRedeemCoupon($couponCode)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/promotion/coupon/", [
                'cookies' => $cookieJar,
                'json' => [
                    'couponCode' => $couponCode
                ]
            ]);
        } catch (GuzzleException $e) {
            $response = $e->getResponse();
            throw $e;
        }
        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Validate CouponCode Code
     * API to validate bonus code.
     *
     * @param $couponCode
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function validateCouponCode($couponCode)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/validate/coupon/code", [
                'cookies' => $cookieJar,
                'json' => [
                    'couponCode' => $couponCode
                ]
            ]);
        } catch (GuzzleException $e) {
            $response = $e->getResponse();
            throw $e;
        }
        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Add CouponCode On ExternalSystem
     * API to claim the bonus.
     *
     * @param $couponCode
     *
     * @return boolean
     *
     * @throws GuzzleException
     */
    public function addCouponExtenal($couponCode)
    {
        $cookieJar = $this->getCookieJar();

        try {
            $response = $this->request('POST', "$this->host/user/add/coupon/code/external", [
                'cookies' => $cookieJar,
                'json' => [
                    'couponCode' => $couponCode
                ]
            ]);
        } catch (GuzzleException $e) {
            $response = $e->getResponse();
            throw $e;
        }
        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Get Player KYC Document Status
     * API to get player document Status.
     *
     * @param $playerId
     *
     * @return array
     *
     */
    public function getDocumentStatus()
    {
        $cookieJar = $this->getCookieJar();

        $response = $this->request('GET', "$this->host/user/document/status", [
            'cookies' => $cookieJar,
        ]);
        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     * Set Player KYC Document Status
     * API to set player document Status.
     *
     * @param int $newStatus Status ID
     *
     * 1 => No Action Required,
     * 2 => Pending Upload
     * 3 => Under Review
     * 4 => Verified
     * 5 => Rejected
     *
     * @return array
     *
     */
    public function setDocumentStatus(int $newStatus) : array
    {
        $cookieJar = $this->getCookieJar();

        $response = $this->request('POST', "$this->host/user/document/status", [
            'cookies' => $cookieJar,
            'json' => [
                'newStatus' => $newStatus,
            ]
        ]);
        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
