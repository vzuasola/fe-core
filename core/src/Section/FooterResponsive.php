<?php

namespace App\Section;

use App\Plugins\Section\SectionInterface;
use Interop\Container\ContainerInterface;
use App\Form\LoginForm;

class FooterResponsive implements SectionInterface
{
    private $quicklinks = 'quicklinks';

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
     *  Private partners
     */
    private $partners = 'partners';

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->views = $container->get('views_fetcher');
        $this->config = $container->get('config_fetcher');
        $this->block = $container->get('block_fetcher');
        $this->menu = $container->get('menu_fetcher');
        $this->lang = $container->get('lang');
        $this->blockUtils = $container->get('block_utils');
    }

    /**
     * Fetches the specified section
     *
     * @param array $options Array of additional options
     */
    public function getSection(array $options)
    {
        try {
            $data = $this->config->getGeneralConfigById('footer_configuration');
            $visibilty = $data['back_to_top_title'] ?? 0;
            $data['back_to_top'] = empty($visibilty) || !$this->blockUtils->isVisibleOn($visibilty);
        } catch (\Exception $e) {
            $data = [];
        }

        try {
            $data[$this->quicklinks] = $this->menu->getMultilingualMenu($this->quicklinks);
        } catch (\Exception $e) {
            $data[$this->quicklinks] = [];
        }

        try {
            $data['sponsorship'] = $this->views->getViewById('sponsors');
        } catch (\Exception $e) {
            $data['sponsorship'] = [];
        }

        try {
            $data[$this->partners] = $this->views->getViewById($this->partners);
        } catch (\Exception $e) {
            $data[$this->partners] = [];
        }

        try {
            $data['socialmedia'] = $this->getSocialMedia();
        } catch (\Exception $e) {
            $data['socialmedia'] = [];
        }
        return $data;
    }

    private function getSocialMedia()
    {
        $socialMediaData = $this->views->getViewById('social-media');
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
