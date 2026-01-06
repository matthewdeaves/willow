<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use App\Service\Api\AiProviderInterface;
use App\Service\Api\Anthropic\AbstractAnthropicGenerator;
use Cake\Http\Client\Response;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

/**
 * AbstractAnthropicGeneratorTest
 *
 * Tests the common functionality provided by the AbstractAnthropicGenerator base class.
 * Uses a concrete test implementation to verify abstract class behavior.
 */
class AbstractAnthropicGeneratorTest extends TestCase
{
    /**
     * Test fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Aiprompts',
    ];

    private AiProviderInterface $mockApiService;
    private AipromptsTable $aipromptsTable;
    private TestAnthropicGenerator $generator;

    /**
     * setUp method
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->mockApiService = $this->createMock(AiProviderInterface::class);
        $this->mockApiService->method('getProviderName')->willReturn('anthropic');
        $this->aipromptsTable = $this->getTableLocator()->get('Aiprompts');
        $this->generator = new TestAnthropicGenerator($this->mockApiService, $this->aipromptsTable);
    }

    /**
     * Test constructor sets dependencies correctly
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(AbstractAnthropicGenerator::class, $this->generator);
    }

    /**
     * Test getPromptData retrieves correct prompt data for Anthropic provider
     */
    public function testGetPromptDataSuccessForAnthropic(): void
    {
        // Create a test prompt in the database
        $promptData = [
            'task_type' => 'test_task',
            'system_prompt' => 'Test system prompt',
            'model' => 'claude-3-sonnet-20240229',
            'openrouter_model' => 'anthropic/claude-3-sonnet',
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];
        $this->aipromptsTable->save($this->aipromptsTable->newEntity($promptData));

        $result = $this->generator->getPromptDataPublic('test_task');

        // Should use the 'model' field for Anthropic provider
        $expected = [
            'system_prompt' => 'Test system prompt',
            'model' => 'claude-3-sonnet-20240229',
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test getPromptData retrieves correct prompt data for OpenRouter provider
     */
    public function testGetPromptDataSuccessForOpenRouter(): void
    {
        // Create a mock provider that returns 'openrouter'
        $openrouterMock = $this->createMock(AiProviderInterface::class);
        $openrouterMock->method('getProviderName')->willReturn('openrouter');

        $generator = new TestAnthropicGenerator($openrouterMock, $this->aipromptsTable);

        // Create a test prompt in the database
        $promptData = [
            'task_type' => 'openrouter_test_task',
            'system_prompt' => 'Test system prompt',
            'model' => 'claude-3-sonnet-20240229',
            'openrouter_model' => 'anthropic/claude-3-sonnet',
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];
        $this->aipromptsTable->save($this->aipromptsTable->newEntity($promptData));

        $result = $generator->getPromptDataPublic('openrouter_test_task');

        // Should use the 'openrouter_model' field for OpenRouter provider
        $expected = [
            'system_prompt' => 'Test system prompt',
            'model' => 'anthropic/claude-3-sonnet',
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test getPromptData falls back to model when openrouter_model is empty
     */
    public function testGetPromptDataFallbackWhenOpenRouterModelEmpty(): void
    {
        // Create a mock provider that returns 'openrouter'
        $openrouterMock = $this->createMock(AiProviderInterface::class);
        $openrouterMock->method('getProviderName')->willReturn('openrouter');

        $generator = new TestAnthropicGenerator($openrouterMock, $this->aipromptsTable);

        // Create a test prompt without openrouter_model
        $promptData = [
            'task_type' => 'fallback_test_task',
            'system_prompt' => 'Test system prompt',
            'model' => 'claude-3-sonnet-20240229',
            'openrouter_model' => null,
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];
        $this->aipromptsTable->save($this->aipromptsTable->newEntity($promptData));

        $result = $generator->getPromptDataPublic('fallback_test_task');

        // Should fall back to 'model' field when openrouter_model is empty
        $expected = [
            'system_prompt' => 'Test system prompt',
            'model' => 'claude-3-sonnet-20240229',
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test getPromptData throws exception for unknown task
     */
    public function testGetPromptDataThrowsExceptionForUnknownTask(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown task: nonexistent_task');

        $this->generator->getPromptDataPublic('nonexistent_task');
    }

    /**
     * Test createPayload creates correct structure
     */
    public function testCreatePayloadWithArrayContent(): void
    {
        $promptData = [
            'system_prompt' => 'Test prompt',
            'model' => 'claude-3-sonnet',
            'max_tokens' => 500,
            'temperature' => 0.5,
        ];

        $content = ['key' => 'value', 'test' => 'data'];

        $result = $this->generator->createPayloadPublic($promptData, $content);

        $expected = [
            'model' => 'claude-3-sonnet',
            'max_tokens' => 500,
            'temperature' => 0.5,
            'system' => 'Test prompt',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => json_encode($content),
                ],
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test createPayload with string content
     */
    public function testCreatePayloadWithStringContent(): void
    {
        $promptData = [
            'system_prompt' => 'Test prompt',
            'model' => 'claude-3-sonnet',
            'max_tokens' => 500,
            'temperature' => 0.5,
        ];

        $content = 'This is a test string';

        $result = $this->generator->createPayloadPublic($promptData, $content);

        $expected = [
            'model' => 'claude-3-sonnet',
            'max_tokens' => 500,
            'temperature' => 0.5,
            'system' => 'Test prompt',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'This is a test string',
                ],
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test ensureExpectedKeys adds missing keys
     */
    public function testEnsureExpectedKeysAddsMissingKeys(): void
    {
        $result = ['existing_key' => 'value'];

        $processedResult = $this->generator->ensureExpectedKeysPublic($result);

        $expected = [
            'existing_key' => 'value',
            'test_key_1' => '',
            'test_key_2' => '',
        ];

        $this->assertEquals($expected, $processedResult);
    }

    /**
     * Test ensureExpectedKeys preserves existing keys
     */
    public function testEnsureExpectedKeysPreservesExistingKeys(): void
    {
        $result = [
            'test_key_1' => 'existing_value',
            'test_key_2' => 'another_value',
        ];

        $processedResult = $this->generator->ensureExpectedKeysPublic($result);

        $this->assertEquals($result, $processedResult);
    }

    /**
     * Test ensureExpectedKeys with custom keys
     */
    public function testEnsureExpectedKeysWithCustomKeys(): void
    {
        $result = ['existing' => 'value'];
        $customKeys = ['custom_key_1', 'custom_key_2'];

        $processedResult = $this->generator->ensureExpectedKeysPublic($result, $customKeys);

        $expected = [
            'existing' => 'value',
            'custom_key_1' => '',
            'custom_key_2' => '',
        ];

        $this->assertEquals($expected, $processedResult);
    }

    /**
     * Test sendApiRequest without timeout
     */
    public function testSendApiRequestWithoutTimeout(): void
    {
        $payload = ['test' => 'data'];
        $expectedResponse = ['result' => 'success'];

        $mockResponse = $this->createMock(Response::class);

        $this->mockApiService->expects($this->once())
            ->method('sendRequest')
            ->with($payload)
            ->willReturn($mockResponse);

        $this->mockApiService->expects($this->once())
            ->method('parseResponse')
            ->with($mockResponse)
            ->willReturn($expectedResponse);

        $result = $this->generator->sendApiRequestPublic($payload);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Test sendApiRequest with timeout
     */
    public function testSendApiRequestWithTimeout(): void
    {
        $payload = ['test' => 'data'];
        $timeout = 45;
        $expectedResponse = ['result' => 'success'];

        $mockResponse = $this->createMock(Response::class);

        $this->mockApiService->expects($this->once())
            ->method('sendRequest')
            ->with($payload, $timeout)
            ->willReturn($mockResponse);

        $this->mockApiService->expects($this->once())
            ->method('parseResponse')
            ->with($mockResponse)
            ->willReturn($expectedResponse);

        $result = $this->generator->sendApiRequestPublic($payload, $timeout);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Test formatContent with array
     */
    public function testFormatContentWithArray(): void
    {
        $content = ['key' => 'value'];
        $result = $this->generator->formatContentPublic($content);

        $this->assertEquals(json_encode($content), $result);
    }

    /**
     * Test formatContent with string
     */
    public function testFormatContentWithString(): void
    {
        $content = 'test string';
        $result = $this->generator->formatContentPublic($content);

        $this->assertEquals($content, $result);
    }
}
