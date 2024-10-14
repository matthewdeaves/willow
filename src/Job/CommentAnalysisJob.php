<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\CommentAnalysisApiService;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

/**
 * CommentAnalysisJob Class
 *
 * This class is responsible for analyzing comments as a background job.
 * It receives comment data, makes API calls to analyze the comments,
 * and logs any inappropriate comments along with user information.
 */
class CommentAnalysisJob implements JobInterface
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
     * Executes the comment analysis job
     *
     * This method processes the job message, makes API calls to analyze the comment,
     * and logs any inappropriate comments along with user information.
     *
     * @param \Cake\Queue\Job\Message $message The job message containing comment analysis details
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    public function execute(Message $message): ?string
    {
        $args = $message->getArgument('args');
        $this->log(
            __('Received comment analysis message: {0}', [json_encode($args)]),
            'debug',
            ['group_name' => 'comment_analysis']
        );

        if (!is_array($args) || !isset($args[0]) || !is_array($args[0])) {
            $this->log(
                __('Invalid argument structure for comment analysis job. Expected array, got: {0}', [gettype($args)]),
                'error',
                ['group_name' => 'comment_analysis']
            );

            return Processor::REJECT;
        }

        $payload = $args[0];
        $commentText = $payload['comment'] ?? null;
        $userId = $payload['user_id'] ?? null;

        if (!$commentText || !$userId) {
            $this->log(
                __(
                    'Missing required fields in comment analysis payload. Comment: {0}, User ID: {1}',
                    [$commentText, $userId]
                ),
                'error',
                ['group_name' => 'comment_analysis']
            );

            return Processor::REJECT;
        }

        try {
            $anthropicService = new AnthropicApiService();
            $analysisResult = $anthropicService->analyzeComment($commentText);

            if ($analysisResult) {
                $isInappropriate = $analysisResult['is_inappropriate'] ?? false;

                if ($isInappropriate) {
                    // Log user information if the comment is flagged as inappropriate
                    $usersTable = TableRegistry::getTableLocator()->get('Users');
                    $user = $usersTable->get($userId);
                    $this->log(
                        __('Inappropriate comment detected. User ID: {0}, Comment: {1}, Reasons: {2}', [
                            $user->id,
                            $commentText,
                            json_encode($analysisResult['reason']),
                        ]),
                        'warning',
                        ['group_name' => 'comment_analysis']
                    );
                }

                $this->log(
                    __('Comment analysis completed successfully. Comment: {0}', [$commentText]),
                    'info',
                    ['group_name' => 'comment_analysis']
                );

                return Processor::ACK;
            } else {
                $this->log(
                    __('Comment analysis failed. No result returned. Comment: {0}', [$commentText]),
                    'error',
                    ['group_name' => 'comment_analysis']
                );

                return Processor::REJECT;
            }
        } catch (Exception $e) {
            $this->log(
                __('Error during comment analysis. Comment: {0}, Error: {1}', [$commentText, $e->getMessage()]),
                'error',
                ['group_name' => 'comment_analysis']
            );

            return Processor::REJECT;
        }
    }
}
