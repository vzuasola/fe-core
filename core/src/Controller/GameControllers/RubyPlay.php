<?php

namespace App\Controller\GameControllers;

use App\Slim\Response;
use Slim\Http\Request;

class RubyPlay extends BaseGameController
{
    const DRUPAL_KEY = 'ruby_play';

    /**
     * Get Game URL
     */
    public function getGameUrl(Request $request, Response $response, $args)
    {
        $data['gameurl'] = false;
        $languageCode = $request->getParam('languageCode');
        $providerProduct = $request->getParam('providerProduct', null);

        try {
            $this->provider = $this->get('game_provider_fetcher');
            $manageProvider = $this->get('game_provider_manager')->getProviders()['ruby_play'];

            $responseData = $this->provider->getGameUrlById('icore_rp', $args['gameid'], [
                'options' => [
                    'languageCode' => $languageCode,
                    'providerProduct' => $providerProduct
                ]
            ]);

            if ($responseData) {
                $url = $manageProvider->overrideGameUrl($request, $responseData['url']);
                $data['gameurl'] = $url ?? $responseData['url'];
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }
}
