<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

/**
 *
 */
class BalanceFetcher extends AbstractIntegration
{
    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * The player Sesion Object
     *
     * @var object
     */
    private $playerSession;

    /**
     * API path to be used
     *
     * @var string
     */
    private $path;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     * @param object $logger
     */
    public function __construct($session, $playerSession, Client $client, $host, $logger, $product)
    {
        parent::__construct($session, $client, $host, $logger, $product);
        $this->playerSession = $playerSession;
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
     * Process the fetch request from API
     *
     * @return array
     */
    protected function getRequest($path, $query = [])
    {

        try {
            // If query string is available
            if (!empty($query)) {
                $params['query'] = $query;

            }
            // Fetch the token and PlayerID
            $secureToken = $this->playerSession->getSecureToken();
            $playerId = $this->playerSession->getPlayerId();
            if (!empty($secureToken) && !empty($playerId)) {
                $params['query']['secureToken'] = $secureToken;
                $params['query']['playerId'] = $playerId;
            } else {
                $cookieJar = $this->getCookieJar();
                $params['cookies'] = $cookieJar;
            }

            $response = $this->request('GET', $path, $params);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }
}
