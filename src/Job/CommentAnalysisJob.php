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
 * CommentAnalysisJob Class
 *
 * This class is responsible for analyzing comments as a background job.
 * It receives comment data, makes API calls to analyze the comments,
 * logs any inappropriate comments along with user information,
 * and updates the comment status in the database if it's inappropriate.
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
     * logs any inappropriate comments along with user information,
     * and updates the comment status in the database if it's inappropriate.
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
        $commentId = $payload['comment_id'] ?? null;

        if (!$commentText || !$userId || !$commentId) {
            $this->log(
                __(
                    'Missing required fields in comment analysis payload. Comment: {0}, User ID: {1}, Comment ID: {2}',
                    [$commentText, $userId, $commentId]
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
                    $this->handleInappropriateComment($userId, $commentId, $commentText, $analysisResult['reason']);
                }

                $this->log(
                    __('Comment analysis completed successfully. Comment ID: {0}', [$commentId]),
                    'info',
                    ['group_name' => 'comment_analysis']
                );

                return Processor::ACK;
            } else {
                $this->log(
                    __('Comment analysis failed. No result returned. Comment ID: {0}', [$commentId]),
                    'error',
                    ['group_name' => 'comment_analysis']
                );

                return Processor::REJECT;
            }
        } catch (Exception $e) {
            $this->log(
                __('Error during comment analysis. Comment ID: {0}, Error: {1}', [$commentId, $e->getMessage()]),
                'error',
                ['group_name' => 'comment_analysis']
            );

            return Processor::REJECT;
        }
    }

    /**
     * Handles the case when a comment is flagged as inappropriate
     *
     * @param int $userId The ID of the user who posted the comment
     * @param int $commentId The ID of the comment
     * @param string $commentText The text of the comment
     * @param array $reasons The reasons why the comment was flagged as inappropriate
     * @return void
     */
    private function handleInappropriateComment(int $userId, int $commentId, string $commentText, array $reasons): void
    {
        // Log user information if the comment is flagged as inappropriate
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->get($userId);
        $this->log(
            __('Inappropriate comment detected. User ID: {0}, Comment: {1}, Reasons: {2}', [
                $user->id,
                $commentText,
                json_encode($reasons),
            ]),
            'warning',
            ['group_name' => 'comment_analysis']
        );

        // Update the comment in the database
        $commentsTable = TableRegistry::getTableLocator()->get('Comments');
        $comment = $commentsTable->get($commentId);
        $comment->is_inappropriate = true;
        $comment->inappropriate_reason = json_encode($reasons);

        if (!$commentsTable->save($comment)) {
            $this->log(
                __('Failed to update comment status. Comment ID: {0}', [$commentId]),
                'error',
                ['group_name' => 'comment_analysis']
            );
        }
    }
}
