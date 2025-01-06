<?php

namespace App\Plugins\Token;

/**
 * Token manager
 */
class TokenManager
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
     * Stores the defined tokens
     *
     * @var array
     */
    private $tokensList = [];

    /**
     * Stores the defined lazy tokens
     *
     * @var array
     */
    private $lazyTokensList = [];

    /**
     * Stores the created tokens
     *
     * @var array
     */
    private $tokenInstances = [];

    /**
     * Public constructor
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->configuration = $container->get('configuration_manager');
    }

    /**
     * Gets all list of defined tokens
     *
     * @return array
     */
    public function getTokenList()
    {
        if (empty($this->tokensList)) {
            $values = $this->configuration->getConfiguration('tokens');

            if (!empty($values['tokens'])) {
                $this->tokensList = $values['tokens'];
            }

            $this->processExtensions($this->tokensList);
        }

        return $this->tokensList;
    }

    /**
     * Gets all list of defined lazy tokens
     *
     * @return array
     */
    public function getLazyTokens()
    {
        if (empty($this->lazyTokensList)) {
            $values = $this->configuration->getConfiguration('tokens');

            if (!empty($values['lazy'])) {
                $tokens = $this->getTokenList();
                $lazy = $values['lazy'] ?? [];

                foreach ($lazy as $value) {
                    if (isset($tokens[$value])) {
                        $this->lazyTokensList[$value] = $tokens[$value];
                    }
                }
            }
        }

        return $this->lazyTokensList;
    }

    /**
     * Gets all list of defined tokens exclusing lazy ones
     *
     * @return array
     */
    public function getNonLazyTokens()
    {
        $lazy = $this->getLazyTokens();
        $tokens = $this->getTokenList();

        return array_diff_key($tokens, $lazy);
    }

    /**
     *
     */
    private function getTokenExtensions()
    {
        $extensions = [];
        $values = $this->configuration->getConfiguration('tokens');

        if (!empty($values['extensions'])) {
            $extensions = $values['extensions'];
        }

        return $extensions;
    }

    /**
     *
     */
    private function processExtensions(&$tokens)
    {
        $extensions = $this->getTokenExtensions();

        foreach ($extensions as $extension) {
            $instance = new $extension;

            if ($instance instanceof TokenExtensionInterface) {
                if (method_exists($instance, 'setContainer')) {
                    $instance->setContainer($this->container);
                }

                $instance->process($tokens);
            }
        }
    }

    /**
     *
     */
    public function getToken($id, $options = [])
    {
        $prefix = $id;

        // because we are using static caching, we need to prefix the cache key
        if ($options) {
            $prefix = $prefix . implode(':', $options);
        }

        if (!isset($this->tokenInstances[$prefix])) {
            $token = new $id();
            $this->tokenInstances[$prefix] = $this->getTokenDefinition($token, $options);
        }

        return $this->tokenInstances[$prefix];
    }

    /**
     *
     */
    private function getTokenDefinition(TokenInterface $token, $options)
    {
        // inject the service container
        if (method_exists($token, 'setContainer')) {
            $token->setContainer($this->container);
        }

        return $token->getToken($options);
    }
}
