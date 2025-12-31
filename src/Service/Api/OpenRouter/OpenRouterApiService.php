<?php
declare(strict_types=1);

namespace App\Service\Api\OpenRouter;

use App\Service\Api\AbstractApiService;
use App\Service\Api\AiProviderInterface;
use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\Http\Client\Response;

/**
 * OpenRouterApiService Class
 *
 * Provides integration with OpenRouter's API for accessing AI models.
 * OpenRouter offers access to various AI models including Anthropic's Claude,
 * OpenAI's GPT, Google's Gemini, Meta's Llama, and many more through a unified API.
 *
 * This service transforms Anthropic-format payloads to OpenRouter's OpenAI-compatible
 * format and parses responses back to a consistent format.
 */
class OpenRouterApiService extends AbstractApiService implements AiProviderInterface
{
    /**
     * The base URL for the OpenRouter API.
     *
     * @var string
     */
    private const API_URL = 'https://openrouter.ai/api/v1/chat/completions';

    /**
     * API version placeholder (not used by OpenRouter but required by parent).
     *
     * @var string
     */
    private const API_VERSION = '1.0';

    /**
     * OpenRouterApiService constructor.
     *
     * Initializes the service with the OpenRouter API key from settings.
     */
    public function __construct()
    {
        $apiKey = SettingsManager::read('Anthropic.openRouterApiKey', '');
        parent::__construct(new Client(), $apiKey, self::API_URL, self::API_VERSION);
    }

    /**
     * Gets the headers for the OpenRouter API request.
     *
     * @return array An associative array of headers.
     */
    protected function getHeaders(): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        // Optional headers for OpenRouter ranking and rate limits
        $siteUrl = SettingsManager::read('SEO.siteUrl', '');
        $siteName = SettingsManager::read('SEO.siteName', 'Willow CMS');

        if (!empty($siteUrl)) {
            $headers['HTTP-Referer'] = $siteUrl;
        }
        $headers['X-Title'] = $siteName;

        return $headers;
    }

    /**
     * Transforms an Anthropic-format payload to OpenRouter/OpenAI format.
     *
     * Key differences:
     * - System prompt moves from root 'system' field to a system role message
     * - Model name is used directly (configured in aiprompts.openrouter_model)
     *
     * @param array $payload The Anthropic-format payload.
     * @return array The OpenRouter/OpenAI-format payload.
     */
    private function transformPayload(array $payload): array
    {
        $messages = [];

        // Convert system prompt to system message (OpenAI format)
        if (!empty($payload['system'])) {
            $messages[] = [
                'role' => 'system',
                'content' => $payload['system'],
            ];
        }

        // Add user/assistant messages
        if (isset($payload['messages']) && is_array($payload['messages'])) {
            foreach ($payload['messages'] as $message) {
                $messages[] = $message;
            }
        }

        return [
            'model' => $payload['model'] ?? 'anthropic/claude-3.5-sonnet',
            'messages' => $messages,
            'max_tokens' => $payload['max_tokens'] ?? 4096,
            'temperature' => $payload['temperature'] ?? 0,
        ];
    }

    /**
     * Sends a request to the OpenRouter API.
     *
     * Overrides the parent method to transform the payload before sending.
     *
     * @param array $payload The Anthropic-format payload.
     * @param int $timeout Request timeout in seconds.
     * @return \Cake\Http\Client\Response The HTTP response from the API.
     * @throws \Cake\Http\Exception\ServiceUnavailableException If the API request fails.
     */
    public function sendRequest(array $payload, int $timeout = 30): Response
    {
        $transformedPayload = $this->transformPayload($payload);

        $response = $this->client->post(
            $this->apiUrl,
            json_encode($transformedPayload),
            [
                'headers' => $this->getHeaders(),
                'timeout' => $timeout,
            ],
        );

        if (!$response->isOk()) {
            $this->handleApiError($response);
        }

        return $response;
    }

    /**
     * Parses the OpenRouter API response.
     *
     * OpenRouter returns responses in OpenAI format:
     * { "choices": [{ "message": { "content": "..." } }] }
     *
     * The content is expected to be JSON, which is then decoded.
     *
     * @param \Cake\Http\Client\Response $response The HTTP response from the API.
     * @return array The parsed response data.
     */
    public function parseResponse(Response $response): array
    {
        $responseData = $response->getJson();

        // Extract content from OpenAI-compatible format
        $content = $responseData['choices'][0]['message']['content'] ?? '';

        // The content should be JSON - decode it
        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Checks if the provider is properly configured.
     *
     * @return bool True if the OpenRouter API key is set and not a placeholder.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Gets the provider name for logging purposes.
     *
     * @return string The provider identifier.
     */
    public function getProviderName(): string
    {
        return 'openrouter';
    }
}
