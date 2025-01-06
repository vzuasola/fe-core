<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use App\Middleware\Affiliates;
use App\Cookies\Cookies;

/**
 * Exposes the token that appends query string for legacy post login
 */
class TrackingCrefererToken implements TokenInterface
{
    const BTAG = 'btag';

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        return $this->processAffiliates(Cookies::get('affiliates'));
    }

    /**
     * Add the affiliate cookies and return the valid query parameters
     *
     * @return array
     */
    private function processAffiliates($affiliates)
    {
        $affiliates = is_array($affiliates) ? $affiliates[0]:$affiliates;
        if (($pos = strpos($affiliates, self::BTAG)) !== false) {
            // Just concat the hardcoded the creferer
            return 'creferer' . substr($affiliates, ($pos + 4));
        }

        return $affiliates;
    }
}
