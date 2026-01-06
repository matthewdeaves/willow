<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api;

use App\Service\Api\AiProviderInterface;
use App\Service\Api\AiService;
use Cake\Http\Client\Response;
use Cake\TestSuite\TestCase;

/**
 * AiServiceTest
 *
 * Tests the AiService class that provides a unified interface for AI operations
 * regardless of the configured provider (Anthropic or OpenRouter).
 */
class AiServiceTest extends TestCase
{
    /**
     * Test fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Aiprompts',
        'app.Settings',
    ];

    /**
     * Test that AiService can be instantiated with a mock provider
     */
    public function testConstructorWithInjectedProvider(): void
    {
        $mockProvider = $this->createMock(AiProviderInterface::class);
        $mockProvider->method('getProviderName')->willReturn('test-provider');

        $service = new AiService($mockProvider);

        $this->assertEquals('test-provider', $service->getProviderName());
        $this->assertSame($mockProvider, $service->getProvider());
    }

    /**
     * Test generateArticleSeo delegates to the provider correctly
     */
    public function testGenerateArticleSeo(): void
    {
        $mockProvider = $this->createMockProviderWithResponse([
            'meta_title' => 'Test Title',
            'meta_description' => 'Test Description',
            'meta_keywords' => 'test, keywords',
            'facebook_description' => 'FB desc',
            'linkedin_description' => 'LI desc',
            'twitter_description' => 'TW desc',
            'instagram_description' => 'IG desc',
        ]);

        $service = new AiService($mockProvider);

        // Create test prompt
        $this->createTestPrompt('article_seo_analysis');

        $result = $service->generateArticleSeo('Test Article', 'This is the article body content.');

        $this->assertArrayHasKey('meta_title', $result);
        $this->assertEquals('Test Title', $result['meta_title']);
    }

    /**
     * Test generateTagSeo delegates to the provider correctly
     */
    public function testGenerateTagSeo(): void
    {
        $mockProvider = $this->createMockProviderWithResponse([
            'meta_title' => 'Tag Title',
            'meta_description' => 'Tag Description',
            'meta_keywords' => 'tag, keywords',
            'facebook_description' => 'FB desc',
            'linkedin_description' => 'LI desc',
            'twitter_description' => 'TW desc',
            'instagram_description' => 'IG desc',
        ]);

        $service = new AiService($mockProvider);

        // Create test prompt
        $this->createTestPrompt('tag_seo_analysis');

        $result = $service->generateTagSeo('Test Tag', 'Tag description text');

        $this->assertArrayHasKey('meta_title', $result);
        $this->assertEquals('Tag Title', $result['meta_title']);
    }

    /**
     * Test analyzeComment delegates to the provider correctly
     */
    public function testAnalyzeComment(): void
    {
        $mockProvider = $this->createMockProviderWithResponse([
            'comment' => 'This is a nice comment',
            'is_inappropriate' => false,
            'reason' => [],
        ]);

        $service = new AiService($mockProvider);

        // Create test prompt
        $this->createTestPrompt('comment_analysis');

        $result = $service->analyzeComment('This is a nice comment');

        $this->assertArrayHasKey('is_inappropriate', $result);
        $this->assertFalse($result['is_inappropriate']);
    }

    /**
     * Test generateTextSummary delegates to the provider correctly
     */
    public function testGenerateTextSummary(): void
    {
        $mockProvider = $this->createMockProviderWithResponse([
            'summary' => 'This is a summary of the article.',
            'key_points' => ['Point 1', 'Point 2'],
            'lede' => 'Opening sentence.',
        ]);

        $service = new AiService($mockProvider);

        // Create test prompt
        $this->createTestPrompt('text_summary');

        $result = $service->generateTextSummary('article', 'Long article content here...');

        $this->assertArrayHasKey('summary', $result);
        $this->assertEquals('This is a summary of the article.', $result['summary']);
    }

    /**
     * Test generateArticleTags delegates to the provider correctly
     */
    public function testGenerateArticleTags(): void
    {
        $mockProvider = $this->createMockProviderWithResponse([
            'tags' => [
                ['tag' => 'Technology', 'description' => 'Tech related'],
            ],
        ]);

        $service = new AiService($mockProvider);

        // Create test prompt
        $this->createTestPrompt('article_tag_generation');

        $result = $service->generateArticleTags(['existing-tag'], 'Test Title', 'Article body');

        $this->assertArrayHasKey('tags', $result);
        $this->assertIsArray($result['tags']);
    }

    /**
     * Create a mock provider that returns a specific response
     */
    private function createMockProviderWithResponse(array $response): AiProviderInterface
    {
        $mockResponse = $this->createMock(Response::class);

        $mockProvider = $this->createMock(AiProviderInterface::class);
        $mockProvider->method('sendRequest')->willReturn($mockResponse);
        $mockProvider->method('parseResponse')->willReturn($response);
        $mockProvider->method('getProviderName')->willReturn('mock');

        return $mockProvider;
    }

    /**
     * Create a test prompt in the database
     */
    private function createTestPrompt(string $taskType): void
    {
        $aipromptsTable = $this->getTableLocator()->get('Aiprompts');
        $prompt = $aipromptsTable->newEntity([
            'task_type' => $taskType,
            'system_prompt' => 'Test system prompt for ' . $taskType,
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ]);
        $aipromptsTable->save($prompt);
    }
}
