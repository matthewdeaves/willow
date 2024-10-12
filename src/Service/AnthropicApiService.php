<?php
declare(strict_types=1);

namespace App\Service;

use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Http\Exception\ServiceUnavailableException;
use Cake\Log\Log;
use InvalidArgumentException;

class AnthropicApiService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';
    private const MODEL = 'claude-3-haiku-20240307';
    private const MAX_TOKENS = 1000;
    private const TEMPERATURE = 0;

    private Client $client;
    private string $apiKey;

    /**
     * Constructor for AnthropicApiService.
     *
     * Initializes the HTTP client and retrieves the API key from settings.
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
     * @return array{alt_text: string, keywords: string} Analysis results.
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

        $payload = [
            'model' => self::MODEL,
            'max_tokens' => self::MAX_TOKENS,
            'temperature' => self::TEMPERATURE,
            'system' => $this->getSystemPrompt('image_analysis'),
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

        $payload = [
            'model' => self::MODEL,
            'max_tokens' => self::MAX_TOKENS,
            'temperature' => self::TEMPERATURE,
            'system' => $this->getSystemPrompt('article_summary'),
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
     * Gets the system prompt for the API request based on the task.
     *
     * @param string $task The task type for the API request.
     * @return string The system prompt.
     */
    private function getSystemPrompt(string $task): string
    {
        switch ($task) {
            case 'image_analysis':
                return 'You are an image analysis robot. You will receive an image and based on the image ' .
                    "generate the following data items:\nalt_text: a string containing alternative text describing " .
                    "the image for visually impaired people. Up to 255 characters long\nkeywords: a string " .
                    'containing space separated keywords based on the content of the image. Maximum 20 unique ' .
                    "words.\nYou will respond only in valid JSON format including only the above data items " .
                    'and their values.';
            case 'article_summary':
                return 'You are an article summarization assistant. Provide a concise summary of the given article ' .
                    'content in no more than 3 paragraphs. Focus on the main points and key takeaways. ' .
                    'Respond with a JSON object containing a single key "summary" with the summarized ' .
                    'content as its value.';
            default:
                throw new InvalidArgumentException("Unknown task: {$task}");
        }
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
