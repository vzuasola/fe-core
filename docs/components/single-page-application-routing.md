# Single Page Application Routing

To enable single page application routing behavior, the following must be true
for your site

* All site's webpage components should be implemented as a [**Components Widget**](component-widget.md)
* All Javascript behaviors should be attached on your Component widget's **onLoad** and **onReload** events
* There should be no Javascript accessing DOM present on a global state
* Controllers should contain no business logic

<br>

# The Client Router Component

The core framework ships with a client routing component which you can activate by
activating it on your **app.ts**

```typescript
import {Router} from '@plugins/ComponentWidget/asset/router';

Router.init();
```

<br>

## How to enable Router for certain links

You just need to put this property on your anchor tags

* **data-router** Should always be set to true
* **data-router-refresh** Specify the component name you want to reload

```twig
<a
    href="{{ url('about') }}"

    data-router="true"
    data-router-refresh="main"
>
    About
</a>
```

<br>

## Manually Invoke the Router Navigation

You can manually trigger a navigate by calling the navigate method

```typescript
import {Router} from '@plugins/ComponentWidget/asset/router';

Router.navigate('/en/about', ['header', 'footer']);
```

<br>

# Listening for Navigation Events

The router trigger a custom event whenever a navigation occurs.

You can use the `Router.on(event, handler)` method to bind on the router events.
It accepts 2 arguments:

* **event** The router event, it can be `RouterClass.beforeNavigate`, `RouterClass.afterNavigate` or `RouterClass.navigateError`
* **handler** A function closure to execute

```typescript
import {Router, RouterClass} from '@plugins/ComponentWidget/asset/router';

Router.on(RouterClass.beforeNavigate, (event) => {
    // do something before loading
});

Router.on(RouterClass.afterNavigate, (event) => {
    // do something after loading
});

Router.on(RouterClass.navigateError, (event) => {
    // do something after a loading error occurs
});
```

You can use this events to add a loader page, for example.
