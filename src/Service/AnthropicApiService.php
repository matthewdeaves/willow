<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\AipromptsTable;
use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Http\Exception\ServiceUnavailableException;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use InvalidArgumentException;

class AnthropicApiService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';

    private Client $client;
    private string $apiKey;
    private AipromptsTable $aipromptsTable;

    /**
     * Constructor for AnthropicApiService.
     *
     * Initializes the HTTP client, retrieves the API key from settings, and loads the Aiprompts table.
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = SettingsManager::read('AI.anthropicApiKey');
        $this->aipromptsTable = TableRegistry::getTableLocator()->get('Aiprompts');
    }

    /**
     * Analyzes an image using the Anthropic API.
     *
     * @param string $imagePath The path to the image file.
     * @return array{name: string, alt_text: string, keywords: string} Analysis results.
     * @throws \InvalidArgumentException If the image file doesn't exist.
     * @throws \Cake\Http\Exception\ServiceUnavailableException If the API request fails.
     */
    public function analyzeImage(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("Image file not found: {$imagePath}");
        }

        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);

        $promptData = $this->getPromptData('image_analysis');

        $payload = [
            'model' => $promptData['model'],
            'max_tokens' => $promptData['max_tokens'],
            'temperature' => $promptData['temperature'],
            'system' => $promptData['system_prompt'],
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
        ];

        $response = $this->client->post(
            self::API_URL,
            json_encode($payload),
            ['headers' => $this->getHeaders()]
        );

        if (!$response->isOk()) {
            $errorBody = $response->getStringBody();
            $statusCode = $response->getStatusCode();
            Log::error("Anthropic API error: Status Code: {$statusCode}, Body: {$errorBody}");
            throw new ServiceUnavailableException(__('Failed to analyze image. Please try again later.'));
        }

        return $this->parseResponse($response);
    }

    /**
     * Summarizes text using the Anthropic API.
     *
     * This method takes the text, strips any HTML tags and decodes HTML entities
     * to ensure plain text before sending it to the Anthropic API for summarization.
     *
     * @param string $text The content to summarize, potentially containing HTML.
     * @return string The summarized content of the article.
     * @throws \Cake\Http\Exception\ServiceUnavailableException If the API request fails or returns an error.
     */
    public function summariseText(string $text): string
    {
        // Strip HTML tags and decode HTML entities to ensure plain text
        $plainTextContent = strip_tags(html_entity_decode($text));

        $promptData = $this->getPromptData('article_summary');

        $payload = [
            'model' => $promptData['model'],
            'max_tokens' => $promptData['max_tokens'],
            'temperature' => $promptData['temperature'],
            'system' => $promptData['system_prompt'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $plainTextContent,
                ],
            ],
        ];

        $response = $this->client->post(
            self::API_URL,
            json_encode($payload),
            ['headers' => $this->getHeaders()]
        );

        if (!$response->isOk()) {
            $errorBody = $response->getStringBody();
            $statusCode = $response->getStatusCode();
            Log::error("Anthropic API error: Status Code: {$statusCode}, Body: {$errorBody}");
            throw new ServiceUnavailableException(__('Failed to summarize article. Please try again later.'));
        }

        $result = $this->parseResponse($response);

        return $result['summary'] ?? '';
    }

    /**
     * Gets the headers for the API request.
     *
     * @return array The headers for the API request.
     */
    private function getHeaders(): array
    {
        return [
            'x-api-key' => $this->apiKey,
            'anthropic-version' => self::API_VERSION,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Gets the prompt data for the API request based on the task.
     *
     * @param string $task The task type for the API request.
     * @return array The prompt data including system_prompt, model, max_tokens, and temperature.
     * @throws \InvalidArgumentException If the task type is not found.
     */
    private function getPromptData(string $task): array
    {
        $prompt = $this->aipromptsTable->find()
            ->where(['task_type' => $task])
            ->first();

        if (!$prompt) {
            throw new InvalidArgumentException("Unknown task: {$task}");
        }

        return [
            'system_prompt' => $prompt->system_prompt,
            'model' => $prompt->model,
            'max_tokens' => $prompt->max_tokens,
            'temperature' => $prompt->temperature,
        ];
    }

    /**
     * Parses the API response.
     *
     * @param \Cake\Http\Client\Response $response The API response.
     * @return array The parsed response.
     */
    private function parseResponse(Response $response): array
    {
        $responseData = $response->getJson();

        return json_decode($responseData['content'][0]['text'], true);
    }
}
