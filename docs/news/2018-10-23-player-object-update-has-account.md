# Player Object Update

Player Details now include **hasAccount** checking. _*Using PaymentAccountFetcher directly should be prohibited*_

### Before

PaymentAccountFetcher should never be used directly

```php
$hasCasinoGold = $this->paymentAccounFetcher->hasAccount('casino-gold');
```

### After

Use the player details instead

```php
$hasCasinoGold = $this->player->hasAccount('casino-gold');
```

Please also note that **GetPlayerDetails** have been deprecated for some time now as noted [here](https://gitlab.ph.esl-asia.com/CMS/fe-core/blob/working/docs/news/2018-07-20-deprecations-player-details.md)