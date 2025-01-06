<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 * Exposes the localized language to be used as a token
 */
class LocalizationToken implements TokenInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->localization = $this->get('localization');
    }

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        if ($localization = $this->localization->getLocalLanguage()) {
            return $localization;
        }

        return '';
    }
}
