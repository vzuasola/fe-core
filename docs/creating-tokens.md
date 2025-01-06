# Creating Token Replacements for Front End

You can create tokens on the front end, so that you can define dynamic replacements
for a certain token.

Usually, tokens come in `{token}` or either `{namespace:token}` format.

# Defining the Token class

The first thing you need to do is to define the token class, which should reside
on the `Token` namespace.

The TokenInterface only needs one method, `getToken` where your dynamic data
should be returned.

```php
namespace App\MyProduct\Token;

use App\Plugins\Token\TokenInterface;

class MyToken implements TokenInterface
{
    public function getToken($options)
    {
        return 'This is my token'
    }
}
```

If your token needs the service container for some dependencies, you can add
this method to your token class. Don't forget to `use` the `ContainerInterface`

```php
namespace App\MyProduct\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

class MyToken implements TokenInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->myDependency = $container->get('my_dependency');
    }
}
```

# Register your Token class

After creating your token class, you must register it.

You can define your token on `config/tokens.yml` under the tokens collection

Define your token as follows:

```yaml
tokens:
    mytoken: App\MyProduct\MyToken
```

where the key `mytoken` corresponds to the key that you will define on the front
end as `{mytoken}`
