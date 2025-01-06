<?php

namespace App\Url;

use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

use App\Drupal\Config;

use Interop\Container\ContainerInterface;

class Asset implements AssetGeneratorInterface
{
    /**
     * Define the image extensions
     */
    const IMG = ['png', 'jpg', 'gif', 'svg'];

    /**
     * Geo IP header
     */
    const GEO_HEADER = 'HTTP_X_CUSTOM_LB_GEOIP_COUNTRY';

    /**
     * Public constructor
     */
    public function __construct($request, $lang, $product, $settings, $config, $manifest = 0)
    {
        $this->request = $request;
        $this->lang = $lang;
        $this->product = $product;
        $this->settings = $settings;
        $this->manifest = $manifest;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function generateAssetUri($uri, $options = [])
    {
        // change uri to manifest uri
        $manifest = $this->getManifestAsset($uri);

        if ($manifest) {
            $uri = $manifest;
        }

        $asset = $this->generateUri($uri, $options);

        return $asset;
    }

    /**
     * Fetches the asset from the manifest if it exists
     *
     * @param string $uri
     *
     * @return string
     */
    private function getManifestAsset($uri)
    {
        $result = false;

        if (!empty($this->manifest)) {
            $isImage = false;

            // alter if it is an image
            foreach (self::IMG as $ext) {
                if (preg_match("/{$ext}$/", $uri)) {
                    $isImage = true;

                    $key = ltrim($uri, '/');
                    $key = "/$key";
                    break;
                }
            }

            if (!$isImage) {
                $uriparts = explode('/', $uri);
                array_shift($uriparts);
                $key = str_replace('bundle.js', 'js', implode('/', $uriparts));
            }

            if (isset($this->manifest[$key])) {
                $result = $this->manifest[$key];
            }
        }

        return trim($result, '/');
    }

    /**
     * Generates the URI based on certain category
     *
     * @param string $uri The uri that needs to be prefixed
     * @param array $options Additional options
     *    Available options
     *        boolean `absolute` If specified will append current domain on the
     *    Asset URI
     *
     * @return string
     */
    private function generateUri($uri, $options)
    {
        $cdn = $this->generateCdnUri($uri, $options);

        if ($cdn) {
            $uri = $cdn;
        } else {
            $uri = $this->generateBaseUri($uri, $options);
        }

        return $uri;
    }

    /**
     *
     */
    private function generateCdnUri($uri, $options)
    {
        try {
            $cdnConfig = $this->config->getGeneralConfigById('cdn_configuration');
            $countryCode = $_SERVER[self::GEO_HEADER] ?? null;

            if (isset($cdnConfig['enable_cdn']) &&
                !empty($cdnConfig['cdn_domain_configuration']) &&
                $cdnConfig['enable_cdn']
            ) {
                $cdnConfiguration = $cdnConfig['cdn_domain_configuration'];
                $domains = Config::parse($cdnConfiguration);

                // define the prefix with language and product
                $allow = $this->checkIfAllowed($options);
                if ($allow) {
                    $uri = $this->checkIfDuplicatePrefix($uri, $options);
                    $product = $this->getProductPrefixOverride($options);
                    $prefix = $this->getUrlPrefix($product);
                } else {
                    $prefix = null;
                }

                // Get domain configuration in the form of array
                foreach ($domains as $pattern => $cdn) {
                    if (fnmatch($pattern, $countryCode)) {
                        $prefixPackage = new PathPackage($prefix, new EmptyVersionStrategy());
                        $uri = $prefixPackage->getUrl($uri);

                        $package = new UrlPackage($cdn, new EmptyVersionStrategy());

                        return $package->getUrl($uri);
                    }
                }
            }
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     *
     */
    private function generateBaseUri($uri, $options)
    {
        $allow = $this->checkIfAllowed($options);

        if ($allow) {
            $uri = $this->checkIfDuplicatePrefix($uri, $options);
            $product = $this->getProductPrefixOverride($options);
            $prefix = $this->getUrlPrefix($product);
            // absolute option
            if (isset($options['absolute']) && $options['absolute']) {
                $baseUri = $this->request->getUri()->getBaseUrl();
                $package = new UrlPackage($baseUri, new EmptyVersionStrategy());

                return $package->getUrl($uri);
            }
        } else {
            $prefix = "/";
        }

        $package = new PathPackage($prefix, new EmptyVersionStrategy());

        return $package->getUrl($uri);
    }

    /**
     * Check if settings and overrides allowed for manipulation
     */
    private function checkIfAllowed($options)
    {
        $prefixing = $this->settings['asset']['allow_prefixing'] ?? null;
        $allow = $this->settings['asset']['prefixed'] ?? null;

        $overrideProduct = $options['product'] ?? null;

        // added option to override prefixing
        if (isset($options['prefixed'])) {
            $prefixing = $options['prefixed'];
        }

        return ($allow || $overrideProduct) && $prefixing;
    }


    /**
     * Return product to be prefixed
     */
    private function getProductPrefixOverride($options)
    {
        $product = $this->settings['asset']['product_prefix'] ?? $this->product;
        $override = $options['product'] ?? null;

        if ($override) {
            $product = $override;
        }

        return $product;
    }

    /**
     *
     */
    private function checkIfDuplicatePrefix($uri, $options)
    {
        if (strpos($uri, '/' . $this->getUrlPrefix()) === 0) {
            $override = $options['product'] ?? null;

            if ($override) {
                $find = $this->getUrlPrefix();
                $replacement = $this->getUrlPrefix($override);

                return str_replace("/$find/", "/$replacement/", $uri);
            }

            return $uri;
        }

        return ltrim($uri, '/');
    }

    /**
     *
     */
    private function getUrlPrefix($override = null)
    {
        $lang = $this->lang;
        $product = $this->settings['asset']['product_prefix'] ?? $this->product;

        if ($override) {
            $product = $override;
        }

        return rtrim("$lang/$product", "/");
    }
}
