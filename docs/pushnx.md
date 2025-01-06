# Push Notification

> There is a Push Notification configuration on `webcomposer_config` module.

By default, manual notifications are displayed, such as promotional contents and other useful information about the site. To display product notifications, set the `Product Type ID` on the configuration.

Product Type ID
* OW Sports: 3
* Live Dealer: 4
* Games: 5

# Activating Push Notification

* Import push notification JS and instantiate the defined class on document ready.

```javascript
import pushNotification from "Base/push-notification";

utility.ready(function () {
    new pushNotification();
});
```

* Import push notification SASS.

```css
@import "../../core/core/assets/sass/components/push-notification";
```

* Template for push notification is already embedded by default in `/core/templates/page.html.twig`. Make sure to add the template if you have a custom `page.html.twig`.

```html
{% block pushNotificationLightbox %}
    {% embed '@base/components/push-notification-lightbox.html.twig' %}{% endembed %}
{% endblock pushNotificationLightbox %}
```

* On your `ExceptionController.php`, add a class for 404 Not Found page.

```php
$data['body_class'] = 'page-404';
```

# Server Configuration

Push server parameter is provisioned on per server environment.
To change the server connection on your local, change the value of `pushnx_server` parameter in `/core/app/parameters.php`. 
```yaml
$parameters['env(PUSHNX_SERVER)'] = 'https://pnxtct.chodeetsu.com';
```

Push server can also be overriden through CMS, go to push notification configuration and configure `Domain`.

# Sending Notifications

To send a notification using Postman: see section `13.1.1 Push-Popup Service` in [IT Ops - Runbook - Push Notifications - v1.5](http://sharepoint.esl-asia.com/ESL/it/Architecture%20and%20Design/Forms/AllItems.aspx?RootFolder=%2FESL%2Fit%2FArchitecture%20and%20Design%2FDesign%20Documents%2FPush%20Notification&FolderCTID=0x012000A3DCA9680D96B349BB31BCDFC1C18B72&View={24B1284D-3D66-4904-BD9F-B7352F43C262}).
