<?php

namespace App\Fetcher\AsyncDrupal;

use App\Async\Definition;
use App\Async\DefinitionCollection;

use App\Fetcher\AbstractFetcher;

use GuzzleHttp\Client;

/**
 *
 */
class MenuFetcher extends AbstractFetcher
{
    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     */
    public function __construct(Client $client, $host, $request, $lang, $logger, $product)
    {
        parent::__construct($client, $logger, $product);

        $this->client = $client;
        $this->host = $host;
        $this->request = $request;
        $this->lang = $lang;
    }

    /**
     * Gets a menu tree by menu machine name
     *
     * @param string $id The menu machine name
     */
    public function getMenu($id)
    {
        $self = clone $this;
        $request = $this->request;

        $callback = function ($data) use ($self, $request) {
            if (!empty($data)) {
                $data = json_decode($data, true);

                return $self->setActiveClass($request, $data['body']);
            }
        };

        return $this->createRequest($this->client, 'GET', "$this->host/menu_translated/$id", [], $callback);
    }

    /**
     * Gets a menu tree by menu machine name with priority on the current language
     * first
     *
     * @param string $id The menu machine name
     */
    public function getMultilingualMenu($id, $cache = true)
    {
        $lang = $this->lang;

        $definitions = [
            'prefixed' => $this->getMenuById("$id-$lang", $cache),
            'normal' => $this->getMenuById($id, $cache),
        ];

        $callback = function ($data, $options) {
            if (!empty($data['prefixed'])) {
                $result = $data['prefixed'];
            } else {
                $result = $data['normal'];
            }

            return $result;
        };

        return new DefinitionCollection($definitions, [], $callback);
    }

    /**
     * Gets a menu tree by menu machine name
     *
     * @param string $id The menu machine name
     */
    public function getMenuById($id, $cache = true)
    {
        $self = clone $this;
        $request = $this->request;

        $callback = function ($data) use ($self, $request) {
            if (!empty($data)) {
                $data = json_decode($data, true);

                return $self->setActiveClass($request, $data['body']);
            }
        };

        return $this->createRequest($this->client, 'GET', "$this->host/menu/view/$id", [], $callback, $cache);
    }

    /**
     * Set menu link active class
     *
     * @param array $request The raw data request
     * @param array $menu The list of menu links
     */
    public function setActiveClass($request, $menu)
    {
        $menuLength = count($menu);
        $activeClass = 'active';

        // get the current path and compare it with menu item.
        $current = substr($request->getUri()->getPath(), 1);
        for ($i = 0; $i < $menuLength; $i++) {
            if ($menu[$i]['uri'] == $current || $menu[$i]['alias'] == $current) {
                $menu[$i][$activeClass] = $activeClass;
            }

            // match active state depending on pattern, this is for matching
            // uri with tokens
            if ($current) {
                if (strpos(preg_replace("/\{.*\}/", "", $menu[$i]['uri']), $current) === 0 ||
                    strpos(preg_replace("/\{.*\}/", "", $menu[$i]['alias']), $current) === 0
                ) {
                    $menu[$i][$activeClass] = $activeClass;
                }
            }

            // set the active attribute for the children items.
            if (isset($menu[$i]['below'])) {
                foreach ($menu[$i]['below'] as &$below) {
                    if ($below['uri'] == $current || $below['alias'] == $current) {
                        $below[$activeClass] = $activeClass;
                        $menu[$i][$activeClass] = $activeClass;
                    }
                }
            }
        }

        return $menu;
    }
}
