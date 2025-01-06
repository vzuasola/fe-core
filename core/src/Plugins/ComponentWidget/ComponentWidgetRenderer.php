<?php

namespace App\Plugins\ComponentWidget;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Stream;

use App\Async\AsyncResolver;

use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Response;
use App\Plugins\ComponentWidget\Exceptions\WidgetNotFoundException;

/**
 * Allows rendering of component widgets
 *
 * This class currently has the ff responsibility
 * - Handles returning of a valid response object for a controller
 * - Handles Async for component widgets
 */
class ComponentWidgetRenderer
{
    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('view'),
            $container->get('router_request'),
            $container->get('component_widget_manager'),
            $container->get('fetcher_cache'),
            new AsyncResolver(),
            $container->get('settings')->get('components')
        );
    }

    /**
     *
     */
    public function __construct($view, $request, $manager, $cacher, $resolver, $settings)
    {
        $this->view = $view;
        $this->request = $request;
        $this->manager = $manager;
        $this->cacher = $cacher;
        $this->resolver = $resolver;
        $this->settings = $settings;
    }

    /**
     *
     */
    public function render(ResponseInterface $response, $template, $data = [], $options = [])
    {
        $isComponent = $this->request->getQueryParam('component-data-widget');
        $this->manager->setOptions($options);

        if ($isComponent) {
            $this->manager->setMode('RENDER');
        } else {
            if ($this->isComponentPreRenderable()) {
                $this->manager->setMode('PRERENDER');
            } else {
                $this->manager->setMode('RENDER');
            }
        }

        $response = $this->view->render($response, $template, $data);

        return $this->resolveResponse($response);
    }

    /**
     *
     */
    private function isComponentPreRenderable()
    {
        return isset($this->settings['render']['mode']) && $this->settings['render']['mode'] == 'prerender';
    }

    /**
     *
     */
    private function resolveResponse($response)
    {
        $isRouteInvoke = $this->request->getQueryParam('component-data-widget');

        // if we are navigating via AJAX, check if we have custom headers
        // available
        if ($isRouteInvoke) {
            $settings = $this->settings['router'] ?? [];
            try {
                $widget = $this->manager->getWidgetById($isRouteInvoke);
            } catch (WidgetNotFoundException $e) {
                return $response;
            }

            if (isset($settings['widget_headers']) && is_array($settings['widget_headers'])) {
                $headers = $settings['widget_headers'];

                if (isset($widget->componentWidgetDefinition['headers']) &&
                    is_array($widget->componentWidgetDefinition['headers'])
                ) {
                    $headers = array_replace_recursive(
                        $headers,
                        $widget->componentWidgetDefinition['headers']
                    );
                }

                foreach ($headers as $header => $value) {
                    $response = $response
                        ->withHeader($header, $value)
                        ->withAttribute("headers-no-override:$header", true);
                }
            }
        }

        return $response;
    }
}
