<?php
declare(strict_types=1);

namespace App\Service\Api;

use Cake\Http\Client\Response;

/**
 * AiProviderInterface
 *
 * Defines the contract for AI API providers (Anthropic, OpenRouter, etc.).
 * This interface allows generators to work with any AI provider that implements it,
 * enabling easy switching between providers without changing generator code.
 */
interface AiProviderInterface
{
    /**
     * Sends a request to the AI provider API.
     *
     * @param array $payload The request payload in Anthropic format.
     * @param int $timeout Request timeout in seconds.
     * @return \Cake\Http\Client\Response The HTTP response from the API.
     * @throws \Cake\Http\Exception\ServiceUnavailableException If the API request fails.
     */
    public function sendRequest(array $payload, int $timeout = 30): Response;

    /**
     * Parses the API response and extracts the content.
     *
     * @param \Cake\Http\Client\Response $response The HTTP response from the API.
     * @return array The parsed response data as an associative array.
     */
    public function parseResponse(Response $response): array;

    /**
     * Checks if the provider is properly configured with valid credentials.
     *
     * @return bool True if the provider has a valid API key configured.
     */
    public function isConfigured(): bool;

    /**
     * Gets the provider name for logging and identification purposes.
     *
     * @return string The provider identifier (e.g., 'anthropic', 'openrouter').
     */
    public function getProviderName(): string;
}
