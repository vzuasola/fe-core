<?php
namespace App\Section\Async;

use App\Plugins\Section\AsyncSectionInterface;
use Interop\Container\ContainerInterface;

class CanonicalsAsync implements AsyncSectionInterface
{
    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->request = $container->get('request');
        $this->uri = $container->get('uri');
    }

    /**
     * @{inheritdoc}
     */
    public function getSectionDefinition(array $options)
    {
        return [];
    }

    /**
     * Fetches the specified section
     *
     * @param array $options Array of additional options
     */
    public function processDefinition($data, array $options)
    {
        try {
            $result = $this->uri->generateCanonicalsFromRequest(
                $this->request,
                $this->request->getUri()->getPath()
            );
        } catch (\Exception $e) {
            $result = [];
        }

        $scheme = 'https';
        $host = $this->request->getUri()->getHost();
        $path = ltrim($this->request->getUri()->getPath(), '/');
        $dirs = explode('/', $path);
        $product = isset($dirs[1]) ? strtolower($dirs[1]) : ''; // else condition supports epg home page 500 error
        $uri = isset($dirs[2]) ? strtolower($dirs[2]) : '';

        $hrefLang = [
            'en' => 'en-my',
            'eu' => 'en-gb',
            'sc' => 'zh-Hans-cn',
            'ch' => 'zh-Hant-cn',
            'th' => 'th-th',
            'vn' => 'vi-vn',
            'id' => 'id-id',
            'jp' => 'ja-ap',
            'kr' => 'ko-kr',
            'in' => 'en-in',
            'gr' => 'el-gr',
            'pl' => 'pl-pl',
        ];

        if (isset($options['product'])) {
            $product = $options['product'];
        }

        foreach ($result as $key => $canonical) {
            $prefix = $canonical['prefix'];

            $canonical['id'] = $hrefLang[$prefix] ?? $hrefLang['en'];
            $canonical['path'] = "$scheme://$host/$prefix/$product";

            if (!empty($uri)) {
                $canonical['path'] .= "/$uri";
            }

            $result[$key] = $canonical;
        }
        return $result;
    }
}
