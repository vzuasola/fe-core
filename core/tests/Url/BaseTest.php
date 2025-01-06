<?php

namespace Tests\Url;

use Tests\BaseTestCase;

use App\Url\Url;
use App\Fetcher\Drupal\LanguageFetcher;

/**
 * Unit test case for Url class
 */
class BaseTest extends BaseTestCase
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

        $this->parser = new class() implements \App\Url\Parser\ParserInterface {
            public function parse($uri)
            {
                return $uri . '?name=leandrew';
            }
        };

        $this->url->setParser($this->parser);
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

        $this->assertEquals('/en/dafabet/simple?name=leandrew', $uri);
    }
}
