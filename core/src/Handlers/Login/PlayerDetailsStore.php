<?php

namespace App\Handlers\Login;

use App\Player\Player;

/**
 *
 */
class PlayerDetailsStore
{
    /**
     *
     */
    public function __construct($container)
    {
        $this->player = $container->get('player');
        $this->session = $container->get('secure_session');
    }

    /**
     *
     */
    public function __invoke()
    {
        $this->session->delete(Player::CACHE_KEY);
        $this->session->delete(Player::ACCOUNT_CACHE_KEY);

        try {
            $this->player->getEmail();
            $this->player->hasAccount('casino-gold');
        } catch (\Exception $e) {
            // do nothing
        }
    }
}
