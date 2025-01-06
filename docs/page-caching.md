# Activating Page Caching

The page caching middleware allows your site to reach blazing speeds of up less
than 100ms.

* To activate page cache, define the following on your site's `settings.php`

```php
$settings['settings']['page_cache']['enable'] = true;
$settings['settings']['page_cache']['default_timeout'] = 1000;
```

Page cache can be configured per route, by specifiying on your **route.yml**, per
route expiration can be defined there.

```yaml
 /:
        method: GET
        action: App\Product\Controller\PageController:home
        page_cache:
            enabled: true

    /games:
        method: GET
        action: App\Product\Controller\GameController:games
        page_cache:
            enabled: true
            expires: 300
```

> For complete details of options, see core's `app/settings.php`

# Altering Page Cache Behavior

Depending on the needs of your product, you may require special caching behaviors.
For this kind of use cases, you may opt to extend the Page caching middleware altogether
to modify it as per your liking.

> To override middlewares, you must already support the new middleware implementation
> as specified on the [Rework of Middleware Implementation](../docs/news/2018-01-08-rework-of-middlewares.md)
> documentation

Alter the page cache middleware by extending it.

The page cache middleware has methods declared as protected for the reason of
overriding it. You may choose which method to override. Like this example below,
overriding how cache keys are generated.

```php
namespace App\MyProduct\Middleware;

use Interop\Container\ContainerInterface;

use App\Middleware\Cache\ResponseCache as Base;

class ResponseCache extends Base
{
    /**
     * Public constructor
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->request = $container->get('request');
    }

    /**
     * Gets the cache key
     */
    protected function getCacheKey()
    {
        $uri = $this->request->getUri()->getBaseUrl();
        $path = $this->request->getUri()->getPath();

        return md5(trim($uri . $path, '/'));
    }
}
```

After overriding the middleware, you need to reregister it on your site `middleware.yml`

```yaml
middlewares:
  request:
    page_cache: App\Demo\Middleware\ResponseCache

  response:
    page_cache: App\Demo\Middleware\ResponseCache

```

# Advance Page Cache Behaviors

See **settings.php** on the **page_cache** index to know more about page cache
specific behaviors
