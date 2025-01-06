# Creating a String Translation

A string translation is a file that stores hard coded translatable values.

## Defining a String Translation

Just like how configuration parameter works, string translation `can be defined on core`
and can also be `defined on a site specific level`.

> Defining a site specific string translation having the same filename as the core
> will override the core translation entirely

To define a translation, just put it on `src\Resources\translation\mytranslation.yml`

The YAML should have this specified format

```yaml
default: An error has occured on the page
translations:
  en: An error occured on the page EN
  sc: An error occured on the page SC
```

The `translations` accepts a key value pair of language code and the translated
string.

The `default` key is the string that will be used when the translation is not found
for a specific language code.

## Getting a Specific Translation

#### Via Controllers

On the controllers, where `mytranslation` is the ID or the filename of the string
translation.

```php
$this->get('translation_manager')->getTranslation('mytranslation');
```

#### Via Container Aware Dependency

```php
$container->get('translation_manager')->getTranslation('mytranslation');
```

# Advance Methods

The methods listed below are for special use cases

## Getting all Translations

You can opt to get all valid translations

```php
$container->get('translation_manager')->getTranslations('mytranslation');
```

This will return an associative array of translations

## Getting the Default or Specific Translation

If you just need the default translation

```php
$container->get('translation_manager')->getDefaultTranslation('mytranslation');
```

Or the language specific one

```php
$container->get('translation_manager')->getTranslationByLanguage('mytranslation', 'sc');
```
