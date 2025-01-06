<?php

namespace App\Plugins\GameProvider;

/**
 * The Game provider manager
 */
class GameProviderManager
{
    /**
     * Exposed the service container on the form manager
     */
    protected $container;

    /**
     * The system configurations manager
     */
    protected $configuration;

    /**
     * Public constuctor.
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->configuration = $container->get('configuration_manager');
    }

    /**
     * Gets all list of defined providers
     */
    public function getProviderList()
    {
        $providers = [];
        $values = $this->configuration->getConfiguration('games');

        if (!empty($values['providers'])) {
            $providers = $values['providers'];
        }

        return $providers;
    }

    /**
     * Gets all defined providers instances
     */
    public function getProviders($options = [])
    {
        $result = [];
        $providers = $this->getProviderList();

        if ($providers) {
            foreach ($providers as $key => $class) {
                $result[$key] = $this->getProviderInstance(new $class, $options);
            }
        }

        return $result;
    }

    /**
     * Get a specific provider instance
     */
    public function getProvider($providerId, $options = [])
    {
        $provider = new $providerId();

        return $this->getProviderDefinition($provider, $options);
    }

    /**
     *
     */
    private function getProviderInstance(GameProviderInterface $provider, $options)
    {
        // inject the service container
        if (method_exists($provider, 'setContainer')) {
            $provider->setContainer($this->container);
        }

        return $provider;
    }
}
