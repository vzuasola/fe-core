# Game Provider

Game providers has two components that should be considered:

* `Server side Adapter` - Provides data to the client side adapter
* `Client side Adapter` - Javascript part of a provider that handles game launching

# Adding a Server side Adapter

* Create a new class that implements `GameProviderInterface`. Your adapter should
be under the `GameProvider` namespace.

* Implement the interface methods. See `GameProviderInterface.php` for method
documentation

```php
namespace App\GameProvider\MyProvider;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use App\Plugins\GameProvider\GameProviderInterface;

/**
 *
 */
class Provider implements GameProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(RequestInterface $request, ResponseInterface $response)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($username, $password)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onSessionDestroy()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getJavascriptAssets()
    {
    }
}
```

* Register your new provider by specifying it on `app/config/games.yml`. Remember
the provider ID you specified here, as the ID is needed on the client side for
later.

```yml
providers:
    my_provider: App\GameProvider\MyProvider\Provider
```

# Client side Adapter

* Create a new script file for your provider, your script file should be
an inheritance of the abstract `game-launch.js`. See example below.

```javascript
import * as utility from "Base/utility";
import GameLaunch from "Base/game/game-launch";

/**
 *
 */
function MyProviderLauncher() {
    /**
     * A custom init method that will be called on document ready
     */
    this.init = function () {
    };

    /**
     * Authenticate using username and password
     *
     * @param string username
     * @param string password
     *
     * @return boolean
     */
    this.login = function (username, password) {
    };

    /**
     * Authenticate by token
     *
     * @param string username
     * @param string password
     *
     * @return boolean
     */
    this.authenticateByToken = function (token) {
    };

    /**
     * Launch a game
     *
     * @param array options
     *
     * @return boolean
     */
    this.launch = function (options) {
    };

    /**
     * Invoked when a player is logout
     *
     * @param array options
     *
     * @return boolean
     */
    this.logout = function () {
    };
}

// inheritance
MyProviderLauncher.prototype = GameLaunch.prototype;

export default MyProviderLauncher;
```

* To register your provider, and activate game launching. ID should be the same
just call the game launcher as below:

```javascript
import GameLauncher from "Base/game/game-launcher";
import MyProviderLauncher from "Base/game/my-provider-launch";

var gameLauncher = new GameLauncher();
gameLauncher.setProvider('my_provider', new MyProviderLauncher());
gameLauncher.init();
```
