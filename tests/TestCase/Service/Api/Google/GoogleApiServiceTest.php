<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api\Google;

use App\Service\Api\Google\GoogleApiService;
use Cake\TestSuite\TestCase;
use ReflectionClass;

/**
 * GoogleApiService Test Case
 *
 * Tests for Google Translate API service functionality
 */
class GoogleApiServiceTest extends TestCase
{
    protected GoogleApiService $googleApiService;

    public function setUp(): void
    {
        parent::setUp();
        // Skip actual API calls for tests
        $this->googleApiService = $this->getMockBuilder(GoogleApiService::class)
            ->onlyMethods(['__construct'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test that gallery placeholders are properly preserved during preprocessing
     */
    public function testPreprocessContentPreservesGalleryPlaceholders(): void
    {
        $content = 'Here is some text with a gallery: [gallery:123e4567-e89b-12d3-a456-426614174000:grid:My Gallery Title] and more text.';

        // Use reflection to access private method
        $reflection = new ReflectionClass(GoogleApiService::class);
        $method = $reflection->getMethod('preprocessContent');
        $method->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        $result = $method->invoke($this->googleApiService, $content);

        // Check that the gallery placeholder was replaced with a preservation placeholder
        $this->assertStringContainsString('<!--PRESERVED_BLOCK_', $result);
        $this->assertStringNotContainsString('[gallery:', $result);
        $this->assertStringContainsString('Here is some text with a gallery:', $result);
        $this->assertStringContainsString('and more text.', $result);

        // Check that the original placeholder was stored
        $preservedBlocks = $preservedBlocksProperty->getValue($this->googleApiService);
        $this->assertNotEmpty($preservedBlocks);
        $this->assertStringContainsString('[gallery:123e4567-e89b-12d3-a456-426614174000:grid:My Gallery Title]', implode('', $preservedBlocks));
    }

    /**
     * Test that postprocessing restores gallery placeholders
     */
    public function testPostprocessContentRestoresGalleryPlaceholders(): void
    {
        $originalContent = '[gallery:123e4567-e89b-12d3-a456-426614174000:grid:My Gallery Title]';

        // Use reflection to access private methods and properties
        $reflection = new ReflectionClass(GoogleApiService::class);

        $preprocessMethod = $reflection->getMethod('preprocessContent');
        $preprocessMethod->setAccessible(true);

        $postprocessMethod = $reflection->getMethod('postprocessContent');
        $postprocessMethod->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        // Preprocess to create placeholders
        $preprocessedContent = $preprocessMethod->invoke($this->googleApiService, $originalContent);

        // Postprocess to restore original content
        $restoredContent = $postprocessMethod->invoke($this->googleApiService, $preprocessedContent);

        // Original content should be fully restored
        $this->assertEquals($originalContent, $restoredContent);
    }

    /**
     * Test that code blocks and YouTube placeholders are still preserved
     */
    public function testPreprocessContentPreservesExistingPatterns(): void
    {
        $codeBlock = "```php\necho \"hello\";\n```";
        $content = "Text with code {$codeBlock} and YouTube [youtube:dQw4w9WgXcQ:1:2:test] and gallery [gallery:123e4567-e89b-12d3-a456-426614174000:grid:Test].";

        // Use reflection to access private method
        $reflection = new ReflectionClass(GoogleApiService::class);
        $method = $reflection->getMethod('preprocessContent');
        $method->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        $result = $method->invoke($this->googleApiService, $content);

        // Check that placeholders were created (at least for gallery and youtube)
        $this->assertStringNotContainsString('[youtube:', $result);
        $this->assertStringNotContainsString('[gallery:', $result);
        $this->assertGreaterThanOrEqual(2, substr_count($result, '<!--PRESERVED_BLOCK_'));

        // Check that preserved content includes our patterns
        $preservedBlocks = $preservedBlocksProperty->getValue($this->googleApiService);
        $allPreservedContent = implode('', $preservedBlocks);
        $this->assertStringContainsString('[youtube:dQw4w9WgXcQ:1:2:test]', $allPreservedContent);
        $this->assertStringContainsString('[gallery:123e4567-e89b-12d3-a456-426614174000:grid:Test]', $allPreservedContent);
    }

    /**
     * Test edge cases with malformed gallery placeholders
     */
    public function testPreprocessContentWithMalformedPlaceholders(): void
    {
        // These should NOT be matched and preserved
        $content = 'Text with [gallery:invalid] and [gallery:123:] and [gallery] and normal text.';

        // Use reflection to access private method
        $reflection = new ReflectionClass(GoogleApiService::class);
        $method = $reflection->getMethod('preprocessContent');
        $method->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        $result = $method->invoke($this->googleApiService, $content);

        // Malformed placeholders should remain unchanged
        $this->assertStringContainsString('[gallery:invalid]', $result);
        $this->assertStringContainsString('[gallery:123:]', $result);
        $this->assertStringContainsString('[gallery]', $result);
        $this->assertStringNotContainsString('<!--PRESERVED_BLOCK_', $result);

        // No blocks should be preserved
        $preservedBlocks = $preservedBlocksProperty->getValue($this->googleApiService);
        $this->assertEmpty($preservedBlocks);
    }

    /**
     * Test that pre and code HTML tags are preserved during preprocessing
     */
    public function testPreprocessContentPreservesHtmlCodeTags(): void
    {
        $content = 'Some text <pre class="highlight">$foo = "bar";</pre> and <code>inline code</code> more text.';

        $reflection = new ReflectionClass(GoogleApiService::class);
        $method = $reflection->getMethod('preprocessContent');
        $method->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        $result = $method->invoke($this->googleApiService, $content);

        // Check that HTML code tags were replaced with placeholders
        $this->assertStringNotContainsString('<pre', $result);
        $this->assertStringNotContainsString('<code>', $result);
        $this->assertStringContainsString('<!--PRESERVED_BLOCK_', $result);

        // Verify the original content was stored
        $preservedBlocks = $preservedBlocksProperty->getValue($this->googleApiService);
        $allPreservedContent = implode('', $preservedBlocks);
        $this->assertStringContainsString('<pre class="highlight">$foo = "bar";</pre>', $allPreservedContent);
        $this->assertStringContainsString('<code>inline code</code>', $allPreservedContent);
    }

    /**
     * Test that multiple gallery placeholders are all preserved
     */
    public function testPreprocessContentPreservesMultipleGalleries(): void
    {
        $content = 'Gallery 1: [gallery:11111111-1111-1111-1111-111111111111:grid:First] text ' .
            '[gallery:22222222-2222-2222-2222-222222222222:carousel:Second] more text ' .
            '[gallery:33333333-3333-3333-3333-333333333333:list:Third]';

        $reflection = new ReflectionClass(GoogleApiService::class);
        $method = $reflection->getMethod('preprocessContent');
        $method->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        $result = $method->invoke($this->googleApiService, $content);

        // Should have 3 preserved blocks
        $preservedBlocks = $preservedBlocksProperty->getValue($this->googleApiService);
        $this->assertCount(3, $preservedBlocks);

        // All galleries should be removed from result
        $this->assertStringNotContainsString('[gallery:', $result);

        // All galleries should be in preserved blocks
        $allPreservedContent = implode('', $preservedBlocks);
        $this->assertStringContainsString('11111111-1111-1111-1111-111111111111', $allPreservedContent);
        $this->assertStringContainsString('22222222-2222-2222-2222-222222222222', $allPreservedContent);
        $this->assertStringContainsString('33333333-3333-3333-3333-333333333333', $allPreservedContent);
    }

    /**
     * Test preprocess and postprocess roundtrip with complex content
     */
    public function testPreprocessPostprocessRoundtrip(): void
    {
        $originalContent = <<<'HTML'
<h1>Article Title</h1>
<p>Here is some content with a code block:</p>
<pre><code class="php">
echo "Hello World";
$x = 1 + 2;
</code></pre>
<p>And here is a gallery:</p>
[gallery:550e8400-e29b-41d4-a716-446655440000:grid:My Photos]
<p>And a YouTube video:</p>
[youtube:dQw4w9WgXcQ:640:480:Rick Roll]
<p>The end.</p>
HTML;

        $reflection = new ReflectionClass(GoogleApiService::class);
        $preprocessMethod = $reflection->getMethod('preprocessContent');
        $preprocessMethod->setAccessible(true);
        $postprocessMethod = $reflection->getMethod('postprocessContent');
        $postprocessMethod->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        // Preprocess
        $preprocessed = $preprocessMethod->invoke($this->googleApiService, $originalContent);

        // Postprocess
        $restored = $postprocessMethod->invoke($this->googleApiService, $preprocessed);

        // Content should be fully restored
        $this->assertEquals($originalContent, $restored);
    }

    /**
     * Test preprocessing with empty content
     */
    public function testPreprocessContentWithEmptyString(): void
    {
        $reflection = new ReflectionClass(GoogleApiService::class);
        $method = $reflection->getMethod('preprocessContent');
        $method->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        $result = $method->invoke($this->googleApiService, '');

        $this->assertEquals('', $result);
        $preservedBlocks = $preservedBlocksProperty->getValue($this->googleApiService);
        $this->assertEmpty($preservedBlocks);
    }

    /**
     * Test preprocessing with content that has no special blocks
     */
    public function testPreprocessContentWithNoSpecialBlocks(): void
    {
        $content = '<p>This is just regular HTML content with <strong>bold</strong> text.</p>';

        $reflection = new ReflectionClass(GoogleApiService::class);
        $method = $reflection->getMethod('preprocessContent');
        $method->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        $result = $method->invoke($this->googleApiService, $content);

        // Content should remain unchanged
        $this->assertEquals($content, $result);
        $preservedBlocks = $preservedBlocksProperty->getValue($this->googleApiService);
        $this->assertEmpty($preservedBlocks);
    }

    /**
     * Test markdown code blocks are preserved
     */
    public function testPreprocessContentPreservesMarkdownCodeBlocks(): void
    {
        $content = "Some text\n```javascript\nfunction hello() {\n  return 'world';\n}\n```\nMore text";

        $reflection = new ReflectionClass(GoogleApiService::class);
        $method = $reflection->getMethod('preprocessContent');
        $method->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        $result = $method->invoke($this->googleApiService, $content);

        // Check that markdown code block was replaced
        $this->assertStringNotContainsString('```javascript', $result);
        $this->assertStringContainsString('<!--PRESERVED_BLOCK_', $result);
        $this->assertStringContainsString('Some text', $result);
        $this->assertStringContainsString('More text', $result);

        // Verify the code block was stored
        $preservedBlocks = $preservedBlocksProperty->getValue($this->googleApiService);
        $allPreservedContent = implode('', $preservedBlocks);
        $this->assertStringContainsString('function hello()', $allPreservedContent);
    }
}
