<?php
declare(strict_types=1);

namespace App\Test\TestCase\Job;

use App\Job\TranslateArticleJob;
use App\Job\TranslateImageGalleryJob;
use App\Job\TranslateTagJob;
use App\Service\Api\Google\GoogleApiService;
use Cake\Queue\Job\Message;
use Cake\TestSuite\TestCase;
use Interop\Queue\Processor;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionMethod;

/**
 * AbstractTranslateJob and Translation Jobs Test Case
 *
 * Tests the translation job functionality including the abstract base class
 * and all three concrete implementations.
 */
class AbstractTranslateJobTest extends TestCase
{
    protected array $fixtures = [
        'app.Articles',
        'app.Tags',
        'app.ImageGalleries',
        'app.Users',
        'app.Settings',
    ];

    private GoogleApiService|MockObject $mockApiService;
    private Message|MockObject $mockMessage;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockApiService = $this->createMock(GoogleApiService::class);
        $this->mockMessage = $this->createMock(Message::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->mockApiService, $this->mockMessage);
    }

    /**
     * Test TranslateArticleJob rejects when missing required id argument
     */
    public function testTranslateArticleJobRejectsMissingId(): void
    {
        $this->mockMessage->method('getArgument')
            ->willReturnCallback(fn($arg) => match ($arg) {
                'id' => null,
                'title' => 'Test Article',
                default => null
            });

        $job = new TranslateArticleJob($this->mockApiService);
        $result = $job->execute($this->mockMessage);

        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test TranslateArticleJob rejects when missing required title argument
     */
    public function testTranslateArticleJobRejectsMissingTitle(): void
    {
        $this->mockMessage->method('getArgument')
            ->willReturnCallback(fn($arg) => match ($arg) {
                'id' => 'test-id',
                'title' => null,
                default => null
            });

        $job = new TranslateArticleJob($this->mockApiService);
        $result = $job->execute($this->mockMessage);

        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test TranslateTagJob rejects when missing required arguments
     */
    public function testTranslateTagJobRejectsMissingArguments(): void
    {
        $this->mockMessage->method('getArgument')
            ->willReturnCallback(fn($arg) => match ($arg) {
                'id' => null,
                'title' => 'Test Tag',
                default => null
            });

        $job = new TranslateTagJob($this->mockApiService);
        $result = $job->execute($this->mockMessage);

        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test TranslateImageGalleryJob rejects when missing required arguments
     */
    public function testTranslateImageGalleryJobRejectsMissingArguments(): void
    {
        $this->mockMessage->method('getArgument')
            ->willReturnCallback(fn($arg) => match ($arg) {
                'id' => 'test-id',
                'name' => null, // ImageGallery uses 'name' not 'title'
                default => null
            });

        $job = new TranslateImageGalleryJob($this->mockApiService);
        $result = $job->execute($this->mockMessage);

        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test TranslateArticleJob rejects when no translations are enabled
     */
    public function testTranslateArticleJobRejectsWhenNoTranslationsEnabled(): void
    {
        // Use a non-existent article ID - job will fail but after checking translations
        $this->mockMessage->method('getArgument')
            ->willReturnCallback(fn($arg) => match ($arg) {
                'id' => '630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e', // Page One from fixtures
                'title' => 'Test Article',
                '_attempt' => 0,
                default => null
            });

        $job = new TranslateArticleJob($this->mockApiService);
        $result = $job->execute($this->mockMessage);

        // Job should reject because no translations are enabled in default fixtures
        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test that TranslateArticleJob correctly identifies HTML fields
     */
    public function testTranslateArticleJobHtmlFieldsConfiguration(): void
    {
        $job = new TranslateArticleJob($this->mockApiService);
        $reflection = new ReflectionClass($job);

        $htmlFieldsMethod = $reflection->getMethod('getHtmlFields');
        $htmlFieldsMethod->setAccessible(true);
        $htmlFields = $htmlFieldsMethod->invoke($job);

        $this->assertContains('body', $htmlFields);

        $useHtmlFormatMethod = $reflection->getMethod('useHtmlFormat');
        $useHtmlFormatMethod->setAccessible(true);
        $useHtmlFormat = $useHtmlFormatMethod->invoke($job);

        $this->assertTrue($useHtmlFormat);
    }

    /**
     * Test that TranslateTagJob does not use HTML format
     */
    public function testTranslateTagJobNoHtmlFormat(): void
    {
        $job = new TranslateTagJob($this->mockApiService);
        $reflection = new ReflectionClass($job);

        $htmlFieldsMethod = $reflection->getMethod('getHtmlFields');
        $htmlFieldsMethod->setAccessible(true);
        $htmlFields = $htmlFieldsMethod->invoke($job);

        $this->assertEmpty($htmlFields);

        $useHtmlFormatMethod = $reflection->getMethod('useHtmlFormat');
        $useHtmlFormatMethod->setAccessible(true);
        $useHtmlFormat = $useHtmlFormatMethod->invoke($job);

        $this->assertFalse($useHtmlFormat);
    }

    /**
     * Test job type strings are correct
     */
    public function testJobTypeStrings(): void
    {
        $reflection = new ReflectionMethod(TranslateArticleJob::class, 'getJobType');
        $reflection->setAccessible(true);
        $this->assertEquals('article translation', $reflection->invoke(null));

        $reflection = new ReflectionMethod(TranslateTagJob::class, 'getJobType');
        $reflection->setAccessible(true);
        $this->assertEquals('tag translation', $reflection->invoke(null));

        $reflection = new ReflectionMethod(TranslateImageGalleryJob::class, 'getJobType');
        $reflection->setAccessible(true);
        $this->assertEquals('image gallery translation', $reflection->invoke(null));
    }

    /**
     * Test table alias configuration
     */
    public function testTableAliasConfiguration(): void
    {
        $articleJob = new TranslateArticleJob($this->mockApiService);
        $tagJob = new TranslateTagJob($this->mockApiService);
        $galleryJob = new TranslateImageGalleryJob($this->mockApiService);

        $reflection = new ReflectionMethod(TranslateArticleJob::class, 'getTableAlias');
        $reflection->setAccessible(true);
        $this->assertEquals('Articles', $reflection->invoke($articleJob));

        $reflection = new ReflectionMethod(TranslateTagJob::class, 'getTableAlias');
        $reflection->setAccessible(true);
        $this->assertEquals('Tags', $reflection->invoke($tagJob));

        $reflection = new ReflectionMethod(TranslateImageGalleryJob::class, 'getTableAlias');
        $reflection->setAccessible(true);
        $this->assertEquals('ImageGalleries', $reflection->invoke($galleryJob));
    }

    /**
     * Test required arguments configuration
     */
    public function testRequiredArgumentsConfiguration(): void
    {
        $articleJob = new TranslateArticleJob($this->mockApiService);
        $tagJob = new TranslateTagJob($this->mockApiService);
        $galleryJob = new TranslateImageGalleryJob($this->mockApiService);

        $reflection = new ReflectionMethod(TranslateArticleJob::class, 'getRequiredArguments');
        $reflection->setAccessible(true);
        $this->assertEquals(['id', 'title'], $reflection->invoke($articleJob));

        $reflection = new ReflectionMethod(TranslateTagJob::class, 'getRequiredArguments');
        $reflection->setAccessible(true);
        $this->assertEquals(['id', 'title'], $reflection->invoke($tagJob));

        $reflection = new ReflectionMethod(TranslateImageGalleryJob::class, 'getRequiredArguments');
        $reflection->setAccessible(true);
        $this->assertEquals(['id', 'name'], $reflection->invoke($galleryJob));
    }

    /**
     * Test display name argument configuration
     */
    public function testDisplayNameArgumentConfiguration(): void
    {
        $articleJob = new TranslateArticleJob($this->mockApiService);
        $tagJob = new TranslateTagJob($this->mockApiService);
        $galleryJob = new TranslateImageGalleryJob($this->mockApiService);

        $reflection = new ReflectionMethod(TranslateArticleJob::class, 'getDisplayNameArgument');
        $reflection->setAccessible(true);
        $this->assertEquals('title', $reflection->invoke($articleJob));

        $reflection = new ReflectionMethod(TranslateTagJob::class, 'getDisplayNameArgument');
        $reflection->setAccessible(true);
        $this->assertEquals('title', $reflection->invoke($tagJob));

        $reflection = new ReflectionMethod(TranslateImageGalleryJob::class, 'getDisplayNameArgument');
        $reflection->setAccessible(true);
        $this->assertEquals('name', $reflection->invoke($galleryJob));
    }

    /**
     * Test entity type name configuration
     */
    public function testEntityTypeNameConfiguration(): void
    {
        $articleJob = new TranslateArticleJob($this->mockApiService);
        $tagJob = new TranslateTagJob($this->mockApiService);
        $galleryJob = new TranslateImageGalleryJob($this->mockApiService);

        $reflection = new ReflectionMethod(TranslateArticleJob::class, 'getEntityTypeName');
        $reflection->setAccessible(true);
        $this->assertEquals('Article', $reflection->invoke($articleJob));

        $reflection = new ReflectionMethod(TranslateTagJob::class, 'getEntityTypeName');
        $reflection->setAccessible(true);
        $this->assertEquals('Tag', $reflection->invoke($tagJob));

        $reflection = new ReflectionMethod(TranslateImageGalleryJob::class, 'getEntityTypeName');
        $reflection->setAccessible(true);
        $this->assertEquals('Gallery', $reflection->invoke($galleryJob));
    }

    /**
     * Test dependency injection works correctly
     */
    public function testDependencyInjection(): void
    {
        // Test with provided service
        $job = new TranslateArticleJob($this->mockApiService);
        $reflection = new ReflectionClass($job);
        $property = $reflection->getProperty('apiService');
        $property->setAccessible(true);

        $this->assertSame($this->mockApiService, $property->getValue($job));
    }
}
