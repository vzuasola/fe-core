<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class AjaxBannerController extends BaseController
{
    use BaseSectionTrait;

    /**
     * Call banners
     * @todo Migrate filtering of scheduler and login state logic to utilize page caching for this route
     */
    public function getBanners($request, $response)
    {
        $banners = [];
        try {
            $options = $request->getQueryParams();
            unset($options['nc']);
            $definition = [
                'banners' => $this->get('views_fetcher_async')->getViewById('webcomposer_slider_v2', $options),
                'localized_banners' => false
            ];
            if ($localization = $this->get('localization')->getLocalLanguage()) {
                $definition['localized_banners'] = $this->get('views_fetcher_async')
                    ->setLanguage($localization)
                    ->getViewById('webcomposer_slider_v2', $options);
            }

            $data = $this->getAsyncData($definition);
            $contents = $data['localized_banners'] ? $data['localized_banners'] : $data['banners'];
            $nodeUtils = $this->get('node_utils');

            // Filter contents base from publish dates
            $nodeUtils->filterByPublishDateUTC($contents);

            foreach ($contents as $item) {
                // Check if content is not valid for the current player state
                if (!$nodeUtils->hasAccess($item['field_log_in_state'])) {
                    continue;
                }

                $state = '';
                if ($this->get('player_session')->isLogin()) {
                    $state = '_post';
                }

                $bannerLink = '';
                if (isset($item['field' . $state . '_banner_link'][0]['uri'])
                && !empty($item['field' . $state . '_banner_link'][0]['uri'])) {
                    $bannerLink = $this->get('uri')
                        ->generateUri($item['field' . $state . '_banner_link'][0]['uri'], []);
                }

                $bannerImg = '';
                if (isset($item['field' . $state . '_banner_image'][0]['url'])
                && !empty($item['field' . $state . '_banner_image'][0]['url'])) {
                    $bannerImg = $this->get('asset')
                        ->generateAssetUri($item['field' . $state . '_banner_image'][0]['url']);
                }

                $banners[] = [
                    'bannerBlurb' => $item['field' . $state . '_banner_blurb'][0]['value'] ?? '',
                    'contentPosition' => $item['field' . $state . '_content_position'][0]['value'] ?? '',
                    'bannerLink' =>  $bannerLink,
                    'bannerLinkTarget' => $item['field' . $state . '_banner_link_target'][0]['value'] ?? '',
                    'bannerImageUrl' => $bannerImg,
                    'bannerImageAlt' => $item['field' . $state . '_banner_image'][0]['alt'] ?? '',
                ];
            }
        } catch (\Exception $e) {
            // do nothing
        }

        return $this->get('rest')->output($response, [
            'data' => $banners
        ]);
    }

    /**
     * Gets Content that will be loaded into slider popup
     */
    public function getContentSliders($request, $response)
    {
        $sliderContents = [];
        try {
            $options = $request->getQueryParams();
            $product = $options['product'] ?? 'entry';
            $definition = [
                'config' => $this->get('config_fetcher_async')->withProduct($product)
                    ->getConfig('webcomposer_config.content_slider_configuration'),
                'sliderContents' => $this->get('views_fetcher_async')->withProduct($product)
                    ->getViewById('webcomposer_content_slider'),
            ];

            $data = $this->getAsyncData($definition);
            $contents = $data['sliderContents'] ?? [];

            foreach ($contents as $item) {
                $sliderContents['slides'][] = [
                    'title' => $item['field_title'][0]['value'] ?? '',
                    'content' => $item['field_html_content'][0]['value'] ?? '',
                ];
            }

            $sliderContents['popupTitle'] = $data['config']['content_slider_title'] ?? '';
            $sliderContents['closeBtnLabel'] = $data['config']['content_slider_close_btn_label'] ?? '';
        } catch (\Exception $e) {
            // do nothing
        }

        return $this->get('rest')->output($response, [
            'data' => $sliderContents
        ]);
    }
}
