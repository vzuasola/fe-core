<?php

namespace App\Handlers\Login;

use App\Cookies\Cookies;
use App\Utils\Host;

/**
 *
 */
class UserCookies
{
    /**
     *
     */
    public function __construct($container)
    {
        $this->player = $container->get('player');
    }

    /**
     *
     */
    public function __invoke()
    {
        try {
            $options = [
                'expire' => strtotime( '+1 year' ),
                'path' => '/',
                'domain' => Host::getDomain(),
            ];
            Cookies::set('gtm-username', $this->player->getUsername(), $options);
            Cookies::set('gtm-userid', $this->player->getPlayerId(), $options);
            Cookies::set('gtm-currency', $this->player->getCurrency(), $options);
        } catch (\Exception $e) {
            // do nothing
        }
    }
}
