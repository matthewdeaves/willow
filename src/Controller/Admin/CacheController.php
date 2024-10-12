<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Http\Response;
use Cake\I18n\FrozenTime;

class CacheController extends AppController
{
    public function clearAll(): ?Response
    {
        $cacheInfo = $this->getCacheInfo();

        if ($this->request->is('post')) {
            $clearedCaches = [];
            $failedCaches = [];

            foreach ($cacheInfo as $config => $info) {
                if (Cache::clear($config)) {
                    $clearedCaches[] = $config;
                    $this->updateLastClearedTime($config);
                } else {
                    $failedCaches[] = $config;
                }
            }

            // Clear SettingsManager cache
            SettingsManager::clearCache();
            $clearedCaches[] = 'SettingsManager';

            if (empty($failedCaches)) {
                $this->Flash->success(__('All caches have been cleared successfully.'));
            } else {
                $this->Flash->warning(__(
                    'Some caches were cleared, but the following failed: {0}',
                    implode(', ', $failedCaches)
                ));
            }

            return $this->redirect(['action' => 'clearAll']);
        }

        $this->set('cacheInfo', $cacheInfo);
        return null;
    }

    private function getCacheInfo(): array
    {
        $cacheInfo = [];
        $configuredCaches = Cache::configured();

        foreach ($configuredCaches as $config) {
            $engineConfig = Cache::getConfig($config);
            $cacheInfo[$config] = [
                'engine' => $engineConfig['className'],
                'settings' => $engineConfig,
                'last_cleared' => $this->getLastClearedTime($config),
            ];
        }

        return $cacheInfo;
    }

    private function getLastClearedTime(string $config): ?FrozenTime
    {
        $time = Cache::read('last_cleared_' . $config, 'default');
        return $time ? FrozenTime::parse($time) : null;
    }

    private function updateLastClearedTime(string $config): void
    {
        $time = FrozenTime::now();
        Cache::write('last_cleared_' . $config, $time, 'default');
    }
}