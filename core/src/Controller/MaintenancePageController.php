<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use App\Utils\Url;
use App\Drupal\Config;
use App\Async\Async;

class MaintenancePageController extends BaseController
{
    use BaseSectionTrait;

    /**
     * Get Soft maintenance configs
     *
     * @param  $request
     * @param  $response
     * @return json
     */
    public function getMaintenanceConfig($request, $response)
    {
        try {
            $data = $this->pageSections([
                'config' => $this->get('config_fetcher_async')
                    ->getGeneralConfigById('webcomposer_site_maintenance')
            ]);

            // Use the site_product attribute to get the correct
            $product_key = $request->getAttribute('site_product');
            $maintenanceData = [
                'title' => $data['config']['maintenance_title_' . $product_key] ?? 'Site Under Maintenance',
                'content' => $data['config']['maintenance_content_' . $product_key ]['value'],
            ];
            $data['maintenance'] = $maintenanceData;
            $data['title'] = $maintenanceData['title'];

            return $this->view->render($response, '@base/components/site-maintenance.html.twig', $data);
        } catch (\Exception $e) {
            // Do nothing
        }
    }
}
