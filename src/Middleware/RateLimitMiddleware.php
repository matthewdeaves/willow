<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Http\Exception\TooManyRequestsException;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Log\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * @var int The maximum number of requests allowed within the period for general routes.
     */
    private int $generalLimit;

    /**
     * @var int The maximum number of requests allowed within the period for sensitive routes.
     */
    private int $sensitiveLimit;

    /**
     * @var int The time period in seconds for rate limiting.
     */
    private int $period;

    /**
     * @var array The list of sensitive routes to apply stricter rate limiting.
     */
    private array $sensitiveRoutes;

    /**
     * Constructor
     *
     * @param array $config Configuration options for rate limiting.
     */
    public function __construct(array $config = [])
    {
        $this->generalLimit = $config['generalLimit'] ?? 20;
        $this->sensitiveLimit = $config['sensitiveLimit'] ?? 3;
        $this->period = $config['period'] ?? 60;
        $this->sensitiveRoutes = $config['sensitiveRoutes'] ?? [
            '/users/login',
            '/users/register',
            '/articles/add-comment/',
        ];
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
        if (SettingsManager::read('Security.trustProxy', false)) {
            $request = $request->withAttribute('trustProxy', true);
        }

        $ip = $request->clientIp();
        $route = $request->getUri()->getPath();

        $isSensitive = $this->isSensitiveRoute($route);
        $limit = $isSensitive ? $this->sensitiveLimit : $this->generalLimit;
        $key = "rate_limit_{$ip}_" . ($isSensitive ? 'sensitive' : 'general');
        $rateData = $this->updateRateLimit($key, $this->period);

        if ($rateData['count'] > $limit) {
            $this->logViolation($ip, $route, $request->getUri()->getQuery(), $rateData);

            throw new TooManyRequestsException(
                __('Too many requests. Please try again later.'),
                null,
                $this->period
            );
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
     * @return void
     */
    private function logViolation(string $ip, string $route, string $query, array $rateData): void
    {
        Log::warning(__('Rate limit exceeded for IP: {0}', [$ip]), [
            'ip' => $ip,
            'route' => $route,
            'query' => $query,
            'count' => $rateData['count'],
            'limit' => $rateData['count'] > $this->sensitiveLimit ? $this->sensitiveLimit : $this->generalLimit,
            'group_name' => 'rate_limiting',
        ]);
    }

    /**
     * Check if the given route is sensitive and should have stricter rate limiting.
     *
     * @param string $route The route to check.
     * @return bool True if the route is sensitive, false otherwise.
     */
    private function isSensitiveRoute(string $route): bool
    {
        foreach ($this->sensitiveRoutes as $sensitiveRoute) {
            // Handle wildcard routes
            if (str_contains($sensitiveRoute, '*')) {
                $pattern = '#^(/[a-z]{2})?/' . str_replace(
                    '*',
                    '.*',
                    ltrim(preg_quote($sensitiveRoute, '#'), '/')
                ) . '$#';
                if (preg_match($pattern, $route)) {
                    return true;
                }
            } else {
                $pattern = '#^(/[a-z]{2})?/' . ltrim(preg_quote($sensitiveRoute, '#'), '/') . '$#';
                if (preg_match($pattern, $route)) {
                    return true;
                }
            }
        }

        return false;
    }
}
