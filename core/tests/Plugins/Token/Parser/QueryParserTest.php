<?php

namespace Tests\Plugins\Parser;

use Tests\BaseTestCase;

use App\Plugins\Token\Parser\QueryParser;

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
        $text = '/about/[query:(name=leandrew)]';

        $this->parser->parse($text);

        $this->assertEquals('/about/?name=leandrew', $text);
    }

    public function testMultipleQuery()
    {
        $text = '/about/[query:(name=leandrew&age=35)]';

        $this->parser->parse($text);

        $this->assertEquals('/about/?name=leandrew&age=35', $text);
    }

    /**
     * Empty Scenarios
     *
     */

    public function testQueryIsEmpty()
    {
        $text = '/about/[query:(name=)]';

        $this->parser->parse($text);

        $this->assertEquals('/about/', $text);
    }

    public function testSomeQueryIsEmpty()
    {
        $text = '/about/[query:(name=&age=35)]';

        $this->parser->parse($text);

        $this->assertEquals('/about/?age=35', $text);
    }

    public function testQueryIsZero()
    {
        $text = '/about/[query:(id=0)]';

        $this->parser->parse($text);

        $this->assertEquals('/about/?id=0', $text);
    }

    /**
     *
     */
    public function testQueryInMarkupSingle()
    {
        $text = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Some Site</title>
            </head>
            <body>
                <h1>Site</h1>
                <a href='http://google.com/about/[query:(name=leandrew)]'>Link</a>
            </body>
            </html>
        ";

        $expected = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Some Site</title>
            </head>
            <body>
                <h1>Site</h1>
                <a href='http://google.com/about/?name=leandrew'>Link</a>
            </body>
            </html>
        ";

        $this->parser->parse($text);

        $this->assertEquals($expected, $text);
    }

    /**
     *
     */
    public function testQueryInMarkupMultiple()
    {
        $text = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Some Site</title>
            </head>
            <body>
                <h1>Site</h1>
                <a href='http://google.com/about/[query:(name=leandrew)]'>Link</a>
                <a href='http://google.com/about/[query:(age=35)]'>Link</a>
            </body>
            </html>
        ";

        $expected = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Some Site</title>
            </head>
            <body>
                <h1>Site</h1>
                <a href='http://google.com/about/?name=leandrew'>Link</a>
                <a href='http://google.com/about/?age=35'>Link</a>
            </body>
            </html>
        ";

        $this->parser->parse($text);

        $this->assertEquals($expected, $text);
    }

    /**
     * Invalid scenarios
     *
     */

    /**
     *
     */
    public function testInvalidQuery()
    {
        $text = '/about/[query:(name)]';

        $this->parser->parse($text);

        $this->assertEquals('/about/', $text);
    }

    /**
     *
     */
    public function testInvalidSyntax()
    {
        $text = '/about/[query(name=leandrew)]';

        $this->parser->parse($text);

        $this->assertEquals('/about/[query(name=leandrew)]', $text);
    }
}
