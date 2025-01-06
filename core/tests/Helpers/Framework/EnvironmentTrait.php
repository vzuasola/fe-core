<?php

namespace Tests\Helpers\Framework;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;

trait EnvironmentTrait
{
    /**
     *
     */
    private function generateEnvironment($requestMethod, $requestUri, $options = [])
    {
        $_SERVER['HTTP_HOST'] = 'localhost';

        $defaultHeaders = [
            'REQUEST_METHOD' => $requestMethod,
            'REQUEST_URI' => $requestUri,
            'HTTP_X_CUSTOM_LB_GEOIP_COUNTRY' => $options['country'] ?? 'KR',
            'QUERY_STRING' => $options['query'] ?? '',
            'CONTENT_TYPE' => $options['content_type'] ?? 'application/json',
        ];

        if (isset($options['headers'])) {
            $headers = array_replace($defaultHeaders, $options['headers']);
        } else {
            $headers = $defaultHeaders;
        }

        $environment = Environment::mock($headers);

        return $environment;
    }
}
