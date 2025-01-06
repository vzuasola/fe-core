<?php

namespace App\Middleware\Cache;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use Slim\Http\Stream;
use Slim\HttpCache\Cache;
use Slim\Http\Body;

use App\Plugins\Middleware\RequestMiddlewareInterface;
use App\Plugins\Middleware\ResponseMiddlewareInterface;

/**
 * Site Maintenance middleware
 */
class SiteMaintenance implements RequestMiddlewareInterface, ResponseMiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(ContainerInterface $container)
    {
        $this->handler = $container->get('handler');
        $this->configs = $container->get('config_fetcher')->getGeneralConfigById('webcomposer_site_maintenance');
        $this->node_utils = $container->get('node_utils');
        $this->product = $container->get('product');
        $this->session = $container->get('session');
        $this->settings = $container->get('settings');
    }

    /**
     * {@inheritdoc}
     */
    public function boot(RequestInterface &$request)
    {
        $site_product = $this->settings['product'];
        try {
            // Add a new request attribute
            // site_product is mapped to either the repository product or
            // another product that is within the same repository (shared repository)
            if (isset($this->settings['keyword']['mapping'])) {
                foreach ($this->settings['keyword']['mapping'] as $product_key => $keywords) {
                    if (in_array($this->product, $keywords)) {
                        $site_product = $product_key;
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        $request = $request->withAttribute('site_product', $site_product);
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response)
    {
        try {
            if ($this->isEligible($request)) {
                $products = explode("\r\n", $this->configs['product_list']);
                $product = str_replace(' ', '', strtolower($request->getAttribute('site_product')));
                $dates =[
                    'field_publish_date' => $this->configs['maintenance_publish_date_' . $product],
                    'field_unpublish_date' => $this->configs['maintenance_unpublish_date_' . $product],
                ];

                if (in_array($product, $products) && $this->scheduledMaintenance($dates)) {
                    // Add the maintenance flag
                    $request = $request->withAttribute('is_maintenance', true);
                    if ($this->session->hasFlash('login.error')) {
                        // Pass flash messages
                        $request = $request->withAttribute(
                            'login.error',
                            $this->session->getFlash('login.error')
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            // Do nothing
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        try {
            // Override only if the supposed response is 200
            if ($response->getStatusCode() === 200 && $request->getAttribute('is_maintenance', false) === true) {
                // Remove processed body stream that came from controller
                $stream = fopen('php://memory', 'r+');
                fwrite($stream, '');
                $response = $response->withBody(new Body($stream));

                // Re-add flash messages
                if ($fmessage = $request->getAttribute('login.error', false)) {
                    $this->session->setFlash('login.error', $fmessage);
                }

                // Call event
                $event = $this->handler->getEvent('site_maintenance');
                $response = $event($request, $response);
            }
        } catch (\Exception $e) {
            // Do nothing
        }
    }

    /**
     * Method to check if a request is eligble for maintenance.
     *
     * Only pages that returns html are eligble for maintenancePage, most if not all APIs are excluded.
     */
    private function isEligible($request)
    {
        return (
            !$request->isXhr() &&
            strpos($request->getUri()->getPath(), '/ajax/') === false &&
            $this->configs['product_list']
        );
    }

    /**
     * Check if the site is within the scheduled maintenance
     */
    private function scheduledMaintenance($data)
    {
        if (empty($data['field_publish_date']) && empty($data['field_unpublish_date'])) {
            return false;
        } elseif ($data['field_unpublish_date']) {
            return $data['field_publish_date'] <= strtotime(date('m/d/Y H:i:s')) &&
            $data['field_unpublish_date'] >= strtotime(date('m/d/Y H:i:s'));
        } else {
            return $data['field_publish_date'] <= strtotime(date('m/d/Y H:i:s'));
        }
    }
}
