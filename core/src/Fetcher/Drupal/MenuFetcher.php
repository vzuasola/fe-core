<?php

namespace App\Fetcher\Drupal;

use App\Fetcher\AbstractFetcher;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface;

/**
 *
 */
class MenuFetcher extends AbstractFetcher
{
    /**
     * The host name to connect to
     *
     * @var string
     */
    private $host;

    /**
     * The current router request
     *
     * @var object
     */
    private $request;

    /**
     * Public constructor.
     *
     * @param Client $client A Guzzle client
     * @param string
     */
    public function __construct(Client $client, $host, $request, $lang, $logger, $product, $cacher)
    {
        parent::__construct($client, $logger, $product, $cacher);

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
        try {
            $response = $this->request('GET', "$this->host/menu_translated/$id");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        $menu = $this->setActiveClass($this->request, $data['body']);

        return $menu;
    }

    /**
     * Gets a menu tree by menu machine name with priority on the current language
     * first
     *
     * TODO Currently, front end is the one handling the fallbacks, this should
     * be Drupal's job
     *
     * @param string $id The menu machine name
     */
    public function getMultilingualMenu($id)
    {
        $lang = $this->lang;

        try {
            $menu = $this->getMenuById("$id-$lang");
        } catch (ClientException $e) {
            $menu = $this->getMenuById($id);
        }

        return $menu;
    }

    /**
     * Gets a menu tree by menu machine name
     *
     * @param string $id The menu machine name
     */
    public function getMenuById($id)
    {
        try {
            $response = $this->request('GET', "$this->host/menu/view/$id");
        } catch (GuzzleException $e) {
            throw $e;
        }

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        $menu = $this->setActiveClass($this->request, $data['body']);

        return $menu;
    }

    /**
     * Set menu link active class
     *
     * @todo URL Standardization Support
     * @param array $request The raw data request
     * @param array $menu    The list of menu links
     */
    private function setActiveClass($request, $menu)
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
