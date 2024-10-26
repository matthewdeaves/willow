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
use App\Utility\SettingsManager;

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

        // Get the enabled translations
        $translations = SettingsManager::read('Translations', null);

        // Extract the first two letters of enabled locale keys
        $enabledLanguages = array_map(function ($locale) {
            return substr($locale, 0, 2);
        }, array_keys(array_filter($translations)));

        // Convert the array of enabled languages to a string for use in the route constraint
        $languageConstraint = implode('|', $enabledLanguages);

        $builder->connect('/sitemap', ['controller' => 'Sitemap', 'action' => 'index'])->setExtensions(['xml']);

        $builder->connect('/{language}', ['controller' => 'Articles', 'action' => 'index'], ['language' => $languageConstraint]);
        $builder->connect('/', ['controller' => 'Articles', 'action' => 'index'], ['language' => 'en']);

        $builder->connect('/users/login', ['controller' => 'Users', 'action' => 'login'], ['language' => 'en']);
        $builder->connect('/{language}/users/login', ['controller' => 'Users', 'action' => 'login'], ['language' => $languageConstraint]);

        $builder->connect('/users/register', ['controller' => 'Users', 'action' => 'register'], ['language' => 'en']);
        $builder->connect('/{language}/users/register', ['controller' => 'Users', 'action' => 'register'], ['language' => $languageConstraint]);

        $builder->connect('/users/logout', ['controller' => 'Users', 'action' => 'logout'], ['language' => 'en']);
        $builder->connect('/{language}/users/logout', ['controller' => 'Users', 'action' => 'logout'], ['language' => $languageConstraint]);

        $builder->connect('/users/confirm-email/*',['controller' => 'Users', 'action' => 'confirmEmail'], ['language' => 'en']);
        $builder->connect('/{language}/users/confirm-email/*',['controller' => 'Users', 'action' => 'confirmEmail'], ['language' => $languageConstraint]);

        $builder->connect('/users/edit/*', ['controller' => 'Users', 'action' => 'edit'], ['language' => 'en']);
        $builder->connect('/{language}/users/edit/*', ['controller' => 'Users', 'action' => 'edit'], ['language' => $languageConstraint]);

        $builder->connect('/articles/add-comment/*', ['controller' => 'Articles', 'action' => 'addComment'], ['language' => 'en']);
        $builder->connect('/{language}/articles/add-comment/*', ['controller' => 'Articles', 'action' => 'addComment'],['language' => $languageConstraint]);

        $builder->connect('/tags', ['controller' => 'Tags', 'action' => 'index'], ['language' => 'en']);
        $builder->connect('/tags/view-by-slug/*', ['controller' => 'Tags', 'action' => 'view-by-slug'], ['language' => 'en']);
        $builder->connect('/tags/view-by-slug/*', ['controller' => 'Tags', 'action' => 'viewBySlug'], ['language' => 'en']);

        $builder->connect('/{language}/tags', ['controller' => 'Tags', 'action' => 'index'], ['language' => $languageConstraint]);
        $builder->connect('/{language}/tags/view-by-slug/*', ['controller' => 'Tags', 'action' => 'view-by-slug'], ['language' => $languageConstraint]);
        $builder->connect('/{language}/tags/view-by-slug/*', ['controller' => 'Tags', 'action' => 'viewBySlug'], ['language' => $languageConstraint]);

        $builder->connect('/{language}/*', ['prefix' => null, 'controller' => 'Articles', 'action' => 'viewBySlug'], ['pass' => ['slug'], 'language' => $languageConstraint]);
        $builder->connect('/*', ['prefix' => null, 'controller' => 'Articles', 'action' => 'viewBySlug'], ['pass' => ['slug'], 'language' => 'en']);

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
        //$builder->fallbacks();
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
