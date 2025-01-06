<?php

namespace App\Section\Async\Responsive;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;
use App\Drupal\Config;

class Footer implements AsyncSectionInterface
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
     * The current product
     *
     * @var string
     */
    private $product;

    /**
     * @var App\Player\PlayerSession
     */
    private $playerSession;

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
        $this->request = $container->get('router_request');
        $this->asset = $container->get('asset');
        $this->product = $container->get('product_default');
        $this->playerSession = $container->get('player_session');
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
            'partners' => $this->views->getViewById('partner_mobile'),
            'curacao' => $this->config->getGeneralConfigById('curacao'),
            'mobile_quicklinks' => $this->menu->getMultilingualMenu("mobile-quicklinks"),
            'customer_support' => $this->menu->getMultilingualMenu("customer-support"),
            'mobile_footer_button' => $this->menu->getMultilingualMenu("mobile-footer-button"),
            'footer_sponsor' =>  $this->config->getGeneralConfigById('webcomposer_sponsor_responsive'),
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
        $this->cleanQuickLinks($data['quicklinks']);
        $result['quicklinks'] = $data['quicklinks'] ?? [];
        $result['mobile_quicklinks'] = $data['mobile_quicklinks'] ?? [];
        $result['customer_support'] = $data['customer_support'] ?? [];
        $result['mobile_footer_button'] = $data['mobile_footer_button'] ?? [];

        // set sponsership.
        $result['field_mobile_sponsor_text_font_size'] = $data['footer_sponsor']['field_mobile_sponsor_text_font_size'] ?? '';
        $result['field_mobile_sponsor_subtext_font_size'] = $data['footer_sponsor']['field_mobile_sponsor_subtext_font_size'] ?? '';
        $result['field_desktop_sponsor_text_font_size'] = $data['footer_sponsor']['field_desktop_sponsor_text_font_size'] ?? '';
        $result['field_desktop_sponsor_subtext_font_size'] = $data['footer_sponsor']['field_desktop_sponsor_subtext_font_size'] ?? '';
        $result['sponsorship'] = $data['sponsorship'] ?? [];

        // set new format sponsors.
        $sponsorNewFormat = $this->getCleanSponsorsData($data['sponsorship']);
        $result['sponsorformat'] = $sponsorNewFormat ?? [];
        if (isset($result['sponsorformat'][0]['field_responsive_sponsor'])) {
            usort($result['sponsorformat'][0]['field_responsive_sponsor'], [$this, 'sortSponsor']);
        }

        if (isset($result['sponsorformat'][0]['field_responsive_sponsor'])) {
            usort($result['sponsorformat'][1]['field_responsive_sponsor'], [$this, 'sortSponsor']);
        }

        // Set the social media.
        $result['socialmedia'] = $data['socialmedia'] ?? [];

        // Partners
        $data['partners'] = $this->getCleanPartnersData($data['partners']);
        $result['partners'] = $data['partners'] ?? [];
        $result['curacao'] = \App\Utils\Curacao::getGCBData($data['curacao'] ?? []);

        // Cookie Notification
        $result['cookie_notification'] = $data['footer']['cookie_notification']['value'] ?? '';
        $result['country_codes'] = $data['footer']['country_codes'] ?? '';

        return $result;
    }

    /**
     * Sort the sponsor weight field .
     *
     * @return array
     */
    public static function sortSponsor($sponsor1, $sponsor2)
    {
        return ($sponsor1['field_weight'] < $sponsor2['field_weight']) ? -1 : 1;
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

    /**
     * Clean sponsors data with absalute path for images
     *
     * @return array
     */
    public function getCleanSponsorsData($data)
    {
        $result = [];
        foreach ($data as $n => $sponsors) {
            foreach ($sponsors as $key => $items) {

                if ($key === 'field_responsive_sponsor') {
                    foreach ($items as $key1 => $item) {
                        foreach ($item as $key2 => $it) {
                            $result[$n][$key][$key1][$key2] = $this->getRequiredField($key2, $it);
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Clean partners data with absolute path for images
     *
     * @return array
     */
    public function getCleanPartnersData($data)
    {
        $result = [];
        foreach ($data as $id => $partner) {
            foreach ($partner as $key => $items) {
                if (
                    $key === 'field_res_partner_desktop_logo' ||
                    $key === 'field_res_partner_tablet_logo' ||
                    $key === 'field_res_partner_mobile_logo'
                ) {
                    foreach ($items as $field => $value) {
                        foreach ($value as $field1 => $value1) {
                            if ($field1 === 'url') {
                                $pieces = explode("/", $value1);
                                $length = count($pieces);
                                $url = '/' . $pieces[$length - 2] . '/' . $pieces[$length - 1];
                                $result[$id][$key][$field][$field1] = $this->asset->generateAssetUri(
                                    $url,
                                    ['product' => $this->product]
                                );;
                            }
                        }
                    }
                } elseif ($key === 'field_partner_alternative_text') {
                    $result[$id][$key] = $items;
                } elseif ($key === 'field_id') {
                    $result[$id][$key] = $items;
                }
            }
        }

        return $result;
    }

    /**
     * Get Required Fields.
     *
     */
    public function getRequiredField($key2, $it)
    {
        if (isset($it[0])) {
            if ($key2 === 'field_res_sponsor_link') {
                return $this->getAbsalutePath($it[0]);
            }

            if ($key2 === 'field_res_sponsor_image') {
                return $this->getAbsalutePath($it[0]);
            }

            if ($key2 === 'field_res_sponsor_mobile_logo') {
                return $this->getAbsalutePath($it[0]);
            }

            if ($key2 === 'field_res_sponsor_tablet_logo') {
                return $this->getAbsalutePath($it[0]);
            }

            if ($key2 === 'field_sponsor_alternative_text') {
                return $this->getAbsalutePath($it[0]);
            }

            if ($key2 === 'field_mobile_sponsor_text') {
                return $this->getAbsalutePath($it[0]);
            }

            if ($key2 === 'field_mobile_sponsor_subtext') {
                return $this->getAbsalutePath($it[0]);
            }

            if ($key2 === 'field_weight') {
                return $it[0]['value'];
            }

            if ($key2 === 'field_enable_sponsor') {
                return $it[0]['value'];
            }

            if ($key2 === 'field_res_sponsor_link_target') {
                return $this->getAbsalutePath($it[0]);
            }
        }
    }

    /**
     * Get Absalute Path.
     *
     */
    public function getAbsalutePath($it)
    {
        foreach ($it as $key3 => $value) {

            if (isset($value)) {
                if ($key3 === 'url') {
                    $pieces = explode("/", $value);
                    $length = count($pieces);
                    $url = '/' . $pieces[$length - 2] . '/' . $pieces[$length - 1];

                    return $this->asset->generateAssetUri(
                        $url,
                        ['product' => $this->product]
                    );
                }

                if ($key3 === 'value') {
                    return $value;
                }
            }
        }
    }

    private function cleanQuickLinks(&$quicklinks)
    {
        if (!$quicklinks) {
            return;
        }
        if (!$this->playerSession->isLogin()) {
            return;
        }
        foreach ($quicklinks as $key => $link) {
            // Check if post-login url needs to be returned
            $postLoginURLEnabled = ($link['attributes']['postLoginURLEnabled'] ?? 0) === 1;
            $postLoginURL = $link['attributes']['postLoginURL'] ?? '#';
            if ($postLoginURLEnabled) {
                $quicklinks[$key]['alias'] = $postLoginURL;
            }
        }
    }
}
