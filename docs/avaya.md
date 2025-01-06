# Avaya Integration

> Make sure that the Avaya Drupal module is enabled before proceeding with this guide

Avaya components ships with a custom section and a JS file. If you are using the
standard Dafabet header (non legacy Dafabet header) JS, the avaya JS should 
already be included as a part of it.

## Activate Livechat Component

* Append the livechat section On your `BaseSectionTrait` or in your Controllers, 
add the Avaya Live chat section as follows:

```php
$data['livechat'] = $this->getSection('livechat');
```

* The Avaya module can be configured on this path `/en/admin/config/webcomposer/avaya/config`

## Creating Links to trigger Livechat

To trigger the livechat, there are two ways of doing it:

* Via data attribute
    * If you have a markup under your control, you can append this attribute on a link `data-avaya-target=true`.
    That links should open livechat

* Via href
    * For links, especially configurable links, you can set your link to `#linkto:avaya` as long that it contains
    `linkto:avaya` string, that link will open up avaya
