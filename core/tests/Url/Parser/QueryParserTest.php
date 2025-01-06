<?php

namespace Tests\Url;

use Tests\BaseTestCase;

use App\Url\Parser\QueryParser;

/**
 * Unit test case for QueryParser class
 */
class QueryParserTest extends BaseTestCase
{
    /**
     * Test setup
     */
    public function setUp()
    {
        $this->parser = new QueryParser();
    }

    public function testSingleQuery()
    {
        $uri = $this->parser->parse('/about/[query:(name=leandrew)]');

        $this->assertEquals('/about/?name=leandrew', $uri);
    }

    public function testMultipleQuery()
    {
        $uri = $this->parser->parse('/about/[query:(name=leandrew&age=35)]');

        $this->assertEquals('/about/?name=leandrew&age=35', $uri);
    }

    /**
     * Empty Scenarios
     *
     */

    public function testQueryIsEmpty()
    {
        $uri = $this->parser->parse('/about/[query:(name=)]');

        $this->assertEquals('/about/', $uri);
    }

    public function testSomeQueryIsEmpty()
    {
        $uri = $this->parser->parse('/about/[query:(name=&age=35)]');

        $this->assertEquals('/about/?age=35', $uri);
    }

    public function testQueryIsZero()
    {
        $uri = $this->parser->parse('/about/[query:(id=0)]');

        $this->assertEquals('/about/?id=0', $uri);
    }

    /**
     * Invalid scenarios
     *
     */

    public function testInvalidQuery()
    {
        $uri = $this->parser->parse('/about/[query:(name)]');

        $this->assertEquals('/about/', $uri);
    }

    public function testInvalidSyntax()
    {
        $uri = $this->parser->parse('/about/[query(name=leandrew)]');

        $this->assertEquals('/about/[query(name=leandrew)]', $uri);
    }
}
