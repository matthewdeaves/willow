<?php
declare(strict_types=1);

namespace App\Test;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class Cleanup
 *
 * Implements the PHPUnit Extension interface to perform cleanup tasks after test execution.
 * This is mainly to avoid any cache permissions errors in the front end site after running tests.
 */
class Cleanup implements Extension
{
    /**
     * Bootstrap method to register the cleanup subscriber.
     *
     * @param Configuration $configuration The PHPUnit configuration object.
     * @param Facade $facade The PHPUnit facade for managing extensions.
     * @param ParameterCollection $parameters The collection of parameters for the extension.
     */
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class implements ExecutionFinishedSubscriber {
            public function notify(ExecutionFinished $event): void
            {
                // Only perform actions if in debug mode
                if (Configure::read('debug')) {
                    // Clear all caches
                    Cache::clearAll();

                    // Reset permissions for tmp, logs, and webroot directories
                    $dirs = [TMP, LOGS, WWW_ROOT];
                    foreach ($dirs as $dir) {
                        $this->setPermissions($dir);
                    }
                }
            }

            private function setPermissions($dir)
            {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($iterator as $item) {
                    if ($item->isDir()) {
                        chmod($item->getRealPath(), 0777); // Directory permissions
                    } else {
                        chmod($item->getRealPath(), 0777); // File permissions
                    }
                }
            }
        });
    }
}
