<?php

namespace App\Player;

use App\Drupal\Config;

class PlayerDetails
{
    protected $player;
    protected $configFetcher;
    protected $playerSession;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('player'),
            $container->get('config_fetcher'),
            $container->get('player_session')
        );
    }

    /**
     * The public constructor
     */
    public function __construct($player, $configFetcher, $playerSession)
    {
        $this->player = $player;
        $this->configFetcher = $configFetcher;
        $this->playerSession = $playerSession;
    }

    /**
     * Get Player VIP Level
     *
     * @deprecated
     */
    public function getPlayerVipLevel()
    {
        if (!$this->playerSession->isLogin()) {
            return;
        }

        $vipConfig = $this->configFetcher->getGeneralConfigById('vip_configuration');
        $playerVipLevel = $this->player->getVipLevel();

        $parseConfig = Config::parseCommaDelimited($vipConfig['vip_mapping_configuration'] ?? []);
        foreach ($parseConfig as $key => $iCoreVipIds) {
            if (in_array($playerVipLevel, $iCoreVipIds)) {
                return $key;
            }
        }

        return 'bronze';
    }
}
