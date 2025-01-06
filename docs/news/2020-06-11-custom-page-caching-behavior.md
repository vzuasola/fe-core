# Customizing Page Caching behavior

By default, page caching agnostically cache a page as long as it's `pre-login`, configured in  `routes.yml` and enabled in `settings.php`. Additionally, certain custom behavior like `site maintenance` or `localized contents` have separate caching. But, all are cached regardless if the served response is expected or not.

This can now be customized using the new response attribute, heres an example of a xhr controller that has an invalid response.
```php
public function getConfig($request, $response)
{
    $results['config'] = $this->get('config_fetcher')->getConfig('soda_casino_games.games_configuration');
    return $this->get('rest')->output(
        $response,
        ['config' => $results['config']]
    );
}
```

Originally, if the `$results['config']` is empty, we usually catch it by adding `??` then a fallback value, then it will be cached since it's a success response.

Here's another example using the new response cache attribute:
```php
use App\Middleware\Cache\ResponseCache;
...
public function getConfig($request, $response)
{
    $results['config'] = $this->get('config_fetcher')->getConfig('soda_casino_games.games_configuration');
    if (isset($results['config']) && !empty($results['config'])) {
        $response = $response->withAttribute(ResponseCache::CACHE_RESPONSE_INVALID, true);
    }

    return $this->get('rest')->output(
        $response,
        ['config' => $results['config']]
    );
}
```

By adding the attribute, you're telling the caching middleware that this response is not expected and should not be cached. This will also give a `Page-Cache: Miss-Invalid` header response in the browser, so it can be tracked.


## Trade-off
By preventing caching behavior to certain scenarios, this might give an error loop on the application (Un-cached routes sends real request to drupal, which can overload and make the errors worst).

Ideally, error handling should be thorough and should be addressed immediately.

## Additional handling
An additional handling is also introduced in the cache set part in `src/Middleware/Cache/ResponseCache.php`
```php
const CACHE_HEADER = 'Page-Cache';
...
const CACHE_INVALID = 'Miss-Invalid';
const CACHE_RESPONSE_INVALID = 'response_cache_invalid';
...
if ($response->getAttribute(self::CACHE_RESPONSE_INVALID, false) === true
    || $response->getStatusCode() !== 200
) {
    return $response->withHeader(self::CACHE_HEADER, self::CACHE_INVALID);
}
```
This block checks for the custom attribute and checks if the statusCode, by doing so, most error pages:

* 404 page
* 500 error page

Will not be cached. Ideally, these pages should have a single cache behavior (cache the supposed response with a different cacheKey logic) since caching this can have expose the site cache attacks.

Cache keys are created via the URL path, if error pages are cached, attackers can generate random 404 pages and create unecessary cache data in redis.

### Additional notes
Future enhancements on caching behavior of error pages should be done.