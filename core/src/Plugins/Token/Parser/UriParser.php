<?php

namespace App\Plugins\Token\Parser;

use App\Plugins\Token\ParserExtensionInterface;
use App\Url\AssetGeneratorInterface;

class UriParser implements ParserExtensionInterface
{
    const PATTERN = '/\[uri:\((.*?)\)\]/';

    /**
     * Asset generator
     *
     * @var object
     */
    private $asset;

    /**
     * Public constructor
     *
     * @param object $asset The asset generator
     */
    public function __construct(AssetGeneratorInterface $asset)
    {
        $this->asset = $asset;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(&$string)
    {
        $callback = function ($matches) {
            if ($matches && count($matches) == 2) {
                list(, $match) = $matches;

                if ($match) {
                    return $this->asset->generateAssetUri($match);
                }
            }
        };

        $callback->bindTo($this);

        $string = preg_replace_callback(self::PATTERN, $callback, $string);
    }
}
