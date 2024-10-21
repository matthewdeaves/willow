<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\AnthropicImageAnalyzer;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

class ImageAnalysisJob implements JobInterface
{
    use LogTrait;

    public static ?int $maxAttempts = 3;
    public static bool $shouldBeUnique = false;
    private AnthropicImageAnalyzer $imageAnalyzer;

    public function execute(Message $message): ?string
    {
        $this->imageAnalyzer = new AnthropicImageAnalyzer();

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

        try {
            $analysisResult = $this->imageAnalyzer->analyze($folderPath . $file);

            if ($analysisResult) {
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
                }
            }

            $this->log(
                __('Image analysis failed. Model: {0} ID: {1}', [$model, $id]),
                'error',
                ['group_name' => 'image_analysis']
            );
        } catch (Exception $e) {
            $this->log(
                __('Error during image analysis: {0}', [$e->getMessage()]),
                'error',
                ['group_name' => 'image_analysis']
            );
        }

        return Processor::REJECT;
    }
}
