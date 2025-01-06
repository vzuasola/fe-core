<?php

namespace App\Controller\GameControllers;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Controller for Solid gaming
 */
class SolidGaming extends BaseController
{
    /**
     * Get Game URL
     */
    public function getGameUrl($request, $response, $args)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $providerProduct = $request->getParam('providerProduct', null);

        try {
            $this->provider = $this->get('game_provider_fetcher');

            $responseData = $this->provider->getGameUrlById('icore_sg', $args['gameid'], [
                'options' => [
                    'languageCode' => $languageCode,
                    'providerProduct' => $providerProduct
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
