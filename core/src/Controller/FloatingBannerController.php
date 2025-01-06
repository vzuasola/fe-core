<?php

namespace App\Controller;

use App\BaseController;

/**
 *
 */
class FloatingBannerController extends BaseController
{
    /**
     *
     */
    public function getItems($request, $response)
    {
        $data = [];

        $banners = $this->get('views_fetcher')->getViewById('floating_banner');

        foreach ($banners as $banner) {
            $visibilty = $banner['field_per_page_config'][0]['value'] ?? 0;

            if ($this->get('block_utils')->isVisibleOn($visibilty, rawurldecode($request->getParam('path')))) {
                $data['items'][] = $this->filterBanner($banner);
            }
        }

        $data['sprites'] = $this->get('asset')->generateAssetUri('images/sprite.png');

        return $this->get('rest')->output($response, $data);
    }

    /**
     *
     */
    public function filterBanner($banner)
    {
        $item = $banner;

        // Get type of banner based on banner type flag value
        $type = $banner['field_floating_banner_type'][0]['value'] === '0' ? 'LEFT' : 'RIGHT';
        $item['field_floating_banner_type'][0]['value'] = strtolower($type);

        // Prefix URLs

        if (isset($banner['field_image'][0]['url'])) {
            $url = $this->get('asset')->generateAssetUri($banner['field_image'][0]['url']);
            $item['field_image'][0]['url'] = $url;
        }

        if (isset($banner['field_link'][0]['value'])) {
            $url = $this->get('uri')->generateUri($banner['field_link'][0]['value'], []);
            $item['field_link'][0]['value'] = $url;
        }

        // Banner Items

        if (!empty($banner['field_banner_item'])) {
            foreach ($banner['field_banner_item'] as $key => $value) {
                // check if item is enabled or not
                if (empty($value['field_enable'][0]['value'])) {
                    unset($item['field_banner_item'][$key]);
                    continue;
                }

                if (isset($value['field_link'][0]['uri'])) {
                    $uri = $this->get('uri')->generateUri($value['field_link'][0]['uri'], []);
                    $item['field_banner_item'][$key]['field_link'][0]['uri'] = $uri;
                }
            }
        }

        $item['field_banner_item'] = array_filter($item['field_banner_item'], function ($value) {
            return $value['field_enable'][0]['value'] ?? false;
        });

        return $item;
    }
}
