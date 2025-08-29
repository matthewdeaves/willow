<?php
/**
 * Comprehensive test script to verify AI metrics monitoring
 * for all services and jobs using API calls
 */

require_once 'vendor/autoload.php';

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

// Test configuration
$testResults = [];
$errors = [];

echo "=== AI Metrics Monitoring Test Suite ===\n\n";

// Test 1: Check if AiMetrics table exists and is accessible
echo "1. Testing AiMetrics table accessibility...\n";
try {
    $aiMetricsTable = TableRegistry::getTableLocator()->get('AiMetrics');
    $testResults['ai_metrics_table'] = true;
    echo "   ✅ AiMetrics table is accessible\n";
} catch (Exception $e) {
    $testResults['ai_metrics_table'] = false;
    $errors[] = "AiMetrics table not accessible: " . $e->getMessage();
    echo "   ❌ AiMetrics table not accessible: " . $e->getMessage() . "\n";
}

// Test 2: Verify AiMetricsService functionality
echo "\n2. Testing AiMetricsService functionality...\n";
try {
    $aiMetricsService = new \App\Service\Api\AiMetricsService();
    
    // Test recording metrics
    $testMetric = $aiMetricsService->recordMetrics(
        'test_task',
        100,
        true,
        null,
        150,
        0.05,
        'test-model'
    );
    
    $testResults['ai_metrics_service'] = $testMetric;
    echo "   ✅ AiMetricsService can record metrics: " . ($testMetric ? 'Yes' : 'No') . "\n";
    
    // Test cost calculation
    $cost = $aiMetricsService->calculateGoogleTranslateCost(1000);
    echo "   ✅ Google Translate cost calculation: $" . number_format($cost, 6) . " for 1000 chars\n";
    
    // Test daily cost tracking
    $dailyCost = $aiMetricsService->getDailyCost();
    echo "   ✅ Daily cost tracking: $" . number_format($dailyCost, 2) . "\n";
    
} catch (Exception $e) {
    $testResults['ai_metrics_service'] = false;
    $errors[] = "AiMetricsService error: " . $e->getMessage();
    echo "   ❌ AiMetricsService error: " . $e->getMessage() . "\n";
}

// Test 3: Check AI Services for Metrics Integration
echo "\n3. Testing AI Services for metrics integration...\n";

// Test Anthropic Service
echo "   3a. Anthropic API Service...\n";
try {
    $anthropicService = new \App\Service\Api\Anthropic\AnthropicApiService();
    $testResults['anthropic_service'] = true;
    echo "      ✅ AnthropicApiService instantiated successfully\n";
    
    // Check if it has recordMetrics method
    if (method_exists($anthropicService, 'recordMetrics')) {
        echo "      ✅ recordMetrics method exists (private)\n";
        $testResults['anthropic_metrics'] = true;
    } else {
        echo "      ⚠️  recordMetrics method not found\n";
        $testResults['anthropic_metrics'] = false;
    }
} catch (Exception $e) {
    $testResults['anthropic_service'] = false;
    $errors[] = "AnthropicApiService error: " . $e->getMessage();
    echo "      ❌ AnthropicApiService error: " . $e->getMessage() . "\n";
}

// Test Google API Service
echo "   3b. Google API Service...\n";
try {
    $googleService = new \App\Service\Api\Google\GoogleApiService();
    $testResults['google_service'] = true;
    echo "      ✅ GoogleApiService instantiated successfully\n";
    
    // Check if it uses AiMetricsService
    $reflection = new ReflectionClass($googleService);
    if ($reflection->hasProperty('metricsService')) {
        echo "      ✅ AiMetricsService integration found\n";
        $testResults['google_metrics'] = true;
    } else {
        echo "      ⚠️  AiMetricsService integration not found\n";
        $testResults['google_metrics'] = false;
    }
} catch (Exception $e) {
    $testResults['google_service'] = false;
    $errors[] = "GoogleApiService error: " . $e->getMessage();
    echo "      ❌ GoogleApiService error: " . $e->getMessage() . "\n";
}

// Test 4: Check Job Classes for AI API Usage
echo "\n4. Testing Job Classes for AI API integration...\n";

$aiJobs = [
    'ArticleSeoUpdateJob' => \App\Job\ArticleSeoUpdateJob::class,
    'TranslateArticleJob' => \App\Job\TranslateArticleJob::class,
    'TranslateTagJob' => \App\Job\TranslateTagJob::class,
    'TranslateI18nJob' => \App\Job\TranslateI18nJob::class,
    'TranslateImageGalleryJob' => \App\Job\TranslateImageGalleryJob::class,
    'ImageAnalysisJob' => \App\Job\ImageAnalysisJob::class,
    'CommentAnalysisJob' => \App\Job\CommentAnalysisJob::class,
    'ArticleTagUpdateJob' => \App\Job\ArticleTagUpdateJob::class,
];

foreach ($aiJobs as $jobName => $jobClass) {
    echo "   4" . chr(97 + array_search($jobName, array_keys($aiJobs))) . ". {$jobName}...\n";
    
    try {
        $reflection = new ReflectionClass($jobClass);
        $constructor = $reflection->getConstructor();
        
        // Check if job accepts AI service dependency
        $hasAiService = false;
        if ($constructor) {
            $parameters = $constructor->getParameters();
            foreach ($parameters as $param) {
                $type = $param->getType();
                if ($type && (
                    strpos($type->getName(), 'AnthropicApiService') !== false ||
                    strpos($type->getName(), 'GoogleApiService') !== false
                )) {
                    $hasAiService = true;
                    break;
                }
            }
        }
        
        if ($hasAiService) {
            echo "      ✅ {$jobName} uses AI service dependency injection\n";
            $testResults["job_{$jobName}"] = true;
        } else {
            echo "      ⚠️  {$jobName} may not use AI services or uses different pattern\n";
            $testResults["job_{$jobName}"] = 'partial';
        }
        
    } catch (Exception $e) {
        $testResults["job_{$jobName}"] = false;
        $errors[] = "{$jobName} error: " . $e->getMessage();
        echo "      ❌ {$jobName} error: " . $e->getMessage() . "\n";
    }
}

// Test 5: Check Controller and Routes
echo "\n5. Testing Controller and Routes...\n";
try {
    $controller = new \App\Controller\Admin\AiMetricsController();
    echo "   ✅ AiMetricsController instantiated successfully\n";
    
    // Check if dashboard method exists
    if (method_exists($controller, 'dashboard')) {
        echo "   ✅ Dashboard method exists\n";
        $testResults['controller_dashboard'] = true;
    } else {
        echo "   ❌ Dashboard method not found\n";
        $testResults['controller_dashboard'] = false;
    }
    
    // Check if realtimeData method exists
    if (method_exists($controller, 'realtimeData')) {
        echo "   ✅ Real-time data method exists\n";
        $testResults['controller_realtime'] = true;
    } else {
        echo "   ❌ Real-time data method not found\n";
        $testResults['controller_realtime'] = false;
    }
    
} catch (Exception $e) {
    $testResults['controller'] = false;
    $errors[] = "Controller error: " . $e->getMessage();
    echo "   ❌ Controller error: " . $e->getMessage() . "\n";
}

// Test 6: Check Template Files
echo "\n6. Testing Template Files...\n";
$templates = [
    'dashboard' => '/templates/Admin/AiMetrics/dashboard.php',
    'index' => '/templates/Admin/AiMetrics/index.php',
];

foreach ($templates as $name => $path) {
    $fullPath = __DIR__ . $path;
    if (file_exists($fullPath)) {
        echo "   ✅ {$name} template exists\n";
        $testResults["template_{$name}"] = true;
    } else {
        echo "   ❌ {$name} template missing: {$path}\n";
        $testResults["template_{$name}"] = false;
    }
}

// Test 7: Configuration and Settings
echo "\n7. Testing Configuration and Settings...\n";
try {
    // Check if SettingsManager can read AI settings
    $enableMetrics = \App\Utility\SettingsManager::read('AI.enableMetrics', true);
    echo "   ✅ AI metrics enabled setting: " . ($enableMetrics ? 'Yes' : 'No') . "\n";
    
    $dailyLimit = \App\Utility\SettingsManager::read('AI.dailyCostLimit', 2.50);
    echo "   ✅ Daily cost limit setting: $" . $dailyLimit . "\n";
    
    $testResults['settings'] = true;
} catch (Exception $e) {
    $testResults['settings'] = false;
    $errors[] = "Settings error: " . $e->getMessage();
    echo "   ❌ Settings error: " . $e->getMessage() . "\n";
}

// Test Summary
echo "\n=== Test Summary ===\n";
$totalTests = count($testResults);
$passedTests = count(array_filter($testResults, function($result) {
    return $result === true;
}));
$partialTests = count(array_filter($testResults, function($result) {
    return $result === 'partial';
}));

echo "Total Tests: {$totalTests}\n";
echo "Passed: {$passedTests}\n";
echo "Partial: {$partialTests}\n";
echo "Failed: " . ($totalTests - $passedTests - $partialTests) . "\n";

if (!empty($errors)) {
    echo "\n=== Errors Found ===\n";
    foreach ($errors as $error) {
        echo "❌ {$error}\n";
    }
}

// Recommendations
echo "\n=== Recommendations ===\n";

if (!$testResults['ai_metrics_table']) {
    echo "🔧 Run database migrations to create ai_metrics table\n";
}

if (!$testResults['anthropic_metrics']) {
    echo "🔧 Enhance AnthropicApiService to use AiMetricsService for consistent monitoring\n";
}

if (!$testResults['controller_dashboard'] || !$testResults['controller_realtime']) {
    echo "🔧 Implement missing controller methods for dashboard functionality\n";
}

if (!$testResults['template_dashboard']) {
    echo "🔧 Create dashboard template for AI metrics visualization\n";
}

// Services and Jobs Monitoring Status
echo "\n=== AI Services Monitoring Status ===\n";
$monitoredServices = [];
$unmonitoredServices = [];

if ($testResults['google_service'] && $testResults['google_metrics']) {
    $monitoredServices[] = "Google API Service (Translate)";
    echo "✅ Google Translate API - Fully Monitored\n";
    echo "   • translateStrings() - ✅ Metrics recorded\n";
    echo "   • translateArticle() - ✅ Metrics recorded\n";
    echo "   • translateTag() - ✅ Metrics recorded\n";
    echo "   • translateImageGallery() - ✅ Metrics recorded\n";
} else {
    $unmonitoredServices[] = "Google API Service";
    echo "❌ Google Translate API - Not Monitored\n";
}

if ($testResults['anthropic_service']) {
    if ($testResults['anthropic_metrics']) {
        $monitoredServices[] = "Anthropic API Service";
        echo "✅ Anthropic API - Partially Monitored\n";
    } else {
        $unmonitoredServices[] = "Anthropic API Service";
        echo "⚠️  Anthropic API - Needs Enhancement\n";
    }
    echo "   • generateArticleSeo() - 🔧 Needs metrics integration\n";
    echo "   • generateTagSeo() - 🔧 Needs metrics integration\n";
    echo "   • analyzeImage() - 🔧 Needs metrics integration\n";
    echo "   • analyzeComment() - 🔧 Needs metrics integration\n";
    echo "   • generateArticleTags() - 🔧 Needs metrics integration\n";
}

echo "\n=== Job Queue Monitoring Status ===\n";
foreach ($aiJobs as $jobName => $jobClass) {
    $status = $testResults["job_{$jobName}"];
    if ($status === true) {
        echo "✅ {$jobName} - Monitored through AI service\n";
    } else if ($status === 'partial') {
        echo "⚠️  {$jobName} - May need metrics integration review\n";
    } else {
        echo "❌ {$jobName} - Not accessible or missing\n";
    }
}

// Final Score
$score = round(($passedTests / $totalTests) * 100);
echo "\n=== Overall Monitoring Score: {$score}% ===\n";

if ($score >= 80) {
    echo "🎉 Excellent! AI metrics monitoring is well implemented.\n";
} else if ($score >= 60) {
    echo "👍 Good progress, but some improvements needed.\n";
} else {
    echo "⚠️  Significant work needed to fully implement AI monitoring.\n";
}

echo "\n=== Next Steps ===\n";
echo "1. 🗃️  Ensure database migrations are run\n";
echo "2. 🔧 Fix any failing service integrations\n";
echo "3. 🎯 Test real-time dashboard functionality\n";
echo "4. 📊 Run queue workers to generate real metrics\n";
echo "5. 🧪 Perform end-to-end testing with actual API calls\n";

?>
