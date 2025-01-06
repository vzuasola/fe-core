<?php

namespace Tests\Url;

use Tests\BaseTestCase;

use App\Url\Url;
use App\Fetcher\Drupal\LanguageFetcher;

/**
 * Unit test case for Url class
 */
class UrlTest extends BaseTestCase
{
    /**
     * Mock language list
     *
     * @var array
     */
    private $languageList = [
        'en' => [
            'name' => 'English',
            'id' => 'en',
            'prefix' => 'en',
        ],
        'zh-hans' => [
            'name' => 'Simplified Chinese',
            'id' => 'zh-hans',
            'prefix' => 'sc',
        ],
        'zh-hant' => [
            'name' => 'Traditional Chinese',
            'id' => 'zh-hant',
            'prefix' => 'ch',
        ],
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
        'product_exclusions' => [
            'mobile' => 'promotions'
        ],
    ];

    /**
     * Test setup
     */
    public function setUp()
    {
        $this->languages = $this->getMockBuilder(LanguageFetcher::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
        ]);

        $this->url = new Url(
            $this->request,
            $this->language,
            $this->product,
            $this->languages,
            $this->settings
        );
    }

    /**
     *
     */
    public function testGenerateUriSimple()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $uri = $this->url->generateUri('simple', []);

        $this->assertEquals('/en/dafabet/simple', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestSimple()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
        ]);

        $uri = $this->url->generateFromRequest($request, 'simple', []);

        $this->assertEquals('/en/dafabet/simple', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestRoot()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
        ]);

        $uri = $this->url->generateFromRequest($request, '/', []);

        $this->assertEquals('/en/dafabet/', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestSimpleAnotherLanguage()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'sc/dafabet',
        ]);

        $uri = $this->url->generateFromRequest($request, '/simple', []);

        $this->assertEquals('/sc/dafabet/simple', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestRelative()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
        ]);

        $uri = $this->url->generateFromRequest($request, '/relative', []);

        $this->assertEquals('/en/dafabet/relative', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestAbsolute()
    {
        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
        ]);

        $uri = $this->url->generateFromRequest($request, 'http://google.com/', []);

        $this->assertEquals('http://google.com/', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestSpecial()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
        ]);

        $uri = $this->url->generateFromRequest($request, 'mailto:leandrew.com', []);

        $this->assertEquals('mailto:leandrew.com', $uri);

        $uri = $this->url->generateFromRequest($request, 'tel:1234567890', []);

        $this->assertEquals('tel:1234567890', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestWithLanguageInUrl()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
        ]);

        $uri = $this->url->generateFromRequest($request, '/en/simple', []);

        $this->assertEquals('/en/dafabet/simple', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestWithQuery()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
        ]);

        $uri = $this->url->generateFromRequest($request, 'simple', [
            'query' => [
                'from' => 'about'
            ],
        ]);

        $this->assertEquals('/en/dafabet/simple?from=about', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestWithExclusion()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/mobile', [
            'language' => 'en',
        ]);

        $uri = $this->url->generateFromRequest($request, 'mobile', []);

        $this->assertEquals('/en/mobile', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestOnPageUri()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
        ]);

        $uri = $this->url->generateFromRequest($request, '#page', []);

        $this->assertEquals('#page', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestWithoutPrefix()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about');

        $uri = $this->url->generateFromRequest($request, '/about', []);

        $this->assertEquals('/about', $uri);
    }

    /**
     *
     */
    public function testGenerateFromRequestWithEmptyLanguage()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'en',
            'attributes' => [
                'empty_language' => true,
            ],
        ]);

        $uri = $this->url->generateFromRequest($request, '/about', []);

        $this->assertEquals('/about', $uri);
    }


    /**
     *
     */
    public function testGenerateCanonicalsFromRequest()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
            'product' => 'dafabet',
        ]);

        $canonicals = [
            [
                'prefix' => 'en/dafabet',
                'id' => 'en',
                'path' => '/en/dafabet/simple',
            ],
            [
                'prefix' => 'sc/dafabet',
                'id' => 'zh-hans',
                'path' => '/sc/dafabet/simple',
            ],
            [
                'prefix' => 'ch/dafabet',
                'id' => 'zh-hant',
                'path' => '/ch/dafabet/simple',
            ]
        ];

        $uri = $this->url->generateCanonicalsFromRequest($request, 'simple', []);

        $this->assertEquals($canonicals, $uri);
    }


    /**
     *
     */
    public function testGetAliasFromUrlSimple()
    {
        $this->languages->expects($this->any())
                ->method('getLanguages')
                ->willReturn($this->languageList);

        $request = $this->createRequest('GET', '/about', [
            'prefix' => 'en/dafabet',
        ]);

        $uri = $this->url->getAliasFromUrl('/en/dafabet/simple');

        $this->assertEquals('simple', $uri);
    }

    /**
     *
     */
    public function testIsExternalRelative()
    {
        $uri = $this->url->isExternal('/en/dafabet/simple');

        $this->assertFalse($uri);
    }

    /**
     *
     */
    public function testIsExternalAbsolute()
    {
        $uri = $this->url->isExternal('http://google.com');

        $this->assertTrue($uri);

        $uri = $this->url->isExternal('https://google.com');

        $this->assertTrue($uri);
    }
}
