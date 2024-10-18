<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Cache\Cache;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Processes an incoming server request and checks if the client's IP address is blocked.
 *
 * This method retrieves the client's IP address from the server parameters and checks if it is blocked.
 * It first checks a cache for the blocked status of the IP. If the status is not cached, it queries the
 * database to determine if the IP is blocked and caches the result. If the IP is blocked, a 403 response
 * is returned. Otherwise, the request is passed to the next handler.
 *
 * @param \Psr\Http\Message\ServerRequestInterface $request The incoming server request.
 * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler to delegate to if the IP is not blocked.
 * @return \Psr\Http\Message\ResponseInterface A response indicating whether access is denied
 * or the result of the next handler.
 * @throws \RuntimeException If there is an issue with caching or database access.
 */
class IpBlockerMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response.
     *
     * This method checks if the client's IP address is blocked. It first attempts to retrieve the blocked status from
     * the cache. If the status is not cached, it queries the database to determine if the IP is blocked. The result is
     * then cached for future requests. If the IP is blocked, a 403 response is returned. Otherwise, the request is
     * passed to the next handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The incoming server request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler to delegate to if the IP
     * is not blocked.
     * @return \Psr\Http\Message\ResponseInterface A response indicating whether the IP is blocked or the result
     * of the next handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getServerParams();

        if (isset($params['REMOTE_ADDR'])) {
            $clientIp = $params['REMOTE_ADDR'];
        } else {
            return $handler->handle($request);
        }

        $cacheKey = 'blocked_ip_' . $clientIp;
        $blockedStatus = Cache::read($cacheKey, 'ip_blocker');

        if ($blockedStatus === null) {
            // If not in cache, check the database
            $blockedIpsTable = TableRegistry::getTableLocator()->get('BlockedIps');
            $blockedIp = $blockedIpsTable->find()
                ->where(['ip_address' => $clientIp])
                ->where(function ($exp) {
                    return $exp->or([
                        'expires_at IS' => null,
                        'expires_at >' => DateTime::now(),
                    ]);
                })
                ->first();

            $blockedStatus = $blockedIp !== null;

            // Cache the result for 5 minutes (adjust as needed)
            Cache::write($cacheKey, $blockedStatus, 'ip_blocker');
        }

        if ($blockedStatus) {
            $response = new Response();

            return $response->withStatus(403)->withStringBody('Access Denied: Your IP is blocked.');
        }

        return $handler->handle($request);
    }
}
