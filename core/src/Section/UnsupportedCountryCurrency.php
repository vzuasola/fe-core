<?php

namespace App\Section;

use App\Plugins\Section\SectionInterface;
use Interop\Container\ContainerInterface;

class UnsupportedCountryCurrency implements SectionInterface
{

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->configFetcher = $container->get('config_fetcher');
    }

    /**
     * @{inheritdoc}
     */
    public function getSection(array $options)
    {
        try {
            $uclConfig =  $this->configFetcher->getGeneralConfigById('unsupported_currency');
            $rclConfig =  $this->configFetcher->getGeneralConfigById('unsupported_country');
            $data['uclConfig'] =  $uclConfig ;
            $data['rclConfig'] = $rclConfig;
        } catch (\Exception $e) {
            $data = [];
        }

        return $data ;
    }
}
