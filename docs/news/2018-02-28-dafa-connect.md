# DafaConnect Detection

### Enable the Setting

To allow FE to detect the existence of DafaConnect browser, configure
your site `settings.php` as follows

```php
// Dafabet Connect

$settings['settings']['dafaconnect']['enable'] = true;
```

This will allow FE to detect the DafaConnect header and pass it to API Layer.

### Configure API Layer Mapping

On API Layer, configure the portal ID mapping equivalent of DafaConnect for your product.

Refer to section [Configuring Product Mapping](https://gitlab.ph.esl-asia.com/CMS/cms-api/blob/working/docs/product-mappings.md)
and use the **dafaconenct** index

