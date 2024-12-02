<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Http\Exception\TooManyRequestsException;
use App\Middleware\RateLimitMiddleware;
use Cake\Cache\Cache;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddlewareTest extends TestCase
{
    /**
     * @var \App\Middleware\RateLimitMiddleware
     */
    private $middleware;

    /**
     * Set up the test case.
     */
    public function setUp(): void
    {
        parent::setUp();
        Cache::clear('rate_limit'); // Clear cache before each test
        $this->middleware = new RateLimitMiddleware([
            'generalLimit' => 2, // Set general limit for testing
            'sensitiveLimit' => 1, // Set sensitive limit for testing
            'period' => 60, // Time period in seconds
            'sensitiveRoutes' => ['/users/login', '/users/register'],
        ]);
    }

    /**
     * Test rate limiting for general routes.
     */
    public function testRateLimitGeneralRoute()
    {
        $request = new ServerRequest(['url' => '/articles/view']);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->exactly(2))->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        // First request should pass
        $this->middleware->process($request, $handler);

        // Second request should pass
        $this->middleware->process($request, $handler);

        // Third request should throw TooManyRequestsException
        $this->expectException(TooManyRequestsException::class);
        $this->middleware->process($request, $handler);
    }

    /**
     * Test rate limiting for sensitive routes.
     */
    public function testRateLimitSensitiveRoute()
    {
        $request = new ServerRequest(['url' => '/users/login']);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        // First request should pass
        $this->middleware->process($request, $handler);

        // Second request should throw TooManyRequestsException
        $this->expectException(TooManyRequestsException::class);
        $this->middleware->process($request, $handler);
    }

    /**
     * Test that the rate limit is not exceeded for multiple different routes.
     */
    public function testDifferentRoutesDoNotAffectRateLimit()
    {
        $request1 = new ServerRequest(['url' => '/articles/view']);
        $request2 = new ServerRequest(['url' => '/articles/edit']);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $handler->method('handle')->willReturn($responseMock);

        // First request should pass
        $response1 = $this->middleware->process($request1, $handler);
        $this->assertSame($responseMock, $response1, 'First request did not pass as expected.');

        // Second request should also pass since it's a different route
        $response2 = $this->middleware->process($request2, $handler);
        $this->assertSame($responseMock, $response2, 'Second request did not pass as expected.');
    }
}
