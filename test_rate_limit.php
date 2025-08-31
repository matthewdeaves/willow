<?php
/**
 * Rate Limit Test Script
 * Run this to test the rate limiting functionality
 */

// Simple test without full CakePHP bootstrap
echo "=== Rate Limit Test ===\n";

$rateLimitFile = __DIR__ . "/src/Service/Api/RateLimitService.php";
if (!file_exists($rateLimitFile)) {
    echo "❌ RateLimitService file not found\n";
    exit(1);
}

// Check file contents
$content = file_get_contents($rateLimitFile);
if (strpos($content, "getCurrentUsageForServices") !== false) {
    echo "✅ Multi-service methods found\n";
} else {
    echo "❌ Multi-service methods missing\n";
}

if (strpos($content, "rate_limit") !== false) {
    echo "✅ Dedicated cache pool configuration found\n";
} else {
    echo "❌ Dedicated cache pool missing\n";
}

$controllerFile = __DIR__ . "/src/Controller/Admin/AiMetricsController.php";
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    if (strpos($controllerContent, "perServiceUsage") !== false) {
        echo "✅ Controller updated with per-service usage\n";
    } else {
        echo "❌ Controller not updated\n";
    }
} else {
    echo "❌ Controller file not found\n";
}

$templateFile = __DIR__ . "/templates/Admin/AiMetrics/dashboard.php";
if (file_exists($templateFile)) {
    $templateContent = file_get_contents($templateFile);
    if (strpos($templateContent, "rate-limit-total") !== false) {
        echo "✅ Template updated with new rate limit display\n";
    } else {
        echo "❌ Template not updated\n";
    }
} else {
    echo "❌ Template file not found\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Access the dashboard: http://localhost:8080/admin/ai-metrics/dashboard\n";
echo "2. Test rate limit increments by calling API services\n";
echo "3. Verify real-time updates in the browser\n";
echo "4. Run PHPUnit tests if available\n";
echo "\nTest completed!\n";
