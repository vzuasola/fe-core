<?php

namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;
use App\Form\LoginForm;

class FooterAsync implements AsyncSectionInterface
{
    /**
     * The views object
     *
     * @var object
     */
    private $views;

    /**
     * The config fetcher
     *
     * @var object
     */
    private $config;

    /**
     * The block fetcher
     *
     * @var object
     */
    private $block;

    /**
     * The menu fetcher
     *
     * @var object
     */
    private $menu;

    /**
     * The current language
     *
     * @var string
     */
    private $lang;

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
        $this->views = $container->get('views_fetcher_async');
        $this->config = $container->get('config_fetcher_async');
        $this->block = $container->get('block_fetcher');
        $this->menu = $container->get('menu_fetcher_async');
        $this->lang = $container->get('lang');
        $this->blockUtils = $container->get('block_utils');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [
            'footer' =>  $this->config->getGeneralConfigById('footer_configuration'),
            'quicklinks' => $this->menu->getMultilingualMenu("quicklinks"),
            'sponsorship' => $this->views->getViewById('sponsors'),
            'socialview' => $this->views->getViewById('social-media'),
        ];
    }

    /**
     * @{inheritdoc}
     */
    public function processDefinition($data, array $options)
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

        // Set quicklinks.
        $result['quicklinks'] = $data['quicklinks'] ?? [];

        // set sponsership.
        $result['sponsorship'] = $data['sponsorship'] ?? [];

        // Set the social media.
        $result['socialmedia'] = $data['socialmedia'] ?? [];

        // Partners
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
