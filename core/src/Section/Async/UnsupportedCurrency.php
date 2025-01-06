<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;
use App\Drupal\Config;

class UnsupportedCurrency implements AsyncSectionInterface
{

    /**
     * Config object
     */
    private $config;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->config = $container->get('config_fetcher_async');
        $this->player = $container->get('player');
        $this->scripts = $container->get('scripts');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'ucl' => $this->config->getGeneralConfigById('unsupported_currency'),
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        $result = [];

        $result['content'] = $data['ucl']['unsupported_currencies_message']['value'] ?? null;
        $result['title'] = $data['ucl']['unsupported_currencies_title'] ?? '';
        $result['button'] = $data['ucl']['unsupported_currencies_button'] ?? '';

        try {
            $user['currency'] = $this->player->getCurrency() ?? '';
        } catch (\Exception $e) {
            $user['currency'] = '';
        }

        // attach scripts to page
        $this->scripts->attach([
            'userDetails' => $user,
            'ucl' => $result['content'] ?? null, // TODO: deprecate this after alignment of games
            'unsupportedCurrency' => [
                'message' => $result['content'] ?? null,
                'providers' => Config::parse($data['ucl']['game_provider_mapping'] ?? ''),
            ]
        ], $options);

        return $result;
    }
}
