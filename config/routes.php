<?php

/* Current routes 
*+-------------------------------------+----------------------------------------------------+--------------+--------+----------------+-------------------+-----------+
| Route name                          | URI template                                       | Plugin       | Prefix | Controller     | Action            | Method(s) |
+-------------------------------------+----------------------------------------------------+--------------+--------+----------------+-------------------+-----------+
| sitemap-root                        | /sitemap                                           |              |        | Sitemap        | index             |           |
| robots-root                         | /robots.txt                                        |              |        | Robots         | index             |           |
| defaulttheme._controller:index      | /default-theme/{controller}                        | DefaultTheme |        |                | index             |           |
| defaulttheme._controller:_action    | /default-theme/{controller}/{action}/*             | DefaultTheme |        |                | index             |           |
| debugkit.toolbar:clearcache         | /debug-kit/toolbar/clear-cache                     | DebugKit     |        | Toolbar        | clearCache        |           |
| debugkit.requests:view              | /debug-kit/toolbar/*                               | DebugKit     |        | Requests       | view              |           |
| debugkit.panels:latesthistory       | /debug-kit/panels/view/latest-history              | DebugKit     |        | Panels         | latestHistory     |           |
| debugkit.panels:view                | /debug-kit/panels/view/*                           | DebugKit     |        | Panels         | view              |           |
| debugkit.panels:index               | /debug-kit/panels/*                                | DebugKit     |        | Panels         | index             |           |
| debugkit.mailpreview:sent           | /debug-kit/mail-preview/sent/{panel}/{id}          | DebugKit     |        | MailPreview    | sent              |           |
| debugkit.mailpreview:email          | /debug-kit/mail-preview/preview                    | DebugKit     |        | MailPreview    | email             |           |
| debugkit.mailpreview:email          | /debug-kit/mail-preview/preview/*                  | DebugKit     |        | MailPreview    | email             |           |
| debugkit.mailpreview:index          | /debug-kit/mail-preview                            | DebugKit     |        | MailPreview    | index             |           |
| debugkit.dashboard:reset            | /debug-kit/dashboard/reset                         | DebugKit     |        | Dashboard      | reset             | POST      |
| debugkit.dashboard:index            | /debug-kit/dashboard                               | DebugKit     |        | Dashboard      | index             | GET       |
| debugkit.composer:checkdependencies | /debug-kit/composer/check-dependencies             | DebugKit     |        | Composer       | checkDependencies |           |
| debugkit._controller:index          | /debug-kit/{controller}                            | DebugKit     |        |                | index             |           |
| debugkit._controller:_action        | /debug-kit/{controller}/{action}/*                 | DebugKit     |        |                | index             |           |
| debugkit.dashboard:index            | /debug-kit                                         | DebugKit     |        | Dashboard      | index             | GET       |
| admin:imagegalleries:removeimage    | /admin/image-galleries/remove-image/{id}/{imageId} |              | Admin  | ImageGalleries | removeImage       |           |
| admin:_controller:index             | /admin/{controller}                                |              | Admin  |                | index             |           |
| admin:_controller:_action           | /admin/{controller}/{action}/*                     |              | Admin  |                | index             |           |
| admintheme._controller:index        | /admin-theme/{controller}                          | AdminTheme   |        |                | index             |           |
| admintheme._controller:_action      | /admin-theme/{controller}/{action}/*               | AdminTheme   |        |                | index             |           |
| admin:articles:index                | /admin                                             |              | Admin  | Articles       | index             |           |
| articles:index                      | /                                                  |              |        | Articles       | index             |           |
| home                                | /{lang}                                            |              |        | Articles       | index             |           |
| robots                              | /{lang}/robots.txt                                 |              |        | Robots         | index             |           |
| sitemap                             | /{lang}/sitemap                                    |              |        | Sitemap        | index             |           |
| rss                                 | /{lang}/feed                                       |              |        | Rss            | index             |           |
| products:index                      | /{lang}/products                                   |              |        | Products       | index             |           |
| products-index                      | /{lang}/products                                   |              |        | Products       | index             |           |
| login                               | /{lang}/users/login                                |              |        | Users          | login             |           |
| users:register                      | /{lang}/users/register                             |              |        | Users          | register          |           |
| forgot-password                     | /{lang}/users/forgot-password                      |              |        | Users          | forgot-password   |           |
| reset-password                      | /{lang}/users/reset-password/{confirmationCode}    |              |        | Users          | reset-password    |           |
| logout                              | /{lang}/users/logout                               |              |        | Users          | logout            |           |
| confirm-email                       | /{lang}/users/confirm-email/{confirmationCode}     |              |        | Users          | confirmEmail      |           |
| account                             | /{lang}/users/edit/{id}                            |              |        | Users          | edit              |           |
| articles:addcomment                 | /{lang}/articles/add-comment/*                     |              |        | Articles       | addComment        |           |
| tags-index                          | /{lang}/tags                                       |              |        | Tags           | index             |           |
| article-by-slug                     | /{lang}/articles/{slug}                            |              |        | Articles       | view-by-slug      |           |
| page-by-slug                        | /{lang}/pages/{slug}                               |              |        | Articles       | view-by-slug      |           |
| tag-by-slug                         | /{lang}/tags/{slug}                                |              |        | Tags           | view-by-slug      |           |
| cookie-consent                      | /{lang}/cookie-consents/edit                       |              |        | CookieConsents | edit              |           |
+-------------------------------------+----------------------------------------------------+--------------+--------+----------------+-------------------+-----------+

The following possible route collisions were detected.
+----------------+------------------+--------+--------+------------+--------+-----------+
| Route name     | URI template     | Plugin | Prefix | Controller | Action | Method(s) |
+----------------+------------------+--------+--------+------------+--------+-----------+
| products:index | /{lang}/products |        |        | Products   | index  |           |
| products-index | /{lang}/products |        |        | Products   | index  |           |
+----------------+------------------+--------+--------+------------+--------+-----------+
*/
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
    $routes->setRouteClass(DashedRoute::class); // Use DashedRoute for consistent URL formatting
    $routes->setExtensions(['xml', 'rss']); // Set default extensions for routes

    // Root robots.txt route must come before the scope
    $routes->connect( // Changed from /robots to /robots.txt
        '/robots.txt',
        [
            'controller' => 'Robots',
            'action' => 'index'
        ],
        [
            '_name' => 'robots-root' // Changed from 'robots' to 'robots-root' to avoid conflict with language-specific routes 
        ]
    );

    // Root sitemap.xml route must come before the scope
    $routes->connect( // Changed from /sitemap to /sitemap.xml
        '/sitemap',
        [
            'controller' => 'Sitemap',
            'action' => 'index',
            '_ext' => 'xml'
        ],
        [
            '_name' => 'sitemap-root' // Changed from 'sitemap' to 'sitemap-root'
        ]
    );

    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->setExtensions(['xml', 'rss']);
        
        $builder->connect('/', ['controller' => 'Articles', 'action' => 'index']);
        $builder->connect(
            '/',
            [
                'controller' => 'Articles',
                'action' => 'index'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'home'
            ]
        );


        // Language-specific robots.txt route
        $builder->connect(
            '/{lang}/robots.txt',
            [
                'controller' => 'Robots',
                'action' => 'index'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'robots',
                'lang' => '[a-z]{2}',
                'pass' => ['lang']
            ]
        );

        // Language-specific sitemap route
        $builder->connect(
            '/{lang}/sitemap',
            [
                'controller' => 'Sitemap',
                'action' => 'index',
                '_ext' => 'xml'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'sitemap',
                'lang' => '[a-z]{2}',
                'pass' => ['lang']
            ]
        );

        // Language-specific rss route
        $builder->connect(
            '/{lang}/feed',  // Changed from /rss to /feed
            [
                'controller' => 'Rss',
                'action' => 'index',
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'rss',
                'lang' => '[a-z]{2}',
                'pass' => ['lang']
            ]
        );

        // Language-specific user routes
        // START: User routes ///////////
        $builder->connect(
            '/users/login',
            [
                'controller' => 'Users',
                'action' => 'login'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'login',
            ]
        );
        $builder->connect('/users/register', ['controller' => 'Users', 'action' => 'register'], ['routeClass' => 'ADmad/I18n.I18nRoute']);
        // Language-specific forgot password route
        $builder->connect(
            '/users/forgot-password',
            [
                'controller' => 'Users',
                'action' => 'forgot-password'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'forgot-password',
            ]
        );
        // Language-specific reset password route
        $builder->connect(
            '/users/reset-password/{confirmationCode}',
            [
                'controller' => 'Users',
                'action' => 'reset-password'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'reset-password',
                'pass' => ['confirmationCode'],
            ]
        );
        // Language-specific logout route
        $builder->connect(
            '/users/logout',
            [
                'controller' => 'Users',
                'action' => 'logout'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'logout',
            ]
        );
        // Language-specific confirm email route
        $builder->connect(
            '/users/confirm-email/{confirmationCode}',
            [
                'controller' => 'Users',
                'action' => 'confirmEmail'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'confirm-email',
                'pass' => ['confirmationCode'],
            ]
        );
        // Language-specific account edit route
        $builder->connect('/users/edit/{id}',
        [
            'controller' => 'Users',
            'action' => 'edit'
        ],
        [
            '_name' => 'account',
            'routeClass' => 'ADmad/I18n.I18nRoute',
            'pass' => ['id'],
        ]);

        // END: User routes ///////////


        // Language-specific article routes
        $builder->connect('/articles/add-comment/*', ['controller' => 'Articles', 'action' => 'addComment'], ['routeClass' => 'ADmad/I18n.I18nRoute']);
        $builder->connect(
            '/tags',
            ['controller' => 'Tags', 'action' => 'index'],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'tags-index',
            ]
        );
        
        // Language-specific article by slug route
        $builder->connect(
            'articles/{slug}',
            [
                'controller' => 'Articles',
                'action' => 'view-by-slug'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'article-by-slug',
                'pass' => ['slug'],
            ]
        );

        // Language-specific page by slug routes
        $builder->connect(
            'pages/{slug}',
            [
                'controller' => 'Articles',
                'action' => 'view-by-slug'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'page-by-slug',
                'pass' => ['slug'] 
            ]
        );

        $builder->connect(
            'tags/{slug}',
            [
                'controller' => 'Tags',
                'action' => 'view-by-slug'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'tag-by-slug',
                'pass' => ['slug'] 
            ]
        );


        // TODO: replace with DRY routes AND remove the commented out code
        //// START: Product routes ///////////
               // Language-specific routes for products, page
        // $builder->connect('/{lang}/products', ['controller' => 'Products', 'action' => 'index']);
        // $builder->connect(
        //     '/{lang}/products',
        //     [
        //         'controller' => 'Products',
        //         'action' => 'index'
        //     ],
        //     [
        //         'routeClass' => 'ADmad/I18n.I18nRoute',
        //         '_name' => 'products-index',
        //         'lang' => '[a-z]{2}',
        //         'pass' => ['lang']
        //     ]
        // );


        // $builder->connect('/products/add-comment/*', ['controller' => 'Products', 'action' => 'addComment'], ['routeClass' => 'ADmad/I18n.I18nRoute']);
        // $builder->connect(
        //     '/product-tags',
        //     ['controller' => 'ProductTags', 'action' => 'index'],
        //     [
        //         'routeClass' => 'ADmad/I18n.I18nRoute',
        //         '_name' => 'product-tags-index',
        //     ]
        // );
        
        // $builder->connect(
        //     'products/{slug}',
        //     [
        //         'controller' => 'Products',
        //         'action' => 'view-by-slug'
        //     ],
        //     [
        //         'routeClass' => 'ADmad/I18n.I18nRoute',
        //         '_name' => 'product-by-slug',
        //         'pass' => ['slug'],
        //     ]
        // );



        $builder->connect(
            'cookie-consents/edit',
            [
                'controller' => 'CookieConsents',
                'action' => 'edit'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'cookie-consent',
            ]
        );
    });
    // END OF DEFAULT ROUTES



    // // contact-manager plugin routes
    // $routes->plugin('ContactManager', function (RouteBuilder $routes) {

        
        
        // Connect the default routes for all controllers in the ContactManager plugin.
        // This will connect the /contact-manager/controller/action URLs to the appropriate controller and action.
        //$routes->fallbacks is used to automatically connect the default routes for all controllers in the plugin.
            // $routes->fallbacks(DashedRoute::class); // Use DashedRoute for consistent URL formatting

        // Connect the default routes for the ContactManager plugin.
        // This will connect the /contact-manager/controller/action URLs to the appropriate controller and action.
        // STARTING THE ADMIN PREFIX ROUTES

    // START: Admin routes
    // Connect the default routes for all controllers.
    // This will connect the /controller/action URLs to the appropriate controller and action.

    $routes->prefix('Admin', function (RouteBuilder $routes) { // Admin prefix routes
        $routes->connect('/', ['controller' => 'Articles', 'action' => 'index', 'prefix' => 'Admin']);
        
        // Specific route for removing images from galleries
        $routes->connect(
            '/image-galleries/remove-image/{id}/{imageId}',
            ['controller' => 'ImageGalleries', 'action' => 'removeImage'],
            ['pass' => ['id', 'imageId']]
        );
        
        $routes->fallbacks(DashedRoute::class); // Use DashedRoute for consistent URL formatting
    });

    // // Connect the default routes for products.
    // // This will connect the /controller/action URLs to the appropriate controller and action.
    // $routes->prefix('Admin', function (RouteBuilder $routes) { // Admin prefix routes
    //     $routes->connect(
    //         '/{controller}',
    //         ['action' => 'index'],
    //         ['routeClass' => 'DashedRoute']
    //     );  // Connects /admin/controller to /admin/controller/index

    //     $routes->connect(
    //         '/{controller}/{action}/*',
    //         [],
    //         ['routeClass' => 'DashedRoute']
    //     );
        // This connects /admin/controller/action/* to the appropriate controller and action
    // });
    // END: Admin routes

    // });



    // Add DebugKit routes with proper context if in debug mode



    // DebugKit routes are only loaded in debug mode
    // This allows you to access the DebugKit toolbar and panels
    if (\Cake\Core\Configure::read('debug')) {
        $routes->plugin('DebugKit', function (RouteBuilder $routes) {
            $routes->fallbacks();
        });
    }

};