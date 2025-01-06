<?php

namespace Tests\Cookies;

use Tests\BaseTestCase;

use App\Cookies\Cookies;

/**
 * Unit test case for the Cookies class
 */
class CookiesTest extends BaseTestCase
{
    /**
     *
     */
    public function testSet()
    {
        $set = Cookies::set('name', 'leandrew');

        $this->assertTrue($set);
    }

    /**
     *
     */
    public function testGet()
    {
        Cookies::set('name', 'leandrew');

        $value = Cookies::get('name');

        $this->assertEquals($value, 'leandrew');
    }

    /**
     *
     */
    public function testGetOnCookie()
    {
        $_COOKIE['name'] = 'leandrew';

        $value = Cookies::get('name');

        $this->assertEquals($value, 'leandrew');
    }

    /**
     *
     */
    public function testRemove()
    {
        Cookies::set('name', 'leandrew');

        $value = Cookies::get('name');

        $this->assertEquals($value, 'leandrew');

        Cookies::remove('name');

        $value = Cookies::get('name');

        $this->assertNull($value);
    }
}
