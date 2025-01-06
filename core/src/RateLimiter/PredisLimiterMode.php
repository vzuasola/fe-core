<?php

namespace App\RateLimiter;

class PredisLimiterMode
{
    const IP_MODE = 'ip_mode';
    const USER_MODE = 'user_mode';
    const PHONE_MODE = 'phone_mode';
    const USER_IP_MODE = 'user_ip_mode';
    const USER_IP_PHONE_MODE = 'user_ip_phone_mode';

    private $mode;

    public static function createIpMode()
    {
        return new self(self::IP_MODE);
    }

    public static function createUserMode()
    {
        return new self(self::USER_MODE);
    }

    public static function createPhoneMode()
    {
        return new self(self::PHONE_MODE);
    }

    public static function createUserIpMode()
    {
        return new self(self::USER_IP_MODE);
    }

    public static function createUserIpPhoneMode()
    {
        return new self(self::USER_IP_PHONE_MODE);
    }

    public static function createPredisLimiterModeByType($type) {
        switch($type) {
            case self::IP_MODE:
                $mode = self::createIpMode();
                break;
            case self::USER_MODE:
                $mode = self::createUserMode();
                break;
            case self::PHONE_MODE:
                $mode = self::createPhoneMode();
                break;
            case self::USER_IP_MODE:
                $mode = self::createUserIpMode();
                break;
            case self::USER_IP_PHONE_MODE:
                $mode = self::createUserIpPhoneMode();
                break;
            default:
                $mode = self::createUserMode();
        }

        return $mode;
    }

    protected function __construct($mode)
    {
        $this->mode = $mode;
    }

    public function isIpMode()
    {
        return $this->mode == self::IP_MODE;
    }

    public function isUserMode()
    {
        return $this->mode == self::USER_MODE;
    }

    public function isPhoneMode()
    {
        return $this->mode == self::PHONE_MODE;
    }

    public function isUserIpMode()
    {
        return $this->mode == self::USER_IP_MODE;
    }

    public function isUserIpPhoneMode()
    {
        return $this->mode == self::USER_IP_PHONE_MODE;
    }

    public function getMode()
    {
        return $this->mode;
    }
}
