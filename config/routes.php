<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
  * So you can use  `$this` to reference the application class instance
  * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        /*
         * Here, we are connecting '/' (base path) to a controller called 'Pages',
         * its action called 'display', and we pass a param to select the view file
         * to use (in this case, templates/Pages/home.php)...
         */
        //$builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

        /*
         * ...and connect the rest of 'Pages' controller's URLs.
         */
        //$builder->connect('/pages/*', 'Pages::display');


        /**
         * Connects the root URL ('/') to the 'index' action of the 'Articles' controller.
         *
         * This route configuration sets the default landing page for the application.
         * When a user visits the root URL, they will be directed to the article listing page.
         *
         * @param string $url The URL pattern to match. In this case, it's the root URL ('/').
         * @param array $defaults An array specifying the default route elements:
         *                        - 'controller': The controller to use ('Articles')
         *                        - 'action': The action to call within the controller ('index')
         *
         * @return \Cake\Routing\Route\Route The created route object.
         *
         * @see \Cake\Routing\RouteBuilder::connect()
         * @see \App\Controller\ArticlesController::index()
         */
        $builder->connect('/', ['controller' => 'Articles', 'action' => 'index']);


        $builder->connect('/sitemap', ['controller' => 'Sitemap', 'action' => 'index'])->setExtensions(['xml']);

        /*
         * Connect catchall routes for all controllers.
         *
         * The `fallbacks` method is a shortcut for
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         *
         * You can remove these routes once you've connected the
         * routes you want in your application.
         */
        $builder->fallbacks();
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */

     $routes->prefix('Admin', function (RouteBuilder $routes) {
        // All routes here will be prefixed with `/admin`, and
        // have the `'prefix' => 'Admin'` route element added that
        // will be required when generating URLs for these routes

        $routes->connect('/', ['controller' => 'Articles', 'action' => 'index', 'prefix' => 'Admin']);
        
        $routes->fallbacks(DashedRoute::class);
    });
};
