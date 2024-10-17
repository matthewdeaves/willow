<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AnthropicApiService;
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
     * Whether there should be only one instance of a job on the queue at a time. (optional property)
     *
     * @var bool
     */
    public static bool $shouldBeUnique = false;

    /**
     * @var \App\Service\Api\AnthropicApiService The Anthropic API service for image analysis.
     */
    private AnthropicApiService $anthropicService;

    /**
     * ImageAnalysisJob constructor.
     *
     * Initializes the AnthropicApiService instance.
     */
    public function __construct()
    {
        $this->anthropicService = new AnthropicApiService();
    }

    /**
     * Execute the image analysis job.
     *
     * This method processes the image analysis job by receiving the message,
     * validating the payload, calling the API for analysis, and saving the
     * results to the database.
     *
     * @param \Cake\Queue\Job\Message $message The message containing job arguments.
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure.
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
        $modelId = $payload['id'] ?? null;
        $model = $payload['model'] ?? null;

        if (!$folder_path || !$file || !$model || !$modelId) {
            $this->log(
                __('Missing required fields in image analysis payload. Path: {0}, File: {1}', [$folder_path, $file]),
                'error',
                ['group_name' => 'image_analysis']
            );

            return Processor::REJECT;
        }

        try {
            $analysisResult = $this->anthropicService->analyzeImage($folder_path . $file);

            if ($analysisResult) {
                $modelTable = TableRegistry::getTableLocator()->get($model);
                $image = $modelTable->get($modelId);
                $image->alt_text = $analysisResult['alt_text'];
                $image->keywords = $analysisResult['keywords'];
                $image->name = $analysisResult['name'];
                $modelTable->save($image);

                $this->log(
                    __('Image analysis completed successfully. Model: {0} ID: {1}', [$model, $modelId]),
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
                __(
                    'Error during image analysis. Image Path: {0}, Error: {1}',
                    [$folder_path . $file, $e->getMessage()]
                ),
                'error',
                ['group_name' => 'image_analysis']
            );

            return Processor::REJECT;
        }
    }
}
