<?php

namespace Tests\Plugins\Token;

use Tests\BaseTestCase;
use Tests\MockContainer;

use App\Plugins\Token\Parser;
use App\Plugins\Token\TokenManager;
use App\Configuration\ConfigurationInterface;

use Tests\Plugins\Token\Mock\MockAgeToken;
use Tests\Plugins\Token\Mock\MockNameToken;
use Tests\Plugins\Token\Mock\MockNewToken;
use Tests\Plugins\Token\Mock\MockOptionToken;
use Tests\Plugins\Token\Mock\MockLazyToken;
use Tests\Plugins\Token\Mock\MockExtension;

class TokenManagerTest extends BaseTestCase
{
    /**
     * Definition of the token configuration
     */
    const CONFIG = [
        'tokens' => [
            'name' => MockNameToken::class,
            'age' => MockAgeToken::class,
            'lazy' => MockLazyToken::class,
        ],
        'extensions' => [
            MockExtension::class,
        ],
        'lazy' => [
            'lazy',
        ],
    ];

    /**
     * Test setup
     */
    public function setUp()
    {
        $this->configuration = $this->getMockBuilder(ConfigurationInterface::class)
            ->getMock();

        $container = MockContainer::createInstance();
        $container->set('configuration_manager', $this->configuration);

        $this->tokens = new TokenManager($container);
    }

    /**
     *
     */
    public function testGetTokenList()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('tokens'))
            ->willReturn([
                'tokens' => self::CONFIG['tokens'],
            ]);

        $result = $this->tokens->getTokenList();

        $expected = [
            'name' => MockNameToken::class,
            'age' => MockAgeToken::class,
            'lazy' => MockLazyToken::class,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     *
     */
    public function testGetLazyTokenList()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('tokens'))
            ->willReturn([
                'tokens' => self::CONFIG['tokens'],
                'lazy' => self::CONFIG['lazy'],
            ]);

        $result = $this->tokens->getLazyTokens();

        $expected = [
            'lazy' => MockLazyToken::class,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     *
     */
    public function testGetNonLazyTokenList()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('tokens'))
            ->willReturn([
                'tokens' => self::CONFIG['tokens'],
                'lazy' => self::CONFIG['lazy'],
            ]);

        $result = $this->tokens->getNonLazyTokens();

        $expected = [
            'name' => MockNameToken::class,
            'age' => MockAgeToken::class,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     *
     */
    public function testGetTokenListWithExtension()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('tokens'))
            ->willReturn([
                'tokens' => self::CONFIG['tokens'],
                'extensions' => self::CONFIG['extensions'],
            ]);

        $result = $this->tokens->getTokenList();

        $expected = [
            'name' => MockNameToken::class,
            'age' => MockAgeToken::class,
            'lazy' => MockLazyToken::class,
            'new' => MockNewToken::class,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     *
     */
    public function testGetToken()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('tokens'))
            ->willReturn([
                'tokens' => self::CONFIG['tokens'],
            ]);

        $result = $this->tokens->getToken(MockNameToken::class);

        $this->assertEquals('leandrew', $result);
    }

    /**
     *
     */
    public function testGetTokenWithDynamicOptions()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('tokens'))
            ->willReturn([
                'tokens' => self::CONFIG['tokens'],
            ]);

        $result = $this->tokens->getToken(MockOptionToken::class, ['name' => 'leandrew']);

        $this->assertEquals('leandrew', $result);

        $result = $this->tokens->getToken(MockOptionToken::class, ['name' => '35']);

        $this->assertEquals('35', $result);
    }

    /**
     *
     */
    public function testGetTokenWithExtension()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('tokens'))
            ->willReturn([
                'tokens' => self::CONFIG['tokens'],
                'extensions' => self::CONFIG['extensions'],
            ]);

        $result = $this->tokens->getToken(MockNewToken::class);

        $this->assertEquals('Lorem ipsum dolor', $result);
    }
}
