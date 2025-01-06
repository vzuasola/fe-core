# Activating the Sitemap

The sitemap component allows you to have a sitemap page

> Before proceeding with these steps, ensure that the `webcomposer_sitemap` module
> has been enabled on Drupal

* Create a route for your sitemap page

```yaml
/sitemap:
    method: GET
    action: App\Demo\Controller\SitemapController:show
```

* Create a controller that utilizes the core `Sitemap` section

```php
namespace App\Demo\Controller;

use App\BaseController;
use App\Async\Async;

use Slim\Exception\NotFoundException;

class SitemapController extends BaseController
{
    use BaseSectionTrait;

    /**
     *
     */
    public function show($request, $response)
    {
        $data = $this->getBaseData();

        $data['sitemap'] = $this->getSection('sitemap');

        return $this->view->render($response, '@site/pages/sitemap.html.twig', $data);
    }
}
```

* Create a sitemap specific SASS stylesheet using the core Sitemap component

```scss
@import "base/common";
@import "../../core/core/assets/sass/components/sitemap";
```

* Create a sitemap specific Javascript using the core Sitemap component

```javascript
// CSS/SCSS
require("./../sass/sitemap.scss");

import './base';
import sitemap from "Base/sitemap";
sitemap();
```

* Add entry for the sitemap js in `webpack.entry.js`

```
sitemap: paths.src + "js/sitemap",
```

* Create a twig template that utilizes the core Sitemap template

```twig
{% extends '@site/page.html.twig' %}

{% block css %}
    <link href="{{ asset('css/sitemap.css') }}" rel="stylesheet">
{% endblock %}

{% block main %}
    <div class="container">
        {{ include('@base/components/sitemap/sitemap.html.twig') }}
    </div>
{% endblock main %}

{% block script %}
    <script src="{{ asset('js/sitemap.bundle.js') }}"></script>
{% endblock %}
```

## Filtering the Sitemap

To filter the sitemap, you can either `filter the sitemap via an option` or `extend the sitemap altogether`.

If you opt for the option, you can filter which nodes the sitemap returns by defining a filter function as an option.
The Sitemap section can receive the option with the index of `filter_node`.

```php
$nodeUtils = $this->get('node_utils');

$filter = function ($node) use ($nodeUtils) {
    if (isset($node['field_log_in_state'])) {
        return $nodeUtils->hasAccess($node['field_log_in_state']);
    }

    return true;
};

$data['sitemap'] = $this->getSection('sitemap', ['filter_node' => $filter]);
```

This will filter all the nodes and will only return nodes of the proper login state.

## Showing 404 on disabled Sitemap

The sitemap also has an option that allows you to specify custom callback when the
sitemap is disabled. The option key is `on_disable` and can be passed when you get
the sitemap section.

```php
// closure to invoke when sitemap is disabled
$onDisable = function () use ($request, $response) {
    throw new NotFoundException($request, $response);
};

$data['sitemap'] = $this->getSection('sitemap', [
    'on_disable' => $onDisable,
]);
```

Make sure to add the use statement

```php
use Slim\Exception\NotFoundException;

class MyClass {

}
```

## Available Sitemap Options

The following will be a list of available sitemap options

* `filter_node` A callback that will be passed to array_filter to filter the nodes
* `on_disable` A callback that will be invoked when the sitemap is disabled

## Extending the Sitemap

If you need to add entries for the sitemap, you can extend the sitemap section

We have two sitemap sections
* `SitemapBase` - Provides default data for the sitemap tree
* `Sitemap` - Provides the quicklinks, and other Dafabet specific common sitemap trees

If you need your own Sitemap, you can choose which Section to extend. In most cases, it is just the `SitemapBase`.

Lets say you are going to add a new tree on the sitemap.

```php
<?php

namespace App\Demo\Section;

use App\Drupal\Config;
use App\Plugins\Section\SectionInterface;

use Interop\Container\ContainerInterface;

/**
 * Sitemap section for Dafa products
 */
class MySitemap extends SitemapBase implements SectionInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        parent::setContainer($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getSection(array $options)
    {
        $data = parent::getSection($options);

        $myCustomTree = [
            'label' => 'My Custom Links',
            'path' => [
                [
                    'label' => 'Node One',
                    'path' => '/node/one',
                    'frequency' => 'daily',
                    'priority' => '0.7',
                ],
                [
                    'label' => 'Node Two',
                    'path' => '/node/two',
                    'frequency' => 'daily',
                    'priority' => '0.7',
                ],
            ],
        ];

        $data['links'][] = $myCustomTree;

        return $data;
    }
}
```

You just need to edit `$data['links']`, add a valid array to the existing links.

And you just need to override the `sections.yml`

```yaml
sections:
  sitemap: App\Demo\Section\MySitemap
```

> Override the sitemap section, the sitemap XML generator controller only refers
> to the current sitemap section

## Valid Sitemap Data

A valid sitemap array is the following:

```php
$item = [
    'label' => 'Node One',
    'path' => '/node/one',
    'frequency' => 'daily',
    'priority' => '0.7',
],
```

And if you want nested, just make the path as an array

```php
$item = [
    'label' => 'My Custom Links',
    'path' => [
        [
            'label' => 'Node One',
            'path' => '/node/one',
            'frequency' => 'daily',
            'priority' => '0.7',
        ],
        [
            'label' => 'Node Two',
            'path' => '/node/two',
            'frequency' => 'daily',
            'priority' => '0.7',
        ],
    ],
];
```

## Sitemap XML

The core includes a predefined sitemap.xml route, accessing `demo.dafabet.dev/sitemap.xml` should refer to your sitemap section
for generating a valid sitemap XML markup.

For more information regarding the Sitemap XML definitions, you can visit https://www.sitemaps.org/protocol.html
