# What is Async

Moving forward, Webcomposer will adopt an asynchronous approach on fetching and posting
external calls. An example of those calls are Fetcher calls.

With Asynchronous, all fetchers will either return a `Definition` or `DefinitionCollection`
object.

A Section will also be returning a `DefinitionCollection`.

## What are Async Definitions

An Async Definition is a special object that can be processed by an async manager.
There are two types:
* `Definition` - Contains a deferred call from an API
* `DefinitionCollection` - Contains a collection of Definitions

An async definition on Webcomposer is an `encapsulated promise` of a Guzzle client call.

### Definition

```php
$callback = function ($data, $options) {
    return json_decode($data);
}

$definition = new Definition($client, 'GET', 'my/api/uri', [], $callback);
```
A definition has the following arguments:
* `Client` - A Guzzle client object that the definition resolution will use
* `Method` - The request of the method, can either be `GET` or `POST`
* `URI` - The path where to issue a request
* `Options` - Additional options passed to the callback
* `Callback` - Optional callback that defines what happens after the request is successful, you can alter the data here before returning it

### Definition Collection

```php

$definitions = [
    new Definition($client, 'GET', 'my/api/uri', [], $callback),
    new Definition($client, 'GET', 'my/api/another', [], $callback),
];

$callback = function ($data, $options) {
    return $data;
}

$definition = new DefinitionCollection($definitions, [], $callback);
```
A definition has the following arguments:
* `Definitions` - A collection of definitions
* `Options` - Additional options passed to the callback
* `Callback` - Optional callback that defines what happens after the request is successful, you can alter the data here before returning it

### Extending Definitions

Definition can be extended with multiple callbacks. Callback are executed in order, the
first callback added will be executed first.

The succeeding callbacks will get the data returned by the previous callback.

```php
    $definition = new Definition($client, 'GET', 'my/api/uri', [], function ($data, $options) {
        return $data;
    });

    $newDefinition = $definition->withCallback(function ($data, $options) {
        // adding a new set of data before returning it
        $data[] = 'leandrew';

        return $data;
    });
```

## How to use the Async Manager

You can use the async manager, and use the `resolve` method. The method
only accepts collection of `Definition` or `DefinitionCollection` objects.

An async fetcher will only return either a `Definition` or `DefinitionCollection` object.

```php
use App\Async\Async;

$results = Async::resolve([
    'product' => $this->get('menu_fetcher_async')->getMenuById('product-menu'),
    'quick' => $this->get('menu_fetcher_async')->getMenuById('quicklinks'),
    'cashier' => $this->get('menu_fetcher_async')->getMenuById('cashier-menu'),
    'profile' => $this->get('menu_fetcher_async')->getMenuById('profile-menu'),
    'product-multi' => $this->get('menu_fetcher_async')->getMultilingualMenu('product-menu'),
]);
```

The results will be an associative array of this format:

```php
$results = [
    'product' => [
        // product data
    ],
    'quick' => [
        // quick data
    ],
    // etc..
];
```

## How to Resolve Definition Data Immediately

Sometimes we just need to resolve a single definition, the real power of async
comes when we are resolving multiple definitions. Resolving multiple definitions
means that external calls will be executed in parallel.

To resolve a single Definition or DefinitionCollection, simple call the `resolve` method.

```php
$definition = $this->get('menu_fetcher_async')->getMenuById('product-menu');

// I want to get the data already so I resolve it

$menu = $definition->resolve();
```
