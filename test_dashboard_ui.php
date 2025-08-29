<?php
/**
 * Simple UI test to check dashboard access and real-time functionality
 */

echo "=== AI Metrics Dashboard UI Test ===\n\n";

// Test 1: Check if dashboard route is accessible
echo "1. Testing dashboard route accessibility...\n";
try {
    $url = 'http://localhost:8080/admin/ai-metrics/dashboard';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ cURL Error: $error\n";
    } else if ($httpCode == 200) {
        echo "   ✅ Dashboard route accessible (HTTP $httpCode)\n";
        
        // Check if response contains expected elements
        if (strpos($response, 'AI Metrics Dashboard') !== false) {
            echo "   ✅ Dashboard title found\n";
        } else {
            echo "   ⚠️  Dashboard title not found\n";
        }
        
        if (strpos($response, 'LIVE') !== false || strpos($response, 'live-indicator') !== false) {
            echo "   ✅ Live indicator elements found\n";
        } else {
            echo "   ⚠️  Live indicator elements not found\n";
        }
        
        if (strpos($response, 'realtime-data') !== false || strpos($response, 'updateMetrics') !== false) {
            echo "   ✅ Real-time JavaScript functionality found\n";
        } else {
            echo "   ⚠️  Real-time JavaScript functionality not found\n";
        }
        
    } else if ($httpCode >= 300 && $httpCode < 400) {
        echo "   ⚠️  Dashboard redirected (HTTP $httpCode) - likely authentication required\n";
    } else if ($httpCode == 404) {
        echo "   ❌ Dashboard not found (HTTP $httpCode) - route may not be configured\n";
    } else {
        echo "   ❌ Dashboard error (HTTP $httpCode)\n";
    }
} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
}

// Test 2: Check real-time data endpoint
echo "\n2. Testing real-time data endpoint...\n";
try {
    $url = 'http://localhost:8080/admin/ai-metrics/realtime-data?timeframe=1h';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ cURL Error: $error\n";
    } else if ($httpCode == 200) {
        echo "   ✅ Real-time data endpoint accessible (HTTP $httpCode)\n";
        
        // Try to decode JSON response
        $jsonData = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "   ✅ Valid JSON response received\n";
            
            if (isset($jsonData['success'])) {
                echo "   ✅ Response contains success flag: " . ($jsonData['success'] ? 'true' : 'false') . "\n";
            }
            
            if (isset($jsonData['data'])) {
                echo "   ✅ Response contains data object\n";
                
                $expectedFields = ['totalCalls', 'successRate', 'totalCost', 'taskMetrics', 'queueStatus'];
                foreach ($expectedFields as $field) {
                    if (isset($jsonData['data'][$field])) {
                        echo "   ✅ Data contains $field\n";
                    } else {
                        echo "   ⚠️  Data missing $field\n";
                    }
                }
            } else {
                echo "   ⚠️  Response missing data object\n";
            }
        } else {
            echo "   ⚠️  Invalid JSON response\n";
            echo "   Response preview: " . substr($response, 0, 200) . "...\n";
        }
        
    } else if ($httpCode >= 300 && $httpCode < 400) {
        echo "   ⚠️  Real-time endpoint redirected (HTTP $httpCode) - likely authentication required\n";
    } else {
        echo "   ❌ Real-time endpoint error (HTTP $httpCode)\n";
    }
} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
}

// Test 3: Check if queue worker is running
echo "\n3. Testing queue worker status...\n";
try {
    // Check for running processes
    $output = shell_exec('ps aux | grep "queue" | grep -v grep');
    if ($output) {
        echo "   ✅ Queue-related processes found:\n";
        $lines = explode("\n", trim($output));
        foreach ($lines as $line) {
            if (trim($line)) {
                echo "   " . trim($line) . "\n";
            }
        }
    } else {
        echo "   ⚠️  No queue worker processes found\n";
        echo "   💡 To test real-time metrics, run: cake_queue_worker\n";
    }
} catch (Exception $e) {
    echo "   ❌ Exception checking queue worker: " . $e->getMessage() . "\n";
}

// Test 4: Generate some sample metrics data
echo "\n4. Testing sample metrics generation...\n";
try {
    echo "   📊 Generating sample AI metrics data...\n";
    
    $sampleMetrics = [
        [
            'task_type' => 'google_translate_test',
            'execution_time_ms' => 250,
            'cost_usd' => 0.002,
            'success' => true,
            'model_used' => 'Google Translate API',
            'created' => date('Y-m-d H:i:s')
        ],
        [
            'task_type' => 'anthropic_seo_test',
            'execution_time_ms' => 1500,
            'tokens_used' => 320,
            'cost_usd' => 0.015,
            'success' => true,
            'model_used' => 'Claude-3-Sonnet',
            'created' => date('Y-m-d H:i:s', strtotime('-2 minutes'))
        ],
        [
            'task_type' => 'google_translate_bulk',
            'execution_time_ms' => 800,
            'cost_usd' => 0.008,
            'success' => false,
            'error_message' => 'Rate limit exceeded',
            'model_used' => 'Google Translate API',
            'created' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
        ]
    ];
    
    foreach ($sampleMetrics as $i => $metric) {
        echo "   📝 Sample Metric " . ($i + 1) . ": " . $metric['task_type'] . 
             " - " . ($metric['success'] ? '✅ Success' : '❌ Failed') . 
             " - Cost: $" . number_format($metric['cost_usd'], 4) . "\n";
    }
    
    echo "   💡 These metrics would be recorded in a real scenario when queue jobs run\n";
    
} catch (Exception $e) {
    echo "   ❌ Exception generating sample data: " . $e->getMessage() . "\n";
}

// Test Summary
echo "\n=== Test Summary ===\n";
echo "✅ Dashboard route testing completed\n";
echo "✅ Real-time data endpoint testing completed\n";
echo "✅ Queue worker status checking completed\n";
echo "✅ Sample metrics generation completed\n";

echo "\n=== How to Test Real-Time Functionality ===\n";
echo "1. 🌐 Open browser to: http://localhost:8080/admin/ai-metrics/dashboard\n";
echo "2. 🔐 Login with admin credentials if required\n";
echo "3. 👀 Look for LIVE/OFFLINE indicator in top right of dashboard\n";
echo "4. ⏱️  Dashboard should update every 10 seconds automatically\n";
echo "5. 🔄 Use timeframe buttons (1H, 24H, 7D, 30D) to test dynamic switching\n";
echo "6. 🎯 Run queue workers to generate real metrics data\n";
echo "7. 📊 Watch metrics update in real-time as jobs process\n";

echo "\n=== Queue Worker Commands ===\n";
echo "# Start queue worker:\n";
echo "docker-compose exec willowcms bin/cake queue worker\n";
echo "\n# Queue some translation jobs:\n";
echo "docker-compose exec willowcms bin/cake queue add TranslateArticleJob '{\"id\":\"123\",\"title\":\"Test Article\"}'\n";
echo "docker-compose exec willowcms bin/cake queue add ArticleSeoUpdateJob '{\"id\":\"123\",\"title\":\"Test Article\"}'\n";

echo "\n🎉 AI Metrics Dashboard UI testing completed!\n";
?>
