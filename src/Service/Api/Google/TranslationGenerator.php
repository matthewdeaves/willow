<?php
declare(strict_types=1);

namespace App\Service\Api\Google;

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
     * Constructor for the TranslationGenerator class.
     *
     * @param \App\Service\Api\Google\GoogleApiService $apiService The API service for handling requests.
     */
    public function __construct(GoogleApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Translates an array of strings from one locale to another.
     *
     * This method uses the Google Cloud Translation API to translate the provided strings
     * from the specified source locale to the target locale.
     *
     * @param array $strings The array of strings to be translated.
     * @param string $localeFrom The locale code of the source language (e.g., 'en').
     * @param string $localeTo The locale code of the target language (e.g., 'fr').
     * @return array The translated strings.
     */
    public function translateStrings(array $strings, string $localeFrom, string $localeTo): array
    {
        $data = [
            'q' => $strings,
            'source' => $localeFrom,
            'target' => $localeTo,
            'format' => 'text',
        ];

        $response = $this->apiService->sendRequest($data);
        $translationData = $this->apiService->parseResponse($response);

        $translatedStrings = [];
        foreach ($translationData['data']['translations'] as $translation) {
            $translatedStrings[] = $translation['translatedText'];
        }

        return $translatedStrings;
    }
}
