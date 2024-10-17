<?php
namespace App\Test;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Cake\Cache\Cache;

class Cleanup implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class implements \PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber {
            public function notify(\PHPUnit\Event\TestRunner\ExecutionFinished $event): void
            {
                // Clear all caches
                Cache::clearAll();

                // Reset permissions for tmp and logs directories
                $dirs = [TMP, LOGS];
                foreach ($dirs as $dir) {
                    $this->setPermissions($dir);
                }
            }

            private function setPermissions($dir)
            {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
                foreach ($iterator as $item) {
                    chmod($item, 0777);
                }
            }
        });
    }
}