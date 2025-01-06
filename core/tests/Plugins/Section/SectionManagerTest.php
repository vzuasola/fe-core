<?php

namespace Tests\Plugins\Token;

use Tests\BaseTestCase;
use Tests\MockContainer;

use App\Plugins\Section\SectionManager;
use App\Configuration\ConfigurationInterface;

use Tests\Plugins\Section\Mock\MockHeader;
use Tests\Plugins\Section\Mock\MockAsyncHeader;
use Tests\Plugins\Section\Mock\MockFooter;

use Tests\Plugins\Section\Mock\MockHeaderAlter;
use Tests\Plugins\Section\Mock\MockHeaderAsyncAlter;

class SectionManagerTest extends BaseTestCase
{
    /**
     * Definition of the section configuration
     */
    const CONFIG = [
        'sections' => [
            'header' => MockHeader::class,
            'header_async' => MockAsyncHeader::class,
            'footer' => MockFooter::class,
        ],
        'alters' => [
            'header' => MockHeaderAlter::class,
            'header_async' => MockHeaderAsyncAlter::class,
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

        $this->section = new SectionManager($container);
    }

    /**
     *
     */
    public function testGetSectionList()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('sections'))
            ->willReturn([
                'sections' => self::CONFIG['sections'],
            ]);

        $result = $this->section->getSectionList();

        $expected = [
            'header' => MockHeader::class,
            'header_async' => MockAsyncHeader::class,
            'footer' => MockFooter::class,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     *
     */
    public function testGetAlterList()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('sections'))
            ->willReturn([
                'alters' => self::CONFIG['alters'],
            ]);

        $result = $this->section->getAlterList();

        $expected = [
            'header' => MockHeaderAlter::class,
            'header_async' => MockHeaderAsyncAlter::class,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     *
     */
    public function testGetSyncSection()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('sections'))
            ->willReturn([
                'sections' => self::CONFIG['sections'],
            ]);

        $result = $this->section->getSection('header');

        $expected = [
            'name' => 'leandrew',
            'age' => 35,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     *
     */
    public function testGetSyncSectionWithOptions()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('sections'))
            ->willReturn([
                'sections' => self::CONFIG['sections'],
            ]);

        $result = $this->section->getSection('header', [
            'extra' => 100,
        ]);

        $expected = [
            'name' => 'leandrew',
            'age' => 35,
            'extra' => 100,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     *
     */
    public function testGetAsyncSection()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('sections'))
            ->willReturn([
                'sections' => self::CONFIG['sections'],
            ]);

        $data['header'] = $this->section->getSection('header_async');

        $result = \App\Async\Async::resolve($data);

        $expected = [
            'leandrew' => [
                'name' => 'leandrew',
                'age' => 35,
            ],
            'alex' => [
                'name' => 'alex',
                'age' => 20,
            ],
        ];

        $this->assertEquals($expected, $result['header']);
    }

    /**
     *
     */
    public function testGetAsyncSectionWithOptions()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('sections'))
            ->willReturn([
                'sections' => self::CONFIG['sections'],
            ]);

        $data['header'] = $this->section->getSection('header_async', [
            'extra' => 100,
        ]);

        $result = \App\Async\Async::resolve($data);

        $expected = [
            'leandrew' => [
                'name' => 'leandrew',
                'age' => 35,
                'extra' => 100,
            ],
            'alex' => [
                'name' => 'alex',
                'age' => 20,
                'extra' => 100,
            ],
        ];

        $this->assertEquals($expected, $result['header']);
    }

    /**
     *
     */
    public function testGetSyncSectionWithAlter()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('sections'))
            ->willReturn([
                'sections' => self::CONFIG['sections'],
                'alters' => self::CONFIG['alters'],
            ]);

        $result = $this->section->getSection('header');

        $expected = [
            'name' => 'Leandrew Vicarpio',
            'age' => 35,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     *
     */
    public function testGetAsyncSectionWithAlter()
    {
        $this->configuration->expects($this->any())
            ->method('getConfiguration')
            ->with($this->equalto('sections'))
            ->willReturn([
                'sections' => self::CONFIG['sections'],
                'alters' => self::CONFIG['alters'],
            ]);

        $data['header'] = $this->section->getSection('header_async');

        $result = \App\Async\Async::resolve($data);

        $expected = [
            'leandrew' => [
                'name' => 'leandrew',
                'age' => 35,
            ],
        ];

        $this->assertEquals($expected, $result['header']);
    }
}
