<?php

namespace Tests\Plugins\Parser;

use Tests\BaseTestCase;

use App\Plugins\Token\Parser\UriParser;
use App\Url\AssetGeneratorInterface;

/**
 * Unit test case for UriParser class
 */
class UriParserTest extends BaseTestCase
{
    /**
     * Test setup
     */
    public function setUp()
    {
        $this->parser = new UriParser(
            new class() implements AssetGeneratorInterface
            {
                public function generateAssetUri($uri)
                {
                    return 'leandrew://' . ltrim($uri, '/');
                }
            }
        );
    }

    public function testSimple()
    {
        $text = '[uri:(/drew)]';

        $this->parser->parse($text);

        $this->assertEquals('leandrew://drew', $text);
    }

    /**
     * Empty Scenarios
     *
     */

    public function testIsEmpty()
    {
        $text = '/about/[uri:()]';

        $this->parser->parse($text);

        $this->assertEquals('/about/', $text);
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
                <a href='[uri:(/drew)]'>Link</a>
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
                <a href='leandrew://drew'>Link</a>
            </body>
            </html>
        ";

        $this->parser->parse($text);

        $this->assertEquals($expected, $text);
    }

    /**
     *
     */
    public function testInvalidSyntax()
    {
        $text = '/about/[uri(/drew)]';

        $this->parser->parse($text);

        $this->assertEquals('/about/[uri(/drew)]', $text);
    }
}
