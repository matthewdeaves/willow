<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\IpBlockerMiddleware;
use Cake\Cache\Cache;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

class IpBlockerMiddlewareTest extends TestCase
{
    protected $middleware;
    protected $blockedIpsTable;

    public array $fixtures = ['app.BlockedIps'];

    /**
     * Set up the test environment.
     *
     * Initializes the IpBlockerMiddleware, gets the BlockedIps table,
     * and clears the ip_blocker cache.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->middleware = new IpBlockerMiddleware();
        $this->blockedIpsTable = TableRegistry::getTableLocator()->get('BlockedIps');
        Cache::clear('ip_blocker');
    }

    /**
     * Clean up after each test.
     *
     * Clears the ip_blocker cache and the table locator.
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        Cache::clear('ip_blocker');
        $this->getTableLocator()->clear();
    }

    /**
     * Test the process method with a blocked IP address.
     *
     * This test ensures that when a request comes from a blocked IP address,
     * the middleware returns a 403 response with the correct error message.
     *
     * @return void
     */
    public function testProcessWithBlockedIp(): void
    {
        $request = new ServerRequest(['environment' => ['REMOTE_ADDR' => '192.0.2.1']]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->blockedIpsTable->save($this->blockedIpsTable->newEntity([
            'ip_address' => '192.0.2.1',
            'reason' => 'Test blocking',
            'created' => new DateTime(),
        ]));

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Your IP is blocked.', (string)$response->getBody());
    }

    /**
     * Test the process method with a non-blocked IP address.
     *
     * This test verifies that when a request comes from a non-blocked IP address,
     * the middleware allows the request to pass through to the next handler,
     * resulting in a 200 response.
     *
     * @return void
     */
    public function testProcessWithNonBlockedIp(): void
    {
        $request = new ServerRequest(['environment' => ['REMOTE_ADDR' => '192.0.2.2']]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test the process method with a cached blocked status.
     *
     * This test ensures that when a blocked IP status is cached,
     * the middleware returns a 403 response without querying the database.
     *
     * @return void
     */
    public function testProcessWithCachedBlockedStatus(): void
    {
        $request = new ServerRequest(['environment' => ['REMOTE_ADDR' => '192.0.2.3']]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        Cache::write('blocked_ip_192.0.2.3', true, 'ip_blocker');

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test the process method with an expired blocked IP address.
     *
     * This test verifies that when a request comes from an IP address
     * that was blocked but the block has expired, the middleware
     * allows the request to pass through to the next handler.
     *
     * @return void
     */
    public function testProcessWithExpiredBlockedIp(): void
    {
        $request = new ServerRequest(['environment' => ['REMOTE_ADDR' => '192.0.2.4']]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $this->blockedIpsTable->save($this->blockedIpsTable->newEntity([
            'ip_address' => '192.0.2.4',
            'reason' => 'Test blocking',
            'created' => new DateTime(),
            'expires_at' => (new DateTime())->modify('-1 day'),
        ]));

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test the process method when the REMOTE_ADDR is missing from the request.
     *
     * This test ensures that when the REMOTE_ADDR is not present in the server parameters,
     * the middleware allows the request to pass through to the next handler without
     * performing any IP blocking checks.
     *
     * @return void
     */
    public function testProcessWithMissingRemoteAddr(): void
    {
        $request = new ServerRequest();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
