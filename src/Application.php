<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use App\Middleware\IpBlockerMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Utility\SettingsManager;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\AbstractIdentifier;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 *
 * @extends \Cake\Http\BaseApplication<\App\Application>
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    /**
     * Bootstrap the application.
     *
     * This method is responsible for setting up the application's initial configuration.
     * It performs the following tasks:
     * - Calls the parent bootstrap method to load bootstrap from files
     * - Clears all cache entries if the application is in debug mode
     * - Loads the logging configuration
     * - Adds the Authentication plugin
     * - Conditionally adds the Cake/Queue plugin (except in test environment)
     * - Sets up the Table locator for non-CLI environments
     *
     * @return void
     * @throws \Exception If there's an error loading plugins or configurations
     * @uses \Cake\Cache\Cache::clearAll()
     * @uses \Cake\Core\Configure::read()
     * @uses \Cake\Core\Plugin::getCollection()
     * @uses \Cake\Datasource\FactoryLocator::add()
     * @uses \Cake\ORM\Locator\TableLocator
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        // Check if the application is in debug mode
        if (Configure::read('debug')) {
            // Clear all cache entries
            //Cache::clearAll();
        }

        require CONFIG . 'log_config.php';

        $this->addPlugin('Authentication');

        // Don't load the Queue plugin if we are running tests, we don't need
        // to test that and the code skips sending messages when testing
        if (env('CAKE_ENV') !== 'test') {
            $this->addPlugin('Cake/Queue');
        }

        if (PHP_SAPI !== 'cli') {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false)
            );
        }
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/5/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

            // Add authentication middleware
            ->add(new AuthenticationMiddleware($this))

            // Cross Site Request Forgery (CSRF) Protection Middleware
            // https://book.cakephp.org/5/en/security/csrf.html#cross-site-request-forgery-csrf-middleware
            ->add(new CsrfProtectionMiddleware([
                'httponly' => true,
            ]))

            /**
             * Adds the IP Blocker Middleware to the middleware queue.
             *
             * This middleware checks the client's IP address on every request,
             * querying the blocked_ips table to determine if the IP is blocked.
             * If blocked, it returns a 403 Forbidden response. Otherwise, it
             * allows the request to proceed normally.
             *
             * @see \App\Middleware\IpBlockerMiddleware
             */
            ->add(new IpBlockerMiddleware())

            /**
             * Adds a RateLimitMiddleware to the middleware queue.
             *
             * This middleware implements rate limiting to prevent abuse and ensure fair usage
             * of the application's resources. It tracks the number of requests made by each client
             * within a specified time period and blocks requests that exceed the defined limit.
             *
             * @param array $options Configuration options for the RateLimitMiddleware
             *     @option int $limit The maximum number of requests allowed within the specified period.
             *                        In this case, 4 requests are allowed.
             *     @option int $period The time frame in seconds for which the limit applies.
             *                         Here, it's set to 60 seconds (1 minute).
             * @return \Psr\Http\Server\MiddlewareInterface The configured RateLimitMiddleware instance.
             * @throws \InvalidArgumentException If the provided options are invalid.
             * @see \App\Middleware\RateLimitMiddleware For full implementation details.
             */
            ->add(new RateLimitMiddleware([
                'limit' => SettingsManager::read('RateLimit.numberOfRequests', 4),
                'period' => SettingsManager::read('RateLimit.numberOfSeconds', 60),
            ]));

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/5/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
    }

    /**
     * Configures and returns an AuthenticationService instance.
     *
     * This method sets up the authentication service with the necessary
     * configuration for handling user authentication. It defines the
     * redirection URL for unauthenticated users and specifies the query
     * parameter for redirection. The method also loads the necessary
     * authenticators and identifiers to handle session and form-based
     * authentication using email and password fields.
     *
     * Configuration:
     * - Sets unauthenticated redirect to the Users login action.
     * - Uses 'redirect' as the query parameter for redirection.
     *
     * Authenticators:
     * - Loads the Authentication.Session authenticator (primary).
     * - Loads the Authentication.Form authenticator with custom field mapping.
     *
     * Identifier:
     * - Loads the Authentication.Password identifier with custom field mapping
     *   and ORM resolver using the 'auth' finder to filter out disabled users.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The server request instance.
     * @return \Authentication\AuthenticationServiceInterface The configured authentication service.
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $service = new AuthenticationService();

        // Define where users should be redirected to when they are not authenticated
        $service->setConfig([
            'unauthenticatedRedirect' => Router::url([
                    'prefix' => false,
                    'plugin' => null,
                    'controller' => 'Users',
                    'action' => 'login',
            ]),
            'queryParam' => 'redirect',
        ]);

        $fields = [
            AbstractIdentifier::CREDENTIAL_USERNAME => 'email',
            AbstractIdentifier::CREDENTIAL_PASSWORD => 'password',
        ];

        // Load the authenticators. Session should be first.
        $service->loadAuthenticator('Authentication.Session');
        $service->loadAuthenticator('Authentication.Form', [
            'fields' => $fields,
            'loginUrl' => Router::url([
                'prefix' => false,
                'plugin' => null,
                'controller' => 'Users',
                'action' => 'login',
            ]),
        ]);

        // Load identifiers
        $service->loadIdentifier('Authentication.Password', [
            'fields' => $fields,
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => 'Users',
                'finder' => 'auth',
            ],
        ]);

        return $service;
    }
}
