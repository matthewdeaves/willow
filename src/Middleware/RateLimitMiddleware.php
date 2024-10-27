<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Http\Exception\TooManyRequestsException;
use Cake\Cache\Cache;
use Cake\Log\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * RateLimitMiddleware
 *
 * This middleware implements rate limiting for specified routes.
 * It tracks the number of requests from an IP address within a given time period
 * and throws an exception if the limit is exceeded.
 */
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
     * Constructor
     *
     * @param array $config Configuration options for rate limiting.
     */
    public function __construct(array $config = [])
    {
        $this->limit = $config['limit'] ?? 3;
        $this->period = $config['period'] ?? 60;
        $this->rateLimitedRoutes = $config['routes'] ?? ['/users/login', '/users/register', '/articles/add-comment/'];
    }

    /**
     * Process a server request and return a response.
     *
     * This method implements the rate limiting logic. It checks if the current route
     * is subject to rate limiting, and if so, it tracks the number of requests from
     * the client's IP address. If the number of requests exceeds the limit within
     * the specified period, it throws a TooManyRequestsException.
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

        if ($this->isRouteLimited($route)) {
            $key = "rate_limit_{$ip}_{$route}";

            $rateData = Cache::read($key, 'rate_limit') ?: ['count' => 0, 'start_time' => time()];

            $currentTime = time();
            if ($currentTime - $rateData['start_time'] > $this->period) {
                $rateData = ['count' => 1, 'start_time' => $currentTime];
            } else {
                $rateData['count']++;
            }

            Cache::write($key, $rateData, 'rate_limit');

            if ($rateData['count'] > $this->limit) {
                Log::warning(__('Rate limit exceeded for IP: {0} on route: {1}', [$ip, $route]), [
                    'ip' => $ip,
                    'route' => $route,
                    'count' => $rateData['count'],
                    'limit' => $this->limit,
                    'group_name' => 'rate_limiting',
                ]);

                $response = $handler->handle($request);
                $response = $response->withStatus(429)
                    ->withHeader('Retry-After', (string)$this->period);
                throw new TooManyRequestsException(
                    __('Too many requests. Please try again later.'),
                    null,
                    $this->period
                );
            }
        }

        return $handler->handle($request);
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
     * This method checks if the given route matches any of the
     * rate-limited routes specified in the configuration, considering
     * language prefixes.
     *
     * @param string $route The route to check.
     * @return bool True if the route should be rate limited, false otherwise.
     */
    private function isRouteLimited(string $route): bool
    {
        foreach ($this->rateLimitedRoutes as $limitedRoute) {
            // Create a regular expression pattern to match the route with language prefix
            $pattern = '#^/[a-z]{2}' . preg_quote($limitedRoute, '#') . '$#';

            if (preg_match($pattern, $route)) {
                return true;
            }
        }

        return false;
    }
}
