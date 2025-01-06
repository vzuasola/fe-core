<?php

// General site settings
$settings = [
    'settings' => [
        'debug' => false, // flag to tell whether debug is enable or not

        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => [
                'asset' => APP_ROOT . 'core/assets/',
                'base' => APP_ROOT . 'core/templates/dafabet/',
                'responsive' => APP_ROOT . 'core/templates/dafabet-responsive/',
                'japan' => APP_ROOT . 'core/templates/japan/'
            ],
            // 'cache_path' => APP_ROOT . '/cache/',
            'cache_path' => false,
        ],

        // Language settings
        'languages' => [
            // if specified, languages will be fetched from this list instead
            // 'supply_languages_list' => [
            //     'en' => [
            //         'name' => 'English',
            //         'id' => 'en',
            //         'prefix' => 'en',
            //     ],
            //     'zh-hans' => [
            //         'name' => 'Chinese',
            //         'id' => 'zh-hans',
            //         'prefix' => 'sc',
            //     ],
            // ],
        ],

        // Cache component
        'cache' => [
            'handler' => 'native', // available options are 'native' and 'predis'

            'default_timeout' => 1800,

            // option for the handler 'predis'
            // 'handler_options' => [
            //     'clients' => [
            //         'tcp://10.55.38.191:26379',
            //         'tcp://10.55.38.191:26380',
            //         'tcp://10.55.38.191:26381',
            //     ],
            //     'options' => [
            //         'replication' => 'sentinel',
            //         'service' => 'pushnx',
            //         'parameters' => ['database' => 2],
            //         'prefix' => 'session:front',
            //     ],
            // ],

            // option for the handler 'native'
            'handler_options' => [
                'namespace' => '',
                'lifetime' => 0,
                'path' => BASE_ROOT . "/cache/",
            ]
        ],

        // Cache signature settings
        'cache_signature' => [
            'enable' => true,

            // redis specific options
            'redis_options' => [
                'clients' => 'tcp://127.0.0.1:6379',
                'options' => [
                    'parameters' => ['database' => 1],
                ],
            ],
        ],

        // Settings for configuration
        'configurations' => [
            'inheritance' => true, // enable or disable inheritance of configuration
        ],

        // PHP error handler settings
        // specify what will handle PHP fatal errors
        'error_handler' => [
            'type' => 'monolog', // valid options are false|'monolog'
            'options' => [
                'channel' => 'default',
            ],
        ],

        // Monolog settings
        'logger' => [
            // if set to true will silence the log mechanism
            // 'disable' => true,

            'name' => 'webcomposer_front',
            'path' => BASE_ROOT . '/logs/app.log',
            'level' => \Monolog\Logger::ERROR,

            // the monolog class for the default channel
            'default_channel' => \App\Monolog\Channels\Stream::class,

            // array of classes
            'channels' => [
                'workflow' => \App\Monolog\Channels\Workflow::class
            ],
        ],

        // Metrics log settings
        'metrics_log' => [
            'name' => 'workflow',
            'level' => \Monolog\Logger::INFO,
            'path' => BASE_ROOT . '/logs/workflow.log',
        ],

        // Product code
        'product' => 'dafabet',
        // 'product_url' => 'wc', // specify the product value in the URL

        // Specify which path will have the product prefixing excluded
        // this means that on 'mobile' path, use 'promotions' as the product
        // substitute (mainly used for URL generation)

        // 'product_exclusions' => ['mobile' => 'promotions'],

        // Platform type
        'platform' => 'desktop', // determines which platform type this instance is

        // Session related settings
        'session' => [
            'timeout' => 5 * 60, // 5 minutes
            'lazy' => false, // specify if we create a session for non logged in users
        ],

        // PHP session handler related settings
        'session_handler' => [
            'cookie_domain' =>  \App\Utils\Host::getDomain(),
            'cache_limiter' => false,

            // Predis related settings
            'handler' => 'native', // valid options are 'native'|'redis'|'predis'

            // option for the handler of choice
            // 'handler_options' => [
            //     'clients' => [
            //         'tcp://10.55.38.191:26379',
            //         'tcp://10.55.38.191:26380',
            //         'tcp://10.55.38.191:26381',
            //     ],
            //     'options' => [
            //         'replication' => 'sentinel',
            //         'service' => 'pushnx',
            //         'parameters' => ['database' => 8],
            //         'prefix' => 'session:front',
            //     ],
            //     'parameters' => [
            //         'gc_probability' => 1,
            //         'gc_divisor' => 100,
            //         'gc_maxlifetime' => 200000,
            //     ]
            // ]
        ],

        // Prefix exclusion
        // Path specified here will be excluded from the language and product
        // prefixes
        // 'prefix_exclusion' => [
        //     '/api/sso/*',
        // ],

        // When specified, an empty language on the URL will not auto redirect
        // but will supply the specified language instead
        // 'supply_language_on_empty' => 'sc',

        // You need to provide the acceptable languages when supply_language_on_empty
        // is enabled, this is to detech which language codes are valid or not
        'acceptable_languages' => [
            'en', 'sc', 'ch', 'th', 'kr', 'in', 'vn', 'id', 'ja', 'jp', 'pl', 'gr', 'es',
        ],

        // Asset related settings
        'asset' => [
            'allow_prefixing' => true, // Must be eneble to allow prefixed
            'prefixed_drupal' => false, // flag to force all assets from Drupal to resolve
            'prefixed' => true, // check if all assets will be prefixed
            // 'product_prefix' => 'dafabet' // specify product prefix
            // 'custom_drupal_prefix' => 'http://something.com', // if specify will use this as the custom drupal prefix
        ],

        // Single sign on

        'sso' => [
            'enable' => false,
        ],

        // Page Caching

        'page_cache' => [
            'enable' => false,

            // the default timeout for all pages in seconds
            'default_timeout' => 1800,

            // flag to determine if the page cache automated computed header
            // should be included
            'include_cache_control_headers' => false,

            // define the directive to use replacement variables
            // $time => the elapsed time of the cached request
            'cache_control_directive' => 'public, max-age=$time',
        ],

        // Affiliate Tracking

        'tracking' => [
            'enable' => false,
        ],

        // Dafabet Connect

        'dafaconnect' => [
            'enable' => false, // flag to enable or disable the dafaconnect detection
        ],

        // Components

        'components' => [
            'async' => true, // enable processing of async classes for components

            'renderer' => [
                // defines the rendering mode, values can be
                // 'render' - components will be rendered immediately on the client side
                // 'prerender' - components will not be rendered on the server side
                'mode' => 'render',

                // defines the component that will be preloaded, only works if mode is
                // set to prerender
                'preload' => ['header', 'footer'],
            ],

            'router' => [
                // defines an array of HTTP headers that will be appended when
                // a component is being called via AJAX
                // 'widget_headers' => [
                //     'Cache-Control' => 'private, max-age=300',
                // ],
            ],
        ],

        // Fetchers

        'fetchers' => [
            'enable_permanent_caching' => false, // if enabled will cache fetcher responses to a cache adapter
        ],
    ],
];
