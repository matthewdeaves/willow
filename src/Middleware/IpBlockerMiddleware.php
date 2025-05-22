<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Service\IpSecurityService;
use Cake\Http\Response;
use Cake\Log\LogTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface; // Use LogTrait to get the logger instance

/**
 * IP Blocker Middleware
 *
 * Provides IP-based security by blocking requests from suspicious or previously blocked IP addresses.
 * This middleware checks each request against a list of blocked IPs and suspicious patterns
 * before allowing the request to proceed.
 *
 * Features:
 * - Blocks requests from previously identified malicious IPs
 * - Detects and blocks suspicious activity for progressive security measures
 */
class IpBlockerMiddleware implements MiddlewareInterface
{
    use LogTrait;

    /**
     * @var \App\Service\IpSecurityService
     */
    private IpSecurityService $ipSecurity;

    /**
     * Constructor
     *
     * @param \App\Service\IpSecurityService|null $ipSecurity IP security service
     */
    public function __construct(?IpSecurityService $ipSecurity = null)
    {
        $this->ipSecurity = $ipSecurity ?? new IpSecurityService();
    }

    /**
     * Process the request through the middleware.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $clientIp = $request->clientIp();
        $uri = $request->getUri();
        $requestPath = $uri->getPath() . ($uri->getQuery() ? '?' . $uri->getQuery() : '');

        if (!$clientIp) {
            // Log for missing client IP - CakePHP 5.x way: $this->getLogger()->level(message, context)

            $this->log(
                sprintf('Request with no client IP detected for URI: %s', $requestPath),
                'warning',
                ['group_name' => 'App\Middleware\IpBlockerMiddleware.php'],
            );

            return $handler->handle($request);
        }

        $route = $uri->getPath();
        $query = $uri->getQuery();

        if ($this->ipSecurity->isIpBlocked($clientIp)) {
            $response = new Response();
            // Log for blocked IP
            $this->log(
                sprintf('Blocked IP address %s attempted to access %s', $clientIp, $route),
                'warning',
                ['group_name' => 'App\Middleware\IpBlockerMiddleware.php'],
            );

            // Respond with a 403 Forbidden status and a static message (not translated)
            return $response->withStatus(403)
                ->withStringBody('Access Denied: Your IP address has been blocked due to suspicious activity.');
        }

        if ($this->ipSecurity->isSuspiciousRequest($request)) {
            $this->ipSecurity->trackSuspiciousActivity($clientIp, $route, $query);

            $response = new Response();
            // Log for suspicious request
            $this->log(
                sprintf('Suspicious request detected from IP %s for URL: %s', $clientIp, $requestPath),
                'warning',
                ['group_name' => 'App\Middleware\IpBlockerMiddleware.php'],
            );

            // Respond with a 403 Forbidden status and a static message (not translated)
            return $response->withStatus(403)
                ->withStringBody('Access Denied: Suspicious request detected.');
        }

        return $handler->handle($request);
    }
}
