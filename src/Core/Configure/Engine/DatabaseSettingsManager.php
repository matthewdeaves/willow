<?php

namespace App\Core\Configure\Engine;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Configure\ConfigEngineInterface;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

class DatabaseSettingsManager implements ConfigEngineInterface
{
    /**
     * Cache key for storing settings
     *
     * @var string
     */
    private const CACHE_KEY = 'database_settings';

    /**
     * Cache configuration to use
     *
     * @var string
     */
    private const CACHE_CONFIG = 'default';

    /**
     * Reads configuration data from cache or database.
     *
     * This method attempts to read configuration data from a cache. If the data is not found in the cache,
     * it will attempt to read from the 'settings' table in the database. If the 'settings' table exists,
     * it retrieves all settings and organizes them into a nested array based on group and key names.
     * The retrieved configuration is then written back to the cache for future requests.
     *
     * @param string|null $key Optional key to specify a particular configuration group or setting.
     *                         Currently not used in the method implementation.
     * @return array The configuration data, organized by group and key names.
     *               Returns an empty array if the 'settings' table does not exist.
     *
     * @throws \Cake\Database\Exception\MissingConnectionException If the database connection is not available.
     * @throws \RuntimeException If there is an error accessing the cache or database.
     */
    public function read($key = null): array
    {
        // Try to fetch from cache first
        $config = false;//Cache::read(self::CACHE_KEY, self::CACHE_CONFIG);

        if (!$config) {
            // Cache miss, read from database but check we have the table first as this might be the first run on new CMS with empty database and no tables
            $connection = ConnectionManager::get('default');
            $schemaCollection = $connection->getSchemaCollection()->listTables();

            if (in_array('settings', $schemaCollection)) {
                $settings = TableRegistry::getTableLocator()->get('Settings');
                $query = $settings->find();

                $config = [];
                foreach ($query as $setting) {
                    $config[$setting->group_name][$setting->key_name] = $setting->value;
                }

                // Write to cache
                Cache::write(self::CACHE_KEY, $config, self::CACHE_CONFIG);
            } else {
                $config = [];
            }
        }

        return $config;
    }

    /**
     * Dumps configuration data to a storage system. Not supported!
     *
     * This method is intended to be implemented if there is a need to support writing configuration
     * data back to a database. It is important to clear the cache if this functionality is implemented
     * to ensure that the changes are reflected in subsequent requests.
     *
     * @param string $key The key under which the configuration data should be stored.
     * @param mixed $data The configuration data to be stored.
     * @return bool Returns true if the configuration data is successfully written to the database.
     * @throws \RuntimeException If writing to the database configuration is not supported.
     */
    public function dump($key, $data): bool 
    {
        // Implement if you want to support writing config back to the database
        // Remember to clear the cache if implemented
        throw new \RuntimeException('Writing to database config is not supported');
    }

    /**
     * Clear the settings cache
     *
     * @return bool
     */
    public static function clearCache(): bool
    {
        return Cache::delete(self::CACHE_KEY, self::CACHE_CONFIG);
    }
}
