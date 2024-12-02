<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Cache\Cache;
use Cake\I18n\DateTime;
use Cake\Log\Log;
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
     *
     * Includes patterns for:
     * - Path traversal and LFI attempts
     * - System file access attempts
     * - Shell injection attempts
     * - SQL injection patterns
     * - Suspicious file extensions
     * - XSS attempts
     * - Common probe attempts
     * - Sensitive path access
     * - Command injection
     * - Protocol handler abuse
     * - Backup file access
     *
     * @var array<string>
     */
    private array $suspiciousPatterns = [
        // Path Traversal & LFI Attempts
        '/\.\.[\/\\\\]/', // Simple directory traversal
        '/%2e%2e[\/\\\\]/', // URL encoded traversal
        '/%252e%252e\//', // Double encoded traversal
        '/%c0%ae%c0%ae\//', // UTF-8 encoded traversal

        // System Files
        '/\/etc\/passwd/',
        '/\/etc\/shadow/',
        '/boot\.ini/',
        '/\.env$/',
        '/\.git\//',
        '/\.htaccess$/',
        '/web\.config$/',
        '/composer\.json$/',

        // Shell Detection
        '/shell\.php$/',
        '/cmd\.php$/',
        '/eval\(/',
        '/system\(/',
        '/exec\(/',
        '/shell_exec\(/',

        // SQL Injection
        '/union.*select/i',
        '/select.*from/i',
        '/insert.*into/i',
        '/update.*set/i',
        '/delete.*from/i',
        '/drop.*table/i',
        '/--.*$/', // SQL comment
        '/\/\*.*\*\//', // SQL comment block

        // File Extensions
        '/\.(php|phtml|php3|php4|php5|phps)$/',
        '/\.(asp|aspx|jsp|jspx)$/',
        '/\.(exe|dll|cgi|pl)$/',

        // XSS
        '/<script/',
        '/<iframe/',
        '/javascript:/',
        '/onerror=/',
        '/onload=/',

        // Common Probes
        '/phpinfo\.php/',
        '/wp-admin/',
        '/wp-content/',
        '/phpmyadmin/',
        '/mysql/',

        // Sensitive Paths
        '/\/proc\/self/',
        '/\/proc\/[0-9]+/',
        '/\/tmp\//',
        '/\/var\/tmp\//',

        // Command Injection
        '/;&|wget/',
        '/;&|curl/',
        '/;&|bash/',
        '/;&|sh/',
        '/;&|nc /',

        // Protocol handlers
        '/php:\/\//',
        '/file:\/\//',
        '/data:\/\//',

        // Backup files
        '/\.bak$/',
        '/\.old$/',
        '/\.backup$/',
        '/\.sql$/',
    ];

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

        if ($blockedStatus === null) {
            $blockedIpsTable = TableRegistry::getTableLocator()->get('BlockedIps');
            $blockedIp = $blockedIpsTable->find()
                ->where(['ip_address' => $ip])
                ->where(function ($exp) {
                    return $exp->or([
                        'expires_at IS' => null,
                        'expires_at >' => DateTime::now(),
                    ]);
                })
                ->first();

            $blockedStatus = $blockedIp !== null;

            if ($blockedStatus) {
                // If IP is blocked, cache the status until the expiry time
                if ($blockedIp->expires_at) {
                    // Calculate duration until expiry
                    $now = DateTime::now();
                    $duration = $blockedIp->expires_at->diffInSeconds($now);
                    if ($duration < 0) {
                        // If already expired, don't cache
                        $blockedStatus = false;
                    }
                }
            }

            // Write to cache with the config name as a string
            Cache::write($cacheKey, $blockedStatus, 'ip_blocker');
        }

        return $blockedStatus;
    }

    /**
     * Blocks an IP address with an optional expiration time.
     *
     * If the IP is already blocked, updates the existing block with new parameters.
     * Logs the blocking action and updates the cache accordingly.
     *
     * @param string $ip The IP address to block
     * @param string $reason The reason for blocking the IP
     * @param \Cake\I18n\DateTime|null $expiresAt Optional expiration time for the block
     * @return bool True if the block was successfully saved, false otherwise
     */
    public function blockIp(string $ip, string $reason, ?DateTime $expiresAt = null): bool
    {
        $blockedIpsTable = TableRegistry::getTableLocator()->get('BlockedIps');

        // Check if IP is already blocked
        $existing = $blockedIpsTable->find()
            ->where(['ip_address' => $ip])
            ->first();

        if ($existing) {
            // Update existing block
            $blockedIpsTable->patchEntity($existing, [
                'reason' => $reason,
                'expires_at' => $expiresAt,
            ]);
            $entity = $existing;
        } else {
            // Create new block
            $entity = $blockedIpsTable->newEntity([
                'ip_address' => $ip,
                'reason' => $reason,
                'expires_at' => $expiresAt,
                'created' => DateTime::now(),
            ]);
        }

        if ($blockedIpsTable->save($entity)) {
            Cache::write('blocked_ip_' . $ip, true, 'ip_blocker');
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
     * Checks if a request matches known suspicious patterns.
     *
     * Analyzes both the raw and decoded versions of the URL to catch various
     * forms of malicious requests. Logs any detected suspicious activity.
     *
     * @param string $route The route being accessed
     * @param string $query The query string of the request
     * @return bool True if the request matches suspicious patterns, false otherwise
     */
    public function isSuspiciousRequest(string $route, string $query): bool
    {
        // Get both raw and decoded versions of the URL
        $fullUrl = $route . ($query ? '?' . $query : '');
        $decodedUrl = urldecode($fullUrl);
        $doubleDecodedUrl = urldecode($decodedUrl); // Catch double-encoded attacks

        // Check all versions against patterns
        foreach ($this->suspiciousPatterns as $pattern) {
            if (
                preg_match($pattern, $fullUrl) ||
                preg_match($pattern, $decodedUrl) ||
                preg_match($pattern, $doubleDecodedUrl)
            ) {
                Log::warning(__('Suspicious request detected: {0}', [$fullUrl]), [
                    'pattern' => $pattern,
                    'raw_url' => $fullUrl,
                    'decoded_url' => $decodedUrl,
                    'double_decoded' => $doubleDecodedUrl,
                    'group_name' => 'security',
                ]);

                return true;
            }
        }

        return false;
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
        $data = Cache::read($key, 'ip_blocker') ?: [
            'count' => 0,
            'first_seen' => time(),
            'routes' => [],
        ];

        $data['count']++;
        $data['routes'][] = [
            'route' => $route,
            'query' => $query,
            'time' => time(),
        ];

        // Keep only last 5 routes
        $data['routes'] = array_slice($data['routes'], -5);

        Cache::write($key, $data, 'ip_blocker');

        // If there are multiple suspicious requests within a short time, block the IP
        if ($data['count'] >= 2 && (time() - $data['first_seen']) <= 86400) { // 24 hours
            $reason = __('Multiple suspicious requests detected');

            // Set default expiry to 24 hours from now
            $expiresAt = DateTime::now()->modify('+24 hours');

            // For repeat offenders (check if they were blocked before)
            $blockedIpsTable = TableRegistry::getTableLocator()->get('BlockedIps');
            $previousBlock = $blockedIpsTable->find()
                ->where(['ip_address' => $ip])
                ->first();

            if ($previousBlock) {
                // Double the block time for repeat offenders (48 hours)
                $expiresAt = DateTime::now()->modify('+48 hours');
                $reason = __('Repeat offender: Multiple suspicious requests detected');
            }

            $this->blockIp($ip, $reason, $expiresAt);
        }
    }
}
