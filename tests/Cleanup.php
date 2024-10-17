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

class Cleanup implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class implements ExecutionFinishedSubscriber {
            public function notify(ExecutionFinished $event): void
            {
                // Only perform actions if in debug mode
                if (Configure::read('debug')) {
                    // Clear all caches
                    Cache::clearAll();

                    // Reset permissions for tmp and logs directories
                    $dirs = [TMP, LOGS, WWW_ROOT];
                    foreach ($dirs as $dir) {
                        $this->setPermissions($dir);
                    }
                }
            }

            private function setPermissions($dir)
            {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
                foreach ($iterator as $item) {
                    chmod($item, 0777);
                }
            }
        });
    }
}
