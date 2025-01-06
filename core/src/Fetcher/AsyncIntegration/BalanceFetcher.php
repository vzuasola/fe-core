<?php

namespace App\Fetcher\AsyncIntegration;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use App\Fetcher\Integration\Exception\AccountLockedException;
use App\Fetcher\Integration\Exception\AccountSuspendedException;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Fetches balances from third party integration services
 */
class BalanceFetcher extends AbstractIntegration
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
     * Gets the balance of the specified wallets
     *
     * @return array realmoney balance for specific product
     */
    public function getBalanceByWalletIds($productIds)
    {
        return $this->getRequest("$this->host/balance/wallet/ids/", $productIds);
    }

    /**
     * Gets all the balance (realmoney) per product
     *
     * @return array realmoney balance for specific product
     */
    public function getBalanceByProductIds($productIds)
    {
        return $this->getRequest("$this->host/balance/ids/", $productIds);
    }

    /**
     * Gets all the balance (bonus) per product
     *
     * @return array bonus balance for specific product
     */
    public function getBonusBalanceByProductIds($productIds)
    {
        return $this->getRequest("$this->host/balance/bonus/ids/", $productIds);
    }

    /**
     * Gets all the balance (reserve) per product
     *
     * @return array Reserve balance for specific product
     */
    public function getReservedBalanceByProductIds($productIds)
    {
        return $this->getRequest("$this->host/balance/reserved/ids/", $productIds);
    }

    /**
     * Gets all the balance (nonwithdrawable) per product
     *
     * @return array Non Wthdrawable balance for specific product
     */
    public function getNonWithdrawableBalanceByProductIds($productIds)
    {
        return $this->getRequest("$this->host/balance/nonwithdrawable/ids/", $productIds);
    }

    /**
     * Sets Dafacoin user wallet priorities
     * @param array $data
     * @return Definition
     */
    public function setDafacoinWalletPriorities(array $data)
    {
        return $this->postRequest("$this->host/apex/dcoin/setWalletPriority/", $data);
    }

    public function setCommonPriorityForAllWallets(array $data)
    {
        return $this->postRequest("$this->host/apex/dcoin/toggleAllWalletsPriority/", $data);
    }

    /**
     * Process the fetch request from API
     *
     * @param $path URI of API endpoint
     * @param array $query Query params to be sent
     * @return Definition
     */
    protected function getRequest($path, $query = [])
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

        // If query string is available
        if (!empty($query)) {
            $options['query'] = $query;
        }

        return $this->createRequest($this->client, 'GET', $path, $options, $callback);
    }

    /**
     * Process post requests to API
     *
     * @param $path URI of API endpoint
     * @param array $data POST data
     * @return Definition
     */
    protected function postRequest($path, array $data = [])
    {
        $cookieJar = $this->getCookieJar();

        $callback = function ($data) {
            if (!empty($data)) {
                $json = json_decode($data, true);
                return json_last_error() === JSON_ERROR_NONE ? $json : $data;
            }
            return $data;
        };

        $options = [
            'cookies' => $cookieJar,
            'form_params' => $data,
        ];

        return $this->createRequest($this->client, 'POST', $path, $options, $callback, false);
    }
}
