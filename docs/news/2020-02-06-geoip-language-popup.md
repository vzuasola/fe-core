# GeoIP Language Popup

A one-time popup will appear upon login where player can select their
preferred language.

## Prerequisite

* Updated fe-core

<br>

## Changes

### Assets

#### CSS

Include the sass and js files of the component.

```scss
@import "../../../core/core/assets/sass/components/geoip-language-popup";
```

#### JS

```javascript
import "Base/geoip-language-popup";
```

### Handlers

#### Login Handler (Optional, if using site-specific login handler)

If the product site has custom **login_success** handler (e.g., games and casino) this change needs to applied on `handlers.php`.

Before:

```php
return $response->withStatus(302)->withHeader('Location', $destination);
```

After:

```php
$handler = $c->get('handler')->getEvent('login_success_redirection');
return $handler($request, $response, $destination);
```

Refer to this implementation [click here](https://gitlab.ph.esl-asia.com/CMS/games/commit/7475706fce34d2b94ee1aa9cb77f3d90d7559274#4ac1a9690f4b34268c919342e0f32b5e8646de07_73_72)
