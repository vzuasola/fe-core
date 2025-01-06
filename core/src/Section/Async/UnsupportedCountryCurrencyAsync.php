<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class UnsupportedCountryCurrencyAsync implements AsyncSectionInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->configFetcher = $container->get('config_fetcher_async');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'ucl' => $this->configFetcher->getGeneralConfigById('unsupported_currency'),
            'rcl' => $this->configFetcher->getGeneralConfigById('unsupported_country'),
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        $data['uclConfig'] =  $data['ucl'];
        $data['rclConfig'] =  $data['rcl'];

        return $data;
    }
}
