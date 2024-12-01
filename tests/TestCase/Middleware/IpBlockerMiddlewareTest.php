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
use Laminas\Diactoros\Uri;
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
        $this->assertEquals('Access Denied: Your IP address has been blocked due to suspicious activity. If you believe this is an error, please contact the site administrator.', (string)$response->getBody());
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

    /**
     * Test the process method with a suspicious request pattern.
     *
     * @return void
     */
    public function testProcessWithSuspiciousRequest(): void
    {
        $uri = new Uri('/etc/passwd');
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.5'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Suspicious request detected.', (string)$response->getBody());
    }

    /**
     * Test the process method with URL encoded suspicious pattern.
     *
     * @return void
     */
    public function testProcessWithEncodedSuspiciousRequest(): void
    {
        $uri = new Uri('/%2e%2e/etc/passwd');
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.6'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Suspicious request detected.', (string)$response->getBody());
    }

    /**
     * Test multiple suspicious requests leading to IP block.
     *
     * @return void
     */
    public function testMultipleSuspiciousRequestsLeadToBlock(): void
    {
        $ip = '192.0.2.7';
        $uri = new Uri('/etc/passwd');
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => $ip],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        // Simulate multiple suspicious requests
        for ($i = 0; $i < 3; $i++) {
            $response = $this->middleware->process($request, $handler);
            $this->assertEquals(403, $response->getStatusCode());
        }

        // Verify IP is now blocked in database
        $blocked = $this->blockedIpsTable->find()
            ->where(['ip_address' => $ip])
            ->first();

        $this->assertNotNull($blocked);
        $this->assertNotNull($blocked->expires_at);
    }

    /**
     * Test path traversal detection.
     *
     * @dataProvider pathTraversalProvider
     * @param string $path The path to test
     * @return void
     */
    public function testPathTraversalDetection(string $path): void
    {
        $uri = new Uri($path);
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.10'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Suspicious request detected.', (string)$response->getBody());
    }

    /**
     * Data provider for path traversal tests.
     *
     * @return array<array<string>>
     */
    public static function pathTraversalProvider(): array
    {
        return [
            ['/../etc/passwd'],
            ['/%2e%2e/etc/passwd'],
            ['/%252e%252e/etc/passwd'],
            ['/%c0%ae%c0%ae/etc/passwd'],
            ['/images/../../etc/passwd'],
            ['/theme/%2e%2e/%2e%2e/etc/shadow'],
        ];
    }

    /**
     * Test SQL injection detection.
     *
     * @dataProvider sqlInjectionProvider
     * @param string $query The query string to test
     * @return void
     */
    public function testSqlInjectionDetection(string $query): void
    {
        $uri = new Uri('/search?query=' . urlencode($query));
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.11'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Suspicious request detected.', (string)$response->getBody());
    }

    /**
     * Data provider for SQL injection tests.
     *
     * @return array<array<string>>
     */
    public static function sqlInjectionProvider(): array
    {
        return [
            ['UNION SELECT username,password FROM users'],
            ['SELECT * FROM users WHERE id = 1'],
            ['INSERT INTO users (username,password) VALUES ("admin","hack")'],
            ['UPDATE users SET password="hacked" WHERE id=1'],
            ['DELETE FROM users WHERE id=1'],
            ['1; DROP TABLE users'],
        ];
    }

    /**
     * Test XSS detection.
     *
     * @dataProvider xssProvider
     * @param string $input The potentially malicious input
     * @return void
     */
    public function testXssDetection(string $input): void
    {
        $uri = new Uri('/profile?bio=' . urlencode($input));
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.12'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Suspicious request detected.', (string)$response->getBody());
    }

    /**
     * Data provider for XSS tests.
     *
     * @return array<array<string>>
     */
    public static function xssProvider(): array
    {
        return [
            ['<script>alert("xss")</script>'],
            ['<iframe src="evil.com"></iframe>'],
            ['javascript:alert("xss")'],
            ['<img src="x" onerror="alert(\'xss\')">'],
            ['<body onload="alert(\'xss\')">'],
            ['<svg/onload=alert(1)>'],
            ['"><script>alert(document.cookie)</script>'],
        ];
    }

    /**
     * Test that legitimate requests are not blocked.
     *
     * @dataProvider legitimateRequestProvider
     * @param string $path The path to test
     * @return void
     */
    public function testLegitimateRequests(string $path): void
    {
        $uri = new Uri($path);
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.13'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Data provider for legitimate request tests.
     *
     * @return array<array<string>>
     */
    public static function legitimateRequestProvider(): array
    {
        return [
            ['/articles/view/1'],
            ['/users/profile'],
            ['/images/photo.jpg'],
            ['/blog/2024/01/my-first-post'],
            ['/search?q=normal+search+term'],
            ['/contact?message=Hello+there'],
        ];
    }
}
