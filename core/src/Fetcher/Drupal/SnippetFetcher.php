<?php

namespace App\Fetcher\Drupal;

use App\Fetcher\AbstractFetcher;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Drupal\Config;
use App\Utils\Host;

/**
 *
 */
class SnippetFetcher extends AbstractFetcher
{
    /**
     *
     */
    const TYPE_JAVASCRIPT = 'js';

    /**
     *
     */
    const TYPE_CSS = 'css';

    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * The route name
     *
     * @var string
     */
    private $route;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     */
    public function __construct(Client $client, $host, $logger, $product, $cacher)
    {
        parent::__construct($client, $logger, $product, $cacher);

        $this->host = $host;
    }

    /**
     * Fetch snippet from API
     *
     * @param  string $string Route name
     * @return array
     */
    public function fetchSnippet($route)
    {
        // use slash for front only
        $route = ($route == '/' || $route == '<front>') ? '/' : "/$route";

        try {
            $response = $this->request('GET', "$this->host/snippet", [
                'query' => [
                    'route' => $route,
                ],
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data['body'];
    }

    /**
     *
     */
    public function getSnippet($path, $type, $position)
    {
        $result = null;

        switch ($type) {
            // CSS
            case self::TYPE_CSS:
                $snippet = $this->formSnippet($path, $position, self::TYPE_CSS);

                $result = "
                    <style>
                        $snippet
                    </style>
                ";
                break;

            // Javascript
            case self::TYPE_JAVASCRIPT:
                $result = $this->formSnippet($path, $position, self::TYPE_JAVASCRIPT);
                break;
        }

        return $result;
    }

    /**
     * Get snippet on API then aggregate values
     *
     * @return void
     */
    private function formSnippet($path, $position, $type)
    {
        $snippet = '';

        try {
            $response = $this->getSnippetByRoute($path);

            if (!empty($response)) {
                // we get the relevant snippet based on the passed position
                foreach ($response as $key => $value) {
                    if ($value['position'] == $position && $value['type'] == $type) {
                        $body = $value['value'];

                        $snippet = "$snippet\n$body";
                    }
                }
            }
        } catch (\Exception $e) {
            // do nothing
        }

        return $snippet;
    }

    /**
     * Get a particular snippet by route
     *
     * @return array
     */
    private function getSnippetByRoute($route)
    {
        $snippets = [];
        $domain = Host::getHostname();
        $data = $this->fetchSnippet($route);

        if (!empty($data)) {
            foreach ($data as $value) {
                if (isset($value['field_body']) &&
                    isset($value['field_type']) &&
                    isset($value['field_position'])
                ) {
                    if (!empty($value['field_marketing_domain'][0]['value'])) {
                        // parse the domain and create snippet if respective domain mentioned
                        $parse = Config::parse($value['field_marketing_domain'][0]['value'] ?? '');
                        if (in_array($domain, $parse)) {
                            $snippets[] = [
                                'value' => $value['field_body'][0]['value'],
                                'type' => $value['field_type'][0]['value'],
                                'position' => $value['field_position'][0]['value'],
                                // Passing this for future use,If any
                                'domain' => $value['field_marketing_domain'][0]['value'],
                            ];
                        }
                    } else {
                        $snippets[] = [
                            'value' => $value['field_body'][0]['value'],
                            'type' => $value['field_type'][0]['value'],
                            'position' => $value['field_position'][0]['value'],
                         ];
                    }
                }
            }
        }
        return $snippets;
    }
}
