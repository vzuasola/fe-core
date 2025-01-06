<?php

namespace App\Controller;

use App\BaseController;
use App\Async\Async;

class MenuWidgetController extends BaseController
{
    /**
     * Route for /api/menu/widgets
     */
    public function widgets($request, $response)
    {
        $data['widgets'] = $this->get('views_fetcher_async')->getViewById('webcomposer_dropdown_menu');

        $data = Async::resolve($data);

        $markup = [];

        if (!empty($data['widgets'])) {
            $list = $this->get('menu_widget_manager')->getWidgets();

            foreach ($data['widgets'] as $key => $value) {
                if (isset($list[$key])) {
                    $instance = $list[$key];

                    $fetch = $instance->alterData($value['settings']);
                    $template = $instance->getTemplate();

                    $markup['widgets'][$key]['markup'] = $this->get('view')->fetch($template, $fetch);

                    if (method_exists($instance, 'getScript')) {
                        $script = getcwd() . DIRECTORY_SEPARATOR . $instance->getScript();

                        if (file_exists($script)) {
                            $markup['widgets'][$key]['script'] = file_get_contents($script);
                        }
                    }
                }
            }
        }

        return $this->view
            ->render($response, '@base/components/mega-menu/widget-container.html.twig', $markup)
            ->withHeader('X-Webcomposer-Dropdown', 'true');
    }
}
