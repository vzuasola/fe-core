<?php

namespace App\Extensions\Token;

use App\Middleware\Cache\ResponseCache;
use App\Plugins\Token\TokenExtensionInterface;
use App\Utils\LazyService;

/**
 *
 */
class DomainTokens implements TokenExtensionInterface
{
    const DOMAIN_CLASS = 'App\Token\DomainPlaceholders';

    /**
     * Set container
     */
    public function setContainer($container)
    {
        $this->domains = $container->get('domain_fetcher');
        $this->request = $container->get('router_request');
    }

    /**
     * {@inheritdoc}
     */
    public function process(&$tokens)
    {
        // hacking you for now because I really can find a permanent solution for
        // you, my head hurts
        if ($this->request->getAttribute(ResponseCache::CACHE_HEADER) == ResponseCache::CACHE_HIT) {
            return;
        }

        try {
            $list = $this->domains->getPlaceholders();

            foreach ($list as $key => $value) {
                if ($key) {
                    $tokens["domain:$key"] = self::DOMAIN_CLASS;
                }
            }
        } catch (\Exception $e) {
            // do nothing
        }
    }
}
