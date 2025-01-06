<?php

namespace Tests\Helpers\Request;

use Slim\Http\Request;

use Psr\Http\Message\ServerRequestInterface;
use Psr7Middlewares\Middleware;
use Slim\Http\RequestBody;

/**
 * Main trait for all request related methods
 */
trait RequestTrait
{
    /**
     * Creates a mock request object
     *
     * @param string $method The request method to use 'GET|POST|PUT|PATCH|DELETE'
     * @param string $uri The URI of this request
     * @param array $options Additional options passed to the request
     *
     * @return ServerRequestInterface
     */
    public function createRequest($method, $uri, $options = [])
    {
        $environment = $this->generateEnvironment($method, $uri, $options);
        $request = Request::createFromEnvironment($environment);

        if (isset($options['prefix'])) {
            $request = $request->withAttribute('prefix', $options['prefix']);
            $request = $this->setRequestPrefix($request, $options['prefix']);
        }

        if (isset($options['language'])) {
            $request = $request->withAttribute('language', $options['language']);
            $request = $this->setRequestLanguage($request, $options['language']);
        }

        if (isset($options['product'])) {
            $request = $request->withAttribute('product', $options['product']);
            $request = $this->setRequestProduct($request, $options['product']);
        }

        if (!empty($options['attributes'])) {
            $attributes = $options['attributes'];

            foreach ($attributes as $key => $attribute) {
                $request = $request->withAttribute($key, $attribute);
                $request = $this->setRequestAttribute($request, $key, $attribute);
            }
        }

        if (isset($options['body'])) {
            $body = new RequestBody();
            $body->write($options['body']);
            $body->rewind();
            $request = $request->withBody($body);
        }

        return $request;
    }

    /**
     *
     */
    public function setRequestPrefix(Request $request, $prefix)
    {
        return $this->setRequestAttribute($request, 'PREFIX', $prefix);
    }

    /**
     *
     */
    public function setRequestLanguage(Request $request, $language)
    {
        return $this->setRequestAttribute($request, 'LANGUAGE', $language);
    }

    /**
     *
     */
    public function setRequestProduct(Request $request, $product)
    {
        return $this->setRequestAttribute($request, 'PRODUCT', $product);
    }

    /**
     *
     */
    protected function setRequestAttribute(Request $request, $name, $value)
    {
        $attributes = $request->getAttribute(Middleware::KEY, []);
        $attributes[$name] = $value;

        return $request->withAttribute(Middleware::KEY, $attributes);
    }
}
