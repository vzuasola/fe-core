<?php

namespace App\Controller\GameControllers;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * @deprecated
 */
class FlowGaming extends BaseController
{
    /**
     * Get Game URL
     */
    public function getGameUrl($request, $response, $args)
    {
        $data['gameurl'] = false;
        try {
            $gameCode = $args['gameid'] ?? false;
            if ($gameCode) {
                $responseData = $this->get('game_provider_fetcher')
                            ->getGameUrlById('icore_flg', $gameCode, [
                                'options' => [
                                    'languageCode' => $request->getParam('languageCode', ''),
                                    'platformCode' => $request->getParam('platformCode', null),
                                    'returnUrl' => $request->getParam('returnUrl', null),
                                    'bankingUrl' => $request->getParam('bankingUrl', null),
                                ]
                            ]);

                $data['gameurl'] = $responseData['url'] ?? false;
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $this->get('rest')->output($response, $data);
    }
}
