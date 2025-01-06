<?php

namespace App\Controller;

use App\BaseController;
use Slim\Exception\NotFoundException;

class SitemapController extends BaseController
{
    /**
     * Route for showing the sitemap.xml
     */
    public function showXML($request, $response)
    {
        $data = [];

        $url = $request->getUri()->getBaseUrl();
        if (strpos($url, 'https') !== false) {
            $data['sitemap_base'] = $url;
        } else {
            $data['sitemap_base'] = preg_replace("/^http:/i", "https:", $url);
        }

        $lang = $response->getHeader('Content-Language');
        $lang = $lang[0];

        $sitemap = $this->getSection('sitemap_async')->resolve();

        if (!empty($sitemap['links'])) {
            $data['sitemap_xml'] = $this->getSitemapItems($sitemap['links'], $data['sitemap_base'], $lang);
        }

        // if there are no sitemap entries to show, then throw a 404
        if (empty($data['sitemap_xml'])) {
            throw new NotFoundException($request, $response);
        }

        return $this->view
            ->render($response, '@base/components/sitemap/sitemap.xml.twig', $data)
            ->withHeader('Content-Type', 'application/xml');
    }

    /**
     * Recursiion function for getting the sitemap
     */
    private function getSitemapItems($sitemap, $base, $lang, $data = [])
    {
        // Language fix
        $langStr = '';
        if ($lang) {
            $langStr = '/' . $lang;
        }
        foreach ($sitemap as $item) {
            if (is_array($item['path'])) {
                $data += $this->getSitemapItems($item['path'], $base, $lang, $data);
            } else {
                // Check if path is a url, if not, add the sitemap_base
                if (filter_var($item['path'], FILTER_VALIDATE_URL) || strpos($item['path'], "domain:entry")) {
                    $data[] = $item;
                } else {
                    if (!preg_match("/^\//", $item['path'])) {
                        $item['path'] = "/" . $item['path'];
                    }
                    $item['path'] = $base . $langStr . $item['path'];
                    $data[] = $item;
                }
            }
        }
        return $data;
    }
}
