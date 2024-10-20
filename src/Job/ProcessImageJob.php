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
    public static bool $shouldBeUnique = false;

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
        // Get the data we need
        $folderPath = $message->getArgument('folder_path');
        $file = $message->getArgument('file');
        $id = $message->getArgument('id');

        $this->log(
            __('Received image processing message: Image ID: {0} Path: {1}', [$id, $folderPath . $file]),
            'info',
            ['group_name' => 'image_processing']
        );

        $imageSizes = SettingsManager::read('ImageSizes');

        $this->log(
            __(
                'Starting image processing job. Path: {0}, Sizes to process: {1}',
                [$folderPath . $file, implode(', ', $imageSizes)]
            ),
            'info',
            ['group_name' => 'image_processing']
        );

        try {
            foreach ($imageSizes as $width) {
                $this->createImage($folderPath, $file, intval($width));
            }
        } catch (Exception $e) {
            $this->log(
                __(
                    'Error during image processing. Path: {0}, Error: {1}',
                    [$folderPath . $file, $e->getMessage()]
                ),
                'error',
                ['group_name' => 'image_processing']
            );

            return Processor::REJECT;
        }

        $this->log(
            __('Image processing job completed successfully. Path: {0}', [$folderPath . $file]),
            'info',
            ['group_name' => 'image_processing']
        );

        return Processor::ACK;
    }

    /**
     * Creates a resized version of an image.
     *
     * This function creates a new directory for the resized image if it doesn't exist,
     * then resizes the original image to the specified width while maintaining aspect ratio.
     * It uses Imagick for image processing and includes extensive error checking and logging.
     *
     * @param string $folder The base folder path where the original image is located.
     * @param string $file The filename of the image to be resized.
     * @param int $width The target width for the resized image.
     * @throws \Exception If unable to create the directory for the resized image.
     * @return void
     * @uses Imagick For image resizing operations.
     * @logs
     * - Error if the original image is not found.
     * - Info if the resized image already exists (skips resizing).
     * - Info upon successful resizing and saving of the image.
     * - Error if any exception occurs during the resizing process.
     * @note All logs are grouped under 'image_processing'.
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
                throw new Exception("Failed to create directory: $sizeFolder");
            }
        }

        try {
            if (!file_exists($folder . $file)) {
                $this->log(
                    __('Original image not found for resizing. Path: {0}', [$folder . $file]),
                    'error',
                    ['group_name' => 'image_processing']
                );

                return;
            }

            if (file_exists($sizeFolder . $file)) {
                $this->log(
                    __(
                        'Skipped resizing, image already exists. Path: {0}',
                        [$sizeFolder . $file]
                    ),
                    'info',
                    ['group_name' => 'image_processing']
                );

                return;
            }

            $imagick = new Imagick($folder . $file);
            $imagick->resizeImage($width, 0, Imagick::FILTER_LANCZOS, 1);
            $imagick->writeImage($sizeFolder . $file);
            $imagick->clear();

            $this->log(
                __(
                    'Successfully resized and saved image. Original: {0}, Resized: {1}, Width: {2}px',
                    [$folder . $file, $sizeFolder . $file, $width]
                ),
                'info',
                ['group_name' => 'image_processing']
            );
        } catch (Exception $e) {
            $this->log(
                __(
                    'Error resizing image. Original: {0}, Target Width: {1}px, Error: {2}',
                    [$folder . $file, $width, $e->getMessage()]
                ),
                'error',
                ['group_name' => 'image_processing']
            );
        }
    }
}
