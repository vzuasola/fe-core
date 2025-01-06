<?php

namespace App\Translations;

/**
 * Class for mapping currency values
 */
class Currency
{
    const CODE_MAP = [
        'RMB' => '人民币',
    ];

    public static function getTranslation($code)
    {
        if (array_key_exists($code, self::CODE_MAP)) {
            return self::CODE_MAP[$code];
        }

        return null;
    }
}
