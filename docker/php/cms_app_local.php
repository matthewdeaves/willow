<?php
/*
 * Local configuration file to provide any overrides to your app.php configuration.
 * Copy and save this file as app_local.php and make changes as required.
 * Note: It is not recommended to commit files with credentials such as app_local.php
 * into source code version control.
 */
return [
    /*
     * Debug Level:
     *
     * Production Mode:
     * false: No error messages, errors, or warnings shown.
     *
     * Development Mode:
     * true: Errors and warnings shown.
     */
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    /*
     * Security and encryption configuration
     *
     * - salt - A random string used in security hashing methods.
     *   The salt value is also used as the encryption key.
     *   You should treat it as extremely sensitive data.
     */
    'Security' => [
        'salt' => env('SECURITY_SALT', '8831764ad771299067333a9779c3a9818d0309dbbd797fdcdf175366486ed397'),
    ],

    /*
     * Connection information used by the ORM to connect
     * to your application's datastores.
     *
     * See app.php for more configuration options.
     */
    'Datasources' => [
        'default' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'mysql',
            'username' => 'cms_user',
            'password' => 'password',
            'database' => 'cms',
            'encoding' => 'utf8mb4',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'port' => 3306
        ],

        /*
         * The test connection is used during the test suite.
         */
        'test' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'mysql',
            'username' => 'root',
            'password' => 'password',
            'database' => 'cms_test',
            'encoding' => 'utf8mb4',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'port' => 3306
        ],
    ],

    /*
     * Email configuration.
     *
     * Host and credential configuration in case you are using SmtpTransport
     *
     * See app.php for more configuration options.
     */
    'EmailTransport' => [
        'default' => [
            'host' => 'mailhog',
            'port' => 1025,
            'username' => null,
            'password' => null,
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],

    /**
     * SiteSettings configuration array.
     *
     * This configuration array contains settings for RabbitMQ and image sizes.
     *
     * @var array $SiteSettings
     * 
     * @property array $RabbitMQ Configuration settings for RabbitMQ.
     * @property string $RabbitMQ['host'] The hostname for the RabbitMQ server.
     * @property string $RabbitMQ['port'] The port number for the RabbitMQ server.
     * @property string $RabbitMQ['username'] The username for connecting to RabbitMQ.
     * @property string $RabbitMQ['password'] The password for connecting to RabbitMQ.
     * 
     * @property array $ImageSizes Configuration for various image width sizes.
     * @property int $ImageSizes['massive'] The size for massive images in pixels.
     * @property int $ImageSizes['extra-large'] The size for extra-large images in pixels.
     * @property int $ImageSizes['large'] The size for large images in pixels.
     * @property int $ImageSizes['medium'] The size for medium images in pixels.
     * @property int $ImageSizes['small'] The size for small images in pixels.
     * @property int $ImageSizes['tiny'] The size for tiny images in pixels.
     * @property int $ImageSizes['teeny'] The size for teeny images in pixels.
     */
    'SiteSettings' => [
        'ImageSizes' => [
            'massive' => 800,
            'extra-large' => 500,
            'large' => 400,
            'medium' => 300,
            'small' => 200,
            'tiny' => 100,
            'teeny' => 50,
        ]
    ]
];
