<?php
declare(strict_types=1);

namespace App\Job;

use App\Utility\SettingsManager;
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
    public static int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time. (optional property)
     *
     * @var bool
     */
    public static bool $shouldBeUnique = true;

    /**
     * Executes the image processing task.
     *
     * This method processes an image based on the provided message arguments. It retrieves the folder path,
     * file name, and image ID from the message, logs the start of the processing job, and processes the image
     * for each specified size. If an error occurs during processing, it logs the error and returns a rejection
     * status. Upon successful completion, it logs the success and returns an acknowledgment status.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the arguments for image processing.
     *                                Expected arguments:
     *                                - 'folder_path': The path to the folder containing the image.
     *                                - 'file': The name of the image file to process.
     *                                - 'id': The ID of the image.
     * @return string|null Returns Processor::ACK on successful processing, or Processor::REJECT on error.
     * @throws \Exception If an error occurs during image processing.
     * @uses \App\Utility\SettingsManager
     */
    public function execute(Message $message): ?string
    {
        if (!extension_loaded('imagick')) {
            $this->log(
                'Imagick extension is not loaded',
                'error',
                ['group_name' => 'App\Job\ProcessImageJob']
            );

            return Processor::REJECT;
        }

        // Get the data we need
        $folderPath = $message->getArgument('folder_path');
        $file = $message->getArgument('file');
        $id = $message->getArgument('id');

        $this->log(
            sprintf('Received image processing message: Image ID: %s Path: %s', $id, $folderPath . $file),
            'info',
            ['group_name' => 'App\Job\ProcessImageJob']
        );

        $imageSizes = SettingsManager::read('ImageSizes');

        $this->log(
            sprintf(
                'Starting image processing job. Path: %s, Sizes to process: %s',
                $folderPath . $file,
                implode(', ', $imageSizes)
            ),
            'info',
            ['group_name' => 'App\Job\ProcessImageJob']
        );

        try {
            foreach ($imageSizes as $width) {
                $this->createImage($folderPath, $file, intval($width));
            }
        } catch (Exception $e) {
            $this->log(
                sprintf(
                    'Error during image processing. Path: %s, Error: %s',
                    $folderPath . $file,
                    $e->getMessage()
                ),
                'error',
                ['group_name' => 'App\Job\ProcessImageJob']
            );

            return Processor::REJECT;
        }

        $this->log(
            sprintf('Image processing job completed successfully. Path: %s', $folderPath . $file),
            'info',
            ['group_name' => 'App\Job\ProcessImageJob']
        );

        return Processor::ACK;
    }

    /**
     * Creates a resized or copied version of an image based on target width.
     *
     * This method ensures that a directory for the processed image exists, creating it if necessary.
     * It checks if the original image exists and whether a processed version already exists.
     * If the original image is smaller than or equal to the target width, it copies the original
     * without resizing to preserve quality. Otherwise, it resizes the image to the specified width
     * while maintaining the aspect ratio using the Imagick library.
     *
     * The method includes extensive error checking and logging throughout the process:
     * - Validates and creates necessary directories
     * - Checks for existence of source and destination files
     * - Compares original and target dimensions
     * - Handles image processing with proper resource cleanup
     *
     * @param string $folder The directory where the original image is stored
     * @param string $file The name of the image file to be processed
     * @param int $width The target width for the processed image
     * @throws \Exception If the directory for the processed image cannot be created
     * @return void
     * @uses \Imagick For image processing operations
     * @logs
     * - Error if the directory cannot be created
     * - Error if the original image is not found
     * - Info if the processed image already exists (skips processing)
     * - Info when original image is copied without resizing (smaller than target)
     * - Info upon successful resizing of larger images
     * - Error if any exception occurs during processing
     * @note All logs are grouped under 'App\Job\ProcessImageJob'
     */
    private function createImage(string $folder, string $file, int $width): void
    {
        // Make sure folder for size exists
        // Ensure the folder path ends with a directory separator
        $folder = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Create the full path including the width
        $sizeFolder = $folder . $width . DIRECTORY_SEPARATOR;

        // Check if the directory exists, if not, create it
        if (!is_dir($sizeFolder)) {
            if (!mkdir($sizeFolder, 0755, true)) {
                $this->log(
                    sprintf('Failed to create directory: %s', $sizeFolder),
                    'error',
                    ['group_name' => 'App\Job\ProcessImageJob']
                );
                throw new Exception("Failed to create directory: $sizeFolder");
            }
        }

        try {
            if (!file_exists($folder . $file)) {
                $this->log(
                    sprintf('Original image not found for resizing. Path: %s', $folder . $file),
                    'error',
                    ['group_name' => 'App\Job\ProcessImageJob']
                );

                return;
            }

            if (file_exists($sizeFolder . $file)) {
                $this->log(
                    sprintf(
                        'Skipped resizing, image already exists. Path: %s',
                        $sizeFolder . $file
                    ),
                    'info',
                    ['group_name' => 'App\Job\ProcessImageJob']
                );

                return;
            }

            $imagick = new Imagick($folder . $file);
            $originalWidth = $imagick->getImageWidth();

            // Check if the original image is smaller than the target width
            if ($originalWidth <= $width) {
                // Just copy the original file without resizing
                copy($folder . $file, $sizeFolder . $file);
                $imagick->clear();

                $this->log(
                    sprintf(
                        'Original image is smaller than target width. Copied without resizing. Original: %s, Saved: %s (Original width: %dpx)',
                        $folder . $file,
                        $sizeFolder . $file,
                        $originalWidth
                    ),
                    'info',
                    ['group_name' => 'App\Job\ProcessImageJob']
                );
            } else {
                // Resize the image since it's larger than the target width
                $imagick->resizeImage($width, 0, Imagick::FILTER_LANCZOS, 1);
                $imagick->writeImage($sizeFolder . $file);
                $imagick->clear();

                $this->log(
                    sprintf(
                        'Successfully resized and saved image. Original: %s, Resized: %s, Width: %dpx (Original width: %dpx)',
                        $folder . $file,
                        $sizeFolder . $file,
                        $width,
                        $originalWidth
                    ),
                    'info',
                    ['group_name' => 'App\Job\ProcessImageJob']
                );
            }
        } catch (Exception $e) {
            $this->log(
                sprintf(
                    'Error processing image. Original: %s, Target Width: %dpx, Error: %s',
                    $folder . $file,
                    $width,
                    $e->getMessage()
                ),
                'error',
                ['group_name' => 'App\Job\ProcessImageJob']
            );
        }
    }
}
