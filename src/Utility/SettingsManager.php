<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;

/**
 * Class SettingsManager
 *
 * This utility class manages application settings with caching capabilities.
 * It uses CakePHP's Cache and ORM components to efficiently retrieve and store settings.
 */
class SettingsManager
{
    /**
     * @var string The cache configuration name used for storing settings.
     */
    private static string $cacheConfig = 'settings_cache';

    /**
     * Reads a setting value from the cache or database.
     *
     * This method attempts to read a setting value identified by the given path. It first checks the cache
     * for the value. If the value is not found in the cache, it retrieves the value from the database using
     * the Settings table. The result is then cached to optimize future requests.
     *
     * The method supports two types of reads:
     * 1. Reading all settings for a category (e.g., 'ImageSizes')
     * 2. Reading a specific setting within a category (e.g., 'ImageSizes.medium')
     *
     * @param string $path The dot-separated path to the setting (e.g., 'category' or 'category.key_name').
     * @param mixed $default The default value to return if the setting is not found.
     * @return mixed The setting value if found, otherwise the default value.
     *               For category-level requests, returns an array of all settings in that category.
     *               For specific setting requests, returns the value of that setting.
     * @throws \RuntimeException If the Settings table cannot be accessed.
     */
    public static function read(string $path, mixed $default = null): mixed
    {
        $cacheKey = 'setting_' . str_replace('.', '_', $path);

        $value = Cache::read($cacheKey, self::$cacheConfig);
        if ($value !== null) {
            return $value;
        }

        $parts = explode('.', $path);
        $category = $parts[0] ?? null;
        $keyName = $parts[1] ?? null;

        $settingsTable = TableRegistry::getTableLocator()->get('Settings');

        if ($keyName === null) {
            // Fetch all settings for the category
            $value = $settingsTable->getSettingValue($category);
        } else {
            // Fetch a single setting
            $value = $settingsTable->getSettingValue($category, $keyName);
        }

        // Cache the result, even if it's null, to avoid repeated database queries
        Cache::write($cacheKey, $value, self::$cacheConfig);

        return $value ?? $default;
    }

    /**
     * Clears the settings cache.
     *
     * Removes all cached settings to ensure fresh data retrieval on subsequent reads.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::clear(self::$cacheConfig);
    }

    /**
     * Get the cache configuration name. Useful for testcases.
     *
     * @return string
     */
    public static function getCacheConfig(): string
    {
        return self::$cacheConfig;
    }
}
