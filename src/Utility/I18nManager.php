<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Core\Configure;
use Cake\I18n\I18n;

/**
 * Class I18nManager
 *
 * This utility class provides internationalization helper functions.
 * It uses the SettingsManager class to retrieve language settings.
 */
class I18nManager
{
    /**
     * Set the enabled languages in the application configuration.
     *
     * This method retrieves the enabled locales using the `getEnabledLocales()`
     * method, extracts the language codes, merges them with the default languages,
     * and sets the merged languages in the application configuration.
     *
     * @return void
     */
    public static function setEnabledLanguages(): void
    {
        $defaultLanguages = ['en'];
        $enabledLanguages = array_keys(self::getEnabledLocales());
        $mergedLanguages = array_merge($defaultLanguages, $enabledLanguages);

        Configure::write('I18n.languages', $mergedLanguages);
    }

    /**
     * Set the locale based on the provided language code.
     *
     * This method retrieves the enabled locales using the `getEnabledLocales()`
     * method, finds the matching locale for the provided language code, and sets
     * the locale using `I18n::setLocale()`. If no matching locale is found, it
     * sets the locale to 'en_GB'.
     *
     * @param string $language The language code to set the locale for.
     * @return void
     */
    public static function setLocaleForLanguage(string $language): void
    {
        $locales = self::getEnabledLocales();

        $matchedLocale = null;
        foreach ($locales as $lang => $locale) {
            if ($language == $lang) {
                $matchedLocale = $locale;
                break;
            }
        }

        if (!empty($matchedLocale)) {
            I18n::setLocale($matchedLocale);
        } else {
            I18n::setLocale('en_GB');
        }
    }

    public static function setLocalForAdminArea() : void
    {
        $adminLocale = SettingsManager::read('i18n.locale', null);
        if (!empty($adminLocale)){
            I18n::setLocale($adminLocale);
        }
    }

    /**
     * Get the enabled locales from the settings.
     *
     * This method retrieves the translation settings using the `SettingsManager`
     * class, filters the enabled locales, and returns an array where the keys are
     * the language codes and the values are the corresponding locales.
     *
     * @return array An array of enabled locales, with language codes as keys and locales as values.
     */
    public static function getEnabledLocales(): array
    {
        $locales = SettingsManager::read('Translations', []);
        $enabledLocales = [];
        foreach ($locales as $locale => $enabled) {
            if ($enabled) {
                $enabledLocales[substr($locale, 0, 2)] = $locale;
            }
        }

        return $enabledLocales;
    }
}
