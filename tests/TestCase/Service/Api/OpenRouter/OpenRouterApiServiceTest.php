<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api\OpenRouter;

use App\Service\Api\AiProviderInterface;
use App\Service\Api\OpenRouter\OpenRouterApiService;
use Cake\Http\Client\Response;
use Cake\TestSuite\TestCase;
use ReflectionClass;

/**
 * OpenRouterApiServiceTest
 *
 * Tests the OpenRouter API service implementation.
 */
class OpenRouterApiServiceTest extends TestCase
{
    /**
     * Test fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Settings',
    ];

    /**
     * Test that the service implements AiProviderInterface
     */
    public function testImplementsInterface(): void
    {
        $service = new OpenRouterApiService();
        $this->assertInstanceOf(AiProviderInterface::class, $service);
    }

    /**
     * Test getProviderName returns 'openrouter'
     */
    public function testGetProviderName(): void
    {
        $service = new OpenRouterApiService();
        $this->assertEquals('openrouter', $service->getProviderName());
    }

    /**
     * Test isConfigured returns false when no API key is set
     */
    public function testIsConfiguredWithoutApiKey(): void
    {
        $service = new OpenRouterApiService();
        $this->assertFalse($service->isConfigured());
    }

    /**
     * Test payload transformation from Anthropic to OpenRouter format
     */
    public function testPayloadTransformation(): void
    {
        $service = new OpenRouterApiService();

        // Use reflection to access the private method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('transformPayload');
        $method->setAccessible(true);

        $payload = [
            'model' => 'anthropic/claude-3.5-sonnet',
            'max_tokens' => 1024,
            'temperature' => 0.7,
            'system' => 'You are a helpful assistant.',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hello, world!',
                ],
            ],
        ];

        $result = $method->invoke($service, $payload);

        // Check model is passed through directly (no mapping)
        $this->assertEquals('anthropic/claude-3.5-sonnet', $result['model']);

        // Check max_tokens and temperature are preserved
        $this->assertEquals(1024, $result['max_tokens']);
        $this->assertEquals(0.7, $result['temperature']);

        // Check messages structure - system should be first message
        $this->assertCount(2, $result['messages']);
        $this->assertEquals('system', $result['messages'][0]['role']);
        $this->assertEquals('You are a helpful assistant.', $result['messages'][0]['content']);
        $this->assertEquals('user', $result['messages'][1]['role']);
        $this->assertEquals('Hello, world!', $result['messages'][1]['content']);
    }

    /**
     * Test payload transformation without system prompt
     */
    public function testPayloadTransformationWithoutSystem(): void
    {
        $service = new OpenRouterApiService();

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('transformPayload');
        $method->setAccessible(true);

        $payload = [
            'model' => 'openai/gpt-4o',
            'max_tokens' => 500,
            'temperature' => 0,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Test message',
                ],
            ],
        ];

        $result = $method->invoke($service, $payload);

        // Model passed through directly
        $this->assertEquals('openai/gpt-4o', $result['model']);

        // Only user message should be present
        $this->assertCount(1, $result['messages']);
        $this->assertEquals('user', $result['messages'][0]['role']);
    }

    /**
     * Test payload transformation with default model
     */
    public function testPayloadTransformationWithDefaultModel(): void
    {
        $service = new OpenRouterApiService();

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('transformPayload');
        $method->setAccessible(true);

        // Payload without model specified
        $payload = [
            'max_tokens' => 500,
            'temperature' => 0,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Test message',
                ],
            ],
        ];

        $result = $method->invoke($service, $payload);

        // Should use default model
        $this->assertEquals('anthropic/claude-3.5-sonnet', $result['model']);
    }

    /**
     * Test response parsing for OpenAI-compatible format
     */
    public function testParseResponse(): void
    {
        $service = new OpenRouterApiService();

        // Create a mock response with OpenAI-compatible format
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getJson')->willReturn([
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion',
            'created' => 1677652288,
            'model' => 'anthropic/claude-3.5-sonnet',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => '{"meta_title": "Test Title", "meta_description": "Test Description"}',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'prompt_tokens' => 100,
                'completion_tokens' => 50,
                'total_tokens' => 150,
            ],
        ]);

        $result = $service->parseResponse($mockResponse);

        $this->assertIsArray($result);
        $this->assertEquals('Test Title', $result['meta_title']);
        $this->assertEquals('Test Description', $result['meta_description']);
    }

    /**
     * Test response parsing with invalid JSON content
     */
    public function testParseResponseWithInvalidJson(): void
    {
        $service = new OpenRouterApiService();

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getJson')->willReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => 'This is not valid JSON',
                    ],
                ],
            ],
        ]);

        $result = $service->parseResponse($mockResponse);

        // Should return empty array for invalid JSON
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test response parsing with empty response
     */
    public function testParseResponseWithEmptyContent(): void
    {
        $service = new OpenRouterApiService();

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getJson')->willReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => '',
                    ],
                ],
            ],
        ]);

        $result = $service->parseResponse($mockResponse);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getHeaders returns correct authorization format
     */
    public function testGetHeadersFormat(): void
    {
        $service = new OpenRouterApiService();

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getHeaders');
        $method->setAccessible(true);

        $headers = $method->invoke($service);

        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertStringStartsWith('Bearer ', $headers['Authorization']);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertArrayHasKey('X-Title', $headers);
    }
}
