<?php

namespace App\Middleware\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use App\Plugins\Middleware\ResponseMiddlewareInterface;

/**
 * Handles last visited product cookie handling
 */
class PartnerMatrixRedirection implements ResponseMiddlewareInterface
{
    /**
     * Default dsb keyword
     */
    private $defaultSportsProduct = 'sports-df';

    /**
     * Enabled Products
     */
    private $enabledProducts = [
        'entry',
        'games',
        'live-dealer',
        'keno',
        'dafa-sports',
        'registration'
    ];

    /**
     * Latam language prefixes
     */
    private $latamLanguages = ['es','pt'];

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->request = $container->get('router_request');
        $this->playerSession = $container->get('player_session');
        $this->player = $container->get('player');
        $this->lang = $container->get('lang');
        $this->settings = $container->get('settings');
    }

    /**
     *
     */
    public function handleResponse(RequestInterface &$request, ResponseInterface &$response)
    {
        try {
            // Check if player is still logged in, and current product lobby
            // is NOT enabled for Agent Player
            if ($this->playerSession->isLogin()) {
                $isPlayerCreatedByAgent = $this->player->getIsPlayerCreatedByAgent();
                $status = $response->getStatusCode();
                $isXhr = $request->isXhr();

                // Check if Player was created via a Partner Agent
                if ($isPlayerCreatedByAgent && ($status === 200) && !$isXhr) {
                    $queryParams = http_build_query($request->getQueryParams());
                    if ($queryParams) {
                        $queryParams = '?' . $queryParams;
                    }

                    $product = $this->settings['product'];
                    $uri = $request->getUri()->getPath();
                    $keyword = $response->getAttribute('product');
                    $isLatamLanguage = in_array($this->lang, $this->latamLanguages);
                    $isProductEnabled = in_array($product, $this->enabledProducts);

                    if (!$isProductEnabled) {
                        switch ($product) { // disabled site, direct-access redirection
                            case 'casino':
                            case 'casino-gold':
                            case 'poker':
                            case 'soda-casino':
                                $lang = $isLatamLanguage ? 'en' : $this->lang;
                                $path = "$lang/live-dealer";
                                break;
                            default:
                                $lang = $isLatamLanguage ? 'en' : $this->lang;
                                $path = "$lang/" . $this->defaultSportsProduct;
                        }
                    } elseif ($isLatamLanguage) {
                        switch ($product) {
                            case 'entry':
                                $path = 'en';
                                break;
                            case 'registration':
                                $path = $uri === "/vip"? "en$uri" : "en/$keyword$uri";
                                break;
                            default:
                                $path = "en/$keyword";
                        }
                    }

                    if (isset($path)) {
                        $path = "/$path$queryParams";
                        $response = $response->withRedirect($path, 302);

                        return $response;
                    }
                }
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $response;
    }
}
