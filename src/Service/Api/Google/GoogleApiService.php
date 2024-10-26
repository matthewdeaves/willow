<?php
declare(strict_types=1);

namespace App\Service\Api\Google;

use App\Utility\SettingsManager;
use Google\Cloud\Translate\V2\TranslateClient;

/**
 * Service class for interacting with the Google Cloud Translate API.
 */
class GoogleApiService
{
    /**
     * The Google Cloud Translate client instance.
     *
     * @var \Google\Cloud\Translate\V2\TranslateClient
     */
    private TranslateClient $translateClient;

    /**
     * Constructor for the GoogleApiService class.
     * Initializes the Google Cloud Translate client with the API key from the settings.
     */
    public function __construct()
    {
        $this->translateClient = new TranslateClient([
            'key' => SettingsManager::read('Google.apiKey', ''),
        ]);
    }

    /**
     * Translates an array of strings from one language to another using batch translation.
     *
     * @param array $strings The array of strings to be translated.
     * @param string $localeFrom The source language locale code (e.g., 'en' for English).
     * @param string $localeTo The target language locale code (e.g., 'fr' for French).
     * @return array An array containing the translated strings and their original values.
     */
    public function translateStrings(array $strings, string $localeFrom, string $localeTo): array
    {
        $results = $this->translateClient->translateBatch($strings, [
            'source' => $localeFrom,
            'target' => $localeTo,
        ]);

        $translatedStrings = [];

        foreach ($results as $result) {
            $translatedStrings['translations'][] = [
                'original' => $result['input'],
                'translated' => $result['text'],
            ];
        }

        return $translatedStrings;
    }
}
