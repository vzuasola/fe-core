<?php

namespace App\Player\Balance;

/**
 * Defines the included wallet types for each wallet
 */
class Definition
{
    // Wallets supported
    const WALLETS = [
        'casino' => 1,
        'casino_gold' => 2,
        'oneworks' => 4,
        'shared_wallet' => 5,
        'als' => 6,
        'fish_hunter' => 7,
        'opus_keno' => 9,
        'exchange' => 10,
        'esports' => 11,
        'ghana' => 12,
        'ptplus' => 15
    ];

    // Wallet types balances
    const WALLET_TYPES = [
        'realmoney' => '1',
        'bonus' => '2',
        'reserved' => '4',
        'nonwithdrawable' => '7'
    ];

    // Corresponding wallet types available for each wallet
    const WALLET_TYPE_MAPPING = [
        1 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['bonus']
        ],
        2 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['bonus']
        ],
        3 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['bonus']
        ],
        4 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['nonwithdrawable']
        ],
        5 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['bonus'],
            self::WALLET_TYPES['reserved']
        ],
        6 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['bonus'],
            self::WALLET_TYPES['nonwithdrawable']
        ],
        7 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['bonus'],
            self::WALLET_TYPES['nonwithdrawable']
        ],
        8 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['bonus']
        ],
        // Obsolete
        9 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['bonus']
        ],
        10 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['bonus']
        ],
        11 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['nonwithdrawable']
        ],
        // Obsolete
        12 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['bonus'],
            self::WALLET_TYPES['nonwithdrawable']
        ],
        13 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['nonwithdrawable']
        ],
        15 => [
            self::WALLET_TYPES['realmoney'],
            self::WALLET_TYPES['bonus']
        ]
    ];

    const FILTER_TYPES = [
        'blocked' => 'block',
        'unsupported_currency' => 'uc',
        'ignore' => 'ignore'
    ];

    const OVERRIDE_CURRENCY = [
        'GHS' => 'GHC',
        'mBC' => 'mBTC'
    ];

    const AGENTPLAYER_ALLOWED = [
        'shared_wallet' => 5,
        'als' => 6
    ];
}
