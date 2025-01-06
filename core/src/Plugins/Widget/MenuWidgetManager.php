<?php

namespace App\Plugins\Widget;

/**
 *
 */
class MenuWidgetManager
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
     * Gets all list of defined Widgets
     */
    public function getWidgetList()
    {
        $widgets = [];
        $values = $this->configuration->getConfiguration('widgets');

        if (!empty($values['menu'])) {
            $widgets = $values['menu'];
        }

        return $widgets;
    }

    /**
     * Gets all defined Widgets instances
     */
    public function getWidgets($options = [])
    {
        $result = [];
        $widgets = $this->getWidgetList();

        if ($widgets) {
            foreach ($widgets as $key => $class) {
                $result[$key] = $this->createInstance($class, MenuWidgetInterface::class);
            }
        }

        return $result;
    }

    /**
     * Get a specific Widget instance
     */
    public function getWidget($widgetId, $options = [])
    {
        return $this->createInstance($widgetId, MenuWidgetInterface::class);
    }

    /**
     *
     */
    private function createInstance($class, $interface = null)
    {
        if (method_exists($class, 'create')) {
            $instance = $class::create($this->container);
        } else {
            $instance = new $class();
        }

        if ($interface && !$instance instanceof $interface) {
            throw new \RuntimeException("$class must implement interface $interface");
        }

        return $instance;
    }
}
