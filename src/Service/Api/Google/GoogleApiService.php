<?php
declare(strict_types=1);

namespace App\Service\Api\Google;

use App\Service\Api\AbstractApiService;
use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\Http\Client\Response;

/**
 * GoogleApiService Class
 *
 * This service class provides an interface to interact with the Google Cloud Translation API,
 * handling translation tasks.
 */
class GoogleApiService extends AbstractApiService
{
    /**
     * @var \App\Service\Api\Google\TranslationGenerator The translation generator service.
     */
    private TranslationGenerator $translationGenerator;

    /**
     * @var string The API key for Google Cloud Translation API.
     */
    private string $apiKey;

    /**
     * GoogleApiService constructor.
     *
     * Initializes the service with necessary dependencies and configurations.
     */
    public function __construct()
    {
        $this->apiKey = SettingsManager::read('Google.apiKey');
        $apiUrl = 'https://translation.googleapis.com/language/translate/v2';
        parent::__construct(new Client(), $this->apiKey, $apiUrl, '');

        $this->translationGenerator = new TranslationGenerator($this);
    }

    /**
     * Retrieves the headers for an HTTP request to the Google API.
     *
     * This method returns an associative array of headers to be used in an HTTP request
     * to the Google API. The headers include:
     * - 'Content-Type': Specifies that the content type of the request is JSON.
     * - 'Authorization': Contains a Bearer token for API authentication, constructed using the apiKey.
     *
     * @return array An associative array of HTTP headers.
     */
    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];
    }

    /**
     * Translates an array of strings from one locale to another.
     *
     * This method utilizes the TranslationGenerator service to perform translations
     * of the provided strings from the specified source locale to the target locale.
     *
     * @param array $strings The array of strings to be translated.
     * @param string $localeFrom The locale code of the source language (e.g., 'en').
     * @param string $localeTo The locale code of the target language (e.g., 'fr').
     * @return array The translated strings.
     */
    public function translateStrings(array $strings, string $localeFrom, string $localeTo): array
    {
        return $this->translationGenerator->translateStrings($strings, $localeFrom, $localeTo);
    }

    /**
     * Parses the response from the API.
     *
     * @param \Cake\Http\Client\Response $response The HTTP response from the API.
     * @return array The parsed response data.
     */
    protected function parseResponse(Response $response): array
    {
        return $response->getJson();
    }
}
