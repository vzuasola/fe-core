# Enabling Outdated Browser Notification

To enable outdated browser notification support you need to configure Front End

<br>

## Site Specific Setup

Make sure the have the latest fe-core and have yarn recompiled.

### Add JS and CSS

Include the outdated browser notification assets.

Import the following on your `_common.scss` or equivalent component

```scss
@import "../../../core/core/assets/responsive/sass/components/outdated-browser";
```

<br>

# Front End Configuration

### Setup your controllers

* Fetch the data

```php
'outdated_browser' => $this->get('config_fetcher_async')->getGeneralConfigById('browser_configuration')
```

* Attach to scripts

```php
'outdated_browser' => $data['outdated_browser']
```

* For test controller

```php
use App\Fetcher\AsyncDrupal\ConfigFetcher as ConfigFetcherAsync;

public $configFetcherAsync;

$this->configFetcherAsync = $this->getMockBuilder(ConfigFetcherAsync::class)
    ->setMethods(['getGeneralConfigById'])
    ->disableOriginalConstructor()
    ->getMock();
$this->configFetcherAsync->expects($this->exactly(1))
    ->method("getGeneralConfigById")
    ->willReturn([
        'outdated_browser' => [
            'message' => [
                'value' => "<h3>Notification</h3>"
            ]
        ]
    ]);
```

* Reminder: `Please check your drupal data of browser configuration`