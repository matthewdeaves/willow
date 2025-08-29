<?php
/**
 * Simple AI Metrics URL Testing Script
 * Tests the accessibility and functionality of AI metrics endpoints
 */

echo "=== AI Metrics URL Testing ===\n\n";

$baseUrl = 'http://localhost:8080';
$endpoints = [
    'dashboard' => '/admin/ai-metrics/dashboard',
    'realtime-data' => '/admin/ai-metrics/realtime-data?timeframe=1h',
    'realtime-data-30d' => '/admin/ai-metrics/realtime-data?timeframe=30d'
];

function testUrl($name, $url) {
    echo "Testing $name: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ cURL Error: $error\n";
        return false;
    }
    
    echo "   📊 HTTP Status: $httpCode\n";
    echo "   📊 Content Type: $contentType\n";
    echo "   📊 Response Time: " . round($totalTime * 1000, 2) . "ms\n";
    
    if ($httpCode == 200) {
        echo "   ✅ Endpoint accessible\n";
        
        // Check if it's JSON for API endpoints
        if (strpos($name, 'realtime') !== false) {
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($response, $headerSize);
            $jsonData = json_decode($body, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "   ✅ Valid JSON response\n";
                if (isset($jsonData['success'])) {
                    echo "   ✅ Success flag: " . ($jsonData['success'] ? 'true' : 'false') . "\n";
                }
                if (isset($jsonData['data'])) {
                    echo "   ✅ Data object present\n";
                    $dataKeys = array_keys($jsonData['data']);
                    echo "   📊 Data keys: " . implode(', ', array_slice($dataKeys, 0, 5)) . "\n";
                }
            } else {
                echo "   ⚠️  Invalid JSON response\n";
            }
        }
        return true;
    } else if ($httpCode == 302) {
        echo "   🔀 Redirected (likely requires authentication)\n";
        // Extract redirect location
        if (preg_match('/Location: (.+)/', $response, $matches)) {
            echo "   📍 Redirect to: " . trim($matches[1]) . "\n";
        }
        return true;
    } else if ($httpCode == 404) {
        echo "   ❌ Not found (route may not exist)\n";
        return false;
    } else {
        echo "   ❌ HTTP Error: $httpCode\n";
        return false;
    }
}

$results = [];
foreach ($endpoints as $name => $path) {
    $fullUrl = $baseUrl . $path;
    $results[$name] = testUrl($name, $fullUrl);
    echo "\n";
}

echo "=== Route Verification ===\n";
echo "Checking if AI metrics routes are configured...\n";

// Test if the routes are properly set up
$routeTestScript = '
require_once "vendor/autoload.php";
require_once "config/bootstrap.php";

use Cake\Routing\Router;
use Cake\Http\ServerRequest;

try {
    $request = new ServerRequest([
        "url" => "/admin/ai-metrics/dashboard",
        "environment" => ["REQUEST_METHOD" => "GET"]
    ]);
    
    $route = Router::parseRequest($request);
    
    if ($route && isset($route["controller"]) && $route["controller"] === "AiMetrics") {
        echo "✅ Dashboard route correctly configured\n";
        echo "   Controller: " . $route["controller"] . "\n";
        echo "   Action: " . $route["action"] . "\n";
    } else {
        echo "❌ Dashboard route not found or misconfigured\n";
    }
} catch (Exception $e) {
    echo "❌ Route test error: " . $e->getMessage() . "\n";
}
';

file_put_contents('/tmp/route_test.php', $routeTestScript);

echo "\n=== Summary ===\n";
$successCount = count(array_filter($results));
$totalCount = count($results);

echo "Endpoint Tests: $successCount/$totalCount successful\n";

if ($results['dashboard']) {
    echo "✅ Dashboard endpoint is accessible\n";
} else {
    echo "❌ Dashboard endpoint has issues\n";
}

if ($results['realtime-data']) {
    echo "✅ Real-time data endpoint is working\n";
} else {
    echo "❌ Real-time data endpoint has issues\n";
}

echo "\n=== Next Steps ===\n";
echo "1. 🔐 Set up admin authentication to access the dashboard\n";
echo "2. 📊 View the dashboard at: $baseUrl/admin/ai-metrics/dashboard\n";
echo "3. 🔄 Test real-time updates by running queue workers\n";
echo "4. 📈 Generate AI operations to see metrics in action\n";

echo "\n=== Authentication Setup ===\n";
echo "To test the dashboard with authentication:\n";
echo "1. Log into the admin area first\n";
echo "2. Then navigate to the AI metrics dashboard\n";
echo "3. Or use a tool like Postman to send authenticated requests\n";

?>
