<?php
declare(strict_types=1);

namespace App\Service\Api\Google;

use Google\Cloud\Translate\V2\TranslateClient;

/**
 * Class TranslationGenerator
 *
 * This class is responsible for generating translations of text using the Google Cloud Translation API.
 * It interacts with the GoogleApiService to send requests and parse responses.
 */
class TranslationGenerator
{
    /**
     * The Google API service used for sending requests and parsing responses.
     *
     * @var \App\Service\Api\Google\GoogleApiService
     */
    private GoogleApiService $apiService;

    /**
     * The Google Cloud Translation client used for performing translations.
     *
     * @var \Google\Cloud\Translate\V2\TranslateClient
     */
    private TranslateClient $translateClient;

    /**
     * Constructor for the TranslationGenerator class.
     *
     * @param \App\Service\Api\Google\GoogleApiService $apiService The API service for handling requests.
     * @param \Google\Cloud\Translate\V2\TranslateClient $translateClient The Google Cloud Translation client.
     */
    public function __construct(GoogleApiService $apiService, TranslateClient $translateClient)
    {
        $this->apiService = $apiService;
        $this->translateClient = $translateClient;
    }

    /**
     * Translates an array of strings from one locale to another.
     *
     * This method uses the Google Cloud Translation client to translate the provided strings
     * from the specified source locale to the target locale.
     *
     * @param array $strings The array of strings to be translated.
     * @param string $localeFrom The locale code of the source language (e.g., 'en').
     * @param string $localeTo The locale code of the target language (e.g., 'fr').
     * @return array The translated strings.
     */
    public function translateStrings(array $strings, string $localeFrom, string $localeTo): array
    {
        $translations = $this->translateClient->translateBatch($strings, [
            'source' => $localeFrom,
            'target' => $localeTo,
        ]);

        $translatedStrings = [];
        foreach ($translations as $translation) {
            $translatedStrings[] = $translation['text'];
        }

        return $translatedStrings;
    }
}