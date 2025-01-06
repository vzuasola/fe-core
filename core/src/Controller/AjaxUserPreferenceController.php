<?php

namespace App\Controller;

use App\BaseController;
use App\Async\Async;

class AjaxUserPreferenceController extends BaseController
{
    /**
     * Handles the status of geoip language popup per geoip
     * and saving of new preferred language to my account
     */
    public function saveGeoIpLanguage($request, $response)
    {
        $data = ['success' => false];

        try {
            if ($this->player_session->isLogin()) {
                $langcode = $request->getParam('langcode') ?? null;
                $geoIp = $request->getParam('geoip') ? strtolower($request->getParam('geoip')) : null;
                $settings = Async::resolve([
                    'settings' => $this->get('config_fetcher_async')->getGeneralConfigById('geoip_language_popup')
                ]);
                $geoIpList = array_map('trim', explode(PHP_EOL, $settings['settings']['geoip_list'] ?? ""));
                $enabledGeoIp = array_filter($geoIpList, function ($geoIp) {
                    return !empty($geoIp);
                });

                if (in_array($geoIp, $enabledGeoIp)) {
                    $key = 'dafabet.language.popup.geoip';
                    $existingData = $this->get('preferences_fetcher')->getPreferences()[$key] ?? [];
                    $newPlayerLanguage = null;

                    if ($langcode) {
                        $playerLanguageMapping = $this->configuration_manager->getConfiguration('player-language');
                        $languages = $playerLanguageMapping['player-language'];
                        $newPlayerLanguage = array_search($langcode, $languages);
                    }

                    if (!in_array($geoIp, $existingData)) {
                        if ($newPlayerLanguage) {
                            $this->updatePlayerLanguage($newPlayerLanguage);
                        }
                        array_push($existingData, $geoIp);
                        $this->get('preferences_fetcher')->savePreference($key, $existingData);
                        $data['success'] = true;
                    }
                }
            }
        } catch (\Exception $e) {
            // do nothing
        }

        return $this->get('rest')->output($response, $data);
    }

    /**
     * Saves the new preferred language of player
     * to my account
     */
    private function updatePlayerLanguage($language)
    {
        $mobileNumbers = $this->player->getMobileNumbers();
        $this->user_fetcher->setPlayerDetails([
            "username" => $this->player->getUsername(),
            "firstname" => $this->player->getFirstName(),
            "lastname" => $this->player->getLastName(),
            "email" => $this->player->getEmail(),
            "countryid" => $this->player->getCountryID(),
            "city" => $this->player->getCity(),
            "address" => $this->player->getAddress(),
            "postalcode" => $this->player->getPostalCode(),
            "language" => $language,
            "gender" => $this->player->getGender(),
            "mobile" => $mobileNumbers["Home"]["number"] ?? null,
            "mobile1" => $mobileNumbers["Mobile 1"]["number"] ?? null,
        ]);
    }
}
