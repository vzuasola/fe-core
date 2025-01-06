# Fetchers

Fetchers are services that fetches or transmit data to and from the CMS API layer.
Most of the time, fetchers are in-sync with the controller APIs exposed by CMS API.

### Usage

Sync fetchers, they return the response immediately

```php
$sessionConfigs = $this->configs->getConfig('webcomposer_config.session_configuration');
```

Async fetchers needs to be resolved, they return definition files

```php
$data['session'] = $this->configs->getConfig('webcomposer_config.session_configuration');

$data = Async::resolve($data);

$sessionConfigs = $data['session'];
```

<br>

### Types

There are many types of fetchers, fetchers are just service classes which you get via the service container.
The most common types of fetchers are:
* Config Fetcher
* Views Fetcher

**Config Fetcher**

Config fetcher allows you to fetch values of configuration forms

```php
class MyController
{
    public function someAction()
    {
        $componentConfigs = $this->get('config_fetcher')->getConfig('my_module.my_component_config_id');
        
        // $componentConfigs now contain all your configuration as an array
    }
}
```

**Views Fetcher**

Views fetcher allows you to fetch values of Views Rest export

```php
class MyController
{
    public function someAction()
    {
        $myList = $this->get('views_fetcher')->getViewById('my_view_rest_export_api_path');
        
        // $myList now contain all your list as an array of entities
    }
}
```

<br>

### Cross Product Calls

To call cross product, specify the product using the **WithProduct** method. This returns
a new fetcher instance with your new product as base.

Example of a service that calls data from other product

```php
class MyService
{
    public function __construct($configs)
    {
        $this->configs = $configs->withProduct('casino');
    }

    public function init()
    {
        $casinoConfig = $this->configs->getConfig('casino.data');
    }
}
```

Usage for service container aware context

```php
// this fetcher will always get data from your current product

$configs = $container->get('config_fetcher');

$myConfig = $configs->getConfig('my.data');

// now this fetcher will always get data from casino product

$configs = $container->get('config_fetcher')->withProduct('casino');

$casinoConfig = $configs->getConfig('casino.data');
```

<br>

### Cross Language Calls

In very special scenarios, you may want to fetch a data from a different language from that
of the current site language.

```php
// this fetcher will always get data from your current language

$configs = $container->get('config_fetcher');

$myConfig = $configs->getConfig('my.data');

// now this fetcher will always get data from a different language

$configs = $container->get('config_fetcher')->withLanguage('sc');

$myConfigSC = $configs->getConfig('casino.data');
```
