<?php
declare(strict_types=1);

namespace App\Service\Api\Google;

use App\Service\Api\AbstractApiService;
use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Google\Cloud\Translate\V2\TranslateClient;

/**
 * GoogleApiService Class
 *
 * This service class provides an interface to interact with the Google Cloud Translation API,
 * handling translation tasks.
 */
class GoogleApiService extends AbstractApiService
{
    /**
     * @var \Google\Cloud\Translate\V2\TranslateClient The Google Cloud Translation client.
     */
    private TranslateClient $translateClient;

    /**
     * @var \App\Service\Api\Google\TranslationGenerator The translation generator service.
     */
    private TranslationGenerator $translationGenerator;

    /**
     * GoogleApiService constructor.
     *
     * Initializes the service with necessary dependencies and configurations.
     */
    public function __construct()
    {
        $apiKey = SettingsManager::read('Google.apiKey');
        parent::__construct(new Client(), $apiKey, '', '');

        $this->translateClient = new TranslateClient([
            'key' => $apiKey,
        ]);

        $this->translationGenerator = new TranslationGenerator($this, $this->translateClient);
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