<?php

namespace App\Fetcher;

class Cache
{
    private $adapter;

    private $cache = [];
    private $allowPermanentCaching = false;
    private $cacheTimeout = 1800;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('apcu_cache_adapter'),
            $container->get('settings')['fetchers']['enable_permanent_caching']
        );
    }

    /**
     *
     */
    public function __construct($adapter, $allowPermanentCaching = false)
    {
        $this->adapter = $adapter;
        $this->allowPermanentCaching = $allowPermanentCaching;
    }

    /**
     *
     */
    public function all()
    {
        return $this->cache;
    }

    /**
     *
     */
    public function get($key, $options = [])
    {
        if (!empty($options)) {
            $hash = md5(json_encode($options));
            $key = "$key:$hash";
        }

        if (isset($this->cache[$key])) {
            return $this->cache[$key]['data'];
        }

        if ($this->allowPermanentCaching) {
            $key = urlencode("fetchers:$key");
            $item = $this->adapter->getItem($key);

            if ($item->isHit()) {
                $data = $item->get();

                return $data['response'];
            }
        }
    }

    /**
     *
     */
    public function set($key, $value, $options = [])
    {
        if (!empty($options)) {
            $hash = md5(json_encode($options));
            $key = "$key:$hash";
        }

        $this->cache[$key]['data'] = $value;

        if ($this->allowPermanentCaching) {
            $key = urlencode("fetchers:$key");
            $item = $this->adapter->getItem($key);

            if (!$item->isHit()) {
                $item->set([
                    'response' => $value,
                    'options' => $options,
                ]);

                $this->adapter->save($item, [
                    'expires' => $this->cacheTimeout,
                ]);
            }
        }
    }
}
