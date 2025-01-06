<?php

namespace App\Fetcher;

/**
 *
 */
trait ConfigurableTrait
{
    /**
     *
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     *
     */
    public function withProduct($product)
    {
        if ($product !== $this->product) {
            $fetcher = clone $this;
            $fetcher->setProduct($product);
        } else {
            $fetcher = $this;
        }

        return $fetcher;
    }

    /**
     *
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     *
     */
    public function withLanguage($language)
    {
        $fetcher = clone $this;
        $fetcher->setLanguage($language);

        return $fetcher;
    }

    /**
     *
     */
    public function setAssetPrefix($assetPrefix)
    {
        $this->assetPrefix = $assetPrefix;
    }

    /**
     *
     */
    public function withAssetPrefix($assetPrefix)
    {
        $fetcher = $this;
        $fetcher->setAssetPrefix($assetPrefix);

        return $fetcher;
    }

    /**
     *
     */
    public function getDefaultRequestOptions($options = [])
    {
        $default = [];
        $default += $options;

        if (isset($this->language)) {
            $default['headers']['Language'] = $this->language;
        }

        if (isset($this->assetPrefix)) {
            $default['headers']['X-FE-Base-Uri'] = $this->assetPrefix;
        }

        return $default;
    }
}
