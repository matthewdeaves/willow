<?php
declare(strict_types=1);

namespace App\Job;

use Cake\Core\Configure;
use Cake\Log\LogTrait;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Imagick;
use Interop\Queue\Processor;

/**
 * ProcessImageJob Class
 *
 * This class is responsible for processing image resizing as a background job.
 * It receives image paths and resize specifications, processes the images using Imagick,
 * and logs the process.
 */
class ProcessImageJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts to process the job
     *
     * @var int|null
     */
    public static ?int $maxAttempts = 3;

    /**
     * Flag to indicate if the job should be unique
     *
     * @var bool
     */
    public static bool $shouldBeUnique = false;

    /**
     * Executes the image processing job
     *
     * This method processes the job message, validates the input, and resizes the image.
     * It logs various stages of the process and handles exceptions.
     *
     * @param \Cake\Queue\Job\Message $message The job message containing image processing details
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    public function execute(Message $message): ?string
    {
        $args = $message->getArgument('args');
        $this->log(
            __('Received image processing message: {0}', [json_encode($args)]),
            'debug',
            ['group_name' => 'image_processing']
        );

        if (!is_array($args) || !isset($args[0]) || !is_array($args[0])) {
            $this->log(
                __('Invalid argument structure for image processing job. Expected array, got: {0}', [gettype($args)]),
                'error',
                ['group_name' => 'image_processing']
            );

            return Processor::REJECT;
        }

        $payload = $args[0];

        $imagePath = $payload['path'] ?? null;
        $imageSizes = Configure::read('SiteSettings.ImageSizes');

        if (!$imagePath || empty($imageSizes)) {
            $this->log(
                __(
                    'Missing required fields in image processing payload. Path: {0}, Sizes: {1}',
                    [$imagePath, json_encode($imageSizes)]
                ),
                'error',
                ['group_name' => 'image_processing']
            );

            return Processor::REJECT;
        }

        $this->log(
            __(
                'Starting image processing job. Path: {0}, Sizes to process: {1}',
                [$imagePath, implode(', ', $imageSizes)]
            ),
            'info',
            ['group_name' => 'image_processing']
        );

        try {
            foreach ($imageSizes as $width) {
                $this->createImage($imagePath, $width);
            }
        } catch (Exception $e) {
            $this->log(
                __(
                    'Error during image processing. Path: {0}, Error: {1}',
                    [$imagePath, $e->getMessage()]
                ),
                'error',
                ['group_name' => 'image_processing']
            );

            return Processor::REJECT;
        }

        $this->log(
            __('Image processing job completed successfully. Path: {0}', [$imagePath]),
            'info',
            ['group_name' => 'image_processing']
        );

        return Processor::ACK;
    }

    /**
     * Function to resize the image to the sizes set in config/app.php
     * Uses Image Magick for PHP
     *
     * @param string $original The path to the original image to resize.
     * @param int $width The width to resize to
     * @return void returns void
     */
    private function createImage(string $original, int $width): void
    {
        $resizedPath = $original . '_' . $width;

        try {
            if (!file_exists($original)) {
                $this->log(
                    __('Original image not found for resizing. Path: {0}', [$original]),
                    'error',
                    ['group_name' => 'image_processing']
                );

                return;
            }

            if (file_exists($resizedPath)) {
                $this->log(
                    __(
                        'Skipped resizing, image already exists. Path: {0}, Width: {1}px',
                        [$resizedPath, $width]
                    ),
                    'info',
                    ['group_name' => 'image_processing']
                );

                return;
            }

            $imagick = new Imagick($original);
            $imagick->resizeImage($width, 0, Imagick::FILTER_LANCZOS, 1);
            $imagick->writeImage($resizedPath);
            $imagick->clear();

            $this->log(
                __(
                    'Successfully resized and saved image. Original: {0}, Resized: {1}, Width: {2}px',
                    [$original, $resizedPath, $width]
                ),
                'info',
                ['group_name' => 'image_processing']
            );
        } catch (Exception $e) {
            $this->log(
                __(
                    'Error resizing image. Original: {0}, Target Width: {1}px, Error: {2}',
                    [$original, $width, $e->getMessage()]
                ),
                'error',
                ['group_name' => 'image_processing']
            );
        }
    }
}
