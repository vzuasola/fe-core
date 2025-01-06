<?php

namespace App\Section\Common;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class Footer extends CommonSectionBase implements AsyncSectionInterface
{
    /**
     * Header name of geoip
     */
    const GEOIP_HEADER = 'x-custom-lb-geoip-country';

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        parent::setContainer($container);

        $this->blockUtils = $container->get('block_utils');
        $this->scripts = $container->get('scripts');
        $this->lang = $container->get('lang');
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
    {
        $result = [];
        $data = parent::processDefinition($data, $options);

        if (isset($data['base']['footer'])) {
            $result = $this->getSectionData($data['base']['footer'], $options);
        }

        return $result;
    }

    /**
     *
     */
    protected function getSectionData($data, array $options)
    {
        $result = [];

        $result = $data['footer'];

        $visibilty = $data['footer']['back_to_top_title'] ?? 0;
        $result['back_to_top'] = empty($visibilty) || !$this->blockUtils->isVisibleOn($visibilty);

        if (!empty($data['socialview'])) {
            $data['socialmedia'] = $this->getSocialMedia($data);
        } else {
            $data['socialmedia'] = [];
        }

        $result['quicklinks'] = $data['quicklinks'] ?? [];
        $result['sponsorship'] = $data['sponsorship'] ?? [];
        $result['socialmedia'] = $data['socialmedia'] ?? [];
        $result['partners'] = $data['partners'] ?? [];
        $result['curacao'] = $data['curacao'] ?? [];

        // Cookie Notification
        $result['cookie_notification'] = $data['footer']['cookie_notification']['value'] ?? '';
        $result['country_codes'] = $data['footer']['country_codes'] ?? '';

        return $result;
    }

    /**
     * Get the social media icons.
     *
     * @param  array $socialData data for the social media.
     *
     * @return array social media config.
     */
    private function getSocialMedia($socialData)
    {
        $socialMediaData = $socialData['socialview'];
        $data = reset($socialMediaData);
        $socialIcons = $data['field_social_media_cmi'] ?? [];
        $media = [];

        foreach ($socialIcons as $icon) {
            if ($icon['field_socialmedia_cmi_enable'][0]['value'] == 1) {
                $media[] = $icon;
            }
        }

        return $media;
    }
}
