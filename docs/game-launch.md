# Game Launching

The game launch provider API allows you to define launchable games using a simple
predefined markup.

To make game launching work, you need to enable a certain configuration on your
site instance.

## Clientside

On the clientside, add the Javascript

```javascript
import GameLauncher from "Base/game/game-launcher";
import PlaytechLaunch from "Base/game/playtech-launch";
import OpusLauncher from "Base/game/opus-launch";

var gameLauncher = new GameLauncher();
gameLauncher.setProvider('playtech', new PlaytechLaunch());
gameLauncher.setProvider('opus', new OpusLauncher());
gameLauncher.init();
```

## Server side

on `app/config/games.yml`

```yml
providers:
  playtech_pas: App\GameProvider\Playtech\Provider
  opus_provider: App\GameProvider\Opus\Provider
```

## Game markup

You can define game markups like this

```html
<a 
    href="#"
    class="btn btn-yellow" 
    data-game-launch="true"
    data-game-provider="playtech"
    data-game-code="whk"
    data-game-real="1"
    data-game-product="casino"
    data-game-type="html5"
>
    LAUNCH GAME
</a>
```
> The `data-game-provider` corresponds to the `key` you have passed on the Javascript

## Playtech Game Launching

### Prerequisites

To make playtech launching work, set your local domain to `dev-www.elysium-dfbt.com`
Playtech servers only support a limited list of whitelisted domains

### Avaialable Options

You can still launch FIm specific games using PAS. You just need to supply the proper
parameters.

`Real money`

```html
<a 
    href="#"
    class="btn btn-yellow" 

    data-game-launch="true"
    data-game-provider="playtech"
    data-game-code="rol"
    data-game-real="1"
    data-game-product="casino"
    data-game-type="flash"

    data-game-params-mode="real"
    data-game-params-fixedsize="0"
>
    REAL PLAY
</a>
```

`Free play`

```html
<a 
    href="#"
    class="btn btn-yellow" 

    data-game-launch="true"
    data-game-provider="playtech"
    data-game-code="rol"
    data-game-real="0"
    data-game-product="casino"
    data-game-type="flash"

    data-game-params-mode=""
    data-game-params-preferedmode="offline"
    data-game-params-fixedsize="0"
    data-game-params-module="rol"
>
    FREE PLAY
</a>
```

## Keno Game Launching

### Avaialable Options

To be populated with available Keno options

## How to Launch Game on Demand

If you want to launch games based on a certain condition, any condition to be specific,
you can invoke the game providers statically.

```javascript
import GameLauncher from "Base/game/game-launcher";

var provider = 'playtech';
var options = {
    code: 'whk',
    real: 1,
    product: 'casino',
    type: 'html5'
};

GameLauncher.prototype.launch(provider, options);
```

> Just make sure that the providers are already set before attempting to launch games this way`
