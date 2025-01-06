<?php

namespace App\Player;

/**
 *
 */
class Player implements PlayerInterface
{
    const KEYS = ['username', 'playerId', 'currency', 'productId', 'vipLevel', 'countryCode', 'countryId'];
    const CACHE_KEY = 'player.detail';
    const ACCOUNT_CACHE_KEY = 'player.account';

    private $playerSession;
    private $session;
    private $users;
    private $paymentAccount;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('player_session'),
            $container->get('secure_session'),
            $container->get('user_fetcher'),
            $container->get('payment_account_fetcher')
        );
    }

    /**
     *
     */
    public function __construct($playerSession, $session, $users, $paymentAccount)
    {
        $this->playerSession = $playerSession;
        $this->session = $session;
        $this->users = $users;
        $this->paymentAccount = $paymentAccount;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        $this->checkSession();

        return $this->playerSession->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency($forceCheck = false)
    {
        $this->checkSession($forceCheck);

        return $this->getValue('currency');
    }

    /**
     * {@inheritdoc}
     */
    public function getPlayerID()
    {
        $this->checkSession();

        return $this->getValue('playerId');
    }

    /**
     * {@inheritdoc}
     */
    public function getProductID()
    {
        $this->checkSession();

        return $this->getValue('productId');
    }

    /**
     * {@inheritdoc}
     */
    public function getVipLevel()
    {
        $this->checkSession();

        return $this->getValue('vipLevel');
    }

    /**
     * {@inheritdoc}
     */
    public function hasAccount($product)
    {
        $this->checkSession();

        $store = $this->session->get(self::ACCOUNT_CACHE_KEY) ?: [];

        if (isset($store[$product])) {
            return $store[$product];
        }

        $hasAccount = $this->paymentAccount->hasAccount($product);
        $store[$product] = $hasAccount;

        $this->session->set(self::ACCOUNT_CACHE_KEY, $store);

        return $hasAccount;
    }

    /**
     * Player Details
     *
     */

    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        $this->checkSession();

        return $this->getValue('countryCode');
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryID()
    {
        $this->checkSession();

        return $this->getValue('countryId');
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryName()
    {
        $this->checkSession();

        return $this->getValue('countryName');
    }

    /**
     * {@inheritdoc}
     */
    public function getGender()
    {
        $this->checkSession();

        return $this->getValue('gender');
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        $this->checkSession();

        return $this->getValue('firstName');
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        $this->checkSession();

        return $this->getValue('lastName');
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        $this->checkSession();

        return $this->getValue('locale');
    }

    /**
     * {@inheritdoc}
     */
    public function getMobileNumbers()
    {
        $this->checkSession();

        return $this->getValue('mobileNumbers');
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        $this->checkSession();

        return $this->getValue('address');
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        $this->checkSession();

        return $this->getValue('city');
    }

    /**
     * {@inheritdoc}
     */
    public function getPostalCode()
    {
        $this->checkSession();

        return $this->getValue('postalCode');
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        $this->checkSession();

        return $this->getValue('email');
    }

    /**
     * {@inheritdoc}
     */
    public function getBirthDate()
    {
        $this->checkSession();

        return $this->getValue('dateOfBirth');
    }

    /**
     * {@inheritdoc}
     */
    public function getIsPlayerCreatedByAgent()
    {
        $this->checkSession();

        return $this->getValue('isPlayerCreatedByAgent');
    }

    /**
     *
     */
    private function checkSession($forceCheck = false)
    {
        if (!$this->playerSession->isLogin($forceCheck)) {
            throw new PlayerInvalidException();
        }
    }

    /**
     * Gets a value from the session or just fetch it via a request
     *
     * @param string $index
     *
     * @return mixed
     */
    private function getValue($index)
    {
        // check on session first

        $store = $this->session->get(self::CACHE_KEY);

        if (isset($store[$index])) {
            return $store[$index];
        }

        // make a user player details request

        $details = $this->users->getPlayerDetails();

        // session cache is empty so we will try to cache it

        if (empty($store)) {
            $this->session->set(self::CACHE_KEY, $details);
        }

        if (isset($details[$index])) {
            return $details[$index];
        }
    }
}
