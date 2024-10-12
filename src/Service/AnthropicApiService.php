<?php
declare(strict_types=1);

namespace App\Service;

use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\Log\Log;

class AnthropicApiService
{
    /**
     * @var \Cake\Http\Client
     */
    private Client $client;

    /**
     * @var string
     */
    private string $apiKey;

    /**
     * Constructor for AnthropicApiService.
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = SettingsManager::read('AI.anthropicApiKey');
    }

    /**
     * Analyzes an image using the Anthropic API.
     *
     * @param string $imagePath The path to the image file.
     * @return array|null An array containing 'alt_text' and 'keywords', or null on failure.
     */
    public function analyzeImage(string $imagePath): ?array
    {
        if (!file_exists($imagePath)) {
            Log::error('Image file not found: ' . $imagePath);

            return null;
        }

        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);

        $response = $this->client->post(
            'https://api.anthropic.com/v1/messages',
            json_encode([
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 1000,
                'temperature' => 0,
                'system' => 'You are an image analysis robot. You will receive an image and based on the image ' .
                    "generate the following data items:\nalt_text: a string containing alternative text describing " .
                    "the image for visually impaired people. Up to 255 characters long\nkeywords: a string " .
                    'containing space separated keywords based on the content of the image. Maximum 20 unique ' .
                    "words.\n\nYou will respond only in valid JSON format including only the above data items " .
                    'and their values.',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'image',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => $mimeType,
                                    'data' => $imageData,
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'anthropic-version' => '2023-06-01',
                ],
            ]
        );

        if ($response->isOk()) {
            $result = json_decode($response->getJson()['content'], true);

            return [
                'alt_text' => $result['alt_text'] ?? '',
                'keywords' => $result['keywords'] ?? '',
            ];
        }

        Log::error('Anthropic API error: ' . $response->getStringBody());

        return null;
    }
}
