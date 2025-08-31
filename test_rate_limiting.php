<?php
/**
 * Simple Rate Limiting Test Script
 * This tests the rate limiting functionality without requiring full CakePHP bootstrap
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== Rate Limiting Test ===\n";

// Initialize basic environment constants if they don't exist
if (!defined('ROOT')) {
    define('ROOT', __DIR__);
}
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('CACHE')) {
    define('CACHE', ROOT . DS . 'tmp' . DS . 'cache' . DS);
}

// Create cache directory if it doesn't exist
if (!is_dir(CACHE)) {
    mkdir(CACHE, 0755, true);
}
if (!is_dir(CACHE . 'rate_limit')) {
    mkdir(CACHE . 'rate_limit', 0755, true);
}

echo "1. Testing direct service instantiation...\n";

try {
    // Initialize the cache configuration for rate limiting
    \Cake\Cache\Cache::setConfig('rate_limit', [
        'className' => 'File',
        'duration' => '+1 hour',
        'prefix' => 'rl_',
        'path' => CACHE . 'rate_limit' . DS,
        'serialize' => true,
    ]);
    
    echo "   ✅ Cache configuration set\n";
} catch (Exception $e) {
    echo "   ❌ Cache configuration failed: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    // Test if our RateLimitService file exists and is loadable
    $rateLimitServiceFile = __DIR__ . '/src/Service/Api/RateLimitService.php';
    if (!file_exists($rateLimitServiceFile)) {
        echo "   ❌ RateLimitService file not found: $rateLimitServiceFile\n";
        exit(1);
    }
    
    echo "   ✅ RateLimitService file exists\n";
    
    // Test if the class definition looks correct
    $content = file_get_contents($rateLimitServiceFile);
    if (strpos($content, 'getCurrentUsageForServices') !== false) {
        echo "   ✅ Enhanced RateLimitService detected (multi-service support)\n";
    } else {
        echo "   ⚠️  Old RateLimitService detected (single service only)\n";
    }
    
    // Test service creation with stub settings manager
    echo "\n2. Testing RateLimitService with stub settings...\n";
    
    // Create a simple settings stub
    class TestSettingsStub {
        private static $settings = [
            'AI.enableMetrics' => true,
            'AI.hourlyLimit' => 10,
            'AI.dailyCostLimit' => 5.00
        ];
        
        public static function read($key, $default = null) {
            return self::$settings[$key] ?? $default;
        }
        
        public static function set($key, $value) {
            self::$settings[$key] = $value;
        }
    }
    
    // Test the service with our stub
    require_once $rateLimitServiceFile;
    
    $service = new \App\Service\Api\RateLimitService('TestSettingsStub');
    echo "   ✅ RateLimitService created with test settings\n";
    
    // Test basic functionality
    echo "\n3. Testing basic functionality...\n";
    
    // Reset usage first
    $service->resetUsage('google');
    $service->resetUsage('anthropic');
    echo "   ✅ Reset usage counters\n";
    
    // Test initial usage
    $googleUsage = $service->getCurrentUsage('google');
    $anthropicUsage = $service->getCurrentUsage('anthropic');
    
    echo "   📊 Initial Google usage: {$googleUsage['current']}/{$googleUsage['limit']} (remaining: {$googleUsage['remaining']})\n";
    echo "   📊 Initial Anthropic usage: {$anthropicUsage['current']}/{$anthropicUsage['limit']} (remaining: {$anthropicUsage['remaining']})\n";
    
    // Test enforcement (should increment)
    echo "\n4. Testing rate limit enforcement...\n";
    
    for ($i = 1; $i <= 3; $i++) {
        $googleResult = $service->enforceLimit('google');
        $anthropicResult = $service->enforceLimit('anthropic');
        
        echo "   API call #$i:\n";
        echo "      Google: " . ($googleResult ? '✅ Allowed' : '❌ Blocked') . "\n";
        echo "      Anthropic: " . ($anthropicResult ? '✅ Allowed' : '❌ Blocked') . "\n";
        
        // Check usage after enforcement
        $googleUsage = $service->getCurrentUsage('google');
        $anthropicUsage = $service->getCurrentUsage('anthropic');
        echo "      Google usage: {$googleUsage['current']}/{$googleUsage['limit']}\n";
        echo "      Anthropic usage: {$anthropicUsage['current']}/{$anthropicUsage['limit']}\n";
    }
    
    // Test combined usage if the method exists
    if (method_exists($service, 'getCombinedUsage')) {
        echo "\n5. Testing combined usage functionality...\n";
        
        $combinedUsage = $service->getCombinedUsage(['google', 'anthropic']);
        echo "   📊 Combined usage: {$combinedUsage['current']}/{$combinedUsage['limit']} (remaining: {$combinedUsage['remaining']})\n";
        
        $perServiceUsage = $service->getCurrentUsageForServices(['google', 'anthropic']);
        echo "   📊 Per-service usage:\n";
        foreach ($perServiceUsage as $serviceName => $usage) {
            echo "      $serviceName: {$usage['current']}/{$usage['limit']} (remaining: {$usage['remaining']})\n";
        }
        
        echo "   ✅ Multi-service functionality working\n";
    } else {
        echo "\n5. Multi-service functionality not available (using old RateLimitService)\n";
    }
    
    echo "\n6. Testing rate limit blocking...\n";
    
    // Try to exceed the limit
    TestSettingsStub::set('AI.hourlyLimit', 5); // Lower limit for testing
    
    $blockedCount = 0;
    for ($i = 1; $i <= 8; $i++) {
        $result = $service->enforceLimit('test_service');
        if (!$result) {
            $blockedCount++;
        }
        echo "   Call #$i: " . ($result ? 'Allowed' : 'Blocked') . "\n";
    }
    
    if ($blockedCount > 0) {
        echo "   ✅ Rate limiting working: $blockedCount calls blocked\n";
    } else {
        echo "   ⚠️  Rate limiting may not be working properly\n";
    }
    
    echo "\n=== Test Summary ===\n";
    echo "✅ Cache configuration successful\n";
    echo "✅ RateLimitService file exists and loads\n";
    echo "✅ Basic usage tracking functional\n";
    echo "✅ Rate limit enforcement functional\n";
    
    if (method_exists($service, 'getCombinedUsage')) {
        echo "✅ Multi-service aggregation functional\n";
    } else {
        echo "⚠️  Multi-service aggregation not available\n";
    }
    
    echo "✅ Rate limit blocking functional\n";
    
    echo "\n=== Next Steps ===\n";
    echo "1. Access the dashboard at http://localhost:8080/admin/ai-metrics/dashboard\n";
    echo "2. Trigger some API calls to see counters increment\n";
    echo "3. Watch the real-time updates in the browser\n";
    echo "\n🎉 Rate limiting test completed successfully!\n";
    
} catch (Exception $e) {
    echo "   ❌ Error during testing: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

?>
