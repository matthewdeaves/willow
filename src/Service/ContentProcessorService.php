<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * Content Processor Service
 * 
 * Handles processing of article content including:
 * - YouTube video placeholder replacement
 * - Image gallery placeholder replacement  
 * - Content sanitization and enhancement
 * - Markdown and HTML content processing
 */
class ContentProcessorService
{
    /**
     * Process content by replacing placeholders and enhancing formatting
     * 
     * @param string $content Raw content with placeholders
     * @param array $options Processing options
     * @return string Processed content
     */
    public function processContent(string $content, array $options = []): string
    {
        // Phase 2: Enhanced content processing pipeline
        $content = $this->processYouTubePlaceholders($content, $options);
        $content = $this->processGalleryPlaceholders($content, $options);
        $content = $this->enhanceContentAlignment($content, $options);
        
        // Only process responsive images if explicitly requested to avoid breaking existing functionality
        if ($options['processResponsiveImages'] ?? false) {
            $content = $this->processResponsiveImages($content, $options);
        }
        
        return $content;
    }

    /**
     * Process YouTube video placeholders in content
     * 
     * @param string $content Content with YouTube placeholders
     * @param array $options Processing options
     * @return string Content with YouTube embeds
     */
    public function processYouTubePlaceholders(string $content, array $options = []): string
    {
        return preg_replace_callback(
            '/\[youtube:([a-zA-Z0-9_-]+)\]/',
            function ($matches) use ($options) {
                $videoId = $matches[1];
                $consentRequired = Configure::read('Youtube.requireConsent', true);
                
                if ($consentRequired) {
                    return $this->generateConsentRequiredYouTubeEmbed($videoId, $options);
                } else {
                    return $this->generateDirectYouTubeEmbed($videoId, $options);
                }
            },
            $content
        );
    }

    /**
     * Process image gallery placeholders in content
     * 
     * @param string $content Content with gallery placeholders
     * @param array $options Processing options
     * @return string Content with gallery HTML
     */
    public function processGalleryPlaceholders(string $content, array $options = []): string
    {
        return preg_replace_callback(
            '/\[gallery:([a-f0-9-]+):([^:]*):([^\]]*)\]/',
            function ($matches) use ($options) {
                $galleryId = $matches[1];
                $theme = $matches[2] ?: 'grid';
                $title = $matches[3] ?: '';
                
                return $this->generateGalleryHtml($galleryId, $theme, $title, $options);
            },
            $content
        );
    }

    /**
     * Enhance content alignment by adding CSS classes and data attributes
     * 
     * @param string $content HTML content
     * @param array $options Processing options
     * @return string Enhanced content
     */
    public function enhanceContentAlignment(string $content, array $options = []): string
    {
        // Add alignment helper classes to paragraphs with inline styles
        $content = preg_replace(
            '/<p([^>]*style[^>]*text-align:\s*(center|left|right|justify)[^>]*)>/i',
            '<p$1 class="content-align-$2">',
            $content
        );

        // Add responsive image classes
        $content = preg_replace(
            '/<img([^>]*class="[^"]*")([^>]*)>/i',
            '<img$1 content-image"$2>',
            $content
        );

        return $content;
    }

    /**
     * Process images to make them responsive and add proper styling
     * 
     * @param string $content HTML content with images
     * @param array $options Processing options
     * @return string Content with responsive images
     */
    public function processResponsiveImages(string $content, array $options = []): string
    {
        // Only add loading="lazy" and avoid adding classes that might break existing functionality
        $content = preg_replace_callback(
            '/<img([^>]*)>/i',
            function ($matches) use ($options) {
                $imgTag = $matches[0];
                $attributes = $matches[1];
                
                // Add loading="lazy" if not present
                if (!preg_match('/loading\s*=/i', $attributes)) {
                    $attributes .= ' loading="lazy"';
                }
                
                // Don't add img-responsive class automatically - it was breaking galleries
                
                return '<img' . $attributes . '>';
            },
            $content
        );

        return $content;
    }

    /**
     * Generate consent-required YouTube embed HTML
     * 
     * @param string $videoId YouTube video ID
     * @param array $options Embed options
     * @return string YouTube embed HTML
     */
    private function generateConsentRequiredYouTubeEmbed(string $videoId, array $options = []): string
    {
        $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
        $watchUrl = "https://www.youtube.com/watch?v={$videoId}";
        
        return sprintf(
            '<div class="youtube-embed" data-video-id="%s">
                <div class="youtube-placeholder">
                    <img src="%s" alt="YouTube Video Thumbnail" loading="lazy">
                    <div class="youtube-consent-overlay">
                        <h3>YouTube Video</h3>
                        <p>This video is hosted by YouTube. By playing it, you accept YouTube\'s privacy policy.</p>
                        <button class="btn btn-primary youtube-consent-btn" data-video-id="%s">Play Video</button>
                        <br><br>
                        <a href="%s" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm">Watch on YouTube</a>
                    </div>
                </div>
            </div>',
            htmlspecialchars($videoId),
            htmlspecialchars($thumbnailUrl),
            htmlspecialchars($videoId),
            htmlspecialchars($watchUrl)
        );
    }

    /**
     * Generate direct YouTube embed HTML
     * 
     * @param string $videoId YouTube video ID
     * @param array $options Embed options
     * @return string YouTube embed HTML
     */
    private function generateDirectYouTubeEmbed(string $videoId, array $options = []): string
    {
        return sprintf(
            '<div class="youtube-embed">
                <div class="youtube-player-container">
                    <iframe src="https://www.youtube.com/embed/%s" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen
                            loading="lazy">
                    </iframe>
                </div>
            </div>',
            htmlspecialchars($videoId)
        );
    }

    /**
     * Generate image gallery HTML
     * 
     * @param string $galleryId Gallery UUID
     * @param string $theme Gallery theme
     * @param string $title Gallery title
     * @param array $options Generation options
     * @return string Gallery HTML
     */
    private function generateGalleryHtml(string $galleryId, string $theme, string $title, array $options = []): string
    {
        try {
            $galleriesTable = TableRegistry::getTableLocator()->get('ImageGalleries');
            $gallery = $galleriesTable->find()
                ->where(['id' => $galleryId])
                ->contain(['Images'])
                ->first();

            if (!$gallery || empty($gallery->images)) {
                return "<!-- Gallery not found or empty: {$galleryId} -->";
            }

            $galleryHtml = '<div class="photo-gallery" data-gallery-id="' . htmlspecialchars($galleryId) . '">';
            
            if (!empty($title)) {
                $galleryHtml .= '<h3 class="gallery-title">' . htmlspecialchars($title) . '</h3>';
            }

            $galleryHtml .= '<div class="gallery-grid">';
            
            foreach ($gallery->images as $image) {
                $largeUrl = $image->largeImageUrl ?: $image->getImageUrlBySize('large');
                $mediumUrl = $image->mediumImageUrl ?: $image->getImageUrlBySize('medium');
                $altText = htmlspecialchars($image->alt_text ?: $image->name);
                
                $galleryHtml .= sprintf(
                    '<div class="gallery-item">
                        <a href="%s" data-pswp-width="800" data-pswp-height="600">
                            <img src="%s" alt="%s" loading="lazy" class="gallery-image" />
                        </a>
                    </div>',
                    htmlspecialchars($largeUrl),
                    htmlspecialchars($mediumUrl),
                    $altText
                );
            }
            
            $galleryHtml .= '</div></div>';
            
            return $galleryHtml;
            
        } catch (\Exception $e) {
            return "<!-- Error loading gallery {$galleryId}: " . htmlspecialchars($e->getMessage()) . " -->";
        }
    }

    /**
     * Process Markdown content with enhanced alignment support
     * 
     * @param string $markdown Markdown content
     * @param array $options Processing options
     * @return string Processed HTML
     */
    public function processMarkdown(string $markdown, array $options = []): string
    {
        // Enhanced Markdown processing with alignment helpers
        $markdown = $this->processMarkdownAlignmentSyntax($markdown, $options);
        
        // Process placeholders before Markdown conversion
        $markdown = $this->processYouTubePlaceholders($markdown, $options);
        $markdown = $this->processGalleryPlaceholders($markdown, $options);
        
        return $markdown;
    }

    /**
     * Process custom Markdown alignment syntax
     * 
     * @param string $markdown Markdown content
     * @param array $options Processing options
     * @return string Enhanced Markdown
     */
    private function processMarkdownAlignmentSyntax(string $markdown, array $options = []): string
    {
        // Convert alignment markers to HTML
        $alignmentPatterns = [
            '/^->(.+)<-$/m' => '<p style="text-align: center;">$1</p>',
            '/^->(.+)$/m' => '<p style="text-align: right;">$1</p>',
            '/^<-(.+)->$/m' => '<p style="text-align: justify;">$1</p>',
        ];
        
        foreach ($alignmentPatterns as $pattern => $replacement) {
            $markdown = preg_replace($pattern, $replacement, $markdown);
        }
        
        return $markdown;
    }
}