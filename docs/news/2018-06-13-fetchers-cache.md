# Enable Fetchers Cache

You can now enable fetcher caching which should significantly increase performance.

Fetcher cache is different from page caching:

* Always kicks-in and does not depend on player state
* Can be cleared via cache signature regeneration
* Only affects Drupal fetchers

Enable page cache by adding this on your **settings.php**

```php
$settings['settings']['fetchers']['enable_permanent_caching'] = true;
```

> Your PHP needs APCU to work, if you are using our docker stack, latest branch already contains
> APCU changes, you only need to reload the stack after taking a pull