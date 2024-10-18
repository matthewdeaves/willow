<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Http\Exception\TooManyRequestsException;
use App\Middleware\RateLimitMiddleware;
use Cake\Cache\Cache;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddlewareTest extends TestCase
{
    protected RateLimitMiddleware $middleware;

    /**
     * Set up method
     *
     * Initializes the RateLimitMiddleware with a specific configuration
     * for testing purposes. This configuration includes a request limit
     * of 3 requests per 60 seconds for the routes '/users/login' and
     * '/users/register'.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->middleware = new RateLimitMiddleware([
            'limit' => 3,
            'period' => 60,
            'routes' => ['/users/login', '/users/register'],
        ]);
    }

    /**
     * Test rate limit not exceeded
     *
     * Verifies that the middleware allows up to the configured limit of
     * requests (3 in this case) within the specified time period without
     * throwing a TooManyRequestsException. It checks that the response
     * status code remains 200 for each request.
     *
     * @return void
     */
    public function testRateLimitNotExceeded(): void
    {
        $request = ServerRequestFactory::fromGlobals()
            ->withUri(ServerRequestFactory::fromGlobals()->getUri()->withPath('/users/login'))
            ->withEnv('REMOTE_ADDR', '127.0.0.1');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());

        for ($i = 0; $i < 3; $i++) {
            $response = $this->middleware->process($request, $handler);
            $this->assertInstanceOf(ResponseInterface::class, $response);
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    /**
     * Test rate limit exceeded
     *
     * Ensures that the middleware throws a TooManyRequestsException
     * when the number of requests exceeds the configured limit within
     * the specified time period. This test sends 4 requests to the
     * '/users/login' route and expects an exception on the fourth request.
     *
     * @return void
     */
    public function testRateLimitExceeded(): void
    {
        $request = ServerRequestFactory::fromGlobals()
            ->withUri(ServerRequestFactory::fromGlobals()->getUri()->withPath('/users/login'))
            ->withEnv('REMOTE_ADDR', '127.0.0.1');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());

        for ($i = 0; $i < 3; $i++) {
            $this->middleware->process($request, $handler);
        }

        $this->expectException(TooManyRequestsException::class);
        $this->middleware->process($request, $handler);
    }

    /**
     * Test rate limit with different IP addresses
     *
     * Confirms that the rate limiting is applied per IP address. This test
     * sends requests from different IP addresses to the '/users/login' route,
     * ensuring that each IP can make requests up to the limit without
     * triggering the rate limit.
     *
     * @return void
     */
    public function testRateLimitWithDifferentIpAddresses(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());

        for ($i = 0; $i < 10; $i++) {
            $ip = $this->generateRandomIp();
            $request = ServerRequestFactory::fromGlobals()
                ->withUri(ServerRequestFactory::fromGlobals()->getUri()->withPath('/users/login'))
                ->withEnv('REMOTE_ADDR', $ip);

            $response = $this->middleware->process($request, $handler);
            $this->assertInstanceOf(ResponseInterface::class, $response);
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    /**
     * Test rate limit not applied to non-limited routes
     *
     * Verifies that routes not specified in the rate-limited configuration
     * are not subject to rate limiting. This test sends multiple requests
     * to a non-limited route and checks that all requests are processed
     * successfully with a 200 status code.
     *
     * @return void
     */
    public function testRateLimitNotAppliedToNonLimitedRoutes(): void
    {
        $request = ServerRequestFactory::fromGlobals()
            ->withUri(ServerRequestFactory::fromGlobals()->getUri()->withPath('/some-other-route'))
            ->withEnv('REMOTE_ADDR', '127.0.0.1');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());

        for ($i = 0; $i < 10; $i++) {
            $response = $this->middleware->process($request, $handler);
            $this->assertInstanceOf(ResponseInterface::class, $response);
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    /**
     * Generate a random IP address.
     *
     * Generates a random IP address for testing purposes. This is used
     * to simulate requests from different clients in the rate limit tests.
     *
     * @return string A randomly generated IP address.
     */
    private function generateRandomIp(): string
    {
        return mt_rand(1, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255);
    }

    /**
     * Tear down
     *
     * Cleans up after each test by clearing the rate limit cache to ensure
     * that subsequent tests are not affected by previous test data.
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        Cache::clear('rate_limit');
    }
}
