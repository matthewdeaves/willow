<?php
declare(strict_types=1);

namespace App\Utility;

use App\Utility\SettingsManager;
use Cake\I18n\I18n;
use Cake\Core\Configure;

/**
 * Class I18nManager
 *
 * This utility class provides internationalization helper functions.
 * It uses the SettingsManager class to retrieve language settings.
 */
class I18nManager
{
    public static function setEnabledLanguages()
    {
        $defaultLanguages = ['en'];
        $enabledLanguages = array_keys(self::getEnabledLocales());
        $mergedLanguages = array_merge($defaultLanguages, $enabledLanguages);

        Configure::write('I18n.languages', $mergedLanguages);
    } 

    public static function setLocaleForLanguage(string $language)
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

    public static function getEnabledLocales()
    {
        $locales = SettingsManager::read('Translations', null);
        $enabledLocales = [];
        foreach ($locales as $locale => $enabled) {
            if ($enabled) {
                $enabledLocales[substr($locale, 0, 2)] = $locale;
            }
        }

        return $enabledLocales;
    }
}