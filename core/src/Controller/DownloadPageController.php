<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;
use App\Utils\Url;
use App\Drupal\Config;
use App\Async\Async;

class DownloadPageController extends BaseController
{
/**
     * Get Downloads
     *
     * @param  $request
     * @param  $response
     * @param  $args
     * @return json
     */
    public function getDownloadEntities($request, $response, $args)
    {
        if (isset($args['name'])) {
            $viewId = 'webcomposer-download-page';
            $this->asset = $this->container->get('asset');

            $definitions['header'] = $this->getSection('header_common');
            $definitions['footer'] = $this->getSection('footer_common');
            $definitions['session'] = $this->getSection('session_timeout_common');
            $definitions['outdated_browser'] = $this->getSection('legacy_browser_common');
            $definitions['announcement_lightbox'] = $this->getSection('announcement_lightbox_common');
            $definitions['livechat_common'] = $this->getSection('livechat_common');
            $definitions['downloadable'] = $this->getSection('downloadable_async');

            $definitions['download'] = $this->get('views_fetcher_async')->getViewById($viewId, [
                'name' => $args['name']]);
            $definitions['config'] =$this->get('config_fetcher_async')->
                getConfig('webcomposer_download_page.download_page');
            $data = Async::resolve($definitions);
            if ($data['download']) {
                $data['download'] = $this->processData($data);
                $data['title'] = $data['download']['title'];

                return $this->view->render($response, '@site/download.html.twig', $data);
            } else {
                throw new NotFoundException($request, $response);
            }
        } else {
            throw new NotFoundException($request, $response);
        }
    }

    public function processData($data)
    {
        $types = ['client', 'mobile'];
        $config = $data['config'] ?? [];
        $items = $data['download']['0']['field_download_step'] ?? [];
        $maxRow = 0;
        $sortedData = [];
        $responseData = [
            'title' => $config['title'] ?? 'Download',
            'labels' => null,
            'data' => [],
        ];

        foreach ($types as $type) {
            // set the labels
            $responseData['labels'][$type] = $config["{$type}_label"] ?? null;
            // sort data by type
            foreach ($items as $item) {
                $itemType = $item['field_type'][0]['value'] ?? null;

                if ($type === $itemType) {
                    $sortedData[$type][] = [
                        'body' => $item['field_body'][0]['value'] ?? null
                    ];
                }
            }

            if (isset($sortedData[$type])) {
                $count = count($sortedData[$type]);

                if ($count > $maxRow) {
                    $maxRow = $count;
                }
            }
        }

        // group the data per row
        for ($i = 0; $i < $maxRow; $i++) {
            $temp = [];
            foreach ($types as $type) {
                $temp[$type] = $sortedData[$type][$i] ?? null;
            }
            $responseData['data'][] = $temp;
        }
        return $responseData;
    }
}
