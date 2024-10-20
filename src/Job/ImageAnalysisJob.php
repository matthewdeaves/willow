<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AnthropicApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
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
     * Executes the image analysis process for a given message.
     *
     * This method retrieves the necessary arguments from the provided message, logs the receipt of the image analysis
     * request, and attempts to analyze the image using the anthropic service. If the analysis is successful, it updates
     * the image record in the database with the analysis results and logs the success. If the analysis fails or an
     * exception is thrown, it logs the error and returns a rejection status.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the arguments for image analysis.
     * @return string|null Returns Processor::ACK if the image analysis and database update are successful,
     *                     or Processor::REJECT if the analysis fails or an error occurs.
     * @throws \Exception If an error occurs during the image analysis process.
     * @uses \Cake\ORM\TableRegistry
     * @uses \App\Service\Api\AnthropicApiService
     */
    public function execute(Message $message): ?string
    {
        $folderPath = $message->getArgument('folder_path');
        $file = $message->getArgument('file');
        $id = $message->getArgument('id');
        $model = $message->getArgument('model');

        $this->log(
            __('Received image analysis message: Image ID: {0} Path: {1}', [$id, $folderPath . $file]),
            'info',
            ['group_name' => 'image_analysis']
        );

        $modelTable = TableRegistry::getTableLocator()->get($model);
        $image = $modelTable->get($id);

        $analysisResult = $this->anthropicService->analyzeImage($folderPath . $file);

        if ($analysisResult) {
            //Set the data we got back
            $image->name = $analysisResult['name'];
            $image->alt_text = $analysisResult['alt_text'];
            $image->keywords = $analysisResult['keywords'];

            if ($modelTable->save($image)) {
                $this->log(
                    __('Image analysis completed successfully. Model: {0} ID: {1}', [$model, $id]),
                    'info',
                    ['group_name' => 'image_analysis']
                );

                return Processor::ACK;
            } else {
                $this->log(
                    __('Image analysis failed. Model: {0} ID: {1}', [$model, $id]),
                    'error',
                    ['group_name' => 'image_analysis']
                );
            }
        } else {
            $this->log(
                __('Image analysis failed. No result returned. Image Path: {0}', [$folderPath . $file]),
                'error',
                ['group_name' => 'image_analysis']
            );
        }

        return Processor::REJECT;
    }
}
