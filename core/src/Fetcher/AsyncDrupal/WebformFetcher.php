<?php

namespace App\Fetcher\AsyncDrupal;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use Symfony\Component\Form\FormBuilderInterface;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use App\Utils\IP;
use App\Fetcher\AbstractFetcher;

/**
 *
 */
class WebformFetcher extends AbstractFetcher
{
    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * Script manager object
     *
     * @var object
     */
    private $scripts;

    /**
     * List of form configurations
     *
     * @var array
     */
    private $configurations;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     */
    public function __construct(Client $client, $host, $logger, $product, $factory, $scripts, $configuration)
    {
        parent::__construct($client, $logger, $product);

        $this->client = $client;
        $this->host = $host;
        $this->factory = $factory;
        $this->scripts = $scripts;
        $this->configurations = $configuration->getConfiguration('forms');
    }

    /**
     * Gets a form data by ID
     *
     * @param string $id The configuration ID
     */
    public function getDataById($id)
    {
        $uri = "$this->host/forms/view/$id";

        return $this->createRequest($this->client, 'GET', $uri, [], function ($data, $options) {
            $data = json_decode($data, true);
            $body = $data['body'];

            return $body;
        }, true);
    }

    /**
     * Gets a Webform List
     */
    public function getFormsList()
    {
        $uri = "$this->host/forms/list";

        return $this->createRequest($this->client, 'GET', $uri, [], function ($data, $options) {
            $data = json_decode($data, true);
            $body = $data['body'];

            return $body;
        }, true);
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
        $definition = $this->getDataById($id);
        $self = clone $this;

        return $definition->withCallback(function ($data, $options) use ($id, $form, $self) {
            $fields = $data['elements'];

            return $self->createFormFromBuilder($id, $form, $fields);
        });
    }

    /**
     *
     */
    public function createFormFromBuilder($id, $form, $fields)
    {
        $builder = $this->factory->createBuilder('webform');

        return $builder->createForm($id, $fields, $form, $options);
    }

    /**
     * Submits a post request to the data layer form
     *
     * @param string $id The configuration ID
     * @param array $data The data to be submitted
     */
    public function sumbitFormById($id, $data)
    {
        $post = $data;

        $post['webform_id'] = $id;
        $post['remote_addr'] = IP::getIpAddress();

        try {
            $response = $this->request('POST', "$this->host/forms/submit", [
                'json' => $post
            ]);
        } catch (GuzzleException $e) {
            throw $e;
        }

        return $response;
    }
}
