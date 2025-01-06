<?php

namespace App\Handlers\Logout;

use App\Player\Player;

/**
 *
 */
class PlayerDetailsRemove
{
    /**
     *
     */
    public function __construct($container)
    {
        $this->session = $container->get('secure_session');
    }

    /**
     *
     */
    public function __invoke()
    {
        $this->session->delete(Player::CACHE_KEY);
        $this->session->delete(Player::ACCOUNT_CACHE_KEY);
    }
}
