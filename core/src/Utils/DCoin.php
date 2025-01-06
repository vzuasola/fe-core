<?php

namespace App\Utils;

/**
 * DCoin Helper Class
 */
class DCoin
{

    /**
     * Returns whether dcoin is enabled or not based on its feature flag
     *  and if player's currency is in the list of allowed currencies
     *
     * @param array $headerConfig CMS Configuration for Header
     * @param \App\Player\PlayerSession $playerSession PlayerSession Object
     *
     * @return bool
     */
    public static function isDafacoinEnabled($headerConfig, $playerSession)
    {

        $userLoggedIn = $playerSession->isLogin() ?? false;
        $dafacoinToggle = $headerConfig['dafacoin_balance_toggle'] ?? false;
        // Default to allow dcoin for non logged in user
        $allowedToPlayer = true;
        if ($userLoggedIn) {
            $dafacoinEnabledCurrencies = array_map(
                'trim',
                explode(PHP_EOL, $headerConfig['dafacoin_enabled_currencies'])
            );

            // we only need to check currency and provisioned if logged in
            $playerCurrency= $playerSession->getDetails()['currency'] ?? 'n/a';
            $currencyAllowed = in_array($playerCurrency, $dafacoinEnabledCurrencies);

            $playerWalletDetails = $playerSession->getDetails()['tokenStatus'] ?? [];
            $playerProvisionedForDcoin = ($playerWalletDetails[0]['TokenBalanceMode'] ?? 0) > 0;

            $allowedToPlayer = $currencyAllowed && $playerProvisionedForDcoin;
        }
        return $dafacoinToggle && $allowedToPlayer;
    }

    /**
     * Returns array of data to be passed to the twig templates
     *
     * @param array $headerConfig CMS Header configuration
     *
     * @return array Data passed to twig templates
     */
    public static function getDafacoinData($headerConfig)
    {
        return [
            'total_balance_label' => strtoupper($headerConfig['dafacoin_total_balance_label'] ?? ''),
            'priority_switch_message' => $headerConfig['dafacoin_priority_switch_message'] ?? '',
            'save_button_label' => $headerConfig['save_button_label'] ?? '',
            'close_button_label' => $headerConfig['close_button_label'] ?? '',
            'yes_button_label' => $headerConfig['yes_button_label'] ?? '',
            'no_button_label' => $headerConfig['no_button_label'] ?? '',
            'unsaved_changes_message' => $headerConfig['dafacoin_unsaved_changes_message'] ?? '',
            'saved_popup_message' => $headerConfig['dafacoin_saved_popup_message'] ?? '',
            'not_saved_popup_message' => $headerConfig['dafacoin_not_saved_popup_message'] ?? '',
            'wallet_switch_all_label' => $headerConfig['wallet_switch_all_label'] ?? '',
            'cashier_mobile_link' => $headerConfig['default_mcashier_link'] ?? '',
        ];
    }

    /**
     * Returns array of data to be passed to the js scripts via attached variables
     *
     * @param array $headerConfig CMS Header configuration
     *
     * @return array Data passed to js scripts
     */
    public static function getAttachmentData($headerConfig)
    {
        return [
            'notificationPopupDisplayTime' => $headerConfig['dafacoin_notification_popup_display_time'] ?? 3,
            'balanceExclusion' => self::getBalanceExclusions($headerConfig),
            'enablePerWalletToggles' => $headerConfig['per_wallet_switch'] ?? true,
            'labels' => [
                'walletRowsHeaderDC' => $headerConfig['wallet_header_dc_label'] ?? '',
                'walletRowsHeaderSwitch' => $headerConfig['wallet_header_switch_label'] ?? '',
            ],
            'fetchErrors' => [
                'priority' => $headerConfig['dcoin_priority_fetch_error'] ?? 'Error Fetching Balance',
                'balance' => $headerConfig['balance_error_text_product'] ?? 'N/A',
            ]
        ];
    }

    /**
     * Returns list of wallet IDs to be excluded in the wallet list
     * Used by both DCoin and old wallet list dropdown
     *
     * @param array $headerConfig CMS Header Configuration
     *
     * @return array List of excluded wallets
     */
    public static function getBalanceExclusions($headerConfig)
    {
        $map = $headerConfig['excluded_balance_mapping'] ?? [];

        if (!empty($map)) {
            $map = explode(PHP_EOL, $map);
            $map = array_map('trim', $map);
        }

        return $map;
    }

    /**
     * Returns equivalent currency label depending on user's currency
     * Used by DCoin menu balance list
     *
     * @param array $headerConfig CMS Header Configuration
     *
     * @return string currency label
     */
    public static function getDCoinCurrencyLabel($headerConfig, $currency)
    {
        $currencyMap = [];
        $userCurrency = strtoupper($currency);
        if (isset($headerConfig['dafacoin_currency_map'])) {
            $map = array_map(function ($value) {
                $val = explode("|", trim($value));
                if ($val[0]) {
                    return [
                        'currency' => strtoupper($val[0]),
                        'label' => $val[1] ?? '',
                    ];
                }
            }, explode(PHP_EOL, $headerConfig['dafacoin_currency_map']));

            foreach ($map as $value) {
                $currencyMap[$value['currency']] = $value['label'];
            }
        }

        return isset($currencyMap[$userCurrency]) ? $currencyMap[$userCurrency] : 'DP';
    }
}
