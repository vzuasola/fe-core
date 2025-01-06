# How to Define 404 on a component based application

Defining a 404 page is differents for application using components

**Handlers.php**

```php
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $controller = $c['resolver']['App\MobileEntry\Controller\ExceptionController'];
        return $controller->exceptionNotFound($request, $response);
    };
};
```

<br>

**Components.yml**

```yaml
components:
  access_denied:
    class: App\MobileEntry\Component\Main\AccessDenied\AccessDeniedComponent
```

<br>

You need to define this override on your controller to swap out the 404 page with
a different component

**Controllers.php**

```php
namespace App\Product\Controller;

use App\Async\Async;
use App\BaseController;

class ExceptionController extends BaseController
{
    /**
     *
     */
    public function exceptionNotFound($request, $response)
    {
        $data['title'] = '404';

        return $this->widgets->render($response, '@site/page.html.twig', $data, [
            'components_override' => [
                'main' => 'access_denied',
            ],
        ]);
    }
}
```
