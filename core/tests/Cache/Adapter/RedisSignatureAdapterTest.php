<?php

namespace Tests\Cookies;

use Tests\BaseTestCase;

/**
 * Unit test case for the Cookies class
 */
class RedisSignatureAdapterTest extends BaseTestCase
{
    /**
     *
     */
    public function testCacheIsHitWithRedisSigAndCacheSig()
    {
        // If has redis sig
        // if has cache sig
        // if cache sig is equal to redis sig
        // good

        $this->assertTrue(true);
    }

    /**
     *
     */
    public function testCacheIsHitWithRedisSigMismatch()
    {
        // If has redis sig
        // if has cache sig
        // if cache sig is not equal to redis sig
        // invalidate

        $this->assertTrue(true);
    }

    /**
     *
     */
    public function testCacheIsHitWithoutRedisSigAndHasCacheSig()
    {
        // If no redis sig
        // if has cache sig
        // invalidate

        $this->assertTrue(true);
    }

    /**
     *
     */
    public function testCacheIsHitWithoutRedisSigAndNoCacheSig()
    {
        // if no redis sig
        // if cache has no sig
        // good

        $this->assertTrue(true);
    }

    /**
     *
     */
    public function testCacheIsMissWithRedisSigSuccess()
    {
        // If has redis sig
        // Set cache with hash

        $this->assertTrue(true);
    }

    /**
     *
     */
    public function testCacheIsMissWithNoRedisSig()
    {
        // if no redis sig
        // Set cache with expiry

        $this->assertTrue(true);
    }
}
