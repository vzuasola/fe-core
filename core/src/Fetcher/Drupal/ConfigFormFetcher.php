<?php

namespace App\Fetcher\Drupal;

use Symfony\Component\Form\FormBuilderInterface;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use App\Utils\IP;
use App\Fetcher\AbstractFetcher;

/**
 *
 */
class ConfigFormFetcher extends AbstractFetcher
{
    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * Propery caching
     *
     * @var array
     */
    private $cache;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     */
    public function __construct(Client $client, $host, $logger, $product, $cacher, $factory)
    {
        parent::__construct($client, $logger, $product, $cacher);

        $this->host = $host;
        $this->factory = $factory;
    }

    /**
     * Gets a form data by ID
     *
     * @param string $id The configuration ID
     */
    public function getDataById($id)
    {
        if (!isset($this->cache[$id])) {
            try {
                $response = $this->request('GET', "$this->host/webcomposer_forms/view/$id");
            } catch (GuzzleException $e) {
                throw $e;
            }

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $body = $data['body'];

            $this->cache[$id] = $body;
        }

        return $this->cache[$id];
    }

    /**
     * Generates a form by ID
     *
     * @param string $id The configuration ID
     * @param $form The form definition object
     * @param $options Additional associative array to be passed to the form builder
     */
    public function getFormById($id, FormBuilderInterface $form = null, $options = [])
    {
        $body = $this->getDataById($id);
        $fields = $body['elements'];

        $builder = $this->factory->createBuilder('configurable_form');

        return $builder->createForm($id, $fields, $form, $options);
    }
}
