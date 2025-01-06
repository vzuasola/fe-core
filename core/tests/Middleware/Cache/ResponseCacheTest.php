<?php

namespace Tests\Middleware\Cache;

use Tests\BaseTestCase;
use Tests\MockContainer;

use Psr\Cache\CacheItemInterface;
use App\Session\SessionHandler;
use App\Player\PlayerSession;
use App\Cache\Adapter\RedisSignatureAdapter;
use App\Profiler\Profiler;
use App\Plugins\Route\RouteManager;
use App\Localization\Localization;

use App\Plugins\Middleware\TerminateAsCacheException;

use App\Middleware\Cache\ResponseCache;

class ResponseCacheTest extends BaseTestCase
{
    public $cache;
    public $container;
    public $request;
    public $response;
    public $session;
    public $playerSession;
    public $cacher;
    public $cacheItem;
    public $profiler;
    public $router;
    public $localization;

    public function setUp()
    {
        $this->container = MockContainer::createInstance();

        $this->request = $this->createRequest('GET', '/', []);

        $this->session = $this->getMockBuilder(SessionHandler::class)
            ->setMethods(['hasFlashes'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->playerSession = $this->getMockBuilder(PlayerSession::class)
            ->setMethods(['isLogin'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheItem = $this->getMockBuilder(CacheItemInterface::class)
            ->setMethods(['isHit','get', 'getKey', 'set', 'expiresAt', 'expiresAfter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacher = $this->getMockBuilder(RedisSignatureAdapter::class)
            ->setMethods(['getItem', 'save'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->profiler = $this->getMockBuilder(Profiler::class)
            ->setMethods(['getRenderTime'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->router = $this->getMockBuilder(RouteManager::class)
            ->setMethods(['getCurrentRouteConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->localization = $this->getMockBuilder(Localization::class)
            ->setMethods(['getLocalLanguage'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->response = $this->createResponse(200, ['headers' => []]);
        $this->response->getBody()->write('<html><body>Some Dynamic Html Content</body></html>');

        $this->container->set('request', $this->request);
        $this->container->set('session', $this->session);
        $this->container->set('player_session', $this->playerSession);
        $this->container->set('page_cache_adapter', $this->cacher);
        $this->container->set('profiler', $this->profiler);
        $this->container->set('route_manager', $this->router);
        $this->container->set('localization', $this->localization);
    }

    public function testBoot()
    {
        $this->assertTrue(true);

        $this->cache = new ResponseCache($this->container);

        $this->cache->boot($this->request);
    }

    public function testHandleRequestAndHandleResponseFailOnPageCacheUndefined()
    {
        $settings = [
            'page_cache' => []
        ];

        $request = clone $this->request;
        $response = clone $this->response;

        $this->container->set('settings', $settings);

        $this->cache = new ResponseCache($this->container);

        $this->cache->handleRequest($request, $response);
        $this->cache->handleResponse($request, $response);

        $this->assertEquals($request, $this->request);
        $this->assertEquals($response, $this->response);
    }

    public function testHandleRequestAndHandleResponseFailOnPageCacheDisabled()
    {
        $settings = [
            'page_cache' => [
                'enable' => false
            ]
        ];

        $request = clone $this->request;
        $response = clone $this->response;

        $this->container->set('settings', $settings);

        $this->cache = new ResponseCache($this->container);

        $this->cache->handleRequest($request, $response);
        $this->cache->handleResponse($request, $response);

        $this->assertEquals($request, $this->request);
        $this->assertEquals($response, $this->response);
    }

    public function testHandleRequestAndHandleResponseFailOnIsCacheableWithGetAttribute()
    {
        $settings = [
            'page_cache' => [
                'enable' => true
            ]
        ];

        $request = clone $this->request;
        $response = clone $this->response;

        $requestClone = clone $this->request;

        $request = $request->withAttribute('skip_page_cache', true)
            ->withMethod('GETs');
        $requestClone = $requestClone->withAttribute('skip_page_cache', true)
            ->withMethod('GETs');

        $this->container->set('settings', $settings);
        $this->container->set('request', $request);

        $this->cache = new ResponseCache($this->container);

        $this->cache->handleRequest($request, $response);
        $this->cache->handleResponse($request, $response);

        $this->assertEquals($request, $requestClone);
        $this->assertEquals($response, $this->response);
    }

    public function testHandleRequestAndHandleResponseFailOnIsCacheableWithMethodNotEqualToGet()
    {
        $settings = [
            'page_cache' => [
                'enable' => true
            ]
        ];

        $request = clone $this->request;
        $response = clone $this->response;

        $requestClone = clone $this->request;

        $request = $request->withMethod('GETs');
        $requestClone = $requestClone->withMethod('GETs');

        $this->container->set('settings', $settings);
        $this->container->set('request', $request);

        $this->cache = new ResponseCache($this->container);

        $this->cache->handleRequest($request, $response);
        $this->cache->handleResponse($request, $response);

        $this->assertEquals($request, $requestClone);
        $this->assertEquals($response, $this->response);
    }

    public function testHandleRequestAndHandleResponseFailOnIsCacheableWithHasFlashes()
    {
        $settings = [
            'page_cache' => [
                'enable' => true
            ]
        ];

        $request = clone $this->request;
        $response = clone $this->response;

        $requestClone = clone $this->request;

        $this->session->expects($this->exactly(2))
            ->method('hasFlashes')
            ->willReturn(true);

        $this->container->set('settings', $settings);
        $this->container->set('session', $this->session);

        $this->cache = new ResponseCache($this->container);

        $this->cache->handleRequest($request, $response);
        $this->cache->handleResponse($request, $response);

        $this->assertEquals($request, $requestClone);
        $this->assertEquals($response, $this->response);
    }

    public function testHandleRequestAndHandleResponseFailOnIsCacheableWithPlayerSessionIsLogin()
    {
        $settings = [
            'page_cache' => [
                'enable' => true
            ]
        ];

        $request = clone $this->request;
        $response = clone $this->response;

        $requestClone = clone $this->request;

        $this->playerSession->expects($this->exactly(2))
            ->method('isLogin')
            ->willReturn(false);

        $this->container->set('settings', $settings);
        $this->container->set('player_session', $this->playerSession);

        $this->cache = new ResponseCache($this->container);

        $this->cache->handleRequest($request, $response);
        $this->cache->handleResponse($request, $response);

        $this->assertEquals($request, $requestClone);
        $this->assertEquals($response, $this->response);
    }

    public function testHandleRequestAndHandleResponseFailOnIsRouteCacheablePageCacheUndefined()
    {
        $settings = [
            'page_cache' => [
                'enable' => true
            ]
        ];

        $request = clone $this->request;
        $response = clone $this->response;

        $requestClone = clone $this->request;

        $this->playerSession->expects($this->exactly(2))
            ->method('isLogin')
            ->willReturn(false);

        $this->container->set('settings', $settings);
        $this->container->set('player_session', $this->playerSession);

        $this->cache = new ResponseCache($this->container);

        $this->cache->handleRequest($request, $response);
        $this->cache->handleResponse($request, $response);

        $this->assertEquals($request, $requestClone);
        $this->assertEquals($response, $this->response);
    }

    public function testHandleRequestAndHandleResponseFailOnIsRouteCacheablePageCacheDisabled()
    {
        $settings = [
            'page_cache' => [
                'enable' => true
            ]
        ];

        $request = clone $this->request;
        $response = clone $this->response;

        $requestClone = clone $this->request;

        $this->playerSession->expects($this->exactly(2))
            ->method('isLogin')
            ->willReturn(false);

        $this->router->expects($this->any())
            ->method('getCurrentRouteConfiguration')
            ->willReturn([
                'page_cache' => [
                    'enabled' => false
                ],
            ]);

        $this->container->set('settings', $settings);
        $this->container->set('player_session', $this->playerSession);
        $this->container->set('route_manager', $this->router);

        $this->cache = new ResponseCache($this->container);

        $this->cache->handleRequest($request, $response);
        $this->cache->handleResponse($request, $response);

        $this->assertEquals($request, $requestClone);
        $this->assertEquals($response, $this->response);
    }

    public function testHandleResponseSuccessOnCacheableCheckingAndFailOnIsHit()
    {
        $settings = [
            'page_cache' => [
                'enable' => true
            ]
        ];

        $request = clone $this->request;
        $response = clone $this->response;

        $requestClone = clone $this->request;

        $this->playerSession->expects($this->once())
            ->method('isLogin')
            ->willReturn(false);

        $this->router->expects($this->any())
            ->method('getCurrentRouteConfiguration')
            ->willReturn([
                'page_cache' => [
                    'enabled' => true
                ],
            ]);

        $this->cacheItem->expects($this->any())
            ->method('isHit')
            ->willReturn(true);

        $this->cacher->expects($this->any())
            ->method('getItem')
            ->willReturn($this->cacheItem);

        $this->localization->expects($this->any())
            ->method('getLocalLanguage')
            ->willReturn(false);

        $this->container->set('settings', $settings);
        $this->container->set('player_session', $this->playerSession);
        $this->container->set('route_manager', $this->router);
        $this->container->set('page_cache_adapter', $this->cacher);
        $this->container->set('localization', $this->localization);

        $this->cache = new ResponseCache($this->container);

        $this->cache->handleResponse($request, $response);

        $this->assertEquals($request, $requestClone);
        $this->assertEquals($response, $this->response);
    }

    public function testHandleRequestSuccessOnCacheableCheckingAndIsHitFalse()
    {
        $responseHeaders = [
            'Page-Cache' => [
                'Miss'
            ]
        ];

        $settings = [
            'page_cache' => [
                'enable' => true
            ]
        ];

        $request = clone $this->request;
        $response = clone $this->response;

        $this->playerSession->expects($this->any())
            ->method('isLogin')
            ->willReturn(false);

        $this->router->expects($this->any())
            ->method('getCurrentRouteConfiguration')
            ->willReturn([
                'page_cache' => [
                    'enabled' => true
                ],
            ]);

        $this->cacheItem->expects($this->any())
            ->method('isHit')
            ->willReturn(false);

        $this->cacher->expects($this->any())
            ->method('getItem')
            ->willReturn($this->cacheItem);

        $this->localization->expects($this->any())
            ->method('getLocalLanguage')
            ->willReturn(false);

        $this->container->set('settings', $settings);
        $this->container->set('player_session', $this->playerSession);
        $this->container->set('route_manager', $this->router);
        $this->container->set('page_cache_adapter', $this->cacher);
        $this->container->set('localization', $this->localization);

        $this->cache = new ResponseCache($this->container);

        $this->cache->handleRequest($request, $response);
        $this->assertEquals($responseHeaders, $response->getHeaders());

        $this->cache->handleResponse($request, $response);
        $this->assertArrayHasKey('page_cache_time', $response->getAttributes());

        $this->assertNotEquals($request, $this->request);
        $this->assertNotEquals($response, $this->response);
    }

    public function testHandleRequestSuccessOnCacheableCheckingAndIsHitTrue()
    {
        $settings = [
            'page_cache' => [
                'enable' => true,
                'default_timeout' => 300,
                'include_cache_control_headers' => true,
                'cache_control_directive' => 'public, max-age=$time'
            ]
        ];

        $request = clone $this->request;
        $response = clone $this->response;

        $this->playerSession->expects($this->any())
            ->method('isLogin')
            ->willReturn(false);

        $this->router->expects($this->any())
            ->method('getCurrentRouteConfiguration')
            ->willReturn([
                'page_cache' => [
                    'enabled' => true
                ],
            ]);

        $this->cacheItem->expects($this->any())
            ->method('isHit')
            ->willReturn(true);

        $this->cacheItem->expects($this->once())
            ->method('get')
            ->willReturn([
                'status' => 200,
                'body' => '<html><body>Some Dynamic Html Content</body></html>',
                'header' => [
                    'Content-Type' => 'PUT',
                    'Content-Language' => 'en'
                ],
                'attributes' => []
            ]);

        $this->cacher->expects($this->any())
            ->method('getItem')
            ->willReturn($this->cacheItem);

        $this->localization->expects($this->any())
            ->method('getLocalLanguage')
            ->willReturn(false);


        $this->container->set('settings', $settings);
        $this->container->set('player_session', $this->playerSession);
        $this->container->set('route_manager', $this->router);
        $this->container->set('page_cache_adapter', $this->cacher);
        $this->container->set('localization', $this->localization);

        $this->cache = new ResponseCache($this->container);

        try {
            $this->cache->handleRequest($request, $response);
        } catch (TerminateAsCacheException $e) {
            $this->assertEquals(200, $response->getStatusCode());

            $this->assertArrayHasKey('Cache-Control', $response->getHeaders());
            $this->assertArrayHasKey('Page-Cache', $response->getHeaders());
            $this->assertArrayHasKey('Content-Language', $response->getHeaders());
            $this->assertArrayHasKey('Content-Type', $response->getHeaders());

            $this->assertEquals('Hit', $response->getHeaders()['Page-Cache'][0]);
            $this->assertEquals('PUT', $response->getHeaders()['Content-Type'][0]);
            $this->assertEquals('en', $response->getHeaders()['Content-Language'][0]);

            $this->cache->handleResponse($request, $response);

            $this->assertNotEquals($request, $this->request);
            $this->assertNotEquals($response, $this->response);
        }
    }
}
