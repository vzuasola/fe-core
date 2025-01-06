<?php

namespace App\Controller;

use App\BaseController;
use App\Translations\Currency;
use App\Drupal\Config;
use App\Player\PlayerInvalidException;
use App\Utils\DCoin;

/**
 * Balance controller
 *
 * - Move entire business logic to a service class, controllers should not be
 * responsible for constructing data and computing it
 */
class BalanceController extends BaseController
{
    const DEFAULT_LABELS = [
        4 => 'OW Sports',
        6 => 'Dafa Sports',
        1 => 'Casino Classic',
        2 => 'Casino Gold',
        5 => 'Live Dealer & Games',
        7 => 'Fish Hunter',
        9 => 'Opus Keno',
        10 => 'Exchange',
        11 => 'Esports',
    ];

    const PRODUCT_BALANCE_MAPPING = [
        'casino' => 1,
        'casino_gold' => 2,
        'oneworks' => 4,
        'shared_wallet' => 5,
        'als' => 6,
        'fish_hunter' => 7,
        'opus_keno' => 9,
        'exchange' => 10,
        'esports' => 11,
        'dafa_ghana' => 12
    ];

    const PRODUCT_MAPPING_MOBILE = [
        'mobile-games' => 5,
        'mobile-casino' => 1,
        'mobile-casino-gold' => 2,
        'mobile-live-dealer' => 5,
        'mobile-lottery' => 5,
        'mobile-arcade' => 5,
        'mobile-virtuals' => 6,
        'mobile-ptplus' => 15
    ];

    const FILTER_TYPES = [
        'blocked' => 'block',
        'unsupported_currency' => 'uc',
        'ignore' => 'ignore'
    ];

    const OVERRIDE_CURRENCY = [
        'GHS' => 'GHC',
        'mBC' => 'mBTC',
        'TUS' => 'USDT'
    ];

    const AGENTPLAYER_ALLOWED = [
        'shared_wallet' => 5,
        'als' => 6
    ];

    /**
     * Fetch all balance with details for each
     *
     * @param  object $request
     * @param  object $response
     * @param  mixed  $args
     * @return json
     */
    public function getDetailedTotalBalance($request, $response, $args)
    {
        try {
            $ignore = $request->getQueryParam('ignore', array());
            $currency = $this->get('player')->getCurrency();
            $countryCode = $this->get('player')->getCountryCode();

            // Labels config are important!! Be sure to configure this on CMS layer!
            // Business didn't require a config page for the labels since it won't be "changed"
            // We created a config page for DEV use and ease of display for the front-end
            $labels = $this->get('config_fetcher')->getGeneralConfigById('header_configuration');
            $headerConfigs = $labels;
            $labelOverride = !empty($labels['balance_label_override']) ? $labels['balance_label_override'] : null;

            if (isset($labels['balance_label_mapping']) && !empty($labels['balance_label_mapping'])) {
                $labels = Config::parse($labels['balance_label_mapping']);

                if ($this->lang == 'sc' && $currency == 'RMB') {
                    $labels['5'] = $labelOverride;
                }
            } else {
                $labels = self::DEFAULT_LABELS;
            }

            // Check the visible balance vs the current territory
            $visibility = $this->filterBalanceRequest($labels, $currency, $countryCode, $ignore);

            list($total, $breakdown) = $this->get('balance')->processBalancesForLegacy(
                $labels,
                $visibility
            );

            $currency = self::OVERRIDE_CURRENCY[$currency] ?? $currency;
            $rawCurrency = $currency;
            $format = $this->totalBalanceFormat($currency);
            $currency = $this->currencyTranslation($currency);
            $breakdown = $this->sortBalances($breakdown);
            $productMap = self::PRODUCT_MAPPING_MOBILE;

            return $this->get('rest')->output($response, [
                'format' => $format ?? null,
                'rawCurrency' => $rawCurrency,
                'currency' => $currency ?? null,
                'total' => isset($total) ? number_format($total, 2, '.', ',') : null,
                'product' => $this->getDefaultProductBalanceFromBreakdown($breakdown),
                'breakdown' => $breakdown,
                'label' => $this->getLabels(),
                'tokenLabel' => DCoin::getDCoinCurrencyLabel($headerConfigs, $currency),
                'productMap' => $productMap
            ]);
        } catch (\Exception $e) {
            if ($e instanceof PlayerInvalidException) {
                return $response->withStatus(403);
            }
            return $response->withStatus(500);
        }
    }

    /**
     * Sets wallets priorities fiat / token
     *
     * @param  object $request
     * @param  object $response
     * @return json
     */
    public function setWalletPriority($request, $response)
    {
        try {
            $body = $request->getParsedBody();
            $toggleAll = $body['toggleAll'] ?? false;
            $walletPriorities = $body['priorities'];

            if (empty($walletPriorities)) {
                return $this->get('rest')->output($response, [
                    'status' => 'error',
                    'msg' => 'Wallet priorities are missing.'
                ])->withStatus(400);
            }

            if ($toggleAll) {
                $priorityStatus = reset($walletPriorities);
                $priority = $priorityStatus == 1 ? 'TOKEN' : 'FIAT';
                $result = $this->get('balance')->setCommonPriorityForAllWallets($this->player->getUsername(), $priority);
            } else {
                // Build priorities
                $priorities = [];
                foreach($walletPriorities as $walletId => $status) {
                    $priorities[] = [
                        'walletId' => $walletId,
                        'status' => $status == 1 ? 'TOKEN' : 'FIAT',
                    ];
                }

                // Send wallet priorities to API
                $result = $this->get('balance')->setWalletPriorities($this->player->getUsername(), $priorities);
            }

            if (!isset($result['responseCode']) || $result['responseCode'] !== 'INT064') {
                throw new \Exception('Error in API response');
            }

            return $this->get('rest')->output($response, [
                'status' => 'ok',
            ]);
        } catch (\Exception $e) {
            return $this->get('rest')->output($response, [
                'status' => 'error',
                'msg' => 'Wallet priorities could not be saved'
            ])->withStatus(500);
        }
    }

    /**
     * Fetchers
     *
     */

    /**
     * Format the total balance with the corresponding currency
     *
     * @param string $currency Registered currency of the player
     * @return string $format Balance formatting
     */
    private function totalBalanceFormat($currency)
    {
        $format = '{currency} {total}';

        // Format the balance display via current language
        switch (strtoupper($currency)) {
            case 'RMB':
            case 'MBTC':
                if (in_array($this->lang, ['sc','ch'])) {
                    $format = '{total} {currency}';
                }
                break;
            default:
                break;
        }
        return $format;
    }

    /**
     * Translate currency depending on current langauge
     *
     * @param string $currency Registered currency of the player
     * @return string $currency Translated currency of the player
     */
    private function currencyTranslation($currency)
    {
        $translation = $this->get('translation_manager')->getTranslation('currency');

        if ($translation && isset($translation[$currency])) {
            return $translation[$currency];
        }

        return $currency;
    }

    /**
     * Fetch the balance
     *
     * @param array $labels All balance ids with labels
     * @param array $visibility Visibility settings of balance ids
     * @return  array All balance with breakdown and details
     */
    private function fetchBalances($labels, $visibility)
    {
        $breakDown = [];
        $grandTotal = null;

        // All filtered (territory blocking, unsupported currency etc..) balance ids to be fetched
        $keys = array_keys(array_diff_key($labels, $visibility));

        // Fetch relevant wallet values
        $realMoneyArr = $this->get('balance_fetcher')->getBalanceByProductIds(['ids' => $keys])['balance'];
        $bonusArr = $this->get('balance_fetcher')->getBonusBalanceByProductIds(['ids' => $keys])['balance'];

        // We'll remove the OW Sports bonus, since it's already part of the "realmoney" balance
        unset($bonusArr[self::PRODUCT_BALANCE_MAPPING['oneworks']]);

        // We'll remove the Esports bonus, since it's already part of the "realmoney" balance
        unset($bonusArr[self::PRODUCT_BALANCE_MAPPING['esports']]);

        // Original flow is reserve balance is only avaible in shared_wallet (live dealer)
        // Counter check the filtered wallet Ids ($keys) versus the wallet Ids that has reserved balance
        // To ensure that we only fetch the filtered (blocking etc..) wallet Ids
        if ($reserveBalanceIds = array_intersect($keys, [
            self::PRODUCT_BALANCE_MAPPING['shared_wallet']
        ])
        ) {
            $reserveBalanceArr = $this->get('balance_fetcher')->getReservedBalanceByProductIds(
                [
                    'ids' => $reserveBalanceIds
                ]
            )['balance'];
        }

        // Counter check the filtered wallet Ids ($keys) versus the wallet Ids that has non-withdrawable balance
        // To ensure that we only fetch the filtered (blocking etc..) wallet Ids
        if ($nonWithdrawableBalanceIds = array_intersect($keys, [
            self::PRODUCT_BALANCE_MAPPING['oneworks'],
            self::PRODUCT_BALANCE_MAPPING['als'],
            self::PRODUCT_BALANCE_MAPPING['fish_hunter'],
            self::PRODUCT_BALANCE_MAPPING['esports'],
            self::PRODUCT_BALANCE_MAPPING['dafa_ghana']
        ])
        ) {
            $nonWithdrawableBalanceArr = $this->get('balance_fetcher')->getNonWithdrawableBalanceByProductIds(
                [
                    'ids' => $nonWithdrawableBalanceIds
                ]
            )['balance'];
        }

        foreach ($labels as $key => $label) {
            $sum = [];
            $total = null;
            $realMoney = $realMoneyArr[$key] ?? null;
            $bonus = $bonusArr[$key] ?? null;

            if (!is_null($realMoney)) {
                $sum[] = $realMoney;
            }

            if (!is_null($bonus)) {
                $sum[] = $bonus;
            }

            if (isset($reserveBalanceArr[$key])) {
                $sum[] = $reserveBalanceArr[$key];
            }

            if (isset($nonWithdrawableBalanceArr[$key])) {
                $sum[] = $nonWithdrawableBalanceArr[$key];
            }

            if ($sum) {
                $total = (float) array_sum($sum);
                $grandTotal += $total;
                $total = number_format($total, 2, '.', ',');
            }

            // Totalize all the balance for this product
            $breakDown[$key] = [
                'label' => $label,
                'visibility' => $visibility[$key] ?? 'true',
                'total' => $total
            ];
        }

        return [
            $grandTotal,
            $breakDown
        ];
    }

    /**
     * Gets the product balance from breakdown
     *
     *  - This should not be like this, balance should be fetchable by calling
     * a definition, not passing responses around
     *
     * @return float
     */
    protected function getDefaultProductBalanceFromBreakdown($breakdown)
    {
        $product = [];
        $config = $this->get('config_fetcher')->getGeneralConfigById('header_configuration');
        $currency = $this->get('player')->getCurrency();

        // Get the product Id and labal mapping config
        $labelOverride = !empty($config ['balance_label_override']) ? $config ['balance_label_override'] : null;
        $id = $config['balance_mapping'] ?? null;
        $getMapping = Config::parse($id);

        if (array_key_exists('5', $getMapping) && $this->lang == 'sc'  && $currency == 'RMB') {
            $getMapping['5'] = $labelOverride;
        }

        if ($id) {
            foreach ($breakdown as $key => $value) {
                if ($value['visibility'] === 'block' || $value['visibility'] === 'uc') {
                    continue;
                }

                if (array_key_exists($value['wallet'], $getMapping)) {
                    $product[] = [
                        'wallet' => $value['wallet'],
                        'label' => $getMapping[$value['wallet']],
                        'total' => $value['total']
                    ];
                }
            }

            return $product;
        }
    }

    /**
     *
     */
    private function getLabels()
    {
        $config = $this->get('config_fetcher')->getGeneralConfigById('header_configuration');

        return [
            'product_balance_label' => $config['product_balance_label'] ?? null,
            'total_balance_label' => $config['total_balance_label'] ?? null,
        ];
    }

    /**
     * Filters
     *
     */

    /**
     * Filter the balance ids that will be requested and add flag the type of filter
     *
     * @param array $balanceId
     * @param string $currency
     * @param string $countryCode
     * @param array $ignore
     *
     * @return array
     */
    private function filterBalanceRequest($balanceId, $currency, $countryCode, $ignore)
    {
        try {
            $restrictedCountries = $this->get('territory_blocking_fetcher')->getRestrictedCountries();
        } catch (\Exception $e) {
            // Do nothing
        }

        $visibilityType = [];
        $serverGeoipCountry = $_SERVER['HTTP_X_CUSTOM_LB_GEOIP_COUNTRY'] ?? null;
        $configs = $this->get('config_fetcher')->getGeneralConfigById('header_configuration');
        $agentPlayer = $this->get('player')->getIsPlayerCreatedByAgent();

        foreach ($balanceId as $key => $value) {
            // Ignore wallet flag
            if (in_array($key, $ignore)) {
                $visibilityType[$key] = self::FILTER_TYPES['ignore'];
            }

            // Skip non-whitelisted wallet for agent player
            if ($agentPlayer && !in_array($key, self::AGENTPLAYER_ALLOWED)) {
                $visibilityType[$key] = self::FILTER_TYPES['blocked'];
                continue;
            }

            // Unsupported Currency based on mapping
            if (!empty($configs['currency_balance_mapping'])) {
                $currencyMap = Config::parseCommaDelimited($configs['currency_balance_mapping']);

                $currency = strtoupper($currency);
                foreach ($currencyMap as $productId => $currencies) {
                    if ($key == $productId && !in_array($currency, $currencies)) {
                        // Unsupported currencies should not display
                        $visibilityType[$key] = self::FILTER_TYPES['unsupported_currency'];
                        continue;
                    }
                }
            }

            // Wallet is blocked for this territory
            if (isset($restrictedCountries) && array_key_exists($key, $restrictedCountries)) {
                if (in_array($serverGeoipCountry, $restrictedCountries[$key]) ||
                    in_array($countryCode, $restrictedCountries[$key])
                ) {
                    $visibilityType[$key] = self::FILTER_TYPES['blocked'];
                    // No need to continue since we should not display blocked products
                    continue;
                }
            }
        }

        return $visibilityType += $this->filterByGoldProvisionStatus();
    }

    /**
     * Sort the balances by the current array positioning
     *
     * @param  array $breakdown
     *
     * @return array
     */
    private function sortBalances($breakdown)
    {
        $sorted = [];
        foreach ($breakdown as $key => $balance) {
            // Add wallet id as a new index
            $balance['wallet'] = $key;
            // Prepare new array
            $sorted[] = $balance;
        }

        return $sorted;
    }

    /**
     * Filter by gold provision status
     */
    private function filterByGoldProvisionStatus()
    {
        $casinoGoldPaymentAccountKey = 'casino-gold';

        try {
            // return if player is gold provision to display the gold balance
            if ($this->get('player')->hasAccount($casinoGoldPaymentAccountKey)) {
                return [];
            }
        } catch (\Exception $e) {
            // do nothing
        }

        // disable casino gold balance if exception is encountered or player
        // is not yet gold provisioned
        return [
            self::PRODUCT_BALANCE_MAPPING['casino_gold'] => self::FILTER_TYPES['blocked']
        ];
    }
}
