<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * IpSecurityService handles IP-based security measures including blocking and suspicious activity detection.
 *
 * This service provides functionality to:
 * - Check if IP addresses are blocked
 * - Block IP addresses with optional expiration
 * - Detect suspicious requests based on URL patterns
 * - Track and respond to suspicious activity
 */
class IpSecurityService
{
    /**
     * Regular expression patterns used to identify suspicious behavior in requests.
     * Loaded from configuration.
     *
     * @var array<string>
     */
    private array $suspiciousPatterns = [];

    /**
     * @var \Cake\ORM\Table
     */
    private Table $blockedIpsTable;

    /**
     * Constructor
     *
     * @param \Cake\ORM\Table|null $blockedIpsTable Table instance for BlockedIps (e.g., TableRegistry::getTableLocator()->get('BlockedIps'))
     */
    public function __construct(?Table $blockedIpsTable = null)
    {
        $this->blockedIpsTable = $blockedIpsTable ?? TableRegistry::getTableLocator()->get('BlockedIps');

        // Load ALL suspicious patterns directly from the configuration file
        // Ensure 'IpSecurity.suspiciousPatterns' is defined in a loaded config file (e.g., config/security.php)
        $this->suspiciousPatterns = Configure::read('IpSecurity.suspiciousPatterns', []);
    }

    /**
     * Checks if an IP address is currently blocked.
     *
     * Uses a caching strategy to minimize database queries. The blocked status
     * is cached until the block expiration time or for a default period if no
     * expiration is set.
     *
     * @param string $ip The IP address to check
     * @return bool True if the IP is blocked, false otherwise
     */
    public function isIpBlocked(string $ip): bool
    {
        $cacheKey = 'blocked_ip_' . $ip;
        $blockedStatus = Cache::read($cacheKey, 'ip_blocker');

        // If not in cache, or if status is 'false' (meaning it was previously checked and not blocked)
        if ($blockedStatus === null) {
            $blockedIp = $this->blockedIpsTable->find()
                ->where(['ip_address' => $ip])
                ->where(function ($exp) {
                    return $exp->or([
                        'expires_at IS' => null, // Permanent block
                        'expires_at >' => DateTime::now(), // Block not yet expired
                    ]);
                })
                ->first();

            $blockedStatus = $blockedIp !== null;

            if ($blockedStatus) {
                // Determine cache TTL based on block's expiration
                $cacheTTLSeconds = 0; // Default to a long duration if no specific expiry
                if ($blockedIp->expires_at) {
                    $now = new DateTime();
                    $diffInSeconds = $blockedIp->expires_at->getTimestamp() - $now->getTimestamp();
                    if ($diffInSeconds <= 0) {
                        // Block has already expired, override status to false and cache for short period
                        $blockedStatus = false;
                        $cacheTTLSeconds = 300; // Cache 'not blocked' for 5 minutes
                    } else {
                        // Cache until block expiry plus a small buffer
                        $cacheTTLSeconds = $diffInSeconds + 60; // Add 60 seconds buffer
                    }
                }
                // Write to cache with specific TTL or a default long duration if expiry is null
                Cache::write(
                    $cacheKey,
                    $blockedStatus,
                    'ip_blocker',
                    $cacheTTLSeconds > 0 ? $cacheTTLSeconds : '1 day',
                );
            } else {
                // IP is not blocked, cache this 'not blocked' status for a short time to reduce DB load
                Cache::write($cacheKey, false, 'ip_blocker', 300); // Cache 'not blocked' for 5 minutes
            }
        }

        return (bool)$blockedStatus; // Ensure boolean type is returned
    }

    /**
     * Blocks an IP address with an optional expiration time.
     *
     * If the IP is already blocked (active and not expired), updates the existing block with new parameters.
     * Logs the blocking action and updates the cache accordingly.
     *
     * @param string $ip The IP address to block
     * @param string $reason The reason for blocking the IP
     * @param \Cake\I18n\DateTime|null $expiresAt Optional expiration time for the block
     * @return bool True if the block was successfully saved, false otherwise
     */
    public function blockIp(string $ip, string $reason, ?DateTime $expiresAt = null): bool
    {
        // Check for an *active* existing block to update
        $existing = $this->blockedIpsTable->find()
            ->where(['ip_address' => $ip])
            ->where(function ($exp) {
                return $exp->or([
                    'expires_at IS' => null,
                    'expires_at >' => DateTime::now(),
                ]);
            })
            ->first();

        if ($existing) {
            // Update existing block
            $entity = $this->blockedIpsTable->patchEntity($existing, [
                'reason' => $reason,
                'expires_at' => $expiresAt,
            ]);
        } else {
            // Create new block
            $entity = $this->blockedIpsTable->newEntity([
                'ip_address' => $ip,
                'reason' => $reason,
                'expires_at' => $expiresAt,
                'created' => DateTime::now(),
            ]);
        }

        if ($this->blockedIpsTable->save($entity)) {
            // Determine cache TTL for the newly set/updated block
            $cacheTTLSeconds = 0;
            if ($expiresAt) {
                $now = new DateTime();
                $diffInSeconds = $expiresAt->getTimestamp() - $now->getTimestamp();
                $cacheTTLSeconds = max(0, $diffInSeconds + 60); // Ensure non-negative and add buffer
            }
            // Write true to cache for this IP using determined TTL or a 1-day default for permanent blocks
            Cache::write('blocked_ip_' . $ip, true, 'ip_blocker', $cacheTTLSeconds > 0 ? $cacheTTLSeconds : '1 day');

            Log::warning(__('IP address blocked: {0} for {1}', [$ip, $reason]), [
                'ip' => $ip,
                'reason' => $reason,
                'expires_at' => $expiresAt ? $expiresAt->format('Y-m-d H:i:s') : 'never',
                'group_name' => 'security',
            ]);

            return true;
        }

        return false;
    }

    /**
     * Unblocks an IP address by removing it from the blocked list.
     * Invalidates the cache for the IP.
     *
     * @param string $ip The IP address to unblock
     * @return bool True if the IP was successfully unblocked or not actively blocked, false otherwise if DB fails.
     */
    public function unblockIp(string $ip): bool
    {
        $blockedIp = $this->blockedIpsTable->find()
            ->where(['ip_address' => $ip])
            ->first(); // Find any block for this IP, expired or not

        if ($blockedIp) {
            if ($this->blockedIpsTable->delete($blockedIp)) {
                Cache::delete('blocked_ip_' . $ip, 'ip_blocker'); // Invalidate cache
                Log::info(__('IP address unblocked: {0}', [$ip]), [
                    'ip' => $ip,
                    'group_name' => 'security',
                ]);

                return true;
            }

            return false; // Failed to delete from DB
        } else {
            // IP was not in the blocked list or already removed; treat as successful
            Log::info(
                __(
                    'Attempted to unblock IP {0} which was not found in the blocked list (or already expired).',
                    [$ip],
                ),
                [
                'ip' => $ip,
                'group_name' => 'security',
                ],
            );
            Cache::delete('blocked_ip_' . $ip, 'ip_blocker'); // Ensure cache is clear just in case

            return true; // Already unblocked/not blocked
        }
    }

    /**
     * Checks if a request matches known suspicious patterns.
     *
     * Analyzes both the raw and decoded versions of the URL to catch various
     * forms of malicious requests. Logs any detected suspicious activity.
     *
     * @param \Cake\Http\ServerRequest $request The full ServerRequest object.
     * @return bool True if the request matches suspicious patterns, false otherwise
     */
    public function isSuspiciousRequest(ServerRequest $request): bool
    {
        $uri = $request->getUri();
        $fullUrl = $uri->getPath() . ($uri->getQuery() ? '?' . $uri->getQuery() : '');
        $decodedUrl = urldecode($fullUrl);
        $doubleDecodedUrl = urldecode($decodedUrl);

        // Gather client information using $request methods for logging and debugging
        $clientInfo = [
            'user_agent' => $request->getHeaderLine('User-Agent') ?: 'unknown',
            'ip_address' => $request->clientIp() ?: 'unknown',
            'request_method' => $request->getMethod() ?: 'unknown',
            'referer' => $request->referer() ?: 'none',
            'accept_language' => $request->getHeaderLine('Accept-Language') ?: 'unknown',
            'accept_encoding' => $request->getHeaderLine('Accept-Encoding') ?: 'unknown',
            'host' => $request->host() ?: 'unknown',
            'protocol' => $request->getProtocolVersion() ?: 'unknown',
            'query_params' => $request->getQueryParams(), // Full query parameters array
            'parsed_body_keys' => array_keys((array)$request->getParsedBody()), // Log only keys to avoid sensitive data in logs by default
            'is_ajax' => $request->is('json') || $request->is('ajax'), // CakePHP's built-in detectors
            'session_id' => $request->getSession()->id() ?: 'none',
            'forwarded_for' => $request->getHeaderLine('X-Forwarded-For') ?: 'none',
            'real_ip' => $request->getHeaderLine('X-Real-IP') ?: 'none',
            'timestamp' => (new DateTime())->format('Y-m-d H:i:s.u'),
        ];

        // Check all versions of the URL against patterns
        foreach ($this->suspiciousPatterns as $pattern) {
            if (
                preg_match($pattern, $fullUrl) ||
                preg_match($pattern, $decodedUrl) ||
                preg_match($pattern, $doubleDecodedUrl)
            ) {
            /*
            debug("MATCH DETECTED!");
            debug("Matched URL: " . $fullUrl);
            debug("Matched pattern: " . $pattern);
            debug("Decoded URL: " . $decodedUrl);
            debug("Double Decoded URL: " . $doubleDecodedUrl);
            */
                // Log the detected suspicious activity
                Log::warning(__('Suspicious request detected: {0}', [$fullUrl]), [
                    'pattern' => $pattern, // The specific pattern that matched
                    'raw_url' => $fullUrl,
                    'decoded_url' => $decodedUrl,
                    'double_decoded' => $doubleDecodedUrl,
                    'group_name' => 'security',
                    'client' => array_filter($clientInfo, fn($v) => !is_array($v) || count($v) > 0), // Filter out empty arrays/nulls for cleaner logs
                    'request_headers' => $request->getHeaders(), // All request headers
                ]);

                return true; // Match found, request is suspicious
            }
        }

        return false; // No suspicious patterns found
    }

    /**
     * Tracks suspicious activity for an IP address and implements progressive blocking.
     *
     * Maintains a count of suspicious activities and their timestamps. If multiple
     * suspicious requests are detected within a short time period, the IP will be
     * blocked with increasing block durations for repeat offenders.
     *
     * @param string $ip The IP address to track
     * @param string $route The route that triggered the suspicious activity
     * @param string $query The query string that triggered the suspicious activity
     * @return void
     */
    public function trackSuspiciousActivity(string $ip, string $route, string $query): void
    {
        $key = 'suspicious_' . $ip;
        // Read existing suspicious data from cache, or initialize if not present
        $data = Cache::read($key, 'ip_blocker') ?: [
            'count' => 0,
            'first_seen' => time(),
            'routes' => [], // Tracks recent suspicious routes
        ];

        $data['count']++;
        $data['routes'][] = [
            'route' => $route,
            'query' => $query,
            'time' => time(),
        ];

        // Keep only the last 5 suspicious routes to limit data size
        $data['routes'] = array_slice($data['routes'], -5);

        // Update the cache with the new suspicious activity count and details
        Cache::write($key, $data, 'ip_blocker', '1 day'); // Cache suspicious activity for 1 day

        // If there are multiple suspicious requests within a recent time window, block the IP
        // Current logic: 2 or more requests within 24 hours trigger a block
        if ($data['count'] >= 2 && (time() - $data['first_seen']) <= 86400) { // 24 hours
            $reason = __('Multiple suspicious requests detected');
            $expiresAt = DateTime::now()->modify('+24 hours'); // Default block duration

            // Check if this IP was previously blocked (any block, active or expired)
            $previousBlock = $this->blockedIpsTable->find()
                ->where(['ip_address' => $ip])
                ->orderByDesc('created') // Get the most recent block record for this IP
                ->first();

            if ($previousBlock) {
                // If there was a previous block that is now expired, or if it was a very short block
                // This means the IP is a repeat offender
                if ($previousBlock->expires_at === null || $previousBlock->expires_at->isPast()) {
                    // Double the block time for repeat offenders (e.g., 48 hours for second offense)
                    $expiresAt = DateTime::now()->modify('+48 hours');
                    $reason = __('Repeat offender: Multiple suspicious requests detected');
                }
            }

            // Block the IP with the determined reason and expiration
            $this->blockIp($ip, $reason, $expiresAt);
        }
    }
}
