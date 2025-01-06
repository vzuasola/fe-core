<?php

namespace App;

use App\Slim\App;
use App\Slim\Response;
use App\Parameter\Parameters;

/**
 * Slim Custom Application Kernel
 *
 * You can add custom logic here that will be executed very early on the
 * application bootstrap phase (well until I put in Kernel extension to
 * make thing extendable)
 */
class Kernel
{
    /**
     * Static variable containers
     */
    private static $app;
    private static $container;
    private static $env;

    /**
     * The default environment
     */
    private $environment;

    /**
     * The settings files location
     */
    private $settings;
    private $dependencies;
    private $handlers;
    private $middleware;
    private $routes;
    private $parameters;

    /**
     * Public constructor
     */
    public function __construct($environment = 'prod')
    {
        // define the app root relative to core directory
        define('APP_ROOT', __DIR__ . '/../../');

        // define the base root relative to core directory
        define('BASE_ROOT', __DIR__ . '/../../../');

        // define the web root relative to public directory
        define('WEB_ROOT', getcwd() . '/..//');

        self::$env = $environment;
        $this->environment = $environment;

        $this->settings = APP_ROOT . '/core/app/settings.php';
        $this->dependencies = APP_ROOT . '/core/app/dependencies.php';
        $this->handlers = APP_ROOT . '/core/app/handlers.php';
        $this->middleware = APP_ROOT . '/core/app/middleware.php';
        $this->parameters = APP_ROOT . '/core/app/parameters.php';
    }

    /**
     * Gets the current app instance
     *
     * @return object
     */
    public static function app()
    {
        return self::$app;
    }

    /**
     * Gets the current environment
     *
     * @return string
     */
    public static function environment()
    {
        return strtoupper(self::$env);
    }

    /**
     * Gets the current container instance
     *
     * @return object
     */
    public static function container()
    {
        return self::$container;
    }

    /**
     * Sets the service container
     */
    public static function setContainer($container)
    {
        self::$container = $container;
    }

    /**
     * Gets the profiler object
     *
     * @return object
     */
    public static function profiler()
    {
        return self::container()->get('profiler');
    }

    /**
     * Gets the logger object
     *
     * @param string $channel The name of the channel
     *
     * @return object
     */
    public static function logger($channel = 'default')
    {
        $instance = self::container()->get('logger');

        return $instance($channel);
    }

    /**
     * Setter
     *
     * @param string $path
     */
    public function setConfigRoot($path)
    {
        define('CONFIG_ROOT', $path);
    }

    /**
     * Setter
     *
     * @param string $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Setter
     *
     * @param string $dependencies
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * Setter
     *
     * @param string $handlers
     */
    public function setHandlers($handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * Setter
     *
     * @param string $middleware
     */
    public function setMiddleware($middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * Setter
     *
     * @deprecated Routes.php will be deprecated soon, please use Routes.yml
     *             instead
     *
     * @param string $route
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }

    /**
     * Setter
     *
     * @param string $parameter
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Gets the app instance
     */
    public function bootstrap()
    {
        // To help the built-in PHP dev server, check if the request was actually
        // for something which should probably be served as a static file
        if (PHP_SAPI == 'cli-server') {
            $url  = parse_url($_SERVER['REQUEST_URI']);
            $file = __DIR__ . $url['path'];

            if (is_file($file)) {
                return false;
            }
        }

        require $this->settings;

        // require the per environment setting overrides if it exists
        $env = strtoupper($this->environment);
        if (file_exists(APP_ROOT . "/core/app/settings.php.$env")) {
            require APP_ROOT . "/core/app/settings.php.$env";
        }

        require $this->parameters;

        // enable the session management
        $this->handleSession($settings, $parameters);

        $app = new App($settings);
        $container = $app->getContainer();

        // assign the container
        self::$container = $container;

        $container['response'] = function ($container) {
            $headers = new \Slim\Http\Headers(['Content-Type' => 'text/html; charset=UTF-8']);
            $response = new Response(200, $headers);

            return $response->withProtocolVersion($container->get('settings')['httpVersion']);
        };

        $container['parameters'] = function () use ($parameters) {
            return new Parameters($parameters);
        };

        require $this->dependencies;
        require $this->handlers;
        require $this->middleware;

        if (isset($this->routes)) {
            require $this->routes;
        }

        $this->registerErrorHandler($app);

        return $app;
    }

    /**
     * Register the error handler
     *
     * TODO Move this somewhere else, maybe a kernel session extension ?
     * But for now stay here, cause I need you to work ASAP
     */
    private function registerErrorHandler($app)
    {
        $container = $app->getContainer();

        $guard = new \Zeuxisoo\Whoops\Provider\Slim\WhoopsGuard();

        $guard->setApp($app);
        $guard->setRequest($container['request']);
        $guard->setHandlers([]);
        $guard->install();
    }

    /**
     * Handles the session
     *
     * TODO Move this somewhere else, maybe a kernel session extension ?
     * But for now stay here, cause I need you to work ASAP
     */
    private function handleSession($settings, $parameters)
    {
        $settings = $settings['settings'];

        if ($settings['session_handler']['handler'] == 'predis') {
            $client = new \Predis\Client(
                $settings['session_handler']['handler_options']['clients'],
                $settings['session_handler']['handler_options']['options']
            );

            $handler = new \Predis\Session\Handler(
                $client,
                $settings['session_handler']['handler_options']['parameters']
            );

            session_set_save_handler($handler);
        }

        // start the session
        session_cache_limiter($settings['session_handler']['cache_limiter']);
    }

    /**
     * Bootstraps and runs the Slim application
     */
    public function run()
    {
        if (isset($_SERVER['HTTP_X_REAL_HOST'])) {
            $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_REAL_HOST'];
        }

        $app = $this->bootstrap();

        // assign the app instance
        self::$app = $app;

        return $app->run();
    }
}
