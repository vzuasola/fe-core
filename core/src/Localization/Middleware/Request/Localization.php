<?php

namespace App\Localization\Middleware\Request;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Plugins\Middleware\RequestMiddlewareInterface;

/**
 * Localization middleware
 * This will manage and detect any localized language to be used on content localization
 */
class Localization implements RequestMiddlewareInterface
{
    /**
     * Localized Language class
     */
    protected $localization;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->localization = $container->get('localization');
    }

    /**
     * {@inheritdoc}
     */
    public function boot(RequestInterface &$request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface &$request, ResponseInterface &$response)
    {
        $this->localization->setResponseHeader($response);
    }
}
