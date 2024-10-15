<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\AnthropicApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

/**
 * ImageAnalysisJob Class
 *
 * This class is responsible for analyzing images as a background job.
 * It receives image paths, makes API calls to analyze the images,
 * and saves the alt text and keywords back to the database.
 */
class ImageAnalysisJob implements JobInterface
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
    public static bool $shouldBeUnique = true;

    /**
     * Executes the image analysis job
     *
     * This method processes the job message, makes API calls to analyze the image,
     * and saves the alt text and keywords back to the database.
     *
     * @param \Cake\Queue\Job\Message $message The job message containing image analysis details
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    public function execute(Message $message): ?string
    {
        $args = $message->getArgument('args');
        $this->log(
            __('Received image analysis message: {0}', [json_encode($args)]),
            'debug',
            ['group_name' => 'image_analysis']
        );

        if (!is_array($args) || !isset($args[0]) || !is_array($args[0])) {
            $this->log(
                __('Invalid argument structure for image analysis job. Expected array, got: {0}', [gettype($args)]),
                'error',
                ['group_name' => 'image_analysis']
            );

            return Processor::REJECT;
        }

        $payload = $args[0];
        $folder_path = $payload['folder_path'] ?? null;
        $file = $payload['file'] ?? null;
        $imageId = $payload['id'] ?? null;
        $model = $payload[''] ?? null;

        if (!$folder_path || !$file) {
            $this->log(
                __('Missing required fields in image analysis payload. Path: {0}, File: {1}', [$folder_path, $file]),
                'error',
                ['group_name' => 'image_analysis']
            );

            return Processor::REJECT;
        }

        try {
            $anthropicService = new AnthropicApiService();
            $analysisResult = $anthropicService->analyzeImage($folder_path . $file);

            if ($analysisResult) {
                $imagesTable = TableRegistry::getTableLocator()->get('Images');
                $image = $imagesTable->get($imageId);
                $image->alt_text = $analysisResult['alt_text'];
                $image->keywords = $analysisResult['keywords'];
                $image->name = $analysisResult['name'];
                $imagesTable->save($image);

                $this->log(
                    __('Image analysis completed successfully. Image ID: {0}', [$imageId]),
                    'info',
                    ['group_name' => 'image_analysis']
                );

                return Processor::ACK;
            } else {
                $this->log(
                    __('Image analysis failed. No result returned. Image Path: {0}', [$folder_path . $file]),
                    'error',
                    ['group_name' => 'image_analysis']
                );

                return Processor::REJECT;
            }
        } catch (Exception $e) {
            $this->log(
                __('Error during image analysis. Image Path: {0}, Error: {1}', [$folder_path . $file, $e->getMessage()]),
                'error',
                ['group_name' => 'image_analysis']
            );

            return Processor::REJECT;
        }
    }
}
