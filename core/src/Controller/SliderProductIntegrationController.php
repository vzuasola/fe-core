<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

class SliderProductIntegrationController extends BaseController
{
    /**
     * Call our banners
     */
    public function getBanners($request, $response)
    {
        $banners = [];
        try {
            $contents = $this->get('views_fetcher')->getViewById('webcomposer_slider_v2');
            $nodeUtils = $this->get('node_utils');
            $isPreview = $request->getParam('prvw') === '1' ? 1 : 0;
            $isMobile = $this->isMobile();

            // Filter contents base from publish dates
            $nodeUtils->filterByPublishDateUTC($contents);

            foreach ($contents as $item) {
                // Check if content is not valid for the current player state
                if (!$nodeUtils->hasAccess($item['field_log_in_state'])) {
                    continue;
                }

                $post = '';
                $ribbon = '_prelogin';
                if ($this->get('player_session')->isLogin()) {
                    $post = '_post';
                    $ribbon = '_postlogin';
                }

                $filters = [
                    'isMobile' => $isMobile,
                    'isPreview' => $isPreview,
                    'status' => $item['field_status'][0]['value'] === '1' ? 1 : 0,
                    'availableOnPreview' => $item['field_preview_display_only'][0]['value'],
                    'availableOnDesktop' => $item['field_available_on_desktop'][0]['value'] ?? 1,
                    'availableOnTablet' => $item['field_available_on_tablet'][0]['value'] ?? 1,
                ];

                if ($this->showSlider($filters)) {
                    $banners[] = [
                        'bannerBlurb' => $item['field' . $post . '_banner_blurb'][0]['value'] ?? '',
                        'contentPosition' => $item['field' . $post . '_content_position'][0]['value'] ?? '',
                        'bannerLink' => $this->get('uri')->generateFromRequest(
                            $request,
                            $item['field' . $post . '_banner_link'][0]['uri'] ?? '',
                            []
                        ),
                        'bannerLinkTarget' => $item['field' . $post . '_banner_link_target'][0]['value'] ?? '',
                        'bannerImageUrl' => $item['field' . $post . '_banner_image'][0]['url'] ?? '',
                        'bannerImageAlt' => $item['field' . $post . '_banner_image'][0]['alt'] ?? '',
                        'bannerRibbonName' => $item['field' . $ribbon . '_slider_ribbon'][0]['name'][0]['value'] ?? '',
                        'bannerRibbonColor' => $item['field' . $ribbon . '_slider_ribbon'][0]
                                        ['field_ribbon_background_color'][0]['color'] ?? '',
                        'bannerRibbonTextColor' => $item['field' . $ribbon . '_slider_ribbon'][0]
                                        ['field_text_color'][0]['color'] ?? '',
                    ];
                }
            }
        } catch (\Exception $e) {
            // do nothing
        }

        return $this->get('rest')->output($response, [
            'data' => $banners
        ]);
    }

    /**
     * Removes Slider entries based on configured Slider entry statuses.
     *
     * @return boolean
     */
    private function showSlider($filters)
    {
        // Remove Slider if:
        // 1. Content is DISABLED
        // 2. Viewed on tablet but not available on tablet
        $tabletUnavailable = $filters['isMobile'] && !$filters['availableOnTablet'];

        // 3. Viewed on desktop but not available on desktop
        $desktopUnavailable = !$filters['isMobile']  && !$filters['availableOnDesktop'];

        // 4. Not on preview mode but only available on preview mode
        $previewUnavailable = !$filters['isPreview'] && $filters['availableOnPreview'];

        if (!$filters['status'] || $tabletUnavailable || $desktopUnavailable || $previewUnavailable) {
            return false;
        }

        return true;
    }

    private function isMobile()
    {
        return $this->request->getHeaderLine('X-Custom-Device-View') === 'mobile';
    }
}
