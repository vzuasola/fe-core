<?php

namespace App\Rest;

use Slim\Http\Stream;

class Resource
{
    /**
     * Request object
     */
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Output the data
     *
     * @param $response
     * @param $data
     *
     * @return json (for enhancement)
     */
    public function output($response, array $data)
    {
        // Currently, we'll just output json
        // TODO: Make this agnostic to any request
        return $response->withJson($data);
    }

    /**
     *
     */
    public function raw($response, $string)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $string);

        $newStream = new Stream($stream);

        return $response->withBody($newStream);
    }

    /**
     *
     */
    public function js($response, $string)
    {
        $response->write($string);

        return $response->withHeader('Content-type', 'application/javascript');
    }
}
