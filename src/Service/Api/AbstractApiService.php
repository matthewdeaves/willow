<?php
declare(strict_types=1);

namespace App\Service\Api;

use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Http\Exception\ServiceUnavailableException;
use Cake\Log\Log;

abstract class AbstractApiService
{
    protected Client $client;
    protected string $apiKey;
    protected string $apiUrl;
    protected string $apiVersion;

    public function __construct(Client $client, string $apiKey, string $apiUrl, string $apiVersion)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->apiVersion = $apiVersion;
    }

    protected function sendRequest(array $payload): Response
    {
        $response = $this->client->post(
            $this->apiUrl,
            json_encode($payload),
            ['headers' => $this->getHeaders()]
        );

        if (!$response->isOk()) {
            $this->handleApiError($response);
        }

        return $response;
    }

    protected function getHeaders(): array
    {
        return [
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
            'Content-Type' => 'application/json',
        ];
    }

    protected function handleApiError(Response $response): void
    {
        $errorBody = $response->getStringBody();
        $statusCode = $response->getStatusCode();
        Log::error("API error: Status Code: {$statusCode}, Body: {$errorBody}");
        throw new ServiceUnavailableException(__('API request failed. Please try again later.'));
    }

    abstract protected function parseResponse(Response $response): array;
}
