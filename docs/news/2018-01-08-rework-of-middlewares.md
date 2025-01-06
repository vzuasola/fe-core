# Rework of Middlewares

Currently, middleware can be registered using `app/middleware.php`. The new
implementation will move this configuration to `app/config/middleware.yml` in
favor of YAML based configuration.

The new middleware will also allow sites to override core middleware by 
reregistering it as a site level middleware.

Comparison of old and new middlewares

```php
// Site specific middleware

require APP_ROOT . '/core/app/middleware.php';

// Legacy Session Handler
$app->add(new \App\Middleware\LegacySessionParams($container), App\Slim::EARLY);

// Override Page Cache Middleware
$app->add(new \App\Demo\Middleware\PageCache($container), App\Slim::EARLY);
```

And this is for the new middleware implementation

```yaml
# Provide a key value pair of middlewares
#
# The key serves as a unique identifier to identify a middleware

middlewares:
  # Request middlewares are executed before the controllers
  request:
    page_cache: App\Middleware\Cache\ResponseCache
    session: App\Middleware\Request\Session
    languages: App\Middleware\Request\Languages
    bootstrap: App\Middleware\Request\Bootstrap
    sso: App\Middleware\Request\SSO
    # legacy_auth: App\Middleware\Request\LegacyAuthentication

  # Response middlewares are executed after the controllers
  response:
    tracking: App\Middleware\Response\Tracking
    script_provider: App\Middleware\Response\ScriptProvider
    game_provider: App\Middleware\Response\GameProvider
    token: App\Middleware\Response\Token
    attachments: App\Middleware\Response\Attachments
    page_cache: App\Middleware\Cache\ResponseCache

  # Cache are response middlewares that are executed on a cached request
  cache:
    tracking: App\Middleware\Cache\LazyTracking
    lazy_replacement: App\Middleware\Cache\LazyReplacement
```

The new middleware has new key concepts:

* `Request Middlewares`
  * They are defined under request and must implement `RequestMiddlewareInterface`
  * They are executed before the controllers and are used to initiate a request or break the response chain

```php
namespace App\Middleware\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Plugins\Middleware\RequestMiddlewareInterface;

class MyRequestMiddleware implements RequestMiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function boot(RequestInterface &$request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response)
    {
    }
}
```

* `Response Middlewares`
  * They are defined under request and must implement `ResponseMiddlewareInterface`
  * They are executed after the controllers and are used to modify the response of the application

```php
namespace App\Middleware\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Plugins\Middleware\ResponseMiddlewareInterface;

class MyResponseMiddleware implements ResponseMiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
    }
}
```

* `Cache Middlewares`
  * They are simply response middlewares that get executed after a Cach HIT only

# Advanced Features

### Execute Before or After

You can also define a site specific middleware that executes before or after
a middleware

```yaml
middlewares:
  request:

    super_cache:
      class: App\Middleware\Request\SuperCache
      execute_before: page_cache

    ultra_cache:
      class: App\Middleware\Request\UltraCache
      execute_after: page_cache
```

### Boot

Request middlewares have an ability to boot. This allows setting of request
parameters very early for the request.

A use case for this is to tell page cache not to cache something based on a dynamic
condition.

```php
namespace App\Middleware\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use App\Plugins\Middleware\RequestMiddlewareInterface;

class MyRequestMiddleware implements RequestMiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function boot(RequestInterface &$request)
    {
        $params = $request->getQueryParams();

        if (isset($params['something'])) {
            $request = $request->withAttribute(ResponseCache::CACHE_SKIP, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response)
    {
    }
}
```

# Migration Guide

It is recommended that you migrate to the new format as soon as you can.

All you need to do is to blank out your `middleware.php` then migrate everything
to `middleware.yml`.

Example is the legacy authentication that is used most of the time.

```yaml
middlewares:
  request:
    legacy_auth: App\Middleware\Request\LegacyAuthentication
```
