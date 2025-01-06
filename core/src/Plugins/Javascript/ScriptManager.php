<?php

namespace App\Plugins\Javascript;

/**
 * The Game provider manager
 */
class ScriptManager
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
        $values = $this->configuration->getConfiguration('scripts');

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

        return $this->getProviderInstance($provider, $options);
    }

    /**
     *
     */
    private function getProviderInstance(ScriptProviderInterface $provider)
    {
        // inject the service container
        if (method_exists($provider, 'setContainer')) {
            $provider->setContainer($this->container);
        }

        return $provider;
    }
}
