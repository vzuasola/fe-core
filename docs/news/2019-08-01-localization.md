# Localization

Localization feature for fe-core can now be consumed. This library is currently used for LATAM territories. The following are the supported localized language and currencies at the moment:

| GEOIP | Currency | Langcode |
|  - | - | - |
| MX | MXN | es |
| PE | PEN | es |
| CL | CLP | es |
| AR | ARS | es |

For enabling the library, in `settings.php` add this flag
```php
// Enable localization library
$settings['settings']['localization']['enable'] = true;
```

**Enabling the middleware**

Within the site level's `middleware.yml`.
```yaml
middlewares:
  request:
    page_cache: App\Games\Middleware\Cache\ResponseCache
    legacy_auth: App\Middleware\Request\LegacyAuthentication
    case_insensitive:
      class: App\Middleware\Request\CaseInsensitiveRoute
      execute_before: page_cache
    localization: App\Localization\Middleware\Request\Localization
```
 Adding this will add (if detected) a custom response header to ALL requests.
 ```yaml
 X-Localized-Content: es-MX
 ```

 **Enabling the token**

Within the site level's `tokens.yml`.
```yaml
tokens:
  product: App\Games\Token\ProductKeyword\Games
  product.keywords.arcade: App\Games\Token\ProductKeyword\Arcade
  localization.lang: App\Localization\Token\Localization
```
Adding this will enable the site to detect any token and expose the localized language (if detected) to the markup.

**NOTE**
```
Enabling the module will prepend the detected localized language to the cache key to be created. A typical cache key is a md5 version of the current route
md5(https://www.dafabet.com/es/games)

Once enabled, the cache key will look like this
md5(es-MX:https://www.dafabet.com/es/games)

In short, this will create an additional cache keys for each detected localized language (number of site language + localized language)
```

**Sample implementation**

Typical implementation of the localization library would look like:
```php
// This will detect if localization is applicable and will override the the fetched content
// setResponseHeader will create the custom response header that the response was localized
if ($localization = $this->get('localization')->setResponseHeader($response)->getLocalLanguage()) {
                $definition['localized_banners'] = $this->get('views_fetcher_async')
                    ->setLanguage($localization)
                    ->getViewById('webcomposer_slider_v2');
            }
```