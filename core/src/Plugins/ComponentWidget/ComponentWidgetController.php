<?php

namespace App\Plugins\ComponentWidget;

use App\BaseController;
use Slim\Exception\NotFoundException;

class ComponentWidgetController extends BaseController
{
    /**
     *
     */
    public function moduleList($request, $response)
    {
        $data['modules'] = $this->get('component_widget_manager')->renderModules();

        return $this->get('rest')->output($response, $data);
    }

    /**
     *
     */
    public function route($request, $response, $args)
    {
        $id = $args['id'];
        $method = $args['method'];

        try {
            $response = $this->get('component_widget_manager')->routeWidget($id, $method, $request, $response);
        } catch (\Exception $e) {
            throw new NotFoundException($request, $response);
        }

        return $response;
    }

    /**
     *
     */
    public function routeStatic($request, $response)
    {
        $path = $request->getUri()->getPath();

        $segments = explode('/', $path);

        $args['id'] = $segments[5];
        $args['method'] = $segments[6];

        return $this->route($request, $response, $args);
    }

    /**
     *
     */
    public function module($request, $response, $args)
    {
        $id = $args['id'];
        $method = $args['method'];

        try {
            $response = $this->get('component_widget_manager')->routeModule($id, $method, $request, $response);
        } catch (\Exception $e) {
            throw new NotFoundException($request, $response);
        }

        return $response;
    }

    /**
     *
     */
    public function moduleStatic($request, $response)
    {
        $path = $request->getUri()->getPath();

        $segments = explode('/', $path);

        $args['id'] = $segments[5];
        $args['method'] = $segments[6];

        return $this->module($request, $response, $args);
    }
}
