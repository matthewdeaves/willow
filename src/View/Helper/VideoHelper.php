<?php
// src/View/Helper/VideoHelper.php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\ORM\TableRegistry;
use Cake\View\Helper;
use Exception;

class VideoHelper extends Helper
{
    /**
     * List of helpers used by this helper
     *
     * @var array<string>
     */
    protected array $helpers = ['Html'];

    /**
     * Process all content placeholders (videos and galleries)
     *
     * @param string $content The content containing placeholders
     * @return string The processed content
     */
    public function processContentPlaceholders(string $content): string
    {
        // Process YouTube placeholders first
        $content = $this->processYouTubePlaceholders($content);

        // Process gallery placeholders
        $content = $this->processGalleryPlaceholders($content);

        return $content;
    }

    /**
     * Replace YouTube video placeholders with GDPR-compliant embed code
     *
     * @param string $content The content containing video placeholders
     * @return string The processed content
     */
    public function processYouTubePlaceholders(string $content): string
    {
        return preg_replace_callback(
            '/\[youtube:([a-zA-Z0-9_-]+):(\d+):(\d+):([^\]]*)\]/',
            function ($matches) {
                $videoId = $matches[1];
                $width = $matches[2];
                $height = $matches[3];
                $title = $matches[4];

                return $this->generateGdprCompliantEmbed($videoId, $width, $height, $title);
            },
            $content,
        );
    }

    /**
     * Generate GDPR-compliant embed code for YouTube videos
     *
     * @param string $videoId YouTube video ID
     * @param string $width Video width
     * @param string $height Video height
     * @param string $title Video title
     * @return string HTML for the video embed
     */
    protected function generateGdprCompliantEmbed(
        string $videoId,
        string $width,
        string $height,
        string $title,
    ): string {
        $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";

        return $this->getView()->element('site/youtube_embed', [
            'videoId' => $videoId,
            'width' => $width,
            'height' => $height,
            'title' => $title,
            'thumbnailUrl' => $thumbnailUrl,
        ]);
    }

    /**
     * Replace gallery placeholders with rendered gallery content
     *
     * @param string $content The content containing gallery placeholders
     * @return string The processed content
     */
    public function processGalleryPlaceholders(string $content): string
    {
        return preg_replace_callback(
            '/\[gallery:([a-zA-Z0-9_-]+):([^:]*):([^\]]*)\]/',
            function ($matches) {
                $galleryId = $matches[1];
                $theme = $matches[2] ?: 'default';
                $title = $matches[3] ?: '';

                return $this->renderGallery($galleryId, $theme, $title);
            },
            $content,
        );
    }

    /**
     * Render a gallery by ID
     *
     * @param string $galleryId Gallery UUID
     * @param string $theme Gallery display theme
     * @param string $title Gallery title override
     * @return string HTML for the gallery
     */
    protected function renderGallery(string $galleryId, string $theme = 'default', string $title = ''): string
    {
        try {
            // Get the ImageGalleries table
            $galleriesTable = TableRegistry::getTableLocator()->get('ImageGalleries');

            // Find the gallery with its images
            $gallery = $galleriesTable->find()
                ->select([
                    'ImageGalleries.id',
                    'ImageGalleries.name',
                    'ImageGalleries.slug',
                    'ImageGalleries.description',
                    'ImageGalleries.is_published',
                ])
                ->contain([
                    'Images' => function ($query) {
                        return $query->select([
                            'Images.id',
                            'Images.name',
                            'Images.image',
                            'Images.dir',
                            'Images.alt_text',
                            'Images.size',
                            'Images.mime',
                        ])
                        ->where([
                            'Images.image IS NOT' => null,
                            'Images.image !=' => '',
                        ])
                        ->orderBy(['ImageGalleriesImages.position' => 'ASC']);
                    },
                ])
                ->where([
                    'ImageGalleries.id' => $galleryId,
                    'ImageGalleries.is_published' => true,
                ])
                ->first();

            if (!$gallery || empty($gallery->images)) {
                return $this->renderGalleryNotFound($galleryId);
            }

            // Use title override if provided, otherwise use gallery name
            $displayTitle = $title ?: $gallery->name;

            // Render the gallery using the photo_gallery element
            return $this->getView()->element('photo_gallery', [
                'images' => $gallery->images,
                'title' => $displayTitle,
                'theme' => $theme,
                'showActions' => false, // No admin actions on frontend
                'galleryId' => null, // No admin links on frontend
            ]);
        } catch (Exception $e) {
            // Log the error and return a graceful fallback
            $this->getView()->log("Error rendering gallery {$galleryId}: " . $e->getMessage(), 'error');

            return $this->renderGalleryError($galleryId, $e->getMessage());
        }
    }

    /**
     * Render a message when gallery is not found
     *
     * @param string $galleryId Gallery ID
     * @return string HTML for not found message
     */
    protected function renderGalleryNotFound(string $galleryId): string
    {
        return '<div class="alert alert-warning gallery-not-found" role="alert">' .
               '<i class="fas fa-exclamation-triangle me-2"></i>' .
               __('Gallery not found or not published.') .
               '</div>';
    }

    /**
     * Render a message when gallery has an error
     *
     * @param string $galleryId Gallery ID
     * @param string $error Error message
     * @return string HTML for error message
     */
    protected function renderGalleryError(string $galleryId, string $error): string
    {
        return '<div class="alert alert-danger gallery-error" role="alert">' .
               '<i class="fas fa-exclamation-circle me-2"></i>' .
               __('Error loading gallery.') .
               '</div>';
    }
}
