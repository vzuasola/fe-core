<?php

namespace App\Middleware\Response;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Slim\Http\Body;

use App\Plugins\Middleware\ResponseMiddlewareInterface;

use App\Drupal\Config;

/**
 *
 */
class UnsupportedCurrency implements ResponseMiddlewareInterface
{
    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->handler = $container->get('handler');
        $this->playerSession = $container->get('player_session');
        $this->ucp = $container->get('config_fetcher')->getGeneralConfigById('page_not_found');
    }

    /**
     * {@inheritdoc}
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        try {
            if ($this->isEligible($request, $response)) {
                $currencyMap = explode("\r\n", $this->ucp['currency_mapping']);
                $currency = $this->playerSession->getDetails()['currency'] ?? null;
                if (in_array($currency, $currencyMap)) {
                    // Remove processed body stream that came from controller
                    $stream = fopen('php://memory', 'r+');
                    fwrite($stream, '');
                    $response = $response->withBody(new Body($stream));

                    // Call event
                    $event = $this->handler->getEvent('unsupported_currency');
                    $response = $event($request, $response);
                }
            }
        } catch (\Exception $e) {
            // Do nothing
        }
    }

    /**
     * Method to check if a request is eligble for UCP.
     *
     * Only pages that returns html are eligble for unsupported currency page, most if not all APIs are excluded.
     */
    private function isEligible($request, $response)
    {
        return (
            $this->playerSession->isLogin() &&
            // Make sure request is NOT ajax
            !$request->isXhr() &&
            // Override only if the supposed response is 200
            $response->getStatusCode() === 200 &&
            // Override only pages that has NO /ajax/ in its path
            strpos($request->getUri()->getPath(), '/ajax/') === false &&
            $this->ucp['currency_mapping']
        );
    }
}
