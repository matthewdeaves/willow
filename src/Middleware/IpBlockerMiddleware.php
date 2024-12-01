<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Service\IpSecurityService;
use Cake\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * IP Blocker Middleware
 *
 * Provides IP-based security by blocking requests from suspicious or previously blocked IP addresses.
 * This middleware checks each request against a list of blocked IPs and suspicious patterns
 * before allowing the request to proceed.
 *
 * Features:
 * - Blocks requests from previously identified malicious IPs
 * - Detects and blocks suspicious request patterns
 * - Tracks suspicious activity for progressive security measures
 */
class IpBlockerMiddleware implements MiddlewareInterface
{
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
     * Performs the following checks in order:
     * 1. Validates the presence of a client IP
     * 2. Checks if the IP is in the blocked list
     * 3. Analyzes the request for suspicious patterns
     * 4. Tracks suspicious activity if detected
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getServerParams();
        $clientIp = $params['REMOTE_ADDR'] ?? null;

        if (!$clientIp) {
            return $handler->handle($request);
        }

        // Check for suspicious activity BEFORE handling the request
        $route = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery();

        if ($this->ipSecurity->isIpBlocked($clientIp)) {
            $response = new Response();

            return $response->withStatus(403)
                ->withStringBody(__('Access Denied: Your IP address has been blocked due to suspicious activity. ' .
                    'If you believe this is an error, please contact the site administrator.'));
        }

        // Check for suspicious patterns BEFORE any redirects might happen
        if ($this->ipSecurity->isSuspiciousRequest($route, $query)) {
            $this->ipSecurity->trackSuspiciousActivity($clientIp, $route, $query);

            // Return 403 immediately for suspicious requests
            $response = new Response();

            return $response->withStatus(403)
                ->withStringBody(__('Access Denied: Suspicious request detected.'));
        }

        return $handler->handle($request);
    }
}
