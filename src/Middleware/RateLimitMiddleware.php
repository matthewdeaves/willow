<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Http\Exception\TooManyRequestsException;
use App\Service\IpSecurityService;
use Cake\Cache\Cache;
use Cake\Http\Response;
use Cake\Log\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * @var int The maximum number of requests allowed within the period.
     */
    private int $limit;

    /**
     * @var int The time period in seconds for rate limiting.
     */
    private int $period;

    /**
     * @var array The list of routes to apply rate limiting.
     */
    private array $rateLimitedRoutes;

    /**
     * @var \App\Service\IpSecurityService
     */
    private IpSecurityService $ipSecurity;

    /**
     * Constructor
     *
     * @param array $config Configuration options for rate limiting.
     * @param \App\Service\IpSecurityService|null $ipSecurity IP security service
     */
    public function __construct(array $config = [], ?IpSecurityService $ipSecurity = null)
    {
        $this->limit = $config['limit'] ?? 3;
        $this->period = $config['period'] ?? 60;
        $this->rateLimitedRoutes = $config['routes'] ?? [
            '/users/login',
            '/users/register',
            '/articles/add-comment/',
            '/admin/*',
        ];
        $this->ipSecurity = $ipSecurity ?? new IpSecurityService();
    }

    /**
     * Process a server request and return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The server request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface The response.
     * @throws \App\Http\Exception\TooManyRequestsException If the rate limit is exceeded.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = $this->getClientIp($request);
        $route = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery();

        // Check if IP is already blocked
        if ($this->ipSecurity->isIpBlocked($ip)) {
            $response = new Response();

            return $response->withStatus(403)
                ->withStringBody('Access Denied: Your IP is blocked.');
        }

        // Check for suspicious patterns in route and query
        $isSuspicious = $this->ipSecurity->isSuspiciousRequest($route, $query);

        if ($isSuspicious) {
            $this->ipSecurity->trackSuspiciousActivity($ip, $route, $query);
        }

        if ($isSuspicious || $this->isRouteLimited($route)) {
            $key = "rate_limit_{$ip}_" . ($isSuspicious ? 'suspicious' : 'normal');

            // Use stricter limits for suspicious requests
            $currentLimit = $isSuspicious ? (int)($this->limit / 3) : $this->limit;
            $currentPeriod = $isSuspicious ? $this->period * 2 : $this->period;

            $rateData = $this->updateRateLimit($key, $currentPeriod);

            if ($rateData['count'] > $currentLimit) {
                $this->logViolation($ip, $route, $query, $rateData, $currentLimit, $isSuspicious);

                throw new TooManyRequestsException(
                    __('Too many requests. Please try again later.'),
                    null,
                    $currentPeriod
                );
            }
        }

        return $handler->handle($request);
    }

    /**
     * Update rate limit data for a given key
     *
     * @param string $key Cache key
     * @param int $period Time period
     * @return array Rate limit data
     */
    private function updateRateLimit(string $key, int $period): array
    {
        $rateData = Cache::read($key, 'rate_limit') ?: ['count' => 0, 'start_time' => time()];

        $currentTime = time();
        if ($currentTime - $rateData['start_time'] > $period) {
            $rateData = ['count' => 1, 'start_time' => $currentTime];
        } else {
            $rateData['count']++;
        }

        Cache::write($key, $rateData, 'rate_limit');

        return $rateData;
    }

    /**
     * Log rate limit violations
     *
     * @param string $ip IP address
     * @param string $route Request route
     * @param string $query Query string
     * @param array $rateData Rate limit data
     * @param int $limit Current limit
     * @param bool $isSuspicious Whether request was flagged as suspicious
     * @return void
     */
    private function logViolation(
        string $ip,
        string $route,
        string $query,
        array $rateData,
        int $limit,
        bool $isSuspicious
    ): void {
        Log::warning(__('Rate limit exceeded for IP: {0}', [$ip]), [
            'ip' => $ip,
            'route' => $route,
            'query' => $query,
            'count' => $rateData['count'],
            'limit' => $limit,
            'suspicious' => $isSuspicious,
            'group_name' => 'rate_limiting',
        ]);
    }

    /**
     * Get the client IP address from the request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The server request.
     * @return string The client IP address.
     */
    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        return $serverParams['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Check if the given route should be rate limited.
     *
     * @param string $route The route to check.
     * @return bool True if the route should be rate limited, false otherwise.
     */
    private function isRouteLimited(string $route): bool
    {
        foreach ($this->rateLimitedRoutes as $limitedRoute) {
            // Handle wildcard routes
            if (str_contains($limitedRoute, '*')) {
                // Allow optional language prefix and the rest of the route
                $pattern = '#^(/[a-z]{2})?/' . str_replace(
                    '*',
                    '.*',
                    ltrim(preg_quote($limitedRoute, '#'), '/')
                ) . '$#';
                if (preg_match($pattern, $route)) {
                    return true;
                }
            } else {
                // Allow optional language prefix for regular routes
                $pattern = '#^(/[a-z]{2})?/' . ltrim(preg_quote($limitedRoute, '#'), '/') . '$#';
                if (preg_match($pattern, $route)) {
                    return true;
                }
            }
        }

        return false;
    }
}
