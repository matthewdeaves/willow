<?php

use Cake\Core\Configure;

// Environment detection
$isTest = env('CAKE_ENV') === 'test';
$isDev = Configure::read('debug');

return [
    // Core authentication plugin - always loaded
    'Authentication' => [
        'bootstrap' => true,
    ],
    
    // Queue system - skip in test environment
    'Cake/Queue' => [
        'bootstrap' => true,
        'routes' => true,
        'ignoreMissing' => $isTest, // Skip loading in test environment
    ],
    
    // CLI-only plugins
    'Bake' => [
        'onlyCli' => true,
        'optional' => true,
    ],
    'Migrations' => [
        'onlyCli' => true,
    ],
    
    // Core application plugins
    'Josegonzalez/Upload' => [],
    'AdminTheme' => [],
    'DefaultTheme' => [],
    'ADmad/I18n' => [],
    
    // Development-only plugins
    'DebugKit' => [
        'onlyDev' => true,
        'optional' => true, // Won't break if missing in --no-dev installs
    ],
];
