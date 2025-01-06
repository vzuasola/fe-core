# Floating Banner Rework

The Floating Banner component has been reworked and is now AJAX driven.

**You must remove any references to the Floating Banner section**

```php
namespace App\Demo\Controller;

trait BaseSectionTrait
{
    /**
     *
     */
    public function getBaseData()
    {
        ...
        // remove this line
        $data['floating_banner'] = $this->getSection('floating_banner_common');
        ...

        return $data;
    }
}
```
