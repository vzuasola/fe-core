# Caching using the built in Cache

This example shows how to use the cache component adapter (which is based on Symfony cache)
and is PSR-6 compliant.

```php
$cache = $this->get('system_cache');
$item = $cache->getItem('key.of.data');

if (!$item->isHit()) {
    $data = []; // some expensive data
    $item->set($data);
    $cache->save($item);
} else {
    $data = $item->get();
}
```

Cache adapters can be changed accordingly. Refer to `settings.php` for a list of
supported adapters and options.

For more information regarding, read about [PSR-6](http://www.php-fig.org/psr/psr-6)
