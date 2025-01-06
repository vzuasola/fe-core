# Affiliate Rework

The affiliate module have been reworked

* Front end migration step

Affiliate tracking is now in the middleware, to enable it, you need to override a setting
on your  site's `settings.php`

```php
$settings['settings']['tracking']['enable'] = true;
```

* Drupal migration step

You need to reinstall the `webcomposer_affilate` module (yep the typo is intended).
Reinstalling will remove the taxonomy and will introduce a new way of configuring form

The new module also has a new rest resource named `Affiliates rest resource` that you
need to enable and grant anonymous permission to.

* Content migration step

The rework also provide a new token, to add affiliate to any link, simply create as follows

```
http://mysite.com/about/[query:({tracking})]
```
