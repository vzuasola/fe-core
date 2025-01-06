<?php

namespace App\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

use Slim\HttpCache\Cache;

/**
 *
 */
class HeaderCache
{
    /**
     * The configuration manager object
     */
    private $configuration;

    /**
     * The http cache dependency object
     */
    private $httpCache;

    /**
     * The http cache scope
     *
     * @var string
     */
    private $scope;

    /**
     * The max age of the request
     *
     * @var int
     */
    private $maxAge;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container,
        $scope,
        $maxAge
    ) {
        $this->configuration = $container->get('configuration_manager');
        $this->httpCache = $container->get('http_cache');

        $this->scope = $scope;
        $this->maxAge = $maxAge;
    }

    /**
     *
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $http = new Cache($this->scope, $this->maxAge);

        $response = $http($request, $response, $next);

        return $response;
    }
}
