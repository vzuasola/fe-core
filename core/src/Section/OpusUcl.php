<?php

namespace App\Section;

use App\Plugins\Section\SectionInterface;
use Interop\Container\ContainerInterface;

class OpusUcl implements SectionInterface
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
            $opusConfig =  $this->configFetcher->getGeneralConfigById('games_opus_provider');
            $gameworxConfig =  $this->configFetcher->getGeneralConfigById('unsupported_country');
            $data['opusucl'] =  $opusConfig ;
            $data['gameworxrcl'] = $gameworxConfig;
        } catch (\Exception $e) {
            $data = [];
        }

        return $data ;
    }
}
