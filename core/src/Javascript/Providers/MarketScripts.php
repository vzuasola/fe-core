<?php

namespace App\Javascript\Providers;

use App\Plugins\Javascript\ScriptProviderInterface;

/**
 *
 */
class MarketScripts implements ScriptProviderInterface
{
    /**
     * Sets the container
     */
    public function setContainer($container)
    {
        $this->config = $container->get('config_fetcher_async');
        $this->player = $container->get('player_session');
    }

    /**
     * @{inheritdoc}
     */
    public function getAttachments()
    {
        $data = [];
        $providers = $this->config
            ->getConfig('webcomposer_marketing_script.providers')
            ->resolve();

        if ($providers) {
            $providers = array_filter($providers, function ($value) {
                return isset($value['enable']) && $value['enable'];
            });
        }

        if ($this->player->isLogin()) {
            $data['username'] = $this->player->getUsername();
        }

        return [
            'marketing_scripts' => $data,
        ];
    }
}
