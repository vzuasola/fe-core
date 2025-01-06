<?php

namespace Tests\Url;

use Tests\BaseTestCase;

use App\Url\Parser\TokenParser;
use App\Plugins\Token\Parser;

/**
 * Unit test case for TokenParser class
 */
class TokenParserTest extends BaseTestCase
{
    /**
     * Test setup
     */
    public function setUp()
    {
        $this->processor = $this->getMockBuilder(Parser::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->parser = new TokenParser($this->processor);
    }

    /**
     *
     */
    public function testSimpleToken()
    {
        $this->processor->expects($this->any())
            ->method('processTokens')
            ->will(
                $this->returnCallback(
                    function ($uri) {
                        return str_replace('{username}', 'leandrew', $uri);
                    }
                )
            );

        $uri = $this->parser->parse('/about/{username}');

        $this->assertEquals('/about/leandrew', $uri);
    }
}
