<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\ContentProcessorService;
use Cake\TestSuite\TestCase;

/**
 * ContentProcessorService Test Case
 *
 * Phase 5: Comprehensive test cases for content rendering improvements
 */
class ContentProcessorServiceTest extends TestCase
{
    protected ContentProcessorService $contentProcessor;

    public function setUp(): void
    {
        parent::setUp();
        $this->contentProcessor = new ContentProcessorService();
    }

    /**
     * Test YouTube placeholder processing
     */
    public function testProcessYouTubePlaceholders(): void
    {
        $content = 'Check out this video: [youtube:dQw4w9WgXcQ] It\'s awesome!';
        $result = $this->contentProcessor->processYouTubePlaceholders($content);

        $this->assertStringContainsString('youtube-embed', $result);
        $this->assertStringContainsString('dQw4w9WgXcQ', $result);
        $this->assertStringContainsString('Check out this video:', $result);
        $this->assertStringContainsString('It\'s awesome!', $result);
    }

    /**
     * Test gallery placeholder processing
     */
    public function testProcessGalleryPlaceholders(): void
    {
        $content = 'Here is a gallery: [gallery:123e4567-e89b-12d3-a456-426614174000:grid:My Gallery] End text.';
        $result = $this->contentProcessor->processGalleryPlaceholders($content);

        // Should attempt to process gallery (will show error/not found since no DB)
        $this->assertStringContainsString('Here is a gallery:', $result);
        $this->assertStringContainsString('End text.', $result);
    }

    /**
     * Test content alignment enhancement
     */
    public function testEnhanceContentAlignment(): void
    {
        $content = '<p style="text-align: center;">Centered text</p><p style="text-align:left;">Left text</p>';
        $result = $this->contentProcessor->enhanceContentAlignment($content);

        $this->assertStringContainsString('content-align-center', $result);
        $this->assertStringContainsString('content-align-left', $result);
    }

    /**
     * Test responsive image processing
     */
    public function testProcessResponsiveImages(): void
    {
        $content = '<img src="test.jpg" alt="Test"> <img src="test2.jpg" class="existing" alt="Test2">';
        $result = $this->contentProcessor->processResponsiveImages($content);

        $this->assertStringContainsString('loading="lazy"', $result);
        // Updated: img-responsive class is no longer automatically added
        $this->assertStringNotContainsString('img-responsive', $result);
    }

    /**
     * Test comprehensive content processing
     */
    public function testProcessContent(): void
    {
        $content = 'Text before [youtube:dQw4w9WgXcQ] and [gallery:123e4567-e89b-12d3-a456-426614174000:grid:Test] with <img src="test.jpg" alt="Test">';
        $result = $this->contentProcessor->processContent($content);

        $this->assertStringContainsString('Text before', $result);
        $this->assertStringContainsString('youtube-embed', $result);
        // Note: YouTube thumbnail will have loading="lazy" but article images won't be processed by default
        $this->assertStringContainsString('loading="lazy"', $result); // From YouTube thumbnail
    }

    /**
     * Test content processing with responsive images enabled
     */
    public function testProcessContentWithResponsiveImages(): void
    {
        $content = 'Text with <img src="test.jpg" alt="Test">';
        $result = $this->contentProcessor->processContent($content, ['processResponsiveImages' => true]);

        $this->assertStringContainsString('loading="lazy"', $result);
    }

    /**
     * Test Markdown alignment syntax processing
     */
    public function testProcessMarkdownAlignmentSyntax(): void
    {
        $markdown = "Normal text\n->Centered text<-\n->Right aligned\n<-Justified text->";
        $result = $this->contentProcessor->processMarkdown($markdown);

        $this->assertStringContainsString('text-align: center', $result);
        $this->assertStringContainsString('text-align: right', $result);
        $this->assertStringContainsString('text-align: justify', $result);
        $this->assertStringContainsString('Normal text', $result);
    }

    /**
     * Test edge cases and error handling
     */
    public function testEdgeCases(): void
    {
        // Empty content
        $result = $this->contentProcessor->processContent('');
        $this->assertEquals('', $result);

        // Invalid YouTube ID with special characters - won't match regex, should remain unchanged
        $content = '[youtube:invalid-id-!!!]';
        $result = $this->contentProcessor->processYouTubePlaceholders($content);
        $this->assertEquals($content, $result); // Should remain unchanged

        // Invalid gallery ID
        $content = '[gallery:invalid:theme:title]';
        $result = $this->contentProcessor->processGalleryPlaceholders($content);
        $this->assertStringContainsString('invalid', $result);
    }

    /**
     * Test XSS protection
     */
    public function testXssProtection(): void
    {
        $maliciousContent = '[youtube:<script>alert("xss")</script>]';
        $result = $this->contentProcessor->processYouTubePlaceholders($maliciousContent);

        // The regex won't match this pattern, so it should remain unchanged
        $this->assertEquals($maliciousContent, $result);

        // Test with a valid YouTube ID that gets processed
        $validContent = '[youtube:dQw4w9WgXcQ]';
        $validResult = $this->contentProcessor->processYouTubePlaceholders($validContent);
        $this->assertStringContainsString('youtube-embed', $validResult);
        $this->assertStringContainsString('dQw4w9WgXcQ', $validResult);
    }

    /**
     * Test multiple placeholders in same content
     */
    public function testMultiplePlaceholders(): void
    {
        $content = 'Video 1: [youtube:abc123] Gallery: [gallery:123e4567-e89b-12d3-a456-426614174000:grid:Test] Video 2: [youtube:def456]';
        $result = $this->contentProcessor->processContent($content);

        $this->assertEquals(2, substr_count($result, 'youtube-embed'));
        $this->assertStringContainsString('abc123', $result);
        $this->assertStringContainsString('def456', $result);
    }

    /**
     * Test Markdown with images and alignment
     */
    public function testMarkdownImageAlignment(): void
    {
        $markdown = "->![Test Image](test.jpg)<-\nNormal text\n->Right aligned image ![Test](test2.jpg)";
        $result = $this->contentProcessor->processMarkdown($markdown);

        $this->assertStringContainsString('text-align: center', $result);
        $this->assertStringContainsString('text-align: right', $result);
        $this->assertStringContainsString('![Test Image](test.jpg)', $result);
    }

    /**
     * Test content processing performance with large content
     */
    public function testPerformanceWithLargeContent(): void
    {
        $largeContent = str_repeat('Lorem ipsum dolor sit amet. [youtube:test123] ', 1000);

        $startTime = microtime(true);
        $result = $this->contentProcessor->processContent($largeContent);
        $endTime = microtime(true);

        $processingTime = $endTime - $startTime;

        // Should process in reasonable time (less than 1 second)
        $this->assertLessThan(1.0, $processingTime);
        $this->assertEquals(1000, substr_count($result, 'youtube-embed'));
    }
}
