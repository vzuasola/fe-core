<?php

namespace App\Player;

use App\Player\Player;
use App\Utils\Strings;

/**
 * Defines how the application should consume the session
 */
class PlayerSession implements PlayerSessionInterface
{
    const ICORESESSION_CHECK_INTERVAL = 300;

    /**
     * Stores the current login state
     *
     * @var boolean
     */
    protected $login;

    /**
     * Stores the current auth token
     *
     * @var string
     */
    protected $authToken;

    /**
     * Stores the current user details
     *
     * @var array
     */
    protected $details;

    /**
     * The public constructor
     */
    public function __construct(
        $session,
        $secureSession,
        $sessionFetcher,
        $userFetcher,
        $handlerManager,
        $cookieSession
    ) {
        $this->session = $session;
        $this->secureSession = $secureSession;
        $this->sessionFetcher = $sessionFetcher;
        $this->userFetcher = $userFetcher;
        $this->handlerManager = $handlerManager;
        $this->cookieSession = $cookieSession;
    }

    /**
     * {@inheritdoc}
     */
    public function isLogin($forceCheck = false)
    {
        $validSession = $this->session->get('login');

        if ($validSession) {
            $secureToken = $this->getSecureToken();
            $playerId = $this->getPlayerId();
            $logonTime = $this->session->get('logonTime');
            $timeDiff = time() - $logonTime;
            if ($timeDiff >= self::ICORESESSION_CHECK_INTERVAL || $forceCheck) {
                $validService = $this->sessionFetcher->isLogin($secureToken, $playerId);
                $this->session->set('login', $validService);
                $this->session->set('logonTime', time());
            }

            return $this->session->get('login');
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        if ($this->isLogin()) {
            return $this->session->get('username');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIsOTPRequired()
    {
        return $this->session->get('otp');
    }

    /**
     * Gets the player details
     *
     * @return array
     */
    public function getDetails()
    {
        $details = [];
        $secureToken = $this->getSecureToken();
        $playerId = $this->getPlayerId();
        if ($this->isLogin()) {
            $details = $this->secureSession->get(Player::CACHE_KEY);

            if (empty($details)) {
                if (!empty($secureToken) && !empty($playerId)) {
                    $details = $this->userFetcher->getPlayerDetails($secureToken, $playerId);
                } else {
                    $details = $this->userFetcher->getPlayerDetails();
                }

                $this->secureSession->set(Player::CACHE_KEY, $details);
            }
        }
        return $details;
    }

    /**
     * {@inheritdoc}
     */
    public function login($username, $password, $options = [])
    {
        // attempt to do a logout first, this is to prevent previous session state
        // still alive
        if ($this->isLogin()) {
            try {
                $token = $this->sessionFetcher->logout();
            } catch (\Exception $e) {
                // do nothing
            }
        }

        try {
            if (!$this->isValidUsername($username)) {
                \App\Kernel::logger('workflow')->info('AUTH.LOGIN', [
                    'status_code' => 'NOT OK',
                    'request' => [
                        'username' => $username,
                        'password' => Strings::asciiSum($password),
                    ],
                    'response' => [
                        'message' => 'Invalid login credentials',
                    ],
                ]);
                throw new \Exception('Invalid username');
            }

            $response = $this->sessionFetcher->login($username, $password, $options);

            \App\Kernel::logger('workflow')->info('AUTH.LOGIN', [
                'status_code' => 'OK',
                'request' => [
                    'username' => $username,
                    'password' => Strings::asciiSum($password),
                ],
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            \App\Kernel::logger('workflow')->info('AUTH.LOGIN', [
                'status_code' => 'NOT OK',
                'request' => [
                    'username' => $username,
                    'password' => Strings::asciiSum($password),
                ],
                'response' => [
                    'message' => $e->getMessage(),
                ],
            ]);

            throw $e;
        }

        $token = $response['token'];
        $secureToken = $response['secureToken'] ?? null;
        $playerId = $response['playerId'] ?? null;
        $this->session->set('login', true);
        $this->session->set('secureToken', $secureToken);
        $this->session->set('token', $token);
        $this->session->set('username', $username);
        $this->session->set('logonTime', time());

        if ($playerId) {
            $this->session->set('pid', $playerId);
        }

        if (isset($response['otp'])) {
            $this->session->set('login', false);
            $this->session->set('otp', $response['otp']);
        }

        $this->handlerManager->broadcast('login_success', [$username, $password]);

        return true;
    }

    private function isValidUsername($username)
    {
        $isInvalidUSername = preg_match('/^[\p{L}0-9_@\/\!\.\+\-]+$/u', $username) ? true : false ;

        return $isInvalidUSername;
    }

    /**
     * {@inheritdoc}
     */
    public function logout()
    {
        if ($this->isLogin()) {
            $username = $this->getUsername();

            \App\Kernel::logger('workflow')->info('AUTH.LOGOUT', [
                'status_code' => 'OK',
                'request' => [
                    'username' => $username,
                ],
            ]);

            try {
                $secureToken = $this->getSecureToken();
                $playerId = $this->getPlayerId();

                if (empty($secureToken) && empty($playerId)) {
                    $token = $this->sessionFetcher->logout();
                } else {
                    $token = $this->sessionFetcher->logout($secureToken, $playerId);
                }
            } catch (\Exception $e) {
                // do nothing
            }

            $this->session->delete('login');
            $this->session->delete('secureToken');
            $this->session->delete('token');
            $this->session->delete('pid');
            $this->session->delete('username');
            $this->session->delete('password');
            $this->session->delete('logonTime');
        }

        // broadcast logout even if user is not logged in
        if (isset($username)) {
            $this->handlerManager->broadcast('logout', [$username]);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        if (!isset($this->authToken) && $this->isLogin()) {
            try {
                $response = $this->sessionFetcher->getAuthToken();
            } catch (\Exception $e) {
                throw $e;
            }

            $this->authToken = $response['token'];
        }

        return $this->authToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecureToken()
    {
        return $this->session->get('secureToken') ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function getPlayerId()
    {
        return $this->session->get('pid') ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateByToken($token)
    {
        try {
            $response = $this->sessionFetcher->authenticateByToken($token);
        } catch (\Exception $e) {
            \App\Kernel::logger('workflow')->info('AUTH.REAUTH', [
                'status_code' => 'NOT OK',
                'request' => [
                    'token' => $token,
                ],
                'response' => [
                    'message' => $e->getMessage(),
                ],
            ]);

            throw $e;
        }

        $token = $response['token'];
        $username = $response['username'];

        \App\Kernel::logger('workflow')->info('AUTH.REAUTH', [
            'status_code' => 'OK',
            'request' => [
                'username' => $username,
                'token' => $token,
            ],
            'response' => $response,
        ]);

        $this->session->set('login', true);
        $this->session->set('token', $token);
        $this->session->set('username', $username);
        $this->session->set('logonTime', time());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function reauthenticateByToken($username, $password, $token)
    {
        $return = $this->authenticateByToken($token);

        $this->handlerManager->broadcast('login_success', [$username, $password]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function validateSessionPassword($username, $password)
    {
        try {
            $response = $this->sessionFetcher->validateSessionPassword($username, $password);
        } catch (\Exception $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken()
    {
        try {
            if ($this->isLogin()) {
                $token = $this->getSecureToken();
                $playerId = $this->getPlayerId();
                $icoreSession = $this->sessionFetcher->isLogin($token, $playerId);
                if ($icoreSession) {
                    $playerDetails = $this->getDetails();
                    $this->cookieSession->set(
                        $playerDetails['username'],
                        $playerDetails['playerId'],
                        $this->getToken(),
                        $playerDetails['currency']
                    );
                }
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }
}
