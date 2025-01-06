<?php

namespace App\Cache\Adapter;

use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * Custom cache adapter that validates using a request hash and a signature
 * stored on Redis
 */
class RedisSignatureAdapter implements AdapterInterface
{
    private $cache;
    private $redis;
    private $timeout;

    /**
     *
     */
    public function __construct(AdapterInterface $cache, $client, $product, $timeout)
    {
        $this->cache = $cache;
        $this->timeout = $timeout;
        $this->cacheKey = "cache:signature:drupal:$product";
        $this->redis = $client;
        $this->signature = $this->getSignature();
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key, $options = [])
    {
        $item = $this->cache->getItem($key);

        if ($item->isHit()) {
            $data = $item->get();

            if (isset($data['attributes']['signature'])) {
                if (empty($this->signature)) {
                    $item = $this->invalidate($item);
                }

                if ($this->signature !== $data['attributes']['signature']) {
                    $item = $this->invalidate($item);
                }
            } elseif (isset($this->signature)) {
                $item = $this->invalidate($item);
            }
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        $items = $this->cache->getItems($keys);

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        return $this->cache->hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->cache->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        return $this->cache->deleteItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        return $this->cache->deleteItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item, $options = [])
    {
        if ($this->signature) {
            $data = $item->get();

            $data['attributes']['signature'] = $this->signature;

            $item->set($data);

            if ($this->timeout) {
                $item->expiresAfter($this->timeout);
            }
        } else {
            if (isset($options['expires'])) {
                $item->expiresAfter($options['expires']);
            }
        }

        return $this->cache->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->cache->saveDeferred($item);
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        return $this->cache->commit();
    }

    /**
     * Non Interface Related Methods
     *
     */

    /**
     * Invalidates a cache
     */
    private function invalidate($item)
    {
        $factory = \Closure::bind(
            function () {
                $this->isHit = false;

                return $this;
            },
            $item,
            CacheItem::class
        );

        return $factory();
    }

    /**
     * Gets the cache signature
     */
    protected function getSignature()
    {
        try {
            $signature = $this->redis->get($this->cacheKey);
        } catch (\Exception $e) {
            $signature = null;
        }

        return $signature;
    }
}
