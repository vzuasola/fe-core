<?php

namespace Tests\Plugins\Token;

use Tests\BaseTestCase;

use App\Plugins\Token\Parser;
use App\Plugins\Token\TokenManager;

use Tests\Plugins\Token\Mock\MockAgeToken;
use Tests\Plugins\Token\Mock\MockNameToken;
use Tests\Plugins\Token\Mock\MockNewToken;
use Tests\Plugins\Token\Mock\MockOptionToken;
use Tests\Plugins\Token\Mock\MockLazyToken;

class ParserTest extends BaseTestCase
{
    /**
     * Definition of the token configuration
     */
    const CONFIG = [
        'tokens' => [
            'name' => MockNameToken::class,
            'age' => MockAgeToken::class,
            'domain:text' => MockNewToken::class,
        ],
        'lazy' => ['name'],
    ];

    /**
     * Test setup
     */
    public function setUp()
    {
        $this->tokens = $this->getMockBuilder(TokenManager::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokens->expects($this->any())
            ->method('getTokenList')
            ->willReturn(self::CONFIG['tokens']);

        $this->tokens->expects($this->any())
            ->method('getLazyTokens')
            ->willReturn([
                'name' => MockNameToken::class,
            ]);

        $this->tokens->expects($this->any())
            ->method('getNonLazyTokens')
            ->willReturn([
                'age' => MockAgeToken::class,
                'domain:text' => MockNewToken::class,
            ]);

        $this->tokens->expects($this->any())
            ->method('getToken')
            ->will(
                $this->returnCallback(
                    function ($key) {
                        $instance = new $key();
                        $options = [];

                        return $instance->getToken($options);
                    }
                )
            );

        $this->parser = new Parser($this->tokens);
    }

    /**
     *
     */
    public function testSimpleParse()
    {
        $text = "My name is {name}";

        $result = $this->parser->processTokens($text);

        $this->assertEquals('My name is leandrew', $result);
    }

    /**
     *
     */
    public function testMultipleTokens()
    {
        $text = "My name is {name} and I am {age} years old";

        $result = $this->parser->processTokens($text);

        $this->assertEquals('My name is leandrew and I am 35 years old', $result);
    }

    /**
     *
     */
    public function testMultiLineTokens()
    {
        $text = "
            My name is {name}
            and I am {age} years old
            {domain:text}
        ";

        $expected = "
            My name is leandrew
            and I am 35 years old
            Lorem ipsum dolor
        ";

        $result = $this->parser->processTokens($text);

        $this->assertEquals($expected, $result);
    }

    /**
     * On non existent tokens, it should still pass the the unparsed token as
     * raw
     */
    public function testNonExistentTokens()
    {
        $text = "My name is {somewhere} and I am {age} years old";

        $result = $this->parser->processTokens($text);

        $this->assertEquals('My name is {somewhere} and I am 35 years old', $result);
    }

    /**
     *
     */
    public function testMarkupTokens()
    {
        $text = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Some Site</title>
            </head>
            <body>
                <h1>I am {name}</h1>
                <p>And I am {age} years old</p>
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
                <h1>I am leandrew</h1>
                <p>And I am 35 years old</p>
            </body>
            </html>
        ";

        $result = $this->parser->processTokens($text);

        $this->assertEquals($expected, $result);
    }

    /**
     *
     */
    public function testJson()
    {
        $text = '{"name": "leandrew", "age": 35}';

        $result = $this->parser->processTokens($text);

        $this->assertEquals($text, $result);
    }

    /**
     *
     */
    public function testJsonTokens()
    {
        $text = '{"name": "{name}", "age": 35}';
        $expected = '{"name": "leandrew", "age": 35}';

        $result = $this->parser->processTokens($text);

        $this->assertEquals($expected, $result);
    }

    /**
     *
     */
    public function testParseLazy()
    {
        $text = "My name is {name} and I am {age} years old";

        $result = $this->parser->processTokens($text, ['lazy_only' => true]);

        $this->assertEquals('My name is leandrew and I am {age} years old', $result);
    }

    /**
     *
     */
    public function testParseNonLazy()
    {
        $text = "My name is {name} and I am {age} years old";

        $result = $this->parser->processTokens($text, ['lazy_only' => false]);

        $this->assertEquals('My name is {name} and I am 35 years old', $result);
    }

    /**
     *
     */
    public function testParseWithFilter()
    {
        $text = "My name is {name} and I am {age} years old";

        $filter = function ($replacement, $key) {
            if ($key === 'age') {
                $replacement = 25;
            }

            return $replacement;
        };

        $result = $this->parser->processTokens($text, ['token_filter' => $filter]);

        $this->assertEquals('My name is leandrew and I am 25 years old', $result);
    }

    /**
     *
     */
    public function testSetParser()
    {
        $instance = new Parser($this->tokens);

        $instance->setParser(
            new class() implements \App\Plugins\Token\ParserExtensionInterface {
                public function parse(&$body)
                {
                    $body = preg_replace('/\[(.*?)\]/', 'leandrew', $body);
                }
            }
        );

        $text = "My name is [name] and I am {age}";

        $result = $instance->processTokens($text);

        $this->assertEquals("My name is leandrew and I am 35", $result);
    }

    /**
     *
     */
    public function testSetParserWithOption()
    {
        $instance = new Parser($this->tokens);

        $instance->setParser(
            new class() implements \App\Plugins\Token\ParserExtensionInterface {
                public function parse(&$body)
                {
                    $body = preg_replace('/\[(.*?)\]/', 'leandrew', $body);
                }
            }
        );

        $text = "My name is [name] and I am {age}";

        $result = $instance->processTokens($text, ['skip_parsers' => true]);

        $this->assertEquals("My name is [name] and I am 35", $result);
    }
}
