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

    /**
     * Translates an article title and body into multiple languages using the Google Translate API.
     *
     * @param string $title The title of the article to be translated.
     * @param string $body The body of the article to be translated (can contain HTML).
     * @return array An associative array where the keys are the locale codes and the values are arrays
     *               containing the translated 'title' and 'body' for each enabled locale.
     *               Example format:
     *               [
     *                   'fr_FR' => [
     *                       'title' => 'Titre traduit',
     *                       'body' => 'Corps traduit',
     *                   ],
     *                   'es_ES' => [
     *                       'title' => 'Título traducido',
     *                       'body' => 'Cuerpo traducido',
     *                   ],
     *                   ...
     *               ]
     */
    public function translateArticle(
        string $title,
        string $body,
        string $summary,
        string $meta_title,
        string $meta_description,
        string $meta_keywords,
        string $facebook_description,
        string $linkedin_description,
        string $instagram_description,
        string $twitter_description
    ): array {
        $locales = SettingsManager::read('Translations', null);

        $translations = [];
        foreach ($locales as $locale => $enabled) {
            if ($enabled) {
                $translationResult = $this->translateClient->translateBatch(
                    [
                        $title,
                        $body,
                        $summary,
                        $meta_title,
                        $meta_description,
                        $meta_keywords,
                        $facebook_description,
                        $linkedin_description,
                        $instagram_description,
                        $twitter_description,
                    ],
                    [
                        'source' => 'en',
                        'target' => $locale,
                        'format' => 'html',
                    ]
                );
                $translations[$locale]['title'] = $translationResult[0]['text'];
                $translations[$locale]['body'] = $translationResult[1]['text'];
                $translations[$locale]['summary'] = $translationResult[2]['text'];
                $translations[$locale]['meta_title'] = $translationResult[3]['text'];
                $translations[$locale]['meta_description'] = $translationResult[4]['text'];
                $translations[$locale]['meta_keywords'] = $translationResult[5]['text'];
                $translations[$locale]['facebook_description'] = $translationResult[6]['text'];
                $translations[$locale]['linkedin_description'] = $translationResult[7]['text'];
                $translations[$locale]['instagram_description'] = $translationResult[8]['text'];
                $translations[$locale]['twitter_description'] = $translationResult[9]['text'];
            }
        }

        return $translations;
    }
}
