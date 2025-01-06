<?php

namespace App\Plugins\ComponentWidget;

use App\Plugins\ComponentWidget\Exceptions\WidgetNotFoundException;
use App\Plugins\ComponentWidget\Exceptions\InvalidRenderModeException;

/**
 * Defines all the getter of the component system
 *
 * Tasks and optimization
 * - This should be further broken down
 * - Fix that render methods are the one creating the class instances
 */
class ComponentWidgetManager
{
    /**
     * The current render mode
     *
     * @var string
     */
    private $mode = 'RENDER';

    private $moduleList = [];

    /**
     * Holds the current instances
     *
     * @var array
     */
    private $instances = [];

    /**
     * Holds the component nesting stack
     *
     * @var array
     */
    private $stack = [];

    /**
     * Holds the general options
     *
     * @var array
     */
    private $options = [];

    /**
     * Public constuctor.
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->request = $container->get('router_request');
        $this->router = $container->get('route_manager');
        $this->configuration = $container->get('configuration_manager');
        $this->settings = $container->get('settings')->get('components');

        // route configuration
        $this->route = $this->router->getRouteConfiguration($this->request);
    }

    /**
     *
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     *
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Instance cache controller
     *
     */

    /**
     *
     */
    public function getInstances()
    {
        return $this->instances;
    }

    /**
     *
     */
    public function setInstances($instances)
    {
        $this->instances = $instances;
    }

    /**
     * Widget Renderers
     *
     */

    /**
     * Renders a widget by ID
     *
     * @param string $id
     *
     * @return string
     */
    public function renderWidget($id, $options = [])
    {
        try {
            $widget = $this->getWidgetById($id, $options);
        } catch (WidgetNotFoundException $e) {
            return;
        }

        if (class_exists("{$widget->componentWidgetNamespace}Scripts")) {
            $scriptClass = "{$widget->componentWidgetNamespace}Scripts";
            $scriptInstance = $this->createInstance($scriptClass, ComponentAttachmentInterface::class);
        }

        if (class_exists("{$widget->componentWidgetNamespace}Async")) {
            $asyncClass = "{$widget->componentWidgetNamespace}Async";
            $asyncInstance = $this->createInstance($asyncClass, AsyncComponentInterface::class);
        }

        // if widget is set to not be rendered, return NULL
        if (!$this->isWidgetRenderable($widget)) {
            return;
        }

        // if we are in a defer state, return placeholder strings instead
        // and store current state in a stack
        if ($this->mode === 'DEFER') {
            // if we are in defered mode, allow the children of this widget
            // to get rendered silently

            $children = $this->getChildrenList($widget);

            foreach ($children as $key) {
                $this->renderWidget($key, $options);
            }

            // asign definition of this widget on the instances property

            $this->instances[$id]['class'] = $widget;
            $this->instances[$id]['options'] = $options;

            if (isset($scriptInstance)) {
                $this->instances[$id]['script'] = $scriptInstance;
            }

            if (isset($asyncInstance)) {
                $this->instances[$id]['async'] = $asyncInstance;
            }

            return "<# WIDGET($id) #>";
        }

        $renderMode = $this->mode;

        // depending on the render state of a component, we may always choose to
        // render that component on page load
        if ($this->isComponentRenderable($id)) {
            $renderMode = 'RENDER';
        }

        // If we are in a render state, return the actual template
        if ($renderMode === 'RENDER') {
            // default data

            $data = $options['data'] ?? [];

            $template = $widget->getTemplate($options);

            $data['component_widget_class_mode'] = 'render';
            $data['component_widget_class'] = $widget->componentWidgetId;
            $data['component_widget_alias'] = $widget->componentWidgetAlias ?? $id;
            $data['component_widget_template'] = $template;

            // data

            if (isset($this->instances[$id]['data'])) {
                $data = array_replace($data, $this->instances[$id]['data']);
            } else {
                $data = array_replace($data, $widget->getData($options) ? : []);
            }

            // attachments

            $attachments = [];

            if (isset($this->instances[$id]['attachments'])) {
                $attachments = $this->instances[$id]['attachments'];
            } else {
                if (isset($scriptInstance)) {
                    $attachments = $scriptInstance->getAttachments();
                }
            }

            $data['component_widget_attachments'] = $attachments;

            $wrapper = file_get_contents(__DIR__ . '/templates/wrapper.html.twig');

            return $this->container->get('view')->fetchFromString($wrapper, $data);
        }

        // If we are in a prerender state, return the actual template
        if ($renderMode === 'PRERENDER') {
            $data['component_widget_class_mode'] = 'prerender';
            $data['component_widget_class'] = $widget->componentWidgetId;
            $data['component_widget_alias'] = $widget->componentWidgetAlias ?? $id;

            $data['component_widget_attachments'] = [];

            $wrapper = file_get_contents(__DIR__ . '/templates/wrapper.html.twig');

            return $this->container->get('view')->fetchFromString($wrapper, $data);
        }

        throw new InvalidRenderModeException('Invalid rendering mode');
    }

    /**
     *
     */
    private function isComponentRenderable($id)
    {
        return isset($this->settings['render']['preload']) && in_array($id, $this->settings['render']['preload']);
    }

    /**
     * Check if widget should be rendered
     *
     * @return boolean
     */
    private function isWidgetRenderable(ComponentWidgetInterface $widget)
    {
        $id = $widget->componentWidgetId;

        $component = $this->request->getQueryParam('component-data-widget');

        // if the component ids does match
        if ($component && $widget->componentWidgetAlias !== $component) {
            $componentsList = $this->getComponentList();

            // if we are rendering a child widget, also allow the parent to be
            // rendered
            if (isset($componentsList[$component]['parent'])) {
                $parent = $componentsList[$component]['parent'];

                if ($parent == $id) {
                    return true;
                }
            }

            $component = (array) $component;

            // if we are rendering a child widget, then allow them to be rendered
            if (!in_array($id, $component)) {
                if (isset($componentsList[$id]['parent']) &&
                    in_array($componentsList[$id]['parent'], $this->stack)
                ) {
                    return true;
                }

                return false;
            }
        }

        return true;
    }

    /**
     *
     */
    public function routeWidget($id, $method, $request, $response)
    {
        $componentsList = $this->getComponentList();

        $widget = $this->getWidgetById($id);
        $namespace = $componentsList[$id]['class'];

        if ($widget &&
            isset($componentsList[$id]['class']) &&
            class_exists("{$namespace}Controller")
        ) {
            $class = "{$namespace}Controller";
            $instance = $this->createInstance($class);

            if (method_exists($instance, $method)) {
                return $instance->$method($request, $response);
            }
        }
    }

    /**
     * Parent Child Getters
     *
     */

    /**
     * Get the children of a widget
     */
    private function getChildrenList(ComponentWidgetInterface $widget)
    {
        $result = [];

        $id = $widget->componentWidgetId;
        $componentsList = $this->getComponentList();

        foreach ($componentsList as $key => $component) {
            if (isset($component['parent']) && $component['parent'] === $id) {
                $result[] = $key;
            }
        }

        return $result;
    }

    /**
     * Getter methods
     *
     */

    /**
     *
     */
    public function getComponentList()
    {
        $values = $this->configuration->getConfiguration('components');

        return $values['components'];
    }

    /**
     *
     */
    public function getWidgetById($id, $options = [])
    {
        $alias = $id;
        $componentsList = $this->getComponentList();

        // option to override a widget by specifying an alis on the routing config

        if (isset($options['components_override'][$id])) {
            $id = $options['components_override'][$id];
        } elseif (isset($this->options['components_override'][$id])) {
            $id = $this->options['components_override'][$id];
        } elseif (isset($this->route['components'][$id])) {
            $id = $this->route['components'][$id];
        }

        if (isset($componentsList[$id])) {
            $component = $componentsList[$id]['class'];

            $instance = $this->getWidget($component);
            $instance->componentWidgetAlias = $alias;
            $instance->componentWidgetId = $id;
            $instance->componentWidgetDefinition = $componentsList[$id];

            $this->stack[] = $id;

            return $instance;
        }

        throw new WidgetNotFoundException("Widget with ID `$id` is not found");
    }

    /**
     * Get a specific Widget instance
     */
    private function getWidget($widgetId)
    {
        $instance = $this->createInstance($widgetId, ComponentWidgetInterface::class);
        $instance->componentWidgetNamespace = $widgetId;

        return $instance;
    }

    /**
     * Modules
     *
     */

    /**
     * Renders the modules set
     *
     * - Remove render functionality for this one, as module list is fetched
     * already via AJAX
     *
     * @param array options
     */
    public function renderModules($options = [])
    {
        $result = [];
        $modules = $this->getModuleList();

        $moduleInstance = [];
        $scriptInstances = [];

        foreach ($modules as $key => $value) {
            $moduleInstance[$key] = $value['instance_class'];

            if (isset($value['instance_scripts'])) {
                $scriptInstances[$key] = $value['instance_scripts'];
            }

            if (isset($value['instance_async'])) {
                $asyncInstance = $value['instance_async'];
            }

            // prevent conflicts with components consuming the instances stack
            $id = "module:$key";

            // if we are in a defer state, return placeholder strings instead
            // and store current state in a stack
            if ($this->mode === 'DEFER') {
                $this->instances[$id]['class'] = $moduleInstance[$key];
                $this->instances[$id]['options'] = $options;

                if (isset($scriptInstance)) {
                    $this->instances[$id]['script'] = $scriptInstance;
                }

                if (isset($asyncInstance)) {
                    $this->instances[$id]['async'] = $asyncInstance;
                }
            }
        }

        // if we are in a defer state, return placeholder strings instead
        // and store current state in a stack
        if ($this->mode === 'DEFER') {
            return "<# MODULE #>";
        }

        if ($this->mode === 'RENDER' || $this->mode === 'PRERENDER') {
            foreach ($moduleInstance as $key => $module) {
                // prevent conflicts with components consuming the instances stack
                $id = "module:$key";

                // attachments
                $attachments = [];

                if (isset($scriptInstances[$key])) {
                    $attachments = $scriptInstances[$key]->getAttachments();
                }

                $data['component_module_attachments'] = $attachments;

                $result[$key] = $data;
            }

            return $result;
        }

        throw new InvalidRenderModeException('Invalid rendering mode');
    }

    /**
     * Renders the modules script includes set
     *
     * Workaround: A dedicated loop should exist in here, although this method
     * will never be called on its own because renderModules will always be
     * called
     *
     * @param array options
     */
    public function renderModuleIncludes($options = [])
    {
        // if we are in a defer state, return placeholder strings instead
        // and store current state in a stack
        if ($this->mode === 'DEFER') {
            return "<# MODULE_SCRIPTS #>";
        }

        if ($this->mode === 'RENDER' || $this->mode === 'PRERENDER') {
            $result = [];
            $modules = $this->getModuleList();

            foreach ($modules as $value) {
                if (isset($value['instance_includes'])) {
                    $includesInstance = $value['instance_includes'];

                    $result = array_merge($result, (array) $includesInstance->getIncludes());
                }
            }

            $wrapper = file_get_contents(__DIR__ . '/templates/includes.html.twig');

            return $this->container->get('view')->fetchFromString($wrapper, ['includes' => $result]);
        }

        throw new InvalidRenderModeException('Invalid rendering mode');
    }

    /**
     *
     */
    public function getModuleList()
    {
        if (empty($this->moduleList)) {
            $values = $this->configuration->getConfiguration('components');
            $list = $values['modules'] ?? [];

            foreach ($list as $key => $value) {
                $class = $value['class'];
                $value['instance_class'] = $this->createInstance(
                    $class,
                    ComponentModuleInterface::class
                );

                if (class_exists("{$class}Scripts")) {
                    $scriptClass = "{$class}Scripts";
                    $value['instance_scripts'] = $this->createInstance(
                        $scriptClass,
                        ComponentAttachmentInterface::class
                    );
                }

                if (class_exists("{$class}Async")) {
                    $asyncClass = "{$class}Async";
                    $value['instance_async'] = $this->createInstance(
                        $asyncClass,
                        AsyncComponentInterface::class
                    );
                }

                if (class_exists("{$class}Includes")) {
                    $includesClass = "{$class}Includes";
                    $value['instance_includes'] = $this->createInstance(
                        $includesClass,
                        ComponentIncludesInterface::class
                    );
                }

                if (class_exists("{$class}Controller")) {
                    $controllerClass = "{$class}Controller";
                    $value['instance_controller'] = $this->createInstance($controllerClass);
                }

                $this->moduleList[$key] = $value;
            }
        }

        return $this->moduleList;
    }

    /**
     *
     */
    public function routeModule($id, $method, $request, $response)
    {
        $modules = $this->getModuleList();

        if (isset($modules[$id]['instance_controller'])) {
            $instance = $modules[$id]['instance_controller'];

            if (method_exists($instance, $method)) {
                return $instance->$method($request, $response);
            }
        }
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
