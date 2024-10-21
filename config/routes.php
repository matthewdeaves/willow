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

        /**
         * Connects a route for the sitemap.
         *
         * This route maps the URL '/sitemap' to the 'index' action of the 'Sitemap' controller.
         * It is configured to handle requests with the 'xml' file extension.
         *
         * @param string $url The URL pattern to match ('/sitemap').
         * @param array $defaults An array of default route parameters:
         *                        - 'controller': The controller to use ('Sitemap')
         *                        - 'action': The action to call within the controller ('index')
         *
         * @return \Cake\Routing\Route\Route The connected route instance.
         *
         * Example usage:
         * - Accessing /sitemap will invoke SitemapController::index()
         * - Accessing /sitemap.xml will also invoke SitemapController::index() with XML response
         */
        $builder->connect('/sitemap', ['controller' => 'Sitemap', 'action' => 'index'])->setExtensions(['xml']);

        /**
         * User-related route configurations.
         *
         * This block defines routes for various user actions including login, registration,
         * logout, email confirmation, and profile editing. These routes map specific URLs
         * to corresponding actions in the Users controller.
         *
         * Routes defined:
         * - /users/login: Handles user login requests.
         * - /users/register: Processes new user registrations.
         * - /users/logout: Manages user logout functionality.
         * - /users/confirm-email/*: Handles email confirmation with a wildcard parameter.
         * - /users/edit/*: Allows users to edit their profile information, with a wildcard parameter.
         *
         * @see \App\Controller\UsersController
         */
        $builder->connect('/users/login', ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/users/register', ['controller' => 'Users', 'action' => 'register']);
        $builder->connect('/users/logout', ['controller' => 'Users', 'action' => 'logout']);
        $builder->connect('/users/confirm-email/*',['controller' => 'Users', 'action' => 'confirmEmail']);
        $builder->connect('/users/edit/*', ['controller' => 'Users', 'action' => 'edit']);
        $builder->connect('/atricles/add-comment/*', ['controller' => 'Articles', 'action' => 'addComment']);
        $builder->connect('/tags', ['controller' => 'Tags', 'action' => 'index']);
        $builder->connect('/tags/view-by-slug/*', ['controller' => 'Tags', 'action' => 'viewBySlug']);
        $builder->connect('/tags/view/*', ['controller' => 'Tags', 'action' => 'view']);

        /**
         * Connects a route to the Articles controller's viewBySlug action.
         *
         * This route is configured to handle all incoming requests with a wildcard ('/*') pattern,
         * directing them to the 'viewBySlug' action of the 'Articles' controller. The route does not
         * use any prefix, ensuring it operates within the non-admin context of the application.
         *
         * The 'slug' parameter is passed to the action, allowing the method to retrieve and display
         * articles based on their slug. This setup is useful for SEO-friendly URLs where the article
         * slug is part of the URL path.
         *
         * Example URL: /some-article-slug
         * This would invoke the 'viewBySlug' method in the ArticlesController with 'some-article-slug'
         * as the slug parameter.
         *
         * @param string $slug The slug of the article to be viewed.
         */
        $builder->connect('/*', ['prefix' => null, 'controller' => 'Articles', 'action' => 'viewBySlug'], ['pass' => ['slug']]);

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
