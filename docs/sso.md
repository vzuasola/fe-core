# SSO

Single sign on allows Webcomposer sites to share session across multiple domain 
entries.

# How to enable SSO support

> Make sure that the `webcomposer_single_signon` Drupal module is configured properly before proceeding to this step

* Enable the flag on your site specific `settings.php`

```php
$settings['settings']['sso']['enable'] = 'true';
```

* On your site specific `base.js`, import the session manager script

```javascript
import "Base/sso/session-manager";
```

* On your `webpack.base.js`, add the XDM script

```javascript
{
    from: entryPoint.paths.baseSrc + "js/vendor/easyXDM.min.js",
    to: entryPoint.paths.dist + "js/easyXDM.min.js"
},
```
