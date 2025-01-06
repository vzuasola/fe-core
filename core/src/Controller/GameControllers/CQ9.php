<?php

namespace App\Controller\GameControllers;

use App\BaseController;
use App\Slim\Response;
use Slim\Http\Request;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;


/**
 * Controller for CQ9
 */
class CQ9 extends BaseGameController
{
    const DRUPAL_KEY = 'cq9';


    /**
     * Get Game URL
     */
    public function getGameUrl(Request $request, Response $response, $args)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $providerProduct = $request->getParam('providerProduct', null);
        $version = $request->getParam('version', 'v1');

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getGameUrlById('icore_cq9', $args['gameid'], [
                'options' => [
                    'languageCode' => $languageCode,
                    'providerProduct' => $providerProduct,
                    'version' => $version,
                ]
            ]);

            if ($responseData['url']) {
                $data['gameurl'] = $responseData['url'];
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }
}
