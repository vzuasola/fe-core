<?php

namespace App;

use Psr\Http\Message\ServerRequestInterface;
use Psr7Middlewares\Middleware\LanguageNegotiator;
use GuzzleHttp\Client;
use Interop\Container\ContainerInterface;
use App\Controller\SectionsQueryControllerTrait;

/**
 * The Base Controller
 *
 * Common helper methods can be defined here
 */
abstract class BaseController
{
    use SectionsQueryControllerTrait;

    /**
     * Service Container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Public constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Bridge container get.
     *
     * @param string $name
     */
    final public function __get($name)
    {
        return $this->container->get($name);
    }

    /**
     * Alias function to fetch services from a container
     *
     * @return mixed
     */
    final public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Gets a section data from the section manager
     *
     * @param string $index The ID section name
     *
     * @return mixed
     */
    public function getSection($index, $options = [])
    {
        $options += [
            'skip' => false,
        ];

        return $this->container->get('section_manager')->getSection($index, $options);
    }

    /**
     * Gets a form view object
     *
     * @param string $index The form ID
     *
     * @return mixed
     */
    public function getForm($index, $options = [])
    {
        return $this->container->get('form_manager')->getForm($index, $options)->createView();
    }

    /**
     * Forwards request to another controller
     *
     * @param string $controller The controller class name
     * @param string $method The method name
     * @param $args Mostly the request and response objects
     */
    public function forward($controller, $method, ...$args)
    {
        return $this->container->get('resolver')[$controller]->$method(...$args);
    }
}
