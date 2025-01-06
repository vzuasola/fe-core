<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

/**
 * Token class for Domain placeholder tokens
 */
class DomainPlaceholders implements TokenInterface
{
    /**
     * The domains fetcher object
     *
     * @var object
     */
    private $domains;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->domains = $container->get('domain_fetcher');
    }

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        $key = str_replace('domain:', '', $options['key']);

        try {
            $placeholders = $this->domains->getPlaceholders();
        } catch (\Exception $e) {
            // do nothing
        }

        return $placeholders[$key] ?? null;
    }
}
