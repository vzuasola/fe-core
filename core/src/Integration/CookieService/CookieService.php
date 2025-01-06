<?php

namespace App\Integration\CookieService;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use App\Integration\CookieService\LogTrait;

/**
 * Cookie Service
 */
class CookieService
{
    use LogTrait;

    /**
     * Guzzle HTTP client
     *
     * @var object
     */
    private $client;

    /**
     * Dynamic monolog logger object
     *
     * @var object
     */
    private $logger;

    /**
     * Request Object
     */
    private $request;

    /**
     * Container resolver
     */
    public static function create($container)
    {
        $baseUrl = $container->get('request')->getUri()->getBaseUrl();
        $hostname = $container->get('parameters')['appsvc.origin.prd'];
        // for mobile domain lower environment overriding cookie service url
        if (preg_match('/https?:\/\/(?<env>[a-z0-9]+)-(mobile|m|www)/', $baseUrl, $matches)) {
            $override = $container->get('parameters')['appsvc.origin.' . $matches['env']];

            if (!empty($override)) {
                $hostname = $override;
            }
        }

        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'base_uri' => "$hostname:50982",
        ]);

        return new static($client, $container->get('logger'), $container->get('request'));
    }

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param object
     */
    public function __construct(Client $client, $logger, $request)
    {
        $this->client = $client;
        $this->logger = $logger; //@see LogTrait
        $this->request = $request;
    }

    /**
     * Cookie cutter - generates JWT
     *
     * @param string $name
     *
     * @return string
     */
    public function cut($data, $uri = '/api/v1/cookie/cut')
    {
        try {
            $response = $this->client->request('POST', $uri, [
                'form_params' => $data,
            ]);

            $body = $response->getBody()->getContents();
            $output = json_decode($body, true);

            $this->logInfo('Success', $uri, $data, $body);

            return $output['body'] ?? [];
        } catch (ConnectException $e) {
            $this->logException('Connection Error', $uri, $data, $e);

            throw $e;
        } catch (ServerException $e) {
            $this->logException('Server Error', $uri, $data, $e);

            throw $e;
        } catch (ClientException $e) {
            $this->logException('Client Error', $uri, $data, $e);

            throw $e;
        } catch (RequestException $e) {
            $this->logException('Request Error', $uri, $data, $e);

            throw $e;
        } catch (\Exception $e) {
            $this->logException('Generic Error', $uri, $data, $e);

            throw $e;
        }
    }

    /**
     * Validates request body.
     *
     * @param array $data
     */
    public function validate($data)
    {
        if (isset($data['username']) && empty($data['username'])) {
            throw new \UnexpectedValueException('Empty username.');
        }

        if (isset($data['playerId']) && empty($data['playerId'])) {
            throw new \UnexpectedValueException('Empty playerId.');
        }

        if (isset($data['sessionToken']) && empty($data['sessionToken'])) {
            throw new \UnexpectedValueException('Empty sessionToken.');
        }
    }
}
