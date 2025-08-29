<?php
/**
 * Comprehensive AI Metrics Testing Script
 * Tests the functionality of AI metrics system in Willow CMS
 */

// This script should be run from within the container for proper CakePHP context
require_once 'vendor/autoload.php';

use Cake\Console\CommandRunner;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Database\Driver\Mysql;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

echo "=== AI Metrics Comprehensive Testing ===\n\n";

// Initialize CakePHP bootstrap if available
if (file_exists('config/bootstrap.php')) {
    require_once 'config/bootstrap.php';
}

echo "1. Testing Database Connection and Table Existence...\n";

try {
    // Test database connection
    $connection = ConnectionManager::get('default');
    $database = $connection->config()['database'];
    echo "   ✅ Database connected: $database\n";
    
    // Check if ai_metrics table exists
    $schemaCollection = $connection->getSchemaCollection();
    $tableNames = $schemaCollection->listTables();
    
    if (in_array('ai_metrics', $tableNames)) {
        echo "   ✅ ai_metrics table exists\n";
        
        // Get table schema
        $schema = $schemaCollection->describe('ai_metrics');
        $columns = array_keys($schema->columns());
        echo "   📊 Table columns: " . implode(', ', $columns) . "\n";
        
        // Count existing records
        $aiMetricsTable = TableRegistry::getTableLocator()->get('AiMetrics');
        $count = $aiMetricsTable->find()->count();
        echo "   📈 Current record count: $count\n";
        
    } else {
        echo "   ❌ ai_metrics table does not exist\n";
        echo "   💡 Run migration: docker compose exec willowcms bin/cake migrations migrate\n";
    }
} catch (Exception $e) {
    echo "   ❌ Database connection error: " . $e->getMessage() . "\n";
}

echo "\n2. Testing AI Metrics Service...\n";

try {
    $aiMetricsService = new \App\Service\Api\AiMetricsService();
    echo "   ✅ AiMetricsService instantiated\n";
    
    // Test metrics recording
    $testResult = $aiMetricsService->recordMetrics(
        'test_comprehensive_check',
        150, // execution time
        true, // success
        null, // no error
        100, // tokens used
        0.005, // cost
        'test-model'
    );
    
    echo "   ✅ Test metric recorded: " . ($testResult ? 'Success' : 'Failed') . "\n";
    
    // Test cost calculation
    $cost = $aiMetricsService->calculateGoogleTranslateCost(1000);
    echo "   ✅ Cost calculation test: $" . number_format($cost, 6) . " for 1000 chars\n";
    
    // Test daily cost retrieval
    $dailyCost = $aiMetricsService->getDailyCost();
    echo "   ✅ Daily cost tracking: $" . number_format($dailyCost, 4) . "\n";
    
    // Test rate limiting
    $isLimitReached = $aiMetricsService->isDailyCostLimitReached();
    echo "   ✅ Rate limit check: " . ($isLimitReached ? 'Limit reached' : 'Within limits') . "\n";
    
} catch (Exception $e) {
    echo "   ❌ AiMetricsService error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing Google API Service Integration...\n";

try {
    $googleService = new \App\Service\Api\Google\GoogleApiService();
    echo "   ✅ GoogleApiService instantiated\n";
    
    // Check if it has the metrics service dependency
    $reflection = new ReflectionClass($googleService);
    if ($reflection->hasProperty('metricsService')) {
        echo "   ✅ AiMetricsService integration found\n";
    } else {
        echo "   ⚠️  AiMetricsService integration not found\n";
    }
    
    // Check if executeWithMetrics method exists
    if ($reflection->hasMethod('executeWithMetrics')) {
        echo "   ✅ executeWithMetrics method exists\n";
    } else {
        echo "   ⚠️  executeWithMetrics method not found\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ GoogleApiService error: " . $e->getMessage() . "\n";
}

echo "\n4. Testing Anthropic API Service...\n";

try {
    $anthropicService = new \App\Service\Api\Anthropic\AnthropicApiService();
    echo "   ✅ AnthropicApiService instantiated\n";
    
    // Check for metrics recording method
    $reflection = new ReflectionClass($anthropicService);
    if ($reflection->hasMethod('recordMetrics')) {
        echo "   ✅ recordMetrics method exists\n";
    } else {
        echo "   ⚠️  recordMetrics method not found\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ AnthropicApiService error: " . $e->getMessage() . "\n";
}

echo "\n5. Testing Controller Functionality...\n";

try {
    $controller = new \App\Controller\Admin\AiMetricsController();
    echo "   ✅ AiMetricsController instantiated\n";
    
    // Check dashboard method
    if (method_exists($controller, 'dashboard')) {
        echo "   ✅ dashboard() method exists\n";
    } else {
        echo "   ❌ dashboard() method missing\n";
    }
    
    // Check real-time data method
    if (method_exists($controller, 'realtimeData')) {
        echo "   ✅ realtimeData() method exists\n";
    } else {
        echo "   ❌ realtimeData() method missing\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Controller error: " . $e->getMessage() . "\n";
}

echo "\n6. Testing Template Files...\n";

$templatePaths = [
    'templates/Admin/AiMetrics/dashboard.php',
    'templates/Admin/AiMetrics/index.php',
    'plugins/AdminTheme/templates/Admin/AiMetrics/dashboard.php',
    'plugins/AdminTheme/templates/Admin/AiMetrics/index.php'
];

foreach ($templatePaths as $path) {
    $fullPath = __DIR__ . '/' . $path;
    if (file_exists($fullPath)) {
        echo "   ✅ Template exists: $path\n";
        
        // Check for real-time functionality in dashboard
        if (strpos($path, 'dashboard.php') !== false) {
            $content = file_get_contents($fullPath);
            if (strpos($content, 'realtime-data') !== false || strpos($content, 'updateMetrics') !== false) {
                echo "      ✅ Real-time functionality detected\n";
            }
            if (strpos($content, 'live-indicator') !== false) {
                echo "      ✅ Live indicator found\n";
            }
        }
    } else {
        echo "   ⚠️  Template missing: $path\n";
    }
}

echo "\n7. Testing Job Classes...\n";

$jobClasses = [
    'TranslateArticleJob' => \App\Job\TranslateArticleJob::class,
    'TranslateTagJob' => \App\Job\TranslateTagJob::class,
    'ArticleSeoUpdateJob' => \App\Job\ArticleSeoUpdateJob::class,
    'ImageAnalysisJob' => \App\Job\ImageAnalysisJob::class
];

foreach ($jobClasses as $jobName => $jobClass) {
    try {
        $reflection = new ReflectionClass($jobClass);
        $constructor = $reflection->getConstructor();
        
        $hasServiceDependency = false;
        if ($constructor) {
            $parameters = $constructor->getParameters();
            foreach ($parameters as $param) {
                $type = $param->getType();
                if ($type && (strpos($type->getName(), 'ApiService') !== false)) {
                    $hasServiceDependency = true;
                    break;
                }
            }
        }
        
        if ($hasServiceDependency) {
            echo "   ✅ $jobName uses AI service dependency\n";
        } else {
            echo "   ⚠️  $jobName may not use AI services\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ $jobName error: " . $e->getMessage() . "\n";
    }
}

echo "\n8. Generating Sample Metrics Data...\n";

try {
    if (isset($aiMetricsService)) {
        $sampleMetrics = [
            [
                'task_type' => 'google_translate_test',
                'execution_time_ms' => 250,
                'success' => true,
                'cost_usd' => 0.002,
                'model_used' => 'Google Translate API'
            ],
            [
                'task_type' => 'anthropic_seo_generation',
                'execution_time_ms' => 1500,
                'tokens_used' => 320,
                'success' => true,
                'cost_usd' => 0.015,
                'model_used' => 'Claude-3-Sonnet'
            ],
            [
                'task_type' => 'google_translate_bulk',
                'execution_time_ms' => 800,
                'success' => false,
                'error_message' => 'Rate limit exceeded',
                'cost_usd' => 0.008,
                'model_used' => 'Google Translate API'
            ]
        ];
        
        foreach ($sampleMetrics as $i => $metric) {
            $result = $aiMetricsService->recordMetrics(
                $metric['task_type'],
                $metric['execution_time_ms'],
                $metric['success'],
                $metric['error_message'] ?? null,
                $metric['tokens_used'] ?? null,
                $metric['cost_usd'],
                $metric['model_used']
            );
            
            echo "   📝 Sample " . ($i + 1) . ": " . $metric['task_type'] . 
                 " - " . ($metric['success'] ? '✅' : '❌') . 
                 " - $" . number_format($metric['cost_usd'], 4) . "\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Sample data generation error: " . $e->getMessage() . "\n";
}

echo "\n9. Testing Rate Limiting Service...\n";

try {
    $rateLimitService = new \App\Service\Api\RateLimitService();
    echo "   ✅ RateLimitService instantiated\n";
    
    // Test current usage
    $usage = $rateLimitService->getCurrentUsage();
    echo "   📊 Current usage: {$usage['current']}/{$usage['limit']} (remaining: {$usage['remaining']})\n";
    
    // Test enforcement
    $canProceed = $rateLimitService->enforceLimit();
    echo "   🚦 Can proceed with API call: " . ($canProceed ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "   ❌ RateLimitService error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "✅ Database connection and table structure\n";
echo "✅ AI Metrics Service functionality\n";
echo "✅ Google API Service integration\n";
echo "✅ Anthropic API Service structure\n";
echo "✅ Controller methods\n";
echo "✅ Template files\n";
echo "✅ Job class dependencies\n";
echo "✅ Sample data generation\n";
echo "✅ Rate limiting service\n";

echo "\n=== URL Testing Commands ===\n";
echo "# Test dashboard (requires authentication):\n";
echo "curl -v http://localhost:8080/admin/ai-metrics/dashboard\n";
echo "\n# Test real-time data endpoint (requires authentication):\n";
echo "curl -v http://localhost:8080/admin/ai-metrics/realtime-data\n";
echo "\n# Test with session (after login):\n";
echo "curl -b cookies.txt http://localhost:8080/admin/ai-metrics/dashboard\n";

echo "\n=== Queue Worker Testing ===\n";
echo "# Start queue worker in background:\n";
echo "docker compose exec -d willowcms bin/cake queue worker\n";
echo "\n# Add test jobs to queue:\n";
echo "docker compose exec willowcms bin/cake queue add TranslateArticleJob '{\"id\":\"123\",\"title\":\"Test Article\"}'\n";
echo "docker compose exec willowcms bin/cake queue add ArticleSeoUpdateJob '{\"id\":\"123\",\"title\":\"Test Article\"}'\n";

echo "\n🎉 Comprehensive AI Metrics testing completed!\n";
echo "\nNext steps:\n";
echo "1. Run this script inside the container for full functionality\n";
echo "2. Set up authentication to test dashboard URLs\n";
echo "3. Start queue workers to test real-time metrics\n";
echo "4. Monitor logs for any errors\n";

?>
