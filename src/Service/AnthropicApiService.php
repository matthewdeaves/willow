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
     * Generates SEO content for an article using the Anthropic API.
     *
     * @param string $title The title of the article.
     * @param string $body The body content of the article.
     * @return array The generated SEO content including meta title, description, keywords, and social media descriptions.
     * @throws \Cake\Http\Exception\ServiceUnavailableException If the API request fails or returns an error.
     */
    public function generateArticleSeo(string $title, string $body): array
    {
        $plainTextContent = strip_tags(html_entity_decode($body));

        $promptData = $this->getPromptData('article_seo_analysis');

        $payload = [
            'model' => $promptData['model'],
            'max_tokens' => $promptData['max_tokens'],
            'temperature' => $promptData['temperature'],
            'system' => $promptData['system_prompt'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Title: {$title}\n\nContent: {$plainTextContent}",
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
            throw new ServiceUnavailableException(__('Failed to generate SEO content. Please try again later.'));
        }

        $result = $this->parseResponse($response);

        // Ensure all expected keys are present in the result
        $expectedKeys = [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'facebook_description',
            'linkedin_description',
            'twitter_description',
            'instagram_description',
        ];

        foreach ($expectedKeys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = '';
            }
        }

        return $result;
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
