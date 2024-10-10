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
     * Initializes the cache configuration for settings.
     *
     * Configures the File cache engine with a specified duration and path,
     * but only if the configuration doesn't already exist.
     *
     * @return void
     */
    public static function initialize(): void
    {
        // Check if the cache configuration already exists
        if (!Cache::getConfig(self::$cacheConfig)) {
            // Configure the File cache engine for settings
            Cache::setConfig(self::$cacheConfig, [
                'className' => 'File',
                'duration' => '+1 hour',
                'path' => CACHE,
                'prefix' => 'settings_',
            ]);
        }
    }

    /**
     * Reads a setting value from the cache or database.
     *
     * Attempts to read the setting from the cache first. If not found, retrieves it from the database
     * and caches the result. Supports nested settings using dot notation.
     *
     * @param string $path The dot-separated path to the setting (e.g., 'category.subcategory.key').
     * @param mixed $default The default value to return if the setting is not found.
     * @return mixed The setting value or the default value if not found.
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
        $subcategory = $parts[1] ?? null;
        $keyName = $parts[2] ?? null;

        if (!$category || !$keyName) {
            return $default;
        }

        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $value = $settingsTable->getSettingValue($category, $subcategory, $keyName);

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
}
