# Header Updates for Specific Dafabet ID Domains

> Make sure to read the Sections documentation, specifically the `Altering Sections` part, before proceeding with this guide

## Async API Implementation

To enable the website header changes for specific Indonesian domains, just register 
these alters on your site's `sections.yml`.

```yaml
sections:
  ...
  header_async: App\Section\Async\HeaderAsync
  exception_404: App\Section\Async\Exception404
  
alters:
  header_async: App\Extensions\Section\HeaderAsyncAlter
  exception_404: App\Extensions\Section\Exception404Alter
```

You must then make sure to use the `header_async` section by either adding it to your `BaseSectionTrait.php`.
The `header_async` name is just an **_id_** of the section; you can rename it to one
that is most appropriate for the section, as long as the **_id_** for both the sections and alters of the `sections.yml` are the same.

```php
namespace App\MyProduct\Controller;

trait BaseSectionTrait
{
    /**
     *
     */
    public function getBaseData()
    {
        // Async definitions
        $data['header'] = $this->getSection('header_async');
        ...

        return $data;
    }
}
```

## Common API Implementation

> Follow the guide for Async API Implementation as the changes will also work for Common API

If you are using the common sections, just write an alter that points to the common section
equivalent
```yaml
sections:
  ...
  header_common: App\Section\Async\HeaderAsync
  exception_404: App\Section\Async\Exception404
  
alters:
  header_common: App\Extensions\Section\HeaderAsyncAlter
  exception_404: App\Extensions\Section\Exception404Alter
```
