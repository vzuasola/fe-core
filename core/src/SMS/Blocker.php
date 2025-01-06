<?php

namespace App\SMS;

use App\Fetcher\Drupal\ConfigFetcher;
use App\Fetcher\Integration\UserFetcher;

class Blocker
{
    /** @var UserFetcher */
    private $userFetcher;

    /** @var ConfigFetcher */
    private $configFetcher;

    /** @var array Holds country rules collection */
    private $countryCollection = [];

    /** @var array Holds drupal config  */
    private $config = [];

    /**
     * @param ConfigFetcher $configFetcher
     * @param UserFetcher $userFetcher
     */
    public function __construct(
        ConfigFetcher $configFetcher,
        UserFetcher $userFetcher
    ) {
        $this->userFetcher = $userFetcher;
        $this->configFetcher = $configFetcher;
        $drupalConfig = $this->configFetcher->withProduct('account')->getConfigById('rate_limit');
        $this->config = [
            'countryList' => $drupalConfig['rate_limit_sms_block_countries_list'] ?? '',
            'errorMessage' => $drupalConfig['rate_limit_sms_block_countries_error_message'] ?? 'Mobile number verification is currently not accessible for your territory.',
        ];
        $this->parseCountryConfig();
    }

    /**
     * Checks if a logged in user mobile number is currently blocked on its territory.
     * @param int $mobileIndex The index key that determines the user mobile phone number to check, similar to what was implemented in SMSVerificationFetcher 1=First Number, 2=Second Number, etc...
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function isBlockedByCountry(int $mobileIndex): bool
    {
        if (empty($this->countryCollection)) {
            return false;
        }
        $user = $this->userFetcher->getPlayerDetails();
        $mobileNumbers = array_values($user['mobileNumbers']);
        // Check if number index exists on player account, if it doesn't do not block
        if (!isset($mobileNumbers[$mobileIndex - 1]['number'])) {
            return false;
        }
        $numberToVerify = trim($mobileNumbers[$mobileIndex - 1]['number']);
        $countryToVerify = strtolower($user['countryCode']);
        // Check if country is not blocked
        if (!isset($this->countryCollection[$countryToVerify])) {
            return false;
        }
        // Since there is a blockage in this user country, check if we are blocking all numbers in this country
        if (current($this->countryCollection[$countryToVerify]) === 'all') {
            return true;
        }
        // Since there is a blockage in this user country, but not for all numbers, check for indicatives
        foreach($this->countryCollection[$countryToVerify] as $indicative) {
            // If users number start with indicative, then block it
            if (substr($numberToVerify, 0, strlen($indicative)) === $indicative) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the error message from the config array or default message.
     * @return mixed|string
     */
    public function getErrorMessage(): string
    {
        return $this->config['errorMessage'];
    }

    /**
     * Parses and build a array data structure of the country config from drupal.
     * Schematic is as follows:
     * - Semicolon, and column separated values eg: us:+152,+236;sp;gb:+156,+365;in:+913,+914
     * - Country values can be specified as:
     *   - "us" : Blocking all US territory;
     *   - "us:all" : Same as above;
     *   - "us:+365" : Block US and +365 numbers only;
     *   - "us:+365,+145" : Block US and +365 and +145 and so on;
     * @return void
     */
    private function parseCountryConfig(): void
    {
        try {
            foreach(explode(';', $this->config['countryList']) as $countryRules) {
                list($country,$indicatives) = array_pad(explode(':', $countryRules),2, null);
                $country = strtolower($country);
                if (!is_string($country) || strlen($country) < 2) {
                    continue;
                }
                if (!isset($this->countryCollection[$country])) {
                    $this->countryCollection[$country] = ['all'];
                }
                if ($indicatives && is_string($indicatives) && strlen($indicatives)) {
                    $indicatives = explode(',', $indicatives);
                    if (current($this->countryCollection[$country]) === 'all') {
                        $this->countryCollection[$country] = [];
                    }
                    foreach($indicatives as $indicative) {
                        if (!in_array($indicative, $this->countryCollection[$country])) {
                            $this->countryCollection[$country][] = $indicative;
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            // Do nothing
        }
    }
}
