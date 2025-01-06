<?php

namespace App\Dependencies;

/**
 *
 */
class Request
{
    /**
     *
     */
    public function __construct($container)
    {
        $this->request = $container->get('request');
        $this->settings = $container->get('settings');
    }

    public function __invoke()
    {
        $request = $this->request;

        // option to supply language list so we don't need to fetch it
        if (!empty($this->settings['languages']) && isset($this->settings['languages']['supply_languages_list'])) {
            $request = $request->withAttribute('languages_list', $this->settings['languages']['supply_languages_list']);
        }

        $language = $this->processLanguage($request);
        $product = $this->processProduct($request);

        if ($product) {
            $prefix = "$language/$product";
        } else {
            $prefix = $language;
        }

        return $request->withAttribute('prefix', $prefix);
    }

    /**
     *
     */
    private function processLanguage(&$request)
    {
        $uri = $request->getUri();

        $path = ltrim($uri->getPath(), '/');
        $dirs = explode('/', $path, 2);
        $language = strtolower(array_shift($dirs));

        // if supply language is specified, override all the language definition
        // and just used the supplied language

        $supplyLanguage = $this->settings['supply_language_on_empty'] ?? false;
        $acceptableLanguages = $this->settings['acceptable_languages'] ?? [];

        if ($supplyLanguage && !in_array($language, $acceptableLanguages)) {
            $request = $request->withAttribute('language', $supplyLanguage);

            return $supplyLanguage;
        }

        if ($language) {
            $request = $this->request->withUri(
                $uri->withPath(
                    strtolower('/' . array_shift($dirs))
                )
            );

            $request = $request->withAttribute('language', $language);
        }

        return $language;
    }

    /**
     *
     */
    private function processProduct(&$request)
    {
        $uri = $request->getUri();

        $path = ltrim($uri->getPath(), '/');
        $dirs = explode('/', $path, 2);
        $product = strtolower(array_shift($dirs));

        $products = $this->settings['product_url'] ?? [];

        if (!empty($product) && in_array($product, $products, true)) {
            $request = $request->withUri($uri->withPath('/' . array_shift($dirs)));
            $request = $request->withAttribute('product', $product);

            return $product;
        }
    }
}
