# Saving and Fetching User/Player Preferences

You can now store username specific player preferences that can persist across multiple
products.

### Getting saved preferences

Preferences here will be an associative array

```php
try {
    $preferences = $this->get('preferences_fetcher')->getPreferences();
} catch (\Exception $e) {
    $preferences = [];
}
```

### Storing preferences

You need to pass one key value pair at a time

Best practice is to use dot concatenated values.

```php
try {
    $this->get('preferences_fetcher')->savePreference('casino.remember', true);
    $this->get('preferences_fetcher')->savePreference('player.games.default', 'GL5');
} catch (\Exception $e) {
    // handle error
}
```

### Remove preferences

Pass an array of keys to remove a preference

```php
try {
    $this->get('preferences_fetcher')->removePreference('casino.remember', 'player.games.default');
} catch (\Exception $e) {
    // handle error
}
```
