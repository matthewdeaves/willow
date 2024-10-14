<?php

use Cake\Cache\Engine\RedisEngine;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Log\Engine\FileLog;
use Cake\Mailer\Transport\MailTransport;

return [
    'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),

    'App' => [
        'namespace' => 'App',
        'encoding' => env('APP_ENCODING', 'UTF-8'),
        'defaultLocale' => env('APP_DEFAULT_LOCALE', 'en_US'),
        'defaultTimezone' => env('APP_DEFAULT_TIMEZONE', 'UTC'),
        'base' => false,
        'dir' => 'src',
        'webroot' => 'webroot',
        'wwwRoot' => WWW_ROOT,
        'fullBaseUrl' => false,
        'imageBaseUrl' => 'img/',
        'cssBaseUrl' => 'css/',
        'jsBaseUrl' => 'js/',
        'paths' => [
            'plugins' => [ROOT . DS . 'plugins' . DS],
            'templates' => [ROOT . DS . 'templates' . DS],
            'locales' => [RESOURCES . 'locales' . DS],
        ],
    ],

    'Security' => [
        'salt' => env('SECURITY_SALT'),
    ],

    'Asset' => [
        //'timestamp' => true,
    ],

    'Cache' => [
        'default' => [
            'className' => RedisEngine::class,
            'url' => env('CACHE_DEFAULT_URL'),
        ],
        '_cake_core_' => [
            'className' => RedisEngine::class,
            'url' => env('CACHE_CAKECORE_URL'),
        ],
        '_cake_model_' => [
            'className' => RedisEngine::class,
            'url' => env('CACHE_CAKEMODEL_URL'),
        ],
        'ip_blocker' => [
            'className' => RedisEngine::class,
            'url' => env('CACHE_DEFAULT_URL'),
            'duration' => '+1 week',
        ],
        'articles' => [
            'className' => RedisEngine::class,
            'url' => env('CACHE_DEFAULT_URL'),
            'duration' => '+1 week',
        ],
        'settings_cache' => [
            'className' => RedisEngine::class,
            'url' => env('CACHE_DEFAULT_URL'),
            'duration' => '+1 month',
        ],
        '_cake_routes_' => [
            'className' => RedisEngine::class,
            'url' => env('CACHE_CAKEROUTES_URL'),
        ],
        'rate_limit' => [
            'className' => RedisEngine::class,
            'url' => env('CACHE_DEFAULT_URL'),
            'duration' => '+1 hour',
        ],
    ],

    'Error' => [
        'errorLevel' => E_ALL & ~E_DEPRECATED,
        'exceptionRenderer' => \App\Error\AppExceptionRenderer::class,
        'skipLog' => [],
        'log' => true,
        'trace' => true,
        'ignoredDeprecationPaths' => [
            'vendor/cakephp/cakephp/src/I18n/I18n.php',
            'vendor/bunny/bunny/src/Bunny/ClientMethods.php',
            'vendor/enqueue/amqp-bunny/AmqpProducer.php',
            'vendor/enqueue/enqueue/Client/Driver/AmqpDriver.php',
            'vendor/enqueue/enqueue/Client/Producer.php',
            'vendor/enqueue/simple-client/SimpleClient.php',
        ],
    ],

    'Debugger' => [
        'editor' => 'phpstorm',
    ],

    'EmailTransport' => [
        'default' => [
            'className' => 'Smtp',
            'host' => 'mailhog',
            'port' => 1025,
            'timeout' => 30,
            'client' => null,
            'tls' => false,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],

    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => 'you@localhost',
        ],
    ],

    'Datasources' => [
        'default' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'timezone' => 'UTC',
            'flags' => [],
            'cacheMetadata' => true,
            'log' => false,
            'quoteIdentifiers' => false,
        ],
        'test' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'timezone' => 'UTC',
            'flags' => [],
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            'log' => false,
        ],
    ],

    'Log' => [
        'debug' => [
            'className' => 'App\Log\Engine\DatabaseLog',
            'levels' => ['notice', 'info', 'debug'],
        ],
        'error' => [
            'className' => 'App\Log\Engine\DatabaseLog',
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        'debug_file' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'debug',
            'url' => env('LOG_DEBUG_URL', null),
            'scopes' => null,
            'levels' => ['notice', 'info', 'debug'],
        ],
        'error_file' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'error',
            'url' => env('LOG_ERROR_URL', null),
            'scopes' => null,
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        'queries' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'queries',
            'url' => env('LOG_QUERIES_URL', null),
            'scopes' => ['cake.database.queries'],
        ],
    ],

    'Session' => [
        'defaults' => 'redis',
        'handler' => [
            'config' => 'session'
        ],
    ],

    'Queue' => [
        'default' => [
            'engine' => 'redis',
            'url' => env('REDIS_URL', 'redis://redis:6379?password=password'),
            'queue' => 'default',
            'logger' => 'stdout',
            'receiveTimeout' => 10000,
            'storeFailedJobs' => true,
            'uniqueCache' => [
                'engine' => 'Redis',
                'duration' => '+24 hours',
            ],
        ],
        'test' => [
            'engine' => 'redis',
            'url' => env('REDIS_TEST_URL', 'redis://redis:6379?password=password'),
            'queue' => 'test_queue',
            'logger' => 'stdout',
            'receiveTimeout' => 10000,
            'storeFailedJobs' => true,
            'uniqueCache' => [
                'engine' => 'Redis',
                'duration' => '+24 hours',
            ],
        ],
    ],
];