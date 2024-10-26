<?php
declare(strict_types=1);

namespace App\Service\Api\Google;

use App\Utility\SettingsManager;
use Google\Cloud\Translate\V2\TranslateClient;

class GoogleApiService
{
    private TranslateClient $translateClient;

    public function __construct()
    {
        $this->translateClient = new TranslateClient([
            'key' => SettingsManager::read('Google.apiKey', ''),
        ]);
    }

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
