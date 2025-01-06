<?php

namespace Tests\Url;

use Tests\BaseTestCase;

use App\Url\Asset;
use App\Fetcher\Drupal\ConfigFetcher;

/**
 * Unit test case for Url class
 */
class AssetTest extends BaseTestCase
{
    /**
     * Mock config list
     *
     * @var array
     */
    private $configList = [
        'cdn_configuration' => [
            'enable_cdn' => false,
            'cdn_domain_configuration' => 'PH|https://cdn.example.com',
        ],
    ];

    /**
     * Manifest
     *
     * @var array
     */
    private $manifest = [
        '404.css' => 'css/404.leandrew1234.css',
        'page.css' => 'css/page.leandrew1234.css',
        '/images/page-sprite.png' => '/images/page-sprite.leandrew1234.png',
        'sample.js' => 'js/sample.leandrew1234.bundle.js',
    ];

    /**
     * The current language key
     *
     * @var string
     */
    private $language = 'en';

    /**
     * The current product key
     *
     * @var string
     */
    private $product = 'dafabet';

    /**
     * Mock settings
     *
     * @var array
     */
    private $settings = [
        'asset' => [
            'allow_prefixing' => true,
            'prefixed' => false,
            'product_prefix' => 'dafabet',
        ],
    ];

    /**
     * Test setup
     */
    public function setUp()
    {
        $this->config = $this->getMockBuilder(ConfigFetcher::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
        ]);

        $this->asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $this->settings,
            $this->config
        );
    }

    /**
     *
     */
    public function testGenerateAssetUriSimple()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $uri = $this->asset->generateAssetUri('images/placeholder.png');

        $this->assertEquals('/images/placeholder.png', $uri);

        $uri = $this->asset->generateAssetUri('sample.js');

        $this->assertEquals('/sample.js', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriAbsolute()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $uri = $this->asset->generateAssetUri('https://google.com/images.png');

        $this->assertEquals('https://google.com/images.png', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithManifest()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $this->settings,
            $this->config,
            $this->manifest
        );

        $uri = $asset->generateAssetUri('css/404.css');

        $this->assertEquals('/css/404.leandrew1234.css', $uri);

        $uri = $asset->generateAssetUri('images/page-sprite.png');

        $this->assertEquals('/images/page-sprite.leandrew1234.png', $uri);

        $uri = $asset->generateAssetUri('js/sample.bundle.js');

        $this->assertEquals('/js/sample.leandrew1234.bundle.js', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithCDN()
    {
        $_SERVER[Asset::GEO_HEADER] = 'PH';

        $configList = [
            'cdn_configuration' => [
                'enable_cdn' => true,
                'cdn_domain_configuration' => 'PH|https://cdn.example.com',
            ],
        ];

        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($configList['cdn_configuration']);

        $uri = $this->asset->generateAssetUri('images/placeholder.png');

        $this->assertEquals('https://cdn.example.com/images/placeholder.png', $uri);

        unset($_SERVER[Asset::GEO_HEADER]);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithWildcardCDN()
    {
        $_SERVER[Asset::GEO_HEADER] = 'PH';

        $configList = [
            'cdn_configuration' => [
                'enable_cdn' => true,
                'cdn_domain_configuration' => "
                    CN|https://cdn.example.com
                    *|https://cdn.pattern.com
                ",
            ],
        ];

        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($configList['cdn_configuration']);

        $uri = $this->asset->generateAssetUri('images/placeholder.png');

        $this->assertEquals('https://cdn.pattern.com/images/placeholder.png', $uri);

        unset($_SERVER[Asset::GEO_HEADER]);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithCDNWithManifest()
    {
        $_SERVER[Asset::GEO_HEADER] = 'PH';

        $configList = [
            'cdn_configuration' => [
                'enable_cdn' => true,
                'cdn_domain_configuration' => 'PH|https://cdn.example.com',
            ],
        ];

        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($configList['cdn_configuration']);


        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $this->settings,
            $this->config,
            $this->manifest
        );

        $uri = $asset->generateAssetUri('css/404.css');

        $this->assertEquals('https://cdn.example.com/css/404.leandrew1234.css', $uri);

        $uri = $asset->generateAssetUri('images/page-sprite.png');

        $this->assertEquals('https://cdn.example.com/images/page-sprite.leandrew1234.png', $uri);

        $uri = $asset->generateAssetUri('js/sample.bundle.js');

        $this->assertEquals('https://cdn.example.com/js/sample.leandrew1234.bundle.js', $uri);

        unset($_SERVER[Asset::GEO_HEADER]);
    }

    /**
     *
     */
    public function testGenerateAssetUriSimpleAndOverride()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => false,
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config
        );

        $uri = $asset->generateAssetUri('/en/images/placeholder.png');

        $this->assertEquals('/en/images/placeholder.png', $uri);
    }

    /**
     * Test cases with prefixes on the URI
     *
     */

    /**
     *
     */
    public function testGenerateAssetUriWithPrefixSimple()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config
        );

        $uri = $asset->generateAssetUri('images/placeholder.png');

        $this->assertEquals('/en/dafabet/images/placeholder.png', $uri);

        $uri = $asset->generateAssetUri('sample.js');

        $this->assertEquals('/en/dafabet/sample.js', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithPrefixSimpleAndOverride()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config
        );

        $options = [
            'product' => 'override'
        ];

        $uri = $asset->generateAssetUri('images/placeholder.png', $options);

        $this->assertEquals('/en/override/images/placeholder.png', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriDuplicatePrefixSimple()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config
        );

        $uri = $asset->generateAssetUri('/en/dafabet/images/placeholder.png');

        $this->assertEquals('/en/dafabet/images/placeholder.png', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriDuplicatePrefixSimpleAndOverride()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config
        );

        $options = [
            'product' => 'override'
        ];

        $uri = $asset->generateAssetUri('/en/dafabet/images/placeholder.png', $options);

        $this->assertEquals('/en/override/images/placeholder.png', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriDuplicateWithOverrideWithoutProduct()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => false,
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config
        );

        $options = [
            'product' => 'override'
        ];

        $uri = $asset->generateAssetUri('/en/dafabet/images/enthumb.png', $options);

        $this->assertEquals('/en/override/images/enthumb.png', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriDuplicateWithOverride()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => false,
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            null,
            $settings,
            $this->config
        );

        $options = [
            'product' => 'override'
        ];

        $uri = $asset->generateAssetUri('/en/images/enthumb.png', $options);

        $this->assertEquals('/en/override/images/enthumb.png', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithPrefixSimpleWithAbsoulteOption()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config
        );

        $options = [
            'absolute' => true
        ];

        $uri = $asset->generateAssetUri('images/placeholder.png', $options);

        $this->assertEquals('http://localhost/images/placeholder.png', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithPrefixAbsolute()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config
        );

        $uri = $asset->generateAssetUri('http://google.com/images.png');

        $this->assertEquals('http://google.com/images.png', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithPrefixWithManifest()
    {
        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($this->configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config,
            $this->manifest
        );

        $uri = $asset->generateAssetUri('css/404.css');

        $this->assertEquals('/en/dafabet/css/404.leandrew1234.css', $uri);

        $uri = $asset->generateAssetUri('images/page-sprite.png');

        $this->assertEquals('/en/dafabet/images/page-sprite.leandrew1234.png', $uri);

        $uri = $asset->generateAssetUri('js/sample.bundle.js');

        $this->assertEquals('/en/dafabet/js/sample.leandrew1234.bundle.js', $uri);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithPrefixWithCDN()
    {
        $_SERVER[Asset::GEO_HEADER] = 'PH';

        $configList = [
            'cdn_configuration' => [
                'enable_cdn' => true,
                'cdn_domain_configuration' => 'PH|https://cdn.example.com',
            ],
        ];

        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config,
            $this->manifest
        );

        $uri = $asset->generateAssetUri('images/placeholder.png');

        $this->assertEquals('https://cdn.example.com/en/dafabet/images/placeholder.png', $uri);

        unset($_SERVER[Asset::GEO_HEADER]);
    }


    /**
     *
     */
    public function testGenerateAssetUriWithPrefixWithCDNandProductOptionOverride()
    {
        $_SERVER[Asset::GEO_HEADER] = 'PH';

        $configList = [
            'cdn_configuration' => [
                'enable_cdn' => true,
                'cdn_domain_configuration' => 'PH|https://cdn.example.com',
            ],
        ];

        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $options = [
            'product' => 'override'
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config,
            $this->manifest
        );

        $uri = $asset->generateAssetUri('images/placeholder.png', $options);

        $this->assertEquals('https://cdn.example.com/en/override/images/placeholder.png', $uri);

        unset($_SERVER[Asset::GEO_HEADER]);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithPrefixWithWildcardCDN()
    {
        $_SERVER[Asset::GEO_HEADER] = 'PH';

        $configList = [
            'cdn_configuration' => [
                'enable_cdn' => true,
                'cdn_domain_configuration' => "
                    CN|https://cdn.example.com
                    *|https://cdn.pattern.com
                ",
            ],
        ];

        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config,
            $this->manifest
        );

        $uri = $asset->generateAssetUri('images/placeholder.png');

        $this->assertEquals('https://cdn.pattern.com/en/dafabet/images/placeholder.png', $uri);

        unset($_SERVER[Asset::GEO_HEADER]);
    }

    /**
     *
     */
    public function testGenerateAssetUriAbsoulteWithPrefixWithCDN()
    {
        $_SERVER[Asset::GEO_HEADER] = 'PH';

        $configList = [
            'cdn_configuration' => [
                'enable_cdn' => true,
                'cdn_domain_configuration' => 'PH|https://cdn.example.com',
            ],
        ];

        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config,
            $this->manifest
        );

        $uri = $asset->generateAssetUri('https://google.com/images/placeholder.png');

        $this->assertEquals('https://google.com/images/placeholder.png', $uri);

        unset($_SERVER[Asset::GEO_HEADER]);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithDuplicatePrefixWithCDN()
    {
        $_SERVER[Asset::GEO_HEADER] = 'PH';

        $configList = [
            'cdn_configuration' => [
                'enable_cdn' => true,
                'cdn_domain_configuration' => 'PH|https://cdn.example.com',
            ],
        ];

        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config,
            $this->manifest
        );

        $uri = $asset->generateAssetUri('/en/dafabet/images/placeholder.png');

        $this->assertEquals('https://cdn.example.com/en/dafabet/images/placeholder.png', $uri);

        unset($_SERVER[Asset::GEO_HEADER]);
    }


    /**
     *
     */
    public function testGenerateAssetUriWithDuplicatePrefixWithCDNwithOptionsOverride()
    {
        $_SERVER[Asset::GEO_HEADER] = 'PH';

        $configList = [
            'cdn_configuration' => [
                'enable_cdn' => true,
                'cdn_domain_configuration' => 'PH|https://cdn.example.com',
            ],
        ];

        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config,
            $this->manifest
        );

        $uri = $asset->generateAssetUri('/en/dafabet/images/placeholder.png', ['product' => 'override']);

        $this->assertEquals('https://cdn.example.com/en/override/images/placeholder.png', $uri);

        unset($_SERVER[Asset::GEO_HEADER]);
    }

    /**
     *
     */
    public function testGenerateAssetUriWithPrefixWithCDNWithManifest()
    {
        $_SERVER[Asset::GEO_HEADER] = 'PH';

        $configList = [
            'cdn_configuration' => [
                'enable_cdn' => true,
                'cdn_domain_configuration' => 'PH|https://cdn.example.com',
            ],
        ];

        $this->config->expects($this->any())
            ->method('getGeneralConfigById')
            ->with($this->equalto('cdn_configuration'))
            ->willReturn($configList['cdn_configuration']);

        $settings = [
            'asset' => [
                'allow_prefixing' => true,
                'prefixed' => true,
                'product_prefix' => 'dafabet',
            ],
        ];

        $asset = new Asset(
            $this->request,
            $this->language,
            $this->product,
            $settings,
            $this->config,
            $this->manifest
        );

        $uri = $asset->generateAssetUri('css/404.css');

        $this->assertEquals('https://cdn.example.com/en/dafabet/css/404.leandrew1234.css', $uri);

        $uri = $asset->generateAssetUri('images/page-sprite.png');

        $this->assertEquals('https://cdn.example.com/en/dafabet/images/page-sprite.leandrew1234.png', $uri);

        $uri = $asset->generateAssetUri('js/sample.bundle.js');

        $this->assertEquals('https://cdn.example.com/en/dafabet/js/sample.leandrew1234.bundle.js', $uri);

        unset($_SERVER[Asset::GEO_HEADER]);
    }
}
