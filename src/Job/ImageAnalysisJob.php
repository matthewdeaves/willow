<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

/**
 * ImageAnalysisJob
 *
 * This job is responsible for analyzing images using the Anthropic API.
 * It processes messages from the queue to analyze images and update their metadata.
 */
class ImageAnalysisJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts for the job.
     *
     * @var int|null
     */
    public static ?int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time.
     *
     * @var bool
     */
    public static bool $shouldBeUnique = false;

    /**
     * Instance of the Anthropic API service.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $anthropicService;

    /**
     * Executes the job to analyze an image and update its metadata.
     *
     * This method processes the message, retrieves the image, analyzes it using the Anthropic API,
     * and updates the image record with the analysis results.
     *
     * @param \Cake\Queue\Job\Message $message The message containing image data.
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure.
     */
    public function execute(Message $message): ?string
    {
        $this->anthropicService = new AnthropicApiService();

        $folderPath = $message->getArgument('folder_path');
        $file = $message->getArgument('file');
        $id = $message->getArgument('id');
        $model = $message->getArgument('model');

        $this->log(
            sprintf('Received image analysis message: Image ID: %s Path: %s', $id, $folderPath . $file),
            'info',
            ['group_name' => 'App\Job\ImageAnalysisJob']
        );

        $modelTable = TableRegistry::getTableLocator()->get($model);
        $image = $modelTable->get($id);

        try {
            $analysisResult = $this->anthropicService->analyzeImage($folderPath . $file);

            if ($analysisResult) {
                $image->name = $analysisResult['name'];
                $image->alt_text = $analysisResult['alt_text'];
                $image->keywords = $analysisResult['keywords'];

                if ($modelTable->save($image)) {
                    $this->log(
                        sprintf('Image analysis completed successfully. Model: %s ID: %s', $model, $id),
                        'info',
                        ['group_name' => 'App\Job\ImageAnalysisJob']
                    );

                    return Processor::ACK;
                }
            }

            $this->log(
                sprintf('Image analysis failed. Model: %s ID: %s', $model, $id),
                'error',
                ['group_name' => 'App\Job\ImageAnalysisJob']
            );
        } catch (Exception $e) {
            $this->log(
                sprintf('Error during image analysis: %s', $e->getMessage()),
                'error',
                ['group_name' => 'App\Job\ImageAnalysisJob']
            );
        }

        return Processor::REJECT;
    }
}
