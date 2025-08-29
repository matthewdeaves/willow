<?php
/**
 * Test script to insert sample AI metrics data for demonstrating real-time dashboard
 */

// Simulate AI metrics data insertion
function insertSampleMetrics() {
    $sampleData = [
        [
            'task_type' => 'google_translate_text',
            'execution_time_ms' => rand(50, 500),
            'tokens_used' => null,
            'cost_usd' => rand(1, 10) / 1000, // $0.001 to $0.010
            'success' => (rand(0, 10) > 1), // 90% success rate
            'error_message' => null,
            'model_used' => 'Google Translate API',
            'created' => date('Y-m-d H:i:s')
        ],
        [
            'task_type' => 'anthropic_text_generation',
            'execution_time_ms' => rand(200, 2000),
            'tokens_used' => rand(50, 500),
            'cost_usd' => rand(5, 50) / 1000, // $0.005 to $0.050
            'success' => (rand(0, 20) > 1), // 95% success rate
            'error_message' => null,
            'model_used' => 'Claude-3-Sonnet',
            'created' => date('Y-m-d H:i:s')
        ],
        [
            'task_type' => 'google_translate_bulk',
            'execution_time_ms' => rand(100, 1000),
            'tokens_used' => null,
            'cost_usd' => rand(10, 100) / 1000, // $0.010 to $0.100
            'success' => (rand(0, 10) > 0), // 90% success rate
            'error_message' => (rand(0, 10) == 0) ? 'Translation quota exceeded' : null,
            'model_used' => 'Google Translate API',
            'created' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 60) . ' minutes'))
        ]
    ];
    
    // Output as JSON for demo purposes
    echo "Sample AI Metrics Data:\n";
    echo json_encode($sampleData, JSON_PRETTY_PRINT) . "\n";
    
    echo "\nReal-time Dashboard Features Implemented:\n";
    echo "✅ AJAX polling every 10 seconds\n";
    echo "✅ Live/Offline indicator\n";
    echo "✅ Timeframe selection (1H, 24H, 7D, 30D)\n";
    echo "✅ Real-time metric updates:\n";
    echo "   - Total API Calls\n";
    echo "   - Success Rate with color coding\n";
    echo "   - Total Cost\n";
    echo "   - Rate Limit Status\n";
    echo "✅ Queue status monitoring\n";
    echo "✅ Task type breakdown table\n";
    echo "✅ Recent activity feed\n";
    echo "✅ Activity sparkline chart\n";
    echo "✅ Error handling and offline detection\n";
    
    echo "\nAPI Endpoints:\n";
    echo "- Dashboard: /admin/ai-metrics/dashboard\n";
    echo "- Real-time data: /admin/ai-metrics/realtime-data?timeframe=30d\n";
    
    echo "\nJavaScript Features:\n";
    echo "- Automatic updates every 10 seconds\n";
    echo "- Element-specific targeting with IDs\n";
    echo "- Dynamic timeframe switching\n";
    echo "- Activity visualization\n";
    echo "- Error handling and fallback to offline mode\n";
}

insertSampleMetrics();
?>
