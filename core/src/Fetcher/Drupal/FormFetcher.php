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
class FormFetcher extends AbstractFetcher
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
    public function __construct(Client $client, $host, $logger, $product, $cacher, $factory, $scripts, $configuration)
    {
        parent::__construct($client, $logger, $product, $cacher);

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
        $response = $this->request('GET', "$this->host/forms/view/$id");

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);
        $body = $data['body'];

        return $body;
    }

    /**
     * Gets a Webform List
     */
    public function getFormsList()
    {
        $response = $this->request('GET', "$this->host/forms/list");

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        $body = $data['body'];

        return $body;
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

        $builder = $this->factory->createBuilder('webform');
        return $builder->createForm($id, $fields, $form, $options);
    }

    /**
     * Submits a post request to the data layer form
     *
     * @param string $id   The configuration ID
     * @param array  $data The data to be submitted
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
