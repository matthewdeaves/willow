<?php
/**
 * Routes configuration for Admin plugin
 * 
 * Add this to your config/routes.php file or plugins/Admin/config/routes.php
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * Admin Plugin Routes
 * Scope: /admin
 */
$routes->plugin('Admin', ['path' => '/admin'], function (RouteBuilder $builder) {
    // Cost Analysis page route
    $builder->connect('/pages/cost-analysis', [
        'controller' => 'Pages',
        'action' => 'costAnalysis'
    ])->setName('admin.pages.cost-analysis');

    // Other admin pages routes
    $builder->connect('/pages/{page}', [
        'controller' => 'Pages',
        'action' => 'display'
    ])->setPass(['page'])->setName('admin.pages.display');

    // Admin index route
    $builder->connect('/', [
        'controller' => 'Dashboard',
        'action' => 'index'
    ])->setName('admin.dashboard');

    // Fallback routes for admin
    $builder->fallbacks(DashedRoute::class);
});

/*
 * Alternative: If not using a plugin, add to main routes
 */
// $routes->scope('/admin', function (RouteBuilder $builder) {
//     $builder->connect('/pages/cost-analysis', [
//         'controller' => 'Admin\Pages',
//         'action' => 'costAnalysis'
//     ])->setName('admin.pages.cost-analysis');
// });
