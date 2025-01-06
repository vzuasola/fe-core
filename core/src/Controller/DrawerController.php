<?php

namespace App\Controller;

use App\BaseController;
use App\Async\Async;

class DrawerController extends BaseController
{
    /**
     * Route for /api/drawer/{id}
     */
    public function drawer($request, $response, $args)
    {
        $id = $args['id'];

        $data = $this->searchEntity($request, $id);

        return $this->get('rest')->output($response, $data);
    }

    /**
     * Gets a single drawer entity by ID
     */
    private function searchEntity($request, $id)
    {
        $views = $this->get('views_fetcher')->getViewById('webcomposer_drawer_entity');

        foreach ($views as $view) {
            if ($view['field_drawer_id'][0]['value'] === $id) {
                if ($view['field_drawer_button']) {
                    $drawerButtonUri = $view['field_drawer_button'][0]['uri'];
                    $drawerButtonUri = $this->get('uri')->generateFromRequest($request, $drawerButtonUri, []);
                    $view['field_drawer_button'][0]['uri'] = $drawerButtonUri;
                }

                $view['field_drawer_media'] = $this->getDrawMedia($view);

                return $view;
            }
        }

        return [];
    }

    /**
     * Get enabled drawer media
     */
    private function getDrawMedia($view)
    {
        $items = [];
        $data = $view['field_drawer_media'] ?? [];

        foreach ($data as $item) {
            if (empty($item['field_disable'][0]['value'])) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
