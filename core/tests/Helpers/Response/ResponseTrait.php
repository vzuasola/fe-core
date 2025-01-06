<?php

namespace Tests\Helpers\Response;

use App\Slim\Response;

/**
 * Main trait for all request related methods
 */
trait ResponseTrait
{
    /**
     *
     */
    public function createResponse($status = 200, $options = [])
    {
        $response = new Response();

        if (isset($options['headers']) && !empty($options['headers'])) {
            foreach ($options['headers'] as $key => $value) {
                $response = $response->withHeader($key, $value);
            }            
        }

        return $response->withStatus($status);
    }
}
