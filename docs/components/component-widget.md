# Component Widgets

Component widgets are sets of functionality with scope limited to that component only.

A component is composed of the following:
* A PHP class to define the data
* A twig template file for markup
* A typescript class to control JavaScript behaviors

<br>

# Table of Contents

* [Best Practices](#best-practices)
* [Component vs Modules](#component-vs-module)
* [Defining a Component Widget](#defining-a-component-widget)
* [Defining a Component Module](#defining-a-component-module)
* [Rendering Components](#rendering-components)
* [Structuring your Components](#structuring-your-components)
* [Route Specific Components](#route-specific-components-and-component-aliases)
* [Nested Components](#nested-components)
* [Reloading Components using Javascript](#reloading-components-using-javascript)
* [Component Javascript Attachments](#component-javascript-attachments)
* [Component Controllers](#component-controllers)
* [Module Controllers](#module-controllers)
* [Dependency Injection](#dependency-injection)
* [Async Components](#async-components)
* [Script Includes](#script-includes)

<br>

# Best Practices

If you are just starting this guide, you can skip this section for now.

### Components under the Main component should be under Main namespace

* src/Components/Main/About/AboutComponent.php
* src/Components/Main/Lobby/LobbyComponent.php

### Components can have it's own template and script folder

Split templates to a more modular approach

* src/Components/Sample/template.html.twig
* src/Components/Sample/templates/header.html.twig
* src/Components/Sample/templates/footer.html.twig

Split script components to a more modular approach

* src/Components/Sample/script.ts
* src/Components/Sample/scripts/library.ts
* src/Components/Sample/scripts/library.ts

### One Typescript file per Javascript Module

* Never use plain JS file, only Typescript (TS) files
* No Typescript class having many responsibility
* One Typescript class should only do one job

### Typescript files should always be classes

Class name should always be CamelCase (Capital first letter)

```typescript
/**
 *
 */
export class MyLibrary {
}
```

### Always provide syntactically modular typescript methods

See method modularity below

```typescript
import {ComponentInterface} from '@plugins/ComponentWidget/asset/component';

/**
 *
 */
export class SampleComponent implements ComponentInterface {
    onLoad(element: HTMLElement, attachments: {}) {
        this.bindLoginButton();
        this.bindMenuButton();
        this.enableSocketListener();
        this.enableAnnouncementListener();
    }

    onReload(element: HTMLElement, attachments: {}) {
        this.bindLoginButton();
        this.bindMenuButton();
        this.enableSocketListener();
        this.enableAnnouncementListener();
    }

    private bindLoginButton() {
        // logic here
    }

    private bindMenuButton() {
        // logic here
    }

    private enableSocketListener() {
        // logic here
    }

    private enableAnnouncementListener() {
        // logic here
    }
}
```

### When passing non-iterable data, always define what is needed rather than pass everything all together

See example below

```php
namespace App\Product\Component\Sample;

use App\Plugins\ComponentWidget\ComponentWidgetInterface;

class SampleComponent implements ComponentWidgetInterface
{
    private $someDatasource;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('someDatasource')
        );
    }

    /**
     * Public constructor
     */
    public function __construct($someDatasource)
    {
        $this->someDatasource = $someDatasource;
    }


    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return '@component/Sample/template.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];
        
        $configs = $this->someDatasource->getConfigs();
        
        // assign values to the data array then provide default values
        // don't pass all the data like doing $data['config'] = $this->someDatasource->getConfigs()
        // do this instead
        $data['some_value_one'] = $configs['some_value'] ?? 'Default Some Value One';
        $data['some_value_two'] = $configs['some_value_two'] ?? 'Default Some Value Two';

        return $data;
    }
}
```

### Always provide modular methods for component classes, use traits when applicable

See method modularity below

```php
namespace App\Product\Component\Sample;

use App\Plugins\ComponentWidget\ComponentWidgetInterface;

class SampleComponent implements ComponentWidgetInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return '@component/Sample/template.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];

        $data['products'] = $this->getProducts();
        $data['tiles'] = $this->getTiles();
        $data['lists'] = $this->getLists();

        return $data;
    }

    private function getProducts()
    {
        // logic here
    }

    private function getTiles()
    {
        // logic here
    }

    private function getLists()
    {
        // logic here
    }
}
```

<br>

# Component vs Module

### Component
* Only called when a component is rendered
* Reacts to component reload via onReload
* Contains a template
* Can be nested
* Can have controllers, attachments and async classes

### Module
* Always called on page load
* Has no onReload method
* No template
* Cannot be nested
* Can have controllers, attachments and async classes

<br>

# Defining a Component Widget

### Create the component structure

A component should be defined under **/src/Component/Sample** where **Sample** is the
namespace of your component.

Create the 3 files as follows:

* `/src/Component/Sample/SampleComponent.php`
* `/src/Component/Sample/template.html.twig`
* `/src/Component/Sample/script.ts`

#### The PHP Class component

The PHP class component requires 2 methods

* **GetTemplate** Returns the path to your template, this should point to your template all the time
* **GetData** Returns the data that will be passed to the twig template

`/src/Component/Sample/SampleComponent.php`

```php
namespace App\Product\Component\Sample;

use App\Plugins\ComponentWidget\ComponentWidgetInterface;

class SampleComponent implements ComponentWidgetInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return '@component/Sample/template.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];

        $data['name'] = 'Drew'

        return $data;
    }
}
```

#### The Twig Template

The plain twig template that defines the markup of your component

`/src/Component/Sample/template.html.twig`

```twig
<div class="sample">
    This is my component with name {{ name }}
</div>
```

#### The Typescript Class

The typescript class requires 2 methods

* **onLoad** Executes after the DOM is loaded
* **onReload** Executes after a non reload navigation has occured, giving the chance for the component to reload

Both methods receives 2 arguments, the **element** object and **attachments** object.

The element object is already the DOM that contains your template, you do not need to
do a document query anymore.

The attachments is for transfering data from PHP to Javascript and will be discussed
on a later part.

`/src/Component/Sample/script.ts`

```typescript
import {ComponentInterface} from '@plugins/ComponentWidget/asset/component';

/**
 *
 */
export class SampleComponent implements ComponentInterface {
    onLoad(element: HTMLElement, attachments: {}) {
    }

    onReload(element: HTMLElement, attachments: {}) {
    }
}
```
<br>

### Register your components

Add the component entry on **app/config/components.yml** where your ID is the machine name of your component widget.

```yaml
components:
  sample:
    class: App\Web\Component\Sample\SampleComponent
```

You also need to add an entry on your **assets/script/app.ts**, import and register
your component using the same ID

```typescript
import {ComponentManager} from '@plugins/ComponentWidget/asset/component';

import {SampleComponent} from '@app/src/Component/Sample/script';

ComponentManager.setComponents({
    'sample': new SampleComponent(),
});

ComponentManager.init();
```

<br>

# Defining a Component Module

### Create the component structure

A component should be defined under **/src/Module/Sample** where **Sample** is the
namespace of your module.

Create the 2 files as follows:

* `/src/Module/Sample/SampleModule.php`
* `/src/Module/Sample/script.ts`

#### The PHP Class component

`/src/Module/Sample/SampleModule.php`

```php
namespace App\Product\Module\Sample;

use App\Plugins\ComponentWidget\ComponentModuleInterface;

class SampleModule implements ComponentModuleInterface
{
}
```

#### The Typescript Class

The typescript class requires 2 methods

* **onLoad** Executes after the DOM is loaded and receives an **attachments** argument.

`/src/Module/Sample/script.ts`

```typescript
import {ModuleInterface} from '@plugins/ComponentWidget/asset/component';

/**
 *
 */
export class SampleModule implements ModuleInterface {
    onLoad(attachments: {}) {
    }
}
```
<br>

### Register your module

Add the component entry on **app/config/components.yml** where your ID is the machine name of your component module.

```yaml
modules:
  sample:
    class: App\Web\Module\Sample\SampleModule
```

You also need to add an entry on your **assets/script/app.ts**, import and register
your component using the same ID

```typescript
import {ComponentManager} from '@plugins/ComponentWidget/asset/component';

import {SampleModule} from '@app/src/Module/Sample/script';

ComponentManager.setModules({
    'sample': new SampleModule(),
});

ComponentManager.init();
```

<br>

# Rendering Components

Suppose we have this route

`app/config/routes.yml`

```yaml
routes:
    /sample:
        method: GET
        action: App\Product\Controller\SampleController:sample
```

With components in play, your controllers should only do no to minimal tasks already.
Controllers should just point to a twig template, at most it requires the page title.

`src/Controllers/SampleController`

```php
namespace App\Product\Controller;

use App\BaseController;

class SampleController extends BaseController
{
    /**
     *
     */
    public function sample($request, $response)
    {
        $data['title'] = 'Home';

        return $this->view->render($response, '@site/sample.html.twig', $data);
    }
}
```

Your page twig will only need to specify which widgets to render.

To render a widget, you just need to call the `{{ widget(component) }}` function
on your twig, passing the widget ID on the argument

`templates/sample.html.twig`

```twig
{% extends '@site/base.html.twig' %}

{% block css %}
<link href="{{ asset('app.css') }}" rel="stylesheet">
{% endblock %}

{% block header %}
    {{ widget('header') }}
{% endblock header %}

{% block body %}
     {{ widget('sample') }}
{% endblock body %}

{% block footer %}
    {{ widget('footer') }}
{% endblock footer %}

{% block script %}
<script src="{{ asset('app.js') }}"></script>
{% endblock %}
```

<br>

# Structuring your Components

Suppose you have the following site structure, each page will have the following
components under it

* **Home**
    * Header Component
    * Home Lobby Component
    * Footer Component
* **Gallery**
    * Header Component
    * Gallery Lobby Component
    * Footer Component
* **Contact**
    * Header Component
    * Contact Form Component
    * Footer Component

The recommended components are as follows

* **src/Component/Header**
* **src/Component/Footer**
* **src/Component/Main/Home**
* **src/Component/Main/Gallery**
* **src/Component/Main/Contact**

Or if you put that on the config, it will look like this

```yaml
components:
  header:
    class: App\Product\Component\Header\HeaderComponent
  footer:
    class: App\Product\Component\Footer\FooterComponent
  home:
    class: App\Product\Component\Main\Home\HomeComponent
  gallery:
    class: App\Product\Component\Main\Gallery\GalleryComponent
  contact:
    class: App\Product\Component\Main\Contact\ContactComponent
```

### Why are some components under the _main_ namespace ?

It is because that Home, Gallery and Contact utilizes the same region, which
is the center of the page. They pertain to the page's main section.

If you structure this site as templates, they will all use the same twig template
which is like this

```twig
{% block header %}
    {{ widget('header') }}
{% endblock header %}

{% block body %}
     {{ widget('main') }}
{% endblock body %}

{% block footer %}
    {{ widget('footer') }}
{% endblock footer %}
```

I bet you are wondering about that main component, how will it render the gallery
and contact component if those components are not even called. This will be explain
on the _route specific components_ section.

> **(TIP)** For the example above, it is a better practice to keep all components
> that refers to a specific alias in its own namespace, which in the case of this
> example is the *main* namespace

<br>

# Route Specific Components and Component Aliases

You can alias your components under an ID and let it use different components
across multiple routes.

If you read the section _structuring your components_ above, we need to have
different substitutes form the _main_ component

To define a different component per route using an alias, refer to the
**app/config/routes.yml**

```yaml
routes:
    /:
        method: GET
        action: App\Sample\Controller\SampleController:view
        components:
          main: home

    /gallery:
        method: GET
        action: App\Sample\Controller\GalleryController:view
        components:
          main: gallery

    /contact:
        method: GET
        action: App\Sample\Controller\ContactController:view
        components:
          main: contact
```

Adding a *component* flag allows you to choose the replacement for an alias for
a specific route.

> **(TIP)** For the example above, it is a better practice to keep all components
> that refers to a specific alias in its own namespace, which in the case of this
> example is the *main* namespace

<br>

# Nested Components

Sometimes you need to nest components. A nested component will have the following structure:

Parent component

* **src/Component/MyParent/MyParentComponent.php**
* **src/Component/MyParent/script.ts**
* **src/Component/MyParent/template.html.twig**

Child component

* **src/Component/MyParent/Child/ChildComponent.php**
* **src/Component/MyParent/Child/script.ts**
* **src/Component/MyParent/Child/template.html.twig**

You can have as many child component as you want. The recommended structure is
that child components should be under the parent's component namespace.

The only difference is that on the parent component's twig, it will call the
child component

```twig
<div class="parent-container">
  {{ widget('child') }}
</div>
```

For child components, you must specify it's parent on **app/config/components.yml**

```yaml
components:
  my_parent:
    class: App\Product\Component\MyParent\MyParentComponent

  child:
    class: App\Product\Component\MyParent\Child\ChildComponent
    parent: my_parent
```

<br>

# Reloading Components using Javascript

Sometimes you need to reload components as a result of a client action (reloading
the header upon login).

```typescript
import * as utility from '@core/assets/js/components/utility';
import {ComponentManager, ComponentInterface} from '@plugins/ComponentWidget/asset/component';

/**
 *
 */
export class SampleComponent implements ComponentInterface {
    onLoad(element: HTMLElement, attachments: {}) {
        this.bindLogin(element);
    }

    onReload(element: HTMLElement, attachments: {}) {
    }

    private bindLogin(element: HTMLElement) {
      // on click of the button using event delegation
      utility.delegate(element, 'btn.auth', 'click', (event) => {

        // do login code here

        ComponentManager.refreshComponents(['main', 'header']);
      });
    }
}
```

Or if you want to reload all (only use for special cases only)

```javascript
ComponentManager.refreshComponents(['*']);
```

<br>

# Component Javascript Attachments

Javascript attachments are data which gets passed from the server side to the
client side.

It is the second argument of you onLoad and onReload methods.

```typescript
export class SampleComponent implements ComponentInterface {
    onLoad(element: HTMLElement, attachments: {}) {
    }

    onReload(element: HTMLElement, attachments: {}) {
    }
}
```

To pass data to the Javascript Attachments, create an Attachment
class (your main component class appended with **Scripts** keyword).

```php
namespace App\Product\Component\Sample;

use App\Plugins\ComponentWidget\ComponentAttachmentInterface;

/**
 *
 */
class SampleComponentScripts implements ComponentAttachmentInterface
{
    /**
     * @{inheritdoc}
     */
    public function getAttachments()
    {
        return [
            'username' => 'leandrew',
        ];
    }
}
```

> Attachments deprecates app.settings in favor of a more strict scope in
> passing of attachments

<br>

# Component Controllers

Components can have their own controllers. In may be an AJAX endpoint or any
API endpoint that is scoped within that component.

To create a component controller, just create a plain controller 
class (your main component class appended with **Controller** keyword).

```php
namespace App\Web\Component\Header;

/**
 *
 */
class HeaderComponentController
{
    /**
     * @var App\Rest\Resource
     */
    private $rest;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('rest')
        );
    }

    /**
     * Public constructor
     */
    public function __construct($rest)
    {
        $this->rest = $rest;
    }

    /**
     *
     */
    public function process($request, $response)
    {
        $data = [
            'token' => '12345',
        ];

        return $this->rest->output($response, $data);
    }
}
```

And in your typescript file, you only need to call `Router.generateRoute(name, method)`

The **GenerateRoute** accepts 2 arguments:
* **name** The machine name of the component
* **method** The method name of your controller

```typescript
import {ComponentInterface} from '@plugins/ComponentWidget/asset/component';
import {Router} from '@plugins/ComponentWidget/asset/router';

/**
 *
 */
export class SampleComponent implements ComponentInterface {
    onLoad(element: HTMLElement, attachments: {}) {
    }

    onReload(element: HTMLElement, attachments: {}) {
    }

    private bindLogin(element: HTMLElement) {
      // on click of the button using event delegation
      utility.delegate(element, 'btn.auth', 'click', (event) => {
          xhr({
              url: Router.generateRoute('sample', 'process'),
              type: 'json',
              method: 'post',
              data: { }
          }).then(response => {
              // do something
          }).fail((error, message) => {
              // do something
          });
      });
    }
}
```

<br>

# Module Controllers

Everything is the same for components conrollers, except for the route generation method. 
You can simply use this method instead.

```typescript
Router.generateModuleRoute('sample', 'process');
```

<br>

# Dependency Injection

Component supports dependency injection by default. You only need to define
the `create` static method which passes the container, and define a
`constructor` method to accept the dependencies.

**This can also be used for Component Controllers, Async and Component Attachments**

```php
namespace App\Product\Component\Sample;

use App\Plugins\ComponentWidget\ComponentWidgetInterface;

class SampleComponent implements ComponentWidgetInterface
{
    /**
     * @var App\Fetcher\Drupal\ConfigFetcher
     */
    private $configs;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('config_fetcher')
        );
    }

    /**
     * Public constructor
     */
    public function __construct($configs)
    {
        $this->configs = $configs;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return '@component/Sample/template.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];

        $data['name'] = 'Drew'

        return $data;
    }
}
```

<br>

# Async Components

Components consuming data fetchers can opt to use Async to significantly increase
performance.

Just create an Async class (your main component class appended with **Async** keyword).

On the Async class, you will define all the fetchers that your component has consumed.
You need to use the Async counterpart of the fetchers when making the calls.

```php
namespace App\Product\Component\Sample;

use App\Plugins\ComponentWidget\ComponentWidgetInterface;

class SampleComponentAsync implements AsyncComponentInterface
{
    /**
     * @var App\Fetcher\AsyncDrupal\ConfigFetcher
     */
    private $configs;

    /**
     * @var App\Fetcher\AsyncDrupal\MenuFetcher
     */
    private $menus;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('config_fetcher_async'),
            $container->get('menu_fetcher_async')
        );
    }

    /**
     * Public constructor
     */
    public function __construct($configs, $menus)
    {
        $this->configs = $configs;
        $this->menus = $menus;
    }

    /**
     *
     */
    public function getDefinitions()
    {
        return [
            $this->configs->getConfig('some-configuration'),
            $this->configs->getConfig('another-configuration'),
            $this->menu->getMenuById('some-menu'),
        ];
    }
}

```

<br>

# Script Includes

Script includes are use to attach Javascript files directly to the DOM

Just create an Include class (your main component class appended with **Includes** keyword).

```php
namespace App\Modules\MyModule;

use App\Plugins\ComponentWidget\ComponentIncludesInterface;

class MyModuleIncludes implements ComponentIncludesInterface
{
    /**
     * @{inheritdoc}
     */
    public function getIncludes()
    {
        $scripts[] = 'my-path-to-absolute-script.js';

        return $scripts;
    }
}
```
