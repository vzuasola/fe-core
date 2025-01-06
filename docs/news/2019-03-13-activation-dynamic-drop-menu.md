# Enabling Dynamic Dropdown Menu

To enable dynamic dropdown support you need to configure Drupal and Front End

<br>

# Table of Contents

* [Drupal Configuration](#drupal-configuration)
* [Front End Configuration](#front-end-configuration)
* [Advanced Widget Options - Javascript Callbacks](#advanced-widget-options)
* [Recommended Setup for Dafa Sites](#recommended-setup-for-dafa-sites)

<br>

# Drupal Configuration

### Add the Menu Attribute

* Add the correct menu attributes

Go to `/admin/config/menu_link_attributes/config` and append the following snippet
on the configuration

```yaml
  dropDownMenu:
    label: 'Drop Down Menu URL'
    description: 'Specify the URL that the drop down menu will call'
```

* Each respective menu should now have  **Drop Down Menu** attribute section. You
specify here the URL for the AJAX endpoint

### Enable the Webcomposer Dropdown Menu Plugin Module

Make sure to enable the Dropdown Menu module and have the plugins created already

> See this [guide](https://gitlab.ph.esl-asia.com/CMS/drupal-data/blob/working/docs/webcomposer-menu-widget-plugin.md) for Drupal

## Site Specific Setup

Make sure the have the latest fe-core and have yarn recompiled.

### Add JS and CSS

Include the dropdown menu assets.

Import the following on your `base.js` or equivalent component

```js
import "Base/mega-menu/mega-menu-manager";
```

Import the following on your `_common.scss` or equivalent component

```scss
@import "../../../core/core/assets/sass/components/mega-menu/mega-menu";
```

<br>

# Front End Configuration

### Define the Widgets Classes

* Create widgets in front end that match widget defined on Drupal

Suppose you have 2 widgets on Drupal having the IDs
* promotion
* games

You need to define the widgets on site specific `widgets.yml`

> Note that the ID you define here should match that of Drupal

```yaml
menu:
  promotion: App\MyProduct\Widget\Menu\Promotion
  games: App\MyProduct\Widget\Menu\Games
```

Define the widgets as an instance of `MenuWidgetInterface`

```php
namespace App\MyProduct\Widget\Menu;

use App\Plugins\Widget\MenuWidgetInterface;

/**
 *
 */
class Promotion implements MenuWidgetInterface
{
    /**
     * {@inheritdoc}
     */
    public function alterData($data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return '@site/widgets/menu/promotion.html.twig';
    }
}
```

To define a menu widget, you need to define two methods.

The **alterData** method accepts 1 argument of `$data` which contains the
configuration you defined on Drupal for your plugin. (Remember that a menu widget
plugin is also a config form, if set up correctly, all those form values should
appear here). Since this is an alter, do any data manipulation as you please, this
variable will be passed to the twig template.

The **getTemplate** should return a string that points to the twig template that
this widget will refer to.

### Define the Template

Templates should reside on **templates/widgets/menu**

An example template

```twig
<div class="col-4">
    <section>
        <h3 class="mega-menu-title">{{ title }}</h3>
        <p>{{ markup }}</p>
    </section>
</div>
```

This contains the markup for the widget class I defined. 1 widget class will
equate to 1 widget template twig file.

### Expose the Controller

By default the endpoint for exposing the menu widget endpoint is disabled

Enable it by defining this on your site's `routes.yml`

```yaml
/api/menu/widgets:
    method: GET
    action: App\Controller\MenuWidgetController:widgets
```

### Add the Dropdown Menu Section

Add this section to your base section trait

```php
namespace App\Demo\Controller;

trait BaseSectionTrait
{
    /**
     *
     */
    public function getBaseData()
    {
        ...
        $data['dropdown_menu'] = $this->getSection('dropdown_menu_async');
        ...

        return $data;
    }
}
```

### Configure the Product Menu

Test the expose endpoint by configuring the product menu.

Since additional attributes were added, configure one menu, and put the following:

* **Dropdown Menu URL** `/api/menu/widgets`

Make sure that the widget in Drupal is not disabled. You should see your widgets
appear on the front end.

<br>

# Advanced Widget Options

### Binding a Custom Javascript to a Widget

Some widget may require that you have a slider, or some fancy JS stuff. The widget
system supports adding of custom callbacks that will be executed after loading a
specific widget.

First is you need to create a new JS file entrypont for your JS that will be used
as a callback

**webpack.entry.js**

```javascript
module.exports = {
    paths,
    entry: {
        ...
        "widgets/about": paths.src + "js/widgets/about",
        ...
    }
};
```

> It is recommended that widget scripts should be organized under a **widgets** folder

You JS file will always return a default module, it will have an argument of **container**
which is the current container containing your individual widget

**about.js**

```javascript
import * as utility from "Base/utility";

export default function AboutWidget(container) {
    // do anything you want to the container element
}
```

You also need to define a **getScript** method to return a script filename, which is usually
defined using the asset generator

```php
namespace App\MyProduct\Widget\Menu;

use App\Plugins\Widget\MenuWidgetInterface;

/**
 *
 */
class MyWidget implements MenuWidgetInterface
{
    private $asset;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('asset')
        );
    }

    /**
     *
     */
    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    /**
     *
     */
    public function alterData($data)
    {
        return $data;
    }

    /**
     *
     */
    public function getScript()
    {
        // asset call begins with slash since this is a nested script in a folder
        return $this->asset->generateAssetUri('/widgets/about.js');
    }

    /**
     *
     */
    public function getTemplate()
    {
        return '@site/widgets/menu/games.html.twig';
    }
}
```

<br>

# Recommended Setup for Dafa Sites

Since the promotion widget is a shared widget, you need to append the promotions script on
your webpack entry file


**webpack.entry.js**

```javascript
module.exports = {
    paths,
    entry: {
        ...
        "widgets/promotions": paths.baseSrc + "js/widgets/promotions",
        ...
    }
};
```
