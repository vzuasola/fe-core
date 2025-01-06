# Framework General Best Practice Guide

Defines the best practices and implementation for common features

<br>

### Table of Contents

* [Components Best Practices](#components-best-practices)
* [Coding Styles](#coding-styles)
* [Controllers](#controllers)
* [Twig](#twig)
* [Using Fetchers](#using-fetchers)

<br>

## Components Best Practices

See here [**Components Widget Best Practices**](components/component-widget.md#best-practices)

<br>

## PHP Coding Styles

Always comply if possible with  [PSR-2 Coding Standards](https://www.php-fig.org/psr/psr-2/)
The rules below are additional to the PSR standard.

* Always use 4 spaces indentations

```php
class Example
{
    const VALUE = 'something';

    /**
     * Some docblock
     * 
     * @return boolean
     */
    public function getSomething()
    {
        return true;
    }
}
```

* Always use camel case for variables

```php
$myVariables = 'something';

$this->someDependency->doSomething();
```

* Never use the array keyword, use the shorthand for arrays

```php
$var = [];

$data = [
    'some' => 'value',
];
```

* Use double quotes for concatentating variables

Do's

```php
$name = "myData[$var]";
```

Don'ts

```php
$name = 'myData[' . $var . ']';
```

* Data arrays should have underscore case keys

Example of data arrays are those you pass to twig templates

```php
$data['my_name'] = 'Drew';
$data['username'] = 'leandrew';
$data['some_desc'] = 'Description';
```

* Methods should have docblocks

Use proper docblock format, see spacing and format below

```php
/**
 * Gets a data
 * 
 * @param string $key The key value
 * @param string $value The value to set
 *
 * @return array
 */
public function getData($key, $value)
{
}
```

Short docblock is acceptable for non important methods

```php
/**
 * Gets a not so important data
 */
public function someData($key)
{
}
```

* Don't overdefine variable names like this example

Don't do this

```php
private function getUser($userDomains)
{
    $userValues = $this->doSomething($userDomains);
    $userUrl = $userValues['url'];

    return $userUrl ? $userUrl : 'http://url.com';
}
```

Instead do this

```php
private function getUser($domains)
{
    $values = $this->doSomething($domains);
    $url = $values ['url'];

    return $url ? $url : 'http://url.com';
}
```

<br>

## Controllers

* Group controllers according to responsibilty

See route example below which uses only 1 controller

```yaml
routes:
    /promotions:
        method: GET
        action: App\Controller\PromotionController:overview

    /api/promotions/featured:
        method: GET
        action: App\Controller\PromotionController:getFeatured

    /api/promotions/product/{product}:
        method: GET
        action: App\Controller\PromotionController:getByProduct
```

* Controllers should contain little to no business logic

```php
namespace App\Controller;

class HomeController extends BaseController
{
    use BaseSectionTrait;

    /**
     *
     */
    public function view($request, $response)
    {
        $data = $this->getBaseData();

        $data['title'] = 'Home';

        $data = Async::resolve($data);

        return $this->view->render($response, '@site/home.html.twig', $data);
    }
}
```

<br>

## Twig

* Naming convention for twig files should be *dash* separated

```
/templates/components/slider-banner.html.twig
```

* Twig variables should be printed with *spaces between braces*

```twig
<p>{{ title }}</p>
```

* Twig filters should have *no space* between them

```twig
<p>{{ title|raw }}</p>
```

* Twig variables should be *underscore case*

```php
$data['title'] = [];
$data['my_component_desc'] = [];
$data['my_sidebar_component_desc'] = [];
```

```twig
<div class="container">
    <p>{{ title }}</p>
    <p>{{ my_component_desc }}</p>
    <p>{{ my_sidebar_component_desc }}</p>
</div>
```

* Always use **url** method for links and **asset** method for asset links

```twig
<a class="link" href="{{ url('/some/inner-link') }}">Link</a>

<img src="{{ asset('my-image.png') }}" alt="Banner">
```

* When including an asset in twig, always put an **ignore missing** flag. Consecutively
always use the generated optimized asset on the web folder

```twig
{% include '@app/web/images/svg/my-image.svg' ignore missing %}
```

<br>

## Using Fetchers

* Always put a try catch on a fetcher, and assign a default value on the initial fetch

```php
try {
    $configs = $this->configs->getConfig('webcomposer_config.login_configuration');
} catch (\Exception $e) {
    $configs = [];
}
```

* Assign values to a seperate variable and supply default value after the catch

```php
try {
    $configs = $this->configs->getConfig('webcomposer_config.login_configuration');
} catch (\Exception $e) {
    $configs = [];
}

$data['login_bottom_label'] = $configs['login_bottom_label'] ?? 'Login';
$data['username_placeholder'] = $configs['username_placeholder'] ?? 'Username';
$data['password_placeholder'] = $configs['password_placeholder'] ?? 'Password';
$data['lightbox_blurb'] = $configs['lightbox_blurb'] ?? 'Not yet a Dafabet member ?';
```
