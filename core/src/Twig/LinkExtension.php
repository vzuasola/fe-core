<?php

namespace App\Twig;

use Psr\Http\Message\ServerRequestInterface;
use App\Utils\Url;

/**
 * Provides link and asset URL generation
 */
class LinkExtension extends \Twig_Extension
{
    /**
     * The current router request
     *
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * The base URI
     *
     * @var string
     */
    private $baseUri;

    /**
     * The URL prefix
     *
     * @var string
     */
    private $prefix;

    /**
     * Public constructor.
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('url', array($this, 'url'), array(
                'is_safe' => array('html'),
            )),
            new \Twig_SimpleFunction('asset', array($this, 'asset'), array(
                'is_safe' => array('html'),
            ))
        );
    }

    /**
     * Generates static URL
     *
     * @param string $link The url link
     */
    public function url($link, $options = [])
    {
        $options += [
            'skip_parsers' => true,
        ];

        return Url::generateFromRequest($this->request, $link, $options);
    }

    /**
     * Generates asset URL
     *
     * @param string $link The asset link
     */
    public function asset($link, $options = [])
    {
        return Url::generateAssetUri($link, $options);
    }
}
