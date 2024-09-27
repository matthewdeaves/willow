<?php
namespace App\Middleware;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to block requests from specific IP addresses.
 *
 * This middleware checks if the client's IP address is blocked by performing the following steps:
 * 1. Attempts to retrieve the blocked status from the cache.
 * 2. If not in cache, queries the database to determine if the IP is blocked.
 * 3. Caches the result for future requests.
 *
 * The database query checks for IP blocks with two possible conditions:
 * - Permanent blocks: Where 'expires_at' is NULL, indicating no expiration.
 * - Temporary blocks: Where 'expires_at' is a future date/time, indicating the block is still active.
 *
 * If the IP is blocked (either permanently or temporarily), a 403 Forbidden response is returned
 * with a message indicating access is denied. Otherwise, the request is passed to the next handler.
 *
 * @package App\Middleware
 */
class IpBlockerMiddleware implements MiddlewareInterface
{
    /**
     * Processes an incoming server request and returns a response.
     *
     * This method checks if the client's IP address is blocked by performing the following steps:
     * 1. Attempts to retrieve the blocked status from the cache.
     * 2. If not in cache, queries the database to determine if the IP is blocked.
     * 3. Caches the result for future requests.
     *
     * The database query checks for IP blocks with two possible conditions:
     * - Permanent blocks: Where 'expires_at' is NULL, indicating no expiration.
     * - Temporary blocks: Where 'expires_at' is a future date/time, indicating the block is still active.
     *
     * If the IP is blocked (either permanently or temporarily), a 403 Forbidden response is returned
     * with a message indicating access is denied. Otherwise, the request is passed to the next handler.
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @param RequestHandlerInterface $handler The request handler to delegate to if the IP is not blocked.
     * @return ResponseInterface The response generated after processing the request.
     * @throws \RuntimeException If there's an error accessing the database or cache.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $clientIp = $request->getServerParams()['REMOTE_ADDR'];
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
                        'expires_at >' => DateTime::now()
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
