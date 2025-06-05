<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;
use Exception;

/**
 * Gallery Helper
 *
 * Handles processing of gallery placeholders in article content.
 * For admin UI components, see AdminGalleryHelper.
 */
class GalleryHelper extends Helper
{
    /**
     * Process gallery placeholders in content
     *
     * @param string $content The content containing gallery placeholders
     * @return string The processed content with rendered galleries
     */
    public function processGalleryPlaceholders(string $content): string
    {
        return preg_replace_callback(
            '/\[gallery:([a-f0-9-]+):([^:]*):([^\]]*)\]/',
            function ($matches) {
                $galleryId = $matches[1];
                $theme = $matches[2] ?: 'default';
                $title = $matches[3] ?: '';

                return $this->renderGalleryFromPlaceholder($galleryId, $theme, $title);
            },
            $content,
        );
    }

    /**
     * Render a gallery from placeholder with proper translations
     *
     * @param string $galleryId Gallery UUID
     * @param string $theme Gallery display theme
     * @param string $title Gallery title override
     * @return string HTML for the gallery
     */
    private function renderGalleryFromPlaceholder(string $galleryId, string $theme = 'default', string $title = ''): string
    {
        try {
            // Get the ImageGalleries table
            $galleriesTable = TableRegistry::getTableLocator()->get('ImageGalleries');
            
            // Set locale for translations - this is crucial for TranslateBehavior
            $currentLocale = I18n::getLocale();
            $galleriesTable->setLocale($currentLocale);

            // Find the gallery with its images - avoid select() as it interferes with TranslateBehavior
            $gallery = $galleriesTable->find()
                ->contain([
                    'Images' => function ($query) {
                        return $query->where([
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
                // Return empty string to silently skip unpublished/missing galleries on frontend
                return '';
            }

            // Use title override if provided, otherwise use translated gallery name
            $displayTitle = $title ?: $gallery->name;

            // Render the gallery using the shared_photo_gallery element
            return $this->getView()->element('shared_photo_gallery', [
                'images' => $gallery->images,
                'title' => $displayTitle,
                'theme' => $theme,
                'showActions' => false, // No admin actions on frontend
                'galleryId' => null, // No admin links on frontend
            ]);
        } catch (Exception $e) {
            // Log the error and return a graceful fallback
            error_log("GalleryHelper: Error rendering gallery {$galleryId}: " . $e->getMessage());

            return '';
        }
    }
}