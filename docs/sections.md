# Sections

Sections are site components grouped into one single unit. Each site component
can have corresponding section.

To define a section, you can add it on your `sections.yml`

> For core sections, you can define the class and add it on the core's `sections.yml`
> otherwise you can just use the site specific one

```yaml
sections:
  my_section: App\Demo\Section\MySection
```

Section can either be implemented using Synchronous or Asynchronous. It is recommended
to always use the asynchronous one using the `AsyncSectionInterface`.

Just create a class implementing that interface, all sections should be under the
`App\Section` namespace or under `App\MyProduct\Section` namespace for site specific
instances.

```php
namespace App\MyProduct\Section;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class MySection implements AsyncSectionInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->config = $container->get('config_fetcher_async');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'base' => $this->config->getGeneralConfigById('my_config'),
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        $result = [];

        if (isset($data['base'])) {
            $result = $data['base'];
        }

        return $result;
    }
}
```

To use your section, you just need to call it on your controller. For Async sections,
definitions needs to be resolved.

```php
$data['my_config'] = $this->getSection('my_section');

$data = Async::resolve($data);

return $this->view->render($response, '@site/home.html.twig', $data);
```

# Best Practices

It is recommended that you group your common sections (sections that are present in
all pages) to a trait.

You can have a `BaseSectionTrait.php` just on your controller namespace.

```php

namespace App\MyProduct\Controller;

trait BaseSectionTrait
{
    /**
     * Common sections
     *
     * @return array
     */
    public function getBaseData()
    {
        $data['header'] = $this->getSection('header_common');
        $data['footer'] = $this->getSection('footer_common');
        $data['session'] = $this->getSection('session_timeout_common');

        return $data;
    }
}
```

With this approach, controllers can be cleaner, and adding common sections will be
easy.

You just need to call the trait on your controllers to be able to use it.

```php
namespace App\MyProduct\Controller;

use App\BaseController;
use App\Async\Async;

class PageController extends BaseController
{
    use BaseSectionTrait;

    /**
     * Manages the route for the home page
     */
    public function home($request, $response)
    {
        $data = $this->getBaseData();

        $data['title'] = 'Home';

        $data = Async::resolve($data);

        return $this->view->render($response, '@site/home.html.twig', $data);
    }
```

# Altering Sections

Section can also be altered by providing `alter classes`. These classes can implement
a specific interface.

> Alter section classes are used to alter a specific section without altering
> the original section itself, these is useful for conditionally modifying
> the data of a section upon meeting a certain criteria

### Create the alter class

You can either implement `App\Plugins\Section\SectionAlterInterface` for altering
non async sections, or you can use `App\Plugins\Section\AsyncSectionAlterInterface`
for async sections.

Section alters should be under the `App\MyProduct\Extensions\Section` for site specific
section alters, or under `App\Extensions\Section` for core section alters.

```php
namespace App\MyProduct\Extensions\Section;

use App\Plugins\Section\AsyncSectionAlterInterface;

class MySectionAlter implements AsyncSectionAlterInterface
{
    /**
     * {@inheritdoc}
     */
    public function alterSectionDefinition(&$definitions, array $options)
    {
        // put here if you want to alter the definition
        // ex: adding additional async calls
    }

    /**
     * {@inheritdoc}
     */
    public function alterprocessDefinition(&$result, $data, array $options)
    {
        // here you can now alter depending on any condition, just alter the
        // first $result argument

        if (isset($result['main_menu'])) {
            foreach ($result['main_menu'] as $key => $menu) {
                if (isset($menu['uri']) && strpos($menu['uri'], 'casino') !== false) {
                    unset($result['main_menu'][$key]);
                }
            }
        }
    }
}
```

### Register the alter class

The next step is to register the alterable class. Registering it means that it
will be put into play.

To register an alter class, create an `alters` section on your `sections.yml`.

> You can have many alter classes on core, but are not registered initially, then
> sites can just implement those alters if they wanted to

The alters key can contain key value pair of data, with the key being the section
key, then the value will be the qualified name of the section alter class.

```yaml
alters:
  my_section: App\MyProduct\Extensions\Section\MySectionAlter

sections:
  my_section: App\MyProduct\Section\MySection
  my_footer: App\MyProduct\Section\MyFooter
```
