# Page Cache Middleware Override Alignment

Because of the contract change, you might need to align your middleware override classes.

**Before**

```php
namespace App\Product\Middleware;

use Interop\Container\ContainerInterface;

use App\Middleware\Cache\ResponseCache as Base;

class ResponseCache extends Base
{
    /**
     *
     */
    protected function getCacheKey()
    {
        ...
    }
}
```

**After**

```php
namespace App\Product\Middleware;

use Interop\Container\ContainerInterface;

use App\Middleware\Cache\ResponseCache as Base;

class ResponseCache extends Base
{
    /**
     *
     */
    protected function getCacheKey($request)
    {
        ...
    }
}
```