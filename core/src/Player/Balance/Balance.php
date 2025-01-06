<?php

namespace App\Player\Balance;

use App\Async\Async;

/**
 * Balance service class
 * - This should be refactored as a real service class and with definitions for each wallet
 * - This is just a temporary "library" class just to fix multiple icore call
 */
class Balance
{
    /**
     * Balance cache
     */
    private static $balance = [];

    private $balanceFetcher;
    private $player;
    private $dcoinEnabled;

    /**
     * Public constructor
     */
    public function __construct($balanceFetcherAsync, $dcoinEnabled = false)
    {
        $this->balanceFetcherAsync = $balanceFetcherAsync;
        $this->dcoinEnabled = $dcoinEnabled;
    }

    /**
     * Override method to process the balance controller
     */
    public function processBalancesForLegacy($labels, $visibility)
    {
        try {
            $breakDown = [];
            $grandTotal = 0;

            // All filtered (territory blocking, unsupported currency etc..) balance ids to be fetched
            $keys = array_keys(array_diff_key($labels, $visibility));

            $balances = $this->getTotalBalancesByWallet($keys);

            foreach ($labels as $key => $label) {
                $total = null;
                $tokenTotal = null;
                $fiatTotal = null;
                $walletPriority = null;
                // Type case the key since we use string in as keys
                $key = (string) $key;

                // Check if the balance result is existing
                $balance = $balances[$key] ?? null;
                if ($balance) {
                    $grandTotal += $balance['balance'];
                    $total = number_format($balance['balance'], 2, '.', ',');
                    $tokenTotal = $balance['dcoinBalance']['TokenBalance'];
                    $fiatTotal = $balance['dcoinBalance']['FiatBalance'];
                    $walletPriority = $balance['dcoinBalance']['WalletPriority'] ?? 'COULD_NOT_FETCH';
                }
                $priorityFetched = in_array($walletPriority, ['FIAT','TOKEN']);
                $balanceFetched = !is_null($balance);
                $walletStatus = [
                    'status' => true,
                    'error' => ''
                ];

                if (!$priorityFetched || !$balanceFetched) {
                    $walletStatus['status'] = false;
                    $walletStatus['error'] = !$balanceFetched ? 'balance' : 'priority';
                }

                $breakDown[$key] = [
                    'label' => $label,
                    'visibility' => $visibility[$key] ?? true,
                    'total' => $total,
                    'totalToken' => number_format($tokenTotal, 2),
                    'totalFiat' => number_format($fiatTotal, 2),
                    'tokenFlag' => $walletPriority === 'TOKEN',
                    'walletStatus' => $walletStatus,
                ];
            }

            return [
                $grandTotal,
                $breakDown
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Fetch and compute the total balance per wallet
     */
    public function getTotalBalancesByWallet($ids)
    {
        $breakdown = [];
        $balances = $this->fetchRawBalancesByIds($ids);

        if ($balances) {
            foreach ($balances['balance'] as $key => $value) {
                // Get the wallet types to be computed
                $types = array_intersect_key($value, array_flip(Definition::WALLET_TYPE_MAPPING[$key]));

                // Get the total of each wallet
                $breakdown[$key]['balance'] = array_sum(array_values($types));
                $breakdown[$key]['dcoinBalance'] = $balances['dcoinBalance'][$key];

            }
        }
        return $breakdown;
    }

    /**
     * Sends user wallet priorities to API
     * @param string $username
     * @param array $priorities
     * @return string[]
     */
    public function setWalletPriorities(string $username, array $priorities)
    {
        return $this->balanceFetcherAsync->setDafacoinWalletPriorities([
            'username' => $username,
            'walletStatuses' => $priorities
        ])->resolve();
    }

    public function setCommonPriorityForAllWallets(string $username, string $priority)
    {
        return $this->balanceFetcherAsync->setCommonPriorityForAllWallets([
            'username' => $username,
            'priority' => $priority
        ])->resolve();
    }

    /**
     * Fetch the raw balance from api
     */
    private function fetchRawBalancesByIds($ids = [])
    {
        if (self::$balance) {
            // Use the static data
            return self::$balance;
        }

        $params = [ 'ids' => $ids ];
        if ($this->dcoinEnabled) {
            $params['dcoinEnabled'] = 'true';
        }

        return $this->balanceFetcherAsync->getBalanceByWalletIds($params)->resolve();
    }

}
