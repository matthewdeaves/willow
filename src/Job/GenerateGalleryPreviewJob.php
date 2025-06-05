<?php
declare(strict_types=1);

namespace App\Job;

use App\Model\Entity\Image;
use App\Model\Entity\ImageGallery;
use Cake\Datasource\FactoryLocator;
use Cake\Log\LogTrait;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Imagick;
use ImagickDraw;
use ImagickPixel;
use Interop\Queue\Processor;

/**
 * GenerateGalleryPreviewJob Class
 *
 * This class generates preview montage images for image galleries using ImageMagick.
 * It creates a 2x3 grid layout showing up to 6 images from the gallery with
 * professional spacing and styling.
 */
class GenerateGalleryPreviewJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts to process the job
     *
     * @var int|null
     */
    public static int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time
     *
     * @var bool
     */
    public static bool $shouldBeUnique = false;

    /**
     * Preview image dimensions
     */
    private const PREVIEW_WIDTH = 400;
    private const PREVIEW_HEIGHT = 300;
    private const THUMB_WIDTH = 120;
    private const THUMB_HEIGHT = 90;
    private const GRID_COLS = 3;
    private const GRID_ROWS = 2;
    private const SPACING = 8;

    /**
     * Executes the gallery preview generation task.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the gallery ID
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on error
     */
    public function execute(Message $message): ?string
    {
        if (!extension_loaded('imagick')) {
            $this->log(
                'Imagick extension is not loaded',
                'error',
                ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
            );

            return Processor::REJECT;
        }

        $galleryId = $message->getArgument('gallery_id');
        if (!$galleryId) {
            $this->log(
                'No gallery_id provided in message',
                'error',
                ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
            );

            return Processor::REJECT;
        }

        $this->log(
            sprintf('Starting gallery preview generation for gallery ID: %s', $galleryId),
            'info',
            ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
        );

        try {
            // Get gallery and images
            $galleriesTable = FactoryLocator::get('Table')->get('ImageGalleries');
            $gallery = $galleriesTable->get($galleryId, [
                'contain' => [
                    'Images' => function ($q) {
                        return $q->orderBy(['ImageGalleriesImages.position' => 'ASC'])
                                ->limit(6); // Only need first 6 images for preview
                    },
                ],
            ]);

            if (empty($gallery->images)) {
                $this->log(
                    sprintf('Gallery %s has no images, skipping preview generation', $galleryId),
                    'info',
                    ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
                );

                // Clear any existing preview image
                $this->clearExistingPreview($gallery);

                return Processor::ACK;
            }

            // Generate the preview
            $previewPath = $this->generatePreview($gallery);

            // Update gallery with preview filename
            $previewFilename = basename($previewPath);
            $gallery->preview_image = $previewFilename;
            $galleriesTable->save($gallery);

            $this->log(
                sprintf('Successfully generated preview for gallery %s: %s', $galleryId, $previewFilename),
                'info',
                ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
            );

            return Processor::ACK;
        } catch (Exception $e) {
            $this->log(
                sprintf('Error generating preview for gallery %s: %s', $galleryId, $e->getMessage()),
                'error',
                ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
            );

            return Processor::REJECT;
        }
    }

    /**
     * Generate preview montage image
     *
     * @param \App\Model\Entity\ImageGallery $gallery
     * @return string Path to generated preview image
     * @throws \Exception
     */
    private function generatePreview(ImageGallery $gallery): string
    {
        // Ensure preview directory exists
        $previewDir = WWW_ROOT . 'files' . DS . 'ImageGalleries' . DS . 'preview' . DS;
        if (!is_dir($previewDir)) {
            if (!mkdir($previewDir, 0755, true)) {
                throw new Exception("Failed to create preview directory: {$previewDir}");
            }
        }

        // Clear any existing preview
        $this->clearExistingPreview($gallery);

        $previewPath = $previewDir . $gallery->id . '.jpg';
        $images = $gallery->images;
        $imageCount = count($images);

        if ($imageCount === 1) {
            // Single image - just resize it
            $this->createSingleImagePreview($images[0], $previewPath);
        } else {
            // Multiple images - create montage
            $this->createMontagePreview($images, $previewPath);
        }

        return $previewPath;
    }

    /**
     * Create preview for single image
     *
     * @param \App\Model\Entity\Image $image
     * @param string $outputPath
     * @throws \Exception
     */
    private function createSingleImagePreview(Image $image, string $outputPath): void
    {
        $imagePath = WWW_ROOT . 'files' . DS . 'Images' . DS . 'image' . DS . $image->image;

        if (!file_exists($imagePath)) {
            throw new Exception("Source image not found: {$imagePath}");
        }

        $imagick = new Imagick($imagePath);

        // Resize to fit preview dimensions while maintaining aspect ratio
        $imagick->thumbnailImage(self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT, true);

        // Create canvas with white background
        $canvas = new Imagick();
        $canvas->newImage(self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT, new ImagickPixel('white'));
        $canvas->setImageFormat('jpeg');

        // Center the image on canvas
        $imageWidth = $imagick->getImageWidth();
        $imageHeight = $imagick->getImageHeight();
        $x = (self::PREVIEW_WIDTH - $imageWidth) / 2;
        $y = (self::PREVIEW_HEIGHT - $imageHeight) / 2;

        $canvas->compositeImage($imagick, Imagick::COMPOSITE_OVER, intval($x), intval($y));

        // Add subtle border
        $this->addBorder($canvas);

        $canvas->writeImage($outputPath);
        $canvas->clear();
        $imagick->clear();
    }

    /**
     * Create montage preview for multiple images
     *
     * @param array $images
     * @param string $outputPath
     * @throws \Exception
     */
    private function createMontagePreview(array $images, string $outputPath): void
    {
        $montage = new Imagick();
        $processedImages = [];

        // Process each image
        foreach (array_slice($images, 0, 6) as $image) {
            $imagePath = WWW_ROOT . 'files' . DS . 'Images' . DS . 'image' . DS . $image->image;

            if (!file_exists($imagePath)) {
                $this->log(
                    sprintf('Skipping missing image: %s', $imagePath),
                    'warning',
                    ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
                );
                continue;
            }

            try {
                $img = new Imagick($imagePath);
                $img->thumbnailImage(self::THUMB_WIDTH, self::THUMB_HEIGHT, true);
                $img->setImageFormat('jpeg');

                // Add to montage
                $montage->addImage($img);
                $processedImages[] = $img;
            } catch (Exception $e) {
                $this->log(
                    sprintf('Error processing image %s: %s', $imagePath, $e->getMessage()),
                    'warning',
                    ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
                );
            }
        }

        if (empty($processedImages)) {
            throw new Exception('No valid images found for montage');
        }

        // Create the montage
        $montageImage = $montage->montageImage(
            new ImagickDraw(),
            sprintf('%dx%d+%d+%d', self::GRID_COLS, self::GRID_ROWS, self::SPACING, self::SPACING),
            sprintf('%dx%d', self::THUMB_WIDTH, self::THUMB_HEIGHT),
            Imagick::MONTAGEMODE_CONCATENATE,
            '0x0+0+0',
        );

        $montageImage = $montageImage->getImage();

        // Resize to final dimensions
        $montageImage->thumbnailImage(self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT, true);

        // Create final canvas with white background
        $canvas = new Imagick();
        $canvas->newImage(self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT, new ImagickPixel('white'));
        $canvas->setImageFormat('jpeg');

        // Center montage on canvas
        $montageWidth = $montageImage->getImageWidth();
        $montageHeight = $montageImage->getImageHeight();
        $x = (self::PREVIEW_WIDTH - $montageWidth) / 2;
        $y = (self::PREVIEW_HEIGHT - $montageHeight) / 2;

        $canvas->compositeImage($montageImage, Imagick::COMPOSITE_OVER, intval($x), intval($y));

        // Add subtle border
        $this->addBorder($canvas);

        $canvas->writeImage($outputPath);

        // Cleanup
        $canvas->clear();
        $montageImage->clear();
        $montage->clear();
        foreach ($processedImages as $img) {
            $img->clear();
        }
    }

    /**
     * Add subtle border to image
     *
     * @param \Imagick $image
     */
    private function addBorder(Imagick $image): void
    {
        $draw = new ImagickDraw();
        $draw->setStrokeColor(new ImagickPixel('#e0e0e0'));
        $draw->setStrokeWidth(1);
        $draw->setFillOpacity(0);
        $draw->rectangle(0, 0, self::PREVIEW_WIDTH - 1, self::PREVIEW_HEIGHT - 1);
        $image->drawImage($draw);
        $draw->clear();
    }

    /**
     * Clear existing preview image if it exists
     *
     * @param \App\Model\Entity\ImageGallery $gallery
     */
    private function clearExistingPreview(ImageGallery $gallery): void
    {
        if ($gallery->preview_image) {
            $existingPath = WWW_ROOT . 'files' . DS . 'ImageGalleries' . DS . 'preview' . DS . $gallery->preview_image;
            if (file_exists($existingPath)) {
                unlink($existingPath);
            }
        }

        // Also clean up any file with the gallery ID (in case preview_image field is out of sync)
        $previewDir = WWW_ROOT . 'files' . DS . 'ImageGalleries' . DS . 'preview' . DS;
        $galleryPreviewPath = $previewDir . $gallery->id . '.jpg';
        if (file_exists($galleryPreviewPath)) {
            unlink($galleryPreviewPath);
        }
    }
}
