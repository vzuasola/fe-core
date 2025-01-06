<?php

namespace App\Widget\Menu;

use App\Plugins\Widget\MenuWidgetInterface;

/**
 *
 */
class Promotion implements MenuWidgetInterface
{
    private $asset;

    /**
     *
     */
    public static function create($container)
    {
        return new static(
            $container->get('asset')
        );
    }

    /**
     *
     */
    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    /**
     *
     */
    public function alterData($data)
    {
        return $data;
    }

    /**
     *
     */
    public function getScript()
    {
        return $this->asset->generateAssetUri('/widgets/promotions.js');
    }

    /**
     *
     */
    public function getTemplate()
    {
        return '@base/widgets/menu/promotion.html.twig';
    }
}
