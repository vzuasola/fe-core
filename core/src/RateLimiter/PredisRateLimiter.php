<?php

namespace App\RateLimiter;

use App\Fetcher\Integration\UserFetcher;
use App\RateLimiter\PredisLimiterMode;
use App\Utils\IP;
use Predis\Client;

class PredisRateLimiter
{
    /** @var Client  */
    private $predisClient;

    /** @var UserFetcher */
    private $user;

    /** @var int User phone index */
    private $userPhoneIndex;

    public function __construct(
        Client $client,
        UserFetcher $userFetcher
    ) {
        $this->predisClient = $client;
        $this->user = $userFetcher;
    }

    public function shouldLimit(string $keyPrefix, PredisLimiterMode $mode, PredisLimiterRate $rate, int $userPhoneIndex = null)
    {
        $counter = 0;
        if ($mode->isIpMode() || $mode->isUserIpMode() || $mode->isUserIpPhoneMode()) {
            $counter += $this->limitByIp($keyPrefix, $rate);
        }
        if ($mode->isUserMode() || $mode->isUserIpMode() || $mode->isUserIpPhoneMode()) {
            $counter += $this->limitByUsername($keyPrefix, $rate);
        }
        if ($mode->isPhoneMode() || $mode->isUserIpPhoneMode()) {
            $counter += $this->limitByPhone($keyPrefix, $rate, $userPhoneIndex);
        }
        return $counter > 0;
    }

    private function limitByUsername(string $keyPrefix, PredisLimiterRate $rate): bool
    {
        return $this->limit($keyPrefix,PredisLimiterMode::createUserMode(), $rate);
    }

    private function limitByIp(string $keyPrefix, PredisLimiterRate $rate): bool
    {
        return $this->limit($keyPrefix,PredisLimiterMode::createIpMode(), $rate);
    }

    private function limitByPhone(string $keyPrefix, PredisLimiterRate $rate, int $userPhoneIndex = null): bool
    {
        if (empty($userPhoneIndex)) {
            return false;
        }
        $this->userPhoneIndex = $userPhoneIndex;
        return $this->limit($keyPrefix,PredisLimiterMode::createPhoneMode(), $rate);
    }

    private function limit(string $keyPrefix, $mode, PredisLimiterRate $rate): bool
    {
        $counterKey = $this->buildKey($keyPrefix, $mode, $rate);
        $counter = $this->predisClient->incr($counterKey);
        if ($counter == 1) {
            $this->predisClient->expire($counterKey, $rate->getInterval());
        }
        return ($counter > $rate->getMaxActions());
    }

    private function buildKey(string $keyPrefix, PredisLimiterMode $mode, PredisLimiterRate $rate)
    {
        $limiterMode = $mode->getMode();
        switch($limiterMode) {
            case PredisLimiterMode::IP_MODE:
                $identifier = IP::getIpAddress();
                break;
            case PredisLimiterMode::USER_MODE:
                $identifier = $this->getUserId();
                break;
            case PredisLimiterMode::PHONE_MODE:
                $identifier = $this->getUserPhone();
                break;
            default:
                $identifier = $this->getUserId();
        }

        $interval = $rate->getInterval();

        return "$keyPrefix$identifier:$interval";
    }

    private function getUserId()
    {
        $user = $this->user->getPlayerDetails();
        if (isset($user['username']) && !empty($user['username'])) {
            return $user['username'];
        }

        throw new \Exception('Username not found for logged in user.');
    }

    private function getUserPhone()
    {
        $user = $this->user->getPlayerDetails();
        $mobileNumbers = array_values($user['mobileNumbers']);
        // Check if number index exists on player account, if it doesn't do not block
        $phone = $mobileNumbers[$this->userPhoneIndex - 1]['number'] ?? null;
        if (!empty($phone)) {
            return $phone;
        }

        throw new \Exception('User phone number not found for logged in user.');
    }
}
