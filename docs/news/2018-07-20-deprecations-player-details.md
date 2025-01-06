# Deprecation of GetPlayerDetails

The player detail calls will now be deprecated in favor of the new **player** object.

The player object has a service ID of **player**.

### Before

Accessing the player details via the user fetcher call

```php
$playerDetails = $this->userFetcher->getPlayerDetails();

$productId = $playerDetails['productId'];
$firstName = $playerDetails['firstName'];
```

### After

Get details using the player object

```php
$productId = $this->player->getProductId();
$firstName = $this->player->getFirsName();
```

_**You can check out the player object for a complete list of available methods**_
