<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Http\Response;

/**
 * Cache Controller
 *
 * This controller handles the management of application cache.
 * It provides functionality to clear all cache.
 */
class CacheController extends AppController
{
    /**
     * Clear Cache method
     *
     * This method displays a view with a button to clear all cache.
     * When the button is pressed (POST request), it clears all configured caches
     * and the SettingsManager cache.
     *
     * @return \Cake\Http\Response|null|void Renders view or redirects after clearing cache
     */
    public function clearAll(): ?Response
    {
        if ($this->request->is('post')) {
            $clearedCaches = [];
            $failedCaches = [];

            // Clear all configured caches
            $configuredCaches = Cache::configured();
            foreach ($configuredCaches as $config) {
                if (Cache::clear($config)) {
                    $clearedCaches[] = $config;
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

            $this->set(compact('clearedCaches', 'failedCaches'));

            return $this->redirect(['action' => 'clearAll']);
        }

        return null;
    }
}